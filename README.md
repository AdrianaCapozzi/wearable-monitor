# Wearable Monitor

Sistema de monitoramento de wearables de segurança que faz ingestão, enriquecimento e visualização de dados de acionamentos.

## 📊 Visão Geral

Este projeto realiza:
1. **Ingestão**: Scraping de dados do site wearable.surflog.com.br
2. **Enriquecimento**: Adição de variáveis derivadas (período do dia, região, etc.)
3. **Visualização**: Galeria HTML com fotos e metadados dos acionamentos

## 💾 Estrutura do Projeto

```
wearable-monitor/
├── ingest_data.py          # Ingestão de dados do site
├── enrich_data.py          # Enriquecimento com variáveis derivadas
├── visualize_data.py       # Visualização em galeria HTML
├── requirements.txt        # Dependências Python
└── README.md               # Este arquivo
```

## ⚙️ Tecnologias

* **Apache Spark / PySpark**: Processamento de dados
* **Delta Lake**: Armazenamento em formato Delta
* **BeautifulSoup**: Web scraping
* **Databricks**: Plataforma de execução

## 🚀 Como Usar

### 1. Instalar dependências

```bash
pip install -r requirements.txt
```

### 2. Executar ingestão

```python
from ingest_data import scrape_wearable_data, save_to_delta

registros = scrape_wearable_data()
save_to_delta(spark, registros)
```

### 3. Enriquecer dados

```python
from enrich_data import enrich_monitoring_data

enrich_monitoring_data(spark)
```

### 4. Visualizar galeria

```python
from visualize_data import exibir_galeria_fotos

exibir_galeria_fotos(spark, limit=12)
```

## 📦 Tabelas Criadas

### `wearable.monitoramento`
Tabela bruta com dados extraídos do site:
* `data_hora`: Timestamp do acionamento
* `cliente_nome`: Nome da usuária
* `cliente_fone`: Telefone
* `mac_hardware`: Endereço MAC do dispositivo
* `mac_status`: Status do dispositivo (ativo/inativo)
* `status_cam`: Status da câmera (ON/OFF)
* `latitude`, `longitude`: Coordenadas GPS
* `imagem_url`: URL da foto capturada
* `ingested_at`: Timestamp da ingestão

### `wearable.monitoramento_enriquecido`
Tabela enriquecida com variáveis derivadas:
* `id_registro`: ID único do registro
* `id_usuario`: ID único da usuária
* `periodo_dia`: Manhã/Tarde/Noite/Madrugada
* `dia_semana`: Nome do dia da semana
* `fim_de_semana`: Sim/Não
* `regiao`: Região geográfica
* `gps_valido`: Indica se há coordenadas GPS
* `link_maps`: Link direto para Google Maps
* `minutos_atras`: Tempo decorrido desde o acionamento

## 🔒 Segurança

Este projeto manipula dados sensíveis. Certifique-se de:
* Não compartilhar credenciais
* Respeitar a LGPD no tratamento de dados pessoais
* Restringir acesso às tabelas apenas a usuários autorizados

## 👥 Autor

Desenvolvido em Databricks para monitoramento de wearables de segurança.

## 📝 Licença

Uso interno. Todos os direitos reservados.
