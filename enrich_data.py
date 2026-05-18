"""Wearable Monitor - Enriquecimento de Dados
Cria tabela enriquecida com variáveis derivadas para análise
"""

from pyspark.sql import SparkSession


def enrich_monitoring_data(spark, source_table="wearable.monitoramento", target_table="wearable.monitoramento_enriquecido"):
    """Cria tabela enriquecida com variáveis calculadas"""
    
    query = f"""
    CREATE OR REPLACE TABLE {target_table} AS
    SELECT
        -- IDs únicos
        CAST(row_number() OVER (ORDER BY data_hora) AS INT) AS id_registro,
        DENSE_RANK() OVER (ORDER BY cliente_fone) AS id_usuario,
        
        -- Campos originais
        data_hora,
        DATE(data_hora) AS data_only,
        HOUR(data_hora) AS hora,
        
        cliente_nome,
        cliente_fone,
        mac_hardware,
        mac_status,
        status_cam,
        
        -- Período do dia
        CASE
            WHEN HOUR(data_hora) >= 6 AND HOUR(data_hora) < 12 THEN 'Manhã'
            WHEN HOUR(data_hora) >= 12 AND HOUR(data_hora) < 18 THEN 'Tarde'
            WHEN HOUR(data_hora) >= 18 AND HOUR(data_hora) < 24 THEN 'Noite'
            ELSE 'Madrugada'
        END AS periodo_dia,
        
        -- Dia da semana
        CASE DAYOFWEEK(data_hora)
            WHEN 1 THEN 'Domingo'
            WHEN 2 THEN 'Segunda-feira'
            WHEN 3 THEN 'Terça-feira'
            WHEN 4 THEN 'Quarta-feira'
            WHEN 5 THEN 'Quinta-feira'
            WHEN 6 THEN 'Sexta-feira'
            WHEN 7 THEN 'Sábado'
        END AS dia_semana,
        
        -- Final de semana?
        CASE WHEN DAYOFWEEK(data_hora) IN (1,7) THEN 'Sim' ELSE 'Não' END AS fim_de_semana,
        
        -- Tempo desde o registro
        ROUND((unix_timestamp(current_timestamp()) - unix_timestamp(data_hora)) / 60, 0) AS minutos_atras,
        
        -- GPS
        latitude,
        longitude,
        CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 'Sim' ELSE 'Não' END AS gps_valido,
        
        -- Região (exemplo: Cotia)
        CASE
            WHEN latitude BETWEEN -23.525 AND -23.510
             AND longitude BETWEEN -46.205 AND -46.185 THEN 'Cotia - Centro'
            WHEN latitude IS NULL OR longitude IS NULL THEN 'Sem sinal GPS'
            ELSE 'Região não mapeada'
        END AS regiao,
        
        -- Link Google Maps
        CASE
            WHEN latitude IS NOT NULL THEN CONCAT('https://www.google.com/maps?q=', latitude, ',', longitude)
            ELSE NULL
        END AS link_maps,
        
        -- Status descritivo
        CASE WHEN status_cam = 'ON' THEN 'Câmera ativa' ELSE 'Câmera desligada' END AS status_cam_descricao,
        CASE WHEN mac_status = 'ativo' THEN 'Sim' ELSE 'Não' END AS dispositivo_ativo,
        
        imagem_url,
        ingested_at
        
    FROM {source_table}
    """
    
    spark.sql(query)
    print(f"✅ Tabela {target_table} criada com sucesso!")


if __name__ == "__main__":
    spark = SparkSession.builder.appName("WearableMonitorEnrich").getOrCreate()
    print("Iniciando enriquecimento dos dados...")
    enrich_monitoring_data(spark)
    print("✅ Enriquecimento concluído!")
