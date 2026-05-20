<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testador de API - Monitoramento</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="datetime-local"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="file"] { padding: 10px 0; }
        button { width: 100%; padding: 10px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #218838; }
        .response { margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>

<div class="container">
    <h2>Simulador - Envio de Dados</h2>
    <form id="apiForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="mac">MAC Address (obrigatório):</label>
            <input type="text" id="mac" name="mac" placeholder="Ex: 00:1A:2B:3C:4D:5E" required>
        </div>

        <div class="form-group">
            <label for="token">Token de Segurança (obrigatório):</label>
            <input type="text" id="token" name="token" placeholder="Token do dispositivo" required>
        </div>

        <div class="form-group">
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude" placeholder="-23.550520">
        </div>

        <div class="form-group">
            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude" placeholder="-46.633308">
        </div>

        <div class="form-group">
            <label for="status_cam">Status da Câmera (on/off):</label>
            <input type="text" id="status_cam" name="status_cam" placeholder="on ou off">
        </div>

        <div class="form-group">
            <label for="status_gps">Status do GPS (on/off):</label>
            <input type="text" id="status_gps" name="status_gps" placeholder="on ou off">
        </div>

        <div class="form-group">
            <label for="data_hora">Data e Hora (opcional):</label>
            <input type="datetime-local" id="data_hora" name="data_hora">
        </div>

        <div class="form-group">
            <label for="imagem">Imagem (opcional):</label>
            <input type="file" id="imagem" name="imagem" accept="image/*">
        </div>

        <button type="submit">Enviar para API</button>
    </form>

    <div class="response" id="responseOutput" style="display: none;"></div>
</div>

<script>
document.getElementById('apiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const responseDiv = document.getElementById('responseOutput');
    const btn = document.querySelector('button');
    
    // Tratando a data para o formato yyyy-mm-dd hh:mm:ss do MySQL
    let dataHoraInput = document.getElementById('data_hora').value;
    if (dataHoraInput) {
        let dateObj = new Date(dataHoraInput);
        let formattedDate = dateObj.toISOString().slice(0, 19).replace('T', ' ');
        formData.set('data_hora', formattedDate);
    } else {
        formData.delete('data_hora');
    }

    btn.disabled = true;
    btn.textContent = 'Enviando...';
    responseDiv.style.display = 'block';
    responseDiv.innerHTML = 'Aguardando resposta...';

    fetch('api/monitoring.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const status = response.status;
        const text = await response.text();
        let jsonStr = text;
        try {
            const jsonObj = JSON.parse(text);
            jsonStr = JSON.stringify(jsonObj, null, 2);
        } catch (err) {
            // Se não for json, mostra o texto bruto
        }
        
        responseDiv.innerHTML = `<strong>Status HTTP:</strong> ${status}\n\n<strong>Resposta:</strong>\n${jsonStr}`;
    })
    .catch(error => {
        responseDiv.innerHTML = `<strong>Erro na requisição:</strong>\n${error}`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Enviar para API';
    });
});
</script>

</body>
</html>