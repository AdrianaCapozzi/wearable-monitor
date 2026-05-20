# Diagrama do Banco de Dados - Wearable API

Abaixo está o Diagrama de Entidade-Relacionamento (ER) do banco de dados utilizado pela aplicação. O diagrama mostra as tabelas, suas colunas e os relacionamentos (chaves estrangeiras) entre elas.

Você pode visualizar este diagrama facilmente no próprio VS Code instalando a extensão "Markdown Preview Mermaid Support" ou visualizando diretamente no GitHub.

```mermaid
erDiagram
    cliente {
        INT id PK
        VARCHAR nome
        DATE data_nascimento
        VARCHAR sexo
        VARCHAR cidade
        VARCHAR estado
        VARCHAR telefone
        VARCHAR whatsapp
        VARCHAR email
        VARCHAR status
    }

    hardware {
        INT id PK
        VARCHAR mac UK "UNIQUE"
        VARCHAR status
        VARCHAR token
    }

    cliente_hardware {
        INT id_cliente PK, FK
        INT id_hardware PK, FK
        DATE data_inicial
        DATE data_final
        VARCHAR status
    }

    monitoring {
        INT id PK
        INT id_hardware FK
        DATETIME data_hora
        DECIMAL latitude
        DECIMAL longitude
        VARCHAR image_url
        TEXT observacao
        VARCHAR status
        ENUM status_cam
        ENUM status_gps
    }

    %% Relacionamentos
    cliente ||--o{ cliente_hardware : "possui"
    hardware ||--o{ cliente_hardware : "associado a"
    hardware ||--o{ monitoring : "registra dados em"
```
