"""Wearable Monitor - Visualização de Dados
Funções para visualizar registros com fotos em formato de galeria
"""

from IPython.display import HTML, display
from pyspark.sql import SparkSession


def exibir_galeria_fotos(spark, table="wearable.monitoramento_enriquecido", limit=12):
    """Exibe galeria HTML com as últimas fotos capturadas"""
    
    df_fotos = spark.sql(f"""
        SELECT 
            id_registro, id_usuario,
            cliente_nome, cliente_fone,
            data_hora, hora, periodo_dia, dia_semana, fim_de_semana,
            regiao, gps_valido, link_maps,
            status_cam_descricao, dispositivo_ativo,
            minutos_atras, imagem_url
        FROM {table}
        WHERE imagem_url IS NOT NULL
        ORDER BY data_hora DESC
        LIMIT {limit}
    """).collect()
    
    html = """
    <style>
      .galeria {{ display:flex; flex-wrap:wrap; gap:16px; font-family:Arial,sans-serif; padding:10px; }}
      .card {{
        border:1px solid #ddd; border-radius:12px; padding:12px; width:240px;
        box-shadow:3px 3px 10px rgba(0,0,0,0.12); background:#fff;
      }}
      .card img {{ width:100%; border-radius:8px; margin-bottom:10px; }}
      .card .titulo {{ font-weight:bold; font-size:13px; color:#222; margin-bottom:8px; 
                      border-bottom:1px solid #eee; padding-bottom:6px; }}
      .card .linha {{ font-size:12px; color:#444; margin:3px 0; }}
      .card .badge {{
        display:inline-block; font-size:11px; padding:2px 7px;
        border-radius:10px; margin-top:4px; font-weight:bold;
      }}
      .badge-verde  {{ background:#d4edda; color:#155724; }}
      .badge-amarelo{{ background:#fff3cd; color:#856404; }}
      .badge-vermelho{{ background:#f8d7da; color:#721c24; }}
      .card a {{ font-size:12px; color:#1a73e8; text-decoration:none; font-weight:bold; }}
    </style>
    <h2 style='font-family:Arial; color:#333'>📸 Registros de Acionamento — Wearable de Segurança</h2>
    <div class='galeria'>
    """
    
    for r in df_fotos:
        maps_btn = f"<a href='{{r.link_maps}}' target='_blank'>📍 Abrir no Maps</a>" if r.link_maps else "<span class='linha'>📍 Sem sinal GPS</span>"
        
        badge_gps = "badge-verde" if r.gps_valido == "Sim" else "badge-vermelho"
        badge_cam = "badge-verde" if r.status_cam_descricao == "Câmera ativa" else "badge-vermelho"
        badge_fds = "badge-amarelo" if r.fim_de_semana == "Sim" else "badge-verde"
        mins = int(r.minutos_atras) if r.minutos_atras else "?"
        
        html += f"""
        <div class='card'>
            <img src='{{r.imagem_url}}'/>
            <div class='titulo'>👤 {{r.cliente_nome}}</div>
            <div class='linha'>🪪 ID usuária: <b>{{r.id_usuario}}</b> &nbsp;|&nbsp; Registro: <b>#{{r.id_registro}}</b></div>
            <div class='linha'>📞 {{r.cliente_fone}}</div>
            <div class='linha'>🕐 {{r.data_hora}}</div>
            <div class='linha'>🌅 {{r.periodo_dia}} — {{r.dia_semana}}</div>
            <div class='linha'>🏖️ Final de semana: <span class='badge {{badge_fds}}'>{{r.fim_de_semana}}</span></div>
            <div class='linha'>🗺️ {{r.regiao}}</div>
            <div class='linha'>📡 GPS: <span class='badge {{badge_gps}}'>{{r.gps_valido}}</span></div>
            <div class='linha'>📷 <span class='badge {{badge_cam}}'>{{r.status_cam_descricao}}</span></div>
            <div class='linha'>⏱️ {{mins}} minutos atrás</div>
            <div class='linha' style='margin-top:6px'>{{maps_btn}}</div>
        </div>
        """
    
    html += "</div>"
    display(HTML(html))


if __name__ == "__main__":
    spark = SparkSession.builder.appName("WearableMonitorVisualize").getOrCreate()
    exibir_galeria_fotos(spark)
