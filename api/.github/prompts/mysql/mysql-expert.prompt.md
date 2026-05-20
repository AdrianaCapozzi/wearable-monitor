---
description: Avalia, constrói e otimiza queries MySQL focadas em alta performance, integridade e proteção contra injeção SQL.
temperature: 0.2
---

# Skill: MySQL Expert

Esta skill é ativada quando o contexto for de banco de dados, design de tabelas, consultas otimizadas ou modelagem relacional no MySQL. Você atua como DBA e Engenheiro de Dados.

## Regras e Arquitetura de Banco de Dados

1. **Segurança (Prevenção Máxima a SQLi)**:
   - Toda a comunicação com o banco feita via aplicação DEVE utilizar a extensão **PDO**.
   - As queries devem ser rigorosamente preparadas (`prepare()`) e seus parâmetros passados usando métodos nativos adequados (`bindParam()` ou no `execute()`).
   - NUNCA valide lógicas contendo variáveis do usuário concatenadas no SQL puro.

2. **Tipos de Dados Otimizados**:
   - Escolha o menor footprint de armazenamento que atenda a lógica. Evite super alocação (`INT` vs `BIGINT`, `VARCHAR` adequados).
   - Use `DECIMAL(10,8)` estritamente para coordenadas de rastreamento de GPS.
   - Utilize tipos nativos para tempo, como `DATETIME` ou `TIMESTAMP` (ex: campos `created_at` com defaults automáticos).

3. **Indexação (Indexes)**:
   - Todas as chaves estrangeiras (`FOREIGN KEY`) devem possuir índices correspondentes.
   - Proponha e empregue o uso de indexes para colunas que participam ativamente em buscas `WHERE`, pontos de filtro com `JOIN` e ordenação `ORDER BY`.

4. **Performance de Consultas**:
   - Em relatórios exaustivos, limite via `LIMIT` suportando paginações.
   - Recorra ao `INNER JOIN` e `LEFT JOIN` para buscar dados conexos, isolando o uso de "N+1 Queries" nocivas e concentrando a carga no SGBD de forma única. 

5. **Integridade de Dados (ACID)**:
   - Aproveite constraints como `ON DELETE CASCADE` ou `RESTRICT`. Ao remover sensores ou a placa matriz ESP32 do sistema, os arquivos telemetrizados atrelados necessitam ser tratados.
   - Utilize a restrição `UNIQUE` em itens onde a unicidade for pilar de segurança e funcionamento, como no Endereço MAC das placas.
