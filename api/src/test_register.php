<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Hardware - API</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: monospace; }
        button { width: 100%; padding: 10px; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .response { margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 4px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; }
        .token-highlight { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Registrar Nova Placa (Hardware)</h2>
    <form id="registerForm">
        <div class="form-group">
            <label for="mac">MAC Address (obrigatório):</label>
            <input type="text" id="mac" name="mac" placeholder="Ex: 00:1A:2B:3C:4D:5E" required>
        </div>

        <button type="submit">Registrar / Consultar</button>
    </form>

    <div class="response" id="responseOutput" style="display: none;"></div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const responseDiv = document.getElementById('responseOutput');
    const btn = document.querySelector('button');
    
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    responseDiv.style.display = 'block';
    responseDiv.innerHTML = 'Aguardando resposta...';

    fetch('api/register_hardware.php', {
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
            
            // Tratamento visual para destacar o Token recebido
            if (jsonObj.token) {
                jsonStr = jsonStr.replace(jsonObj.token, `<span class="token-highlight">${jsonObj.token}</span>`);
            } else if (jsonObj.token_existente) {
                jsonStr = jsonStr.replace(jsonObj.token_existente, `<span class="token-highlight">${jsonObj.token_existente}</span>`);
            }
        } catch (err) {
            // Caso não retorne JSON
        }
        
        responseDiv.innerHTML = `<strong>Status HTTP:</strong> ${status}\n\n<strong>Resposta:</strong>\n${jsonStr}`;
    })
    .catch(error => {
        responseDiv.innerHTML = `<strong>Erro na requisição:</strong>\n${error}`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Registrar / Consultar';
    });
});
</script>

</body>
</html>