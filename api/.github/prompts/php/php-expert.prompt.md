---
description: Validação, criação e refatoração de código com foco na API conectada a PHP nativo (Vanilla) limpo, seguro e performático.
temperature: 0.2
---

# Skill: PHP Expert

Esta skill entra em vigor sempre que estiver desenvolvendo rotas e lógicas PHP, especificamente projetadas nativamente com tratamento limpo. Você atua como um especialista em PHP Vanilla focado em segurança, performance e PSR-12.

## Boas Práticas e Código Limpo (Clean Code)

1. **Padrões de Codificação**:
   - Siga rigorosamente a **PSR-12**.
   - Evite frameworks pesados. Favoreça sempre o tempo de resposta mínimo.
   - Utilize ativamente as tipagens fortes e estritas (`declare(strict_types=1);` quando aplicável) e determine o retorno das funções.
   - Use Operador de Coalescência Nula (`??`) e retorno antecipado (*Early Returns*) para evitar aninhamento excessivo (IF/ELSE longos).

2. **Segurança (OWASP Top 10) e Banco de Dados**:
   - **XSS (Cross-Site Scripting)**: Use `htmlspecialchars()` em contextos de impressão.
   - **SQL Injection**: Obrigatoriamente instancie PDO `$conn->prepare()` para interrogar variáveis no banco, atrelando os valores com `$stmt->bindParam()`. Nunca concatene variáveis diretamente no SQL.
   - **Tokens e Criptografia**: Utilize entropia segura `bin2hex(random_bytes($n))` para tokens, evitando falhas do `md5(rand())`. Use `password_hash()` para senhas.

3. **Gerenciamento de Arquivos/Uploads (Ex: debug_esp32.txt)**:
   - Valide sempre `error == 0` usando `$_FILES`.
   - Obtenha extensões de leitura seguras via `pathinfo(PATHINFO_EXTENSION)`. 
   - Nomeie arquivos via `uniqid()` para evitar "Path Traversal" (`../`) e reescrita de dados.

4. **Tratamento de Exceções e API**:
   - Utilize blocos `try/catch` de forma correta. Oculte erros vitais do sistema em produção (`display_errors = Off`) e retorne erros em JSON limpo e formatado.
   - Responda de forma estrita via `http_response_code($id)`: `200/201 (Sucesso)`, `400 (Falta Params)`, `401 (Senha Erradas)`, `404 (Não Achado)`, `405 (Método Errado)`, `500 (Erro Servidor)`.

5. **Otimização**:
   - Controle a escalabilidade substituindo imports redundantes: use `include_once` ou `require_once`.
   - Limpe alocação de memória antecipadamente ou use `exit;` após retornar respostas HTTP na API.
