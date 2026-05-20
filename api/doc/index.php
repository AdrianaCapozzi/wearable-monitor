<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API - Wearable</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f6f9; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #0056b3; }
        h1 { border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; font-size: 0.95em; color: #d63384; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 4px solid #0056b3; }
        pre code { background: none; color: inherit; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; }
        th, td { padding: 12px; border: 1px solid #e0e0e0; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.75em; font-weight: bold; text-transform: uppercase; color: white; }
        .badge.required { background: #dc3545; }
        .badge.optional { background: #6c757d; }
        .response-list li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>API - Documentação e Integração (Wearable)</h1>
        <p>Esta documentação descreve o padrão exigido para que as placas (Ex: ESP32) se registrem e enviem os dados de monitoramento (telemetria e imagens) para o servidor central da aplicação.</p>

        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <h2>Acessos Rápidos da Aplicação</h2>
        <ul>
            <li><strong>Dashboard de Monitoramento:</strong> <a href="https://wearable.surflog.com.br/" target="_blank">Acessar Painel Principal</a> (Exibição dos últimos 50 registros, clientes e mapas em tempo real).</li>
            <li><strong>Diagrama Entidade-Relacionamento:</strong> <a href="diagrama_bd.md" target="_blank">diagrama_bd.md</a> (Exibição da arquitetura do banco de dados na notação Mermaid).</li>
        </ul>

        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <h2>1. Cadastro e Geração de Token (Setup)</h2>
        <p>Antes de uma placa enviar dados de monitoramento, ela deve ser registrada para gerar seu token de segurança exclusivo.</p>
        <p><strong>URL:</strong> <code>https://wearable.surflog.com.br/api/register_hardware.php</code></p>
        <p><strong>Método HTTP:</strong> <code>POST</code></p>

        <h3>Parâmetros de Requisição</h3>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Tipo</th>
                    <th>Obrigatório</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>mac</code></td>
                    <td>String</td>
                    <td><span class="badge required">Sim</span></td>
                    <td>Endereço MAC de fábrica da placa ESP32 (Ex: <code>AA:BB:CC:DD:EE:FF</code>).</td>
                </tr>
            </tbody>
        </table>

        <h3>Exemplo Prático (cURL)</h3>
        <pre><code>curl -X POST https://wearable.surflog.com.br/api/register_hardware.php \
  -F "mac=AA:BB:CC:DD:EE:FF"</code></pre>
  
        <h3>Exemplo de Retorno (Sucesso)</h3>
        <pre><code>{
  "message": "Hardware registrado com sucesso.",
  "mac": "AA:BB:CC:DD:EE:FF",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
}</code></pre>
        <p><em>Guarde este <code>token</code>, pois ele será exigido em todos os envios de monitoramento.</em></p>

        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <h2>2. Endpoint de Monitoramento</h2>
        <p><strong>URL:</strong> <code>https://wearable.surflog.com.br/api/monitoring.php</code></p>
        <p><strong>Método HTTP:</strong> <code>POST</code></p>
        <p><strong>Content-Type Recomendado:</strong> <code>multipart/form-data</code></p>

        <h3>Parâmetros de Requisição</h3>
        <p>Abaixo estão os campos que devem ser estruturados no corpo do POST:</p>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Tipo</th>
                    <th>Obrigatório</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>mac</code></td>
                    <td>String</td>
                    <td><span class="badge required">Sim</span></td>
                    <td>Endereço MAC de fábrica da placa (Ex: <code>AA:BB:CC:DD:EE:FF</code>). Usado pela API como identificador do dispositivo.</td>
                </tr>
                <tr>
                    <td><code>token</code></td>
                    <td>String</td>
                    <td><span class="badge required">Sim</span></td>
                    <td>Chave de segurança única da placa para atestar a veracidade da origem e autorizar o envio.</td>
                </tr>
                <tr>
                    <td><code>data_hora</code></td>
                    <td>String</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Data e hora exata do registro no formato MySQL (Ex: <code>2026-05-02 14:30:00</code>). Se não for enviado, o servidor aplicará o horário atual de recebimento.</td>
                </tr>
                <tr>
                    <td><code>latitude</code></td>
                    <td>Decimal</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Coordenada geográfica de latitude (Ex: <code>-23.550520</code>).</td>
                </tr>
                <tr>
                    <td><code>longitude</code></td>
                    <td>Decimal</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Coordenada geográfica de longitude (Ex: <code>-46.633308</code>).</td>
                </tr>
                <tr>
                    <td><code>status_cam</code></td>
                    <td>Enum</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Status da câmera da placa, podendo ser <code>on</code> ou <code>off</code>. O padrão é <code>off</code>.</td>
                </tr>
                <tr>
                    <td><code>status_gps</code></td>
                    <td>Enum</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Status do GPS da placa, podendo ser <code>on</code> ou <code>off</code>. O padrão é <code>off</code>.</td>
                </tr>
                <tr>
                    <td><code>imagem</code></td>
                    <td>File</td>
                    <td><span class="badge optional">Não</span></td>
                    <td>Arquivo físico da fotografia capturada pela placa, enviado como anexo do formulário.</td>
                </tr>
            </tbody>
        </table>

        <h3>Exemplo Prático (cURL)</h3>
        <p>Modelo de requisição simulando o comportamento da placa ao enviar todas as variáveis e foto:</p>
        <pre><code>curl -X POST https://wearable.surflog.com.br/api/monitoring.php \
  -F "mac=AA:BB:CC:DD:EE:FF" \
  -F "token=de0a5b226c217910ed62b10659ada28a" \
  -F "latitude=-23.550520" \
  -F "longitude=-46.633308" \
  -F "status_cam=on" \
  -F "status_gps=on" \
  -F "imagem=@/caminho/completo/para/captura.jpg"</code></pre>

        <h3>Tabela de Retornos (Status Code)</h3>
        <ul class="response-list">
            <li><strong>201 Created:</strong> <code>{ "message": "Dados registrados com sucesso." }</code></li>
            <li><strong>400 Bad Request:</strong> MAC e/ou Token não foram informados na requisição.</li>
            <li><strong>401 Unauthorized:</strong> O token de segurança não é válido para a placa informada.</li>
            <li><strong>404 Not Found:</strong> O MAC fornecido não consta na base de dados de clientes.</li>
            <li><strong>500 Internal Error:</strong> Ocorreu uma falha no MySQL do servidor remoto que impediu a gravação.</li>
        </ul>
    </div>
</body>
</html>