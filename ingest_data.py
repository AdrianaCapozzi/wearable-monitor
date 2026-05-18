"""Wearable Monitor - Ingestão de Dados
Faz scraping do site wearable.surflog.com.br e salva os dados no Delta Lake
"""

import requests
import re
from bs4 import BeautifulSoup
from datetime import datetime, timezone
from pyspark.sql import SparkSession
from pyspark.sql.types import StructType, StructField, StringType, TimestampType, DoubleType


def scrape_wearable_data(base_url="https://wearable.surflog.com.br/", timeout=30):
    """Faz scraping do site wearable e retorna lista de registros"""
    resp = requests.get(base_url, timeout=timeout)
    soup = BeautifulSoup(resp.text, "lxml")
    
    registros = []
    for row in soup.find("table").find_all("tr")[1:]:
        cols = row.find_all("td")
        if len(cols) < 6:
            continue
        
        try:
            data_hora = datetime.strptime(cols[0].get_text(strip=True), "%d/%m/%Y %H:%M:%S")
        except:
            data_hora = None
        
        cliente_raw = cols[1].get_text(" ", strip=True)
        match_fone = re.search(r"\(\d+\)\d[\d\-]+", cliente_raw)
        cliente_fone = match_fone.group(0) if match_fone else None
        cliente_nome = cliente_raw.replace(cliente_fone, "").strip() if cliente_fone else cliente_raw
        
        mac_raw = cols[2].get_text(" ", strip=True)
        mac_match = re.search(r"([\dA-Fa-f]{2}(?:-[\dA-Fa-f]{2}){5})", mac_raw)
        mac = mac_match.group(1).upper() if mac_match else None
        mac_status = "ativo" if "ativo" in mac_raw.lower() else "inativo"
        
        status_cam = cols[3].get_text(strip=True).upper()
        
        # Pega o link do Google Maps que contém lat e lng exatas
        link_maps = cols[5].find("a") if len(cols) > 5 else None
        if link_maps is None:
            link_maps = cols[4].find("a")
        href = link_maps["href"] if link_maps else ""
        nums = re.findall(r"-?\d+\.\d+", href)
        lat = float(nums[0]) if len(nums) >= 1 else None
        lng = float(nums[1]) if len(nums) >= 2 else None
        
        # Considera 0,0 como sem sinal GPS
        if lat == 0.0 and lng == 0.0:
            lat, lng = None, None
        
        # Imagem — última coluna
        img_tag = cols[-1].find("img")
        imagem_url = None
        if img_tag:
            src = img_tag.get("src", "")
            if src.startswith("http"):
                imagem_url = src
            elif src:
                imagem_url = base_url.rstrip("/") + "/" + src.lstrip("/")
        
        registros.append({
            "data_hora": data_hora,
            "cliente_nome": cliente_nome.strip(),
            "cliente_fone": cliente_fone,
            "mac_hardware": mac,
            "mac_status": mac_status,
            "status_cam": status_cam,
            "latitude": lat,
            "longitude": lng,
            "imagem_url": imagem_url,
            "ingested_at": datetime.now(timezone.utc),
        })
    
    return registros


def save_to_delta(spark, registros, database="wearable", table="monitoramento"):
    """Salva os registros em uma tabela Delta Lake"""
    SCHEMA = StructType([
        StructField("data_hora", TimestampType(), True),
        StructField("cliente_nome", StringType(), True),
        StructField("cliente_fone", StringType(), True),
        StructField("mac_hardware", StringType(), True),
        StructField("mac_status", StringType(), True),
        StructField("status_cam", StringType(), True),
        StructField("latitude", DoubleType(), True),
        StructField("longitude", DoubleType(), True),
        StructField("imagem_url", StringType(), True),
        StructField("ingested_at", TimestampType(), True),
    ])
    
    df = spark.createDataFrame(registros, schema=SCHEMA)
    spark.sql(f"CREATE DATABASE IF NOT EXISTS {database}")
    df.write.format("delta").mode("overwrite") \
        .option("overwriteSchema", "true") \
        .saveAsTable(f"{database}.{table}")
    
    print(f"✅ {len(registros)} registros salvos em {database}.{table}")


if __name__ == "__main__":
    spark = SparkSession.builder.appName("WearableMonitorIngest").getOrCreate()
    print("Iniciando ingestão de dados do wearable...")
    registros = scrape_wearable_data()
    save_to_delta(spark, registros)
    print("✅ Ingestão concluída!")
