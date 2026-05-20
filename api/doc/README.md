# Wearable ESP32 API

Esta API foi criada em PHP puro sem frameworks para receber dados de monitoramento (coordenadas geográficas, envio textual e imagens) enviados via POST por uma placa ESP32, salvando as informações automaticamente no banco de dados MySQL associado ao projeto.

## Passo a Passo para Configuração

1. **Subdomínio**: A aplicação assumirá que está hospedada em `https://wearable.surflog.com.br` e que o document root da hospedagem aponte para a pasta raiz, permitindo o caminho de rede nas rotas como `/api/monitoring.php`.
2. **Banco de Dados**: 
   - Ao iniciar, execute o arquivo de consulta `setup_bd/schema.sql` em seu servidor MySQL para criar o banco chamado `wearable` e as 4 respectivas tabelas lógicas descritas no script.
   - As tabelas relacionais são ligadas pelas restrições na chave primária/estrangeira de forma coerente. 
   - Certifique-se de que o host (ex: `wearable.mysql.dbaas.com.br`), usuário, senha e dbname sejam iguais ou estão corretamente representados na parametrização `config/database.php` (`user: wearable` / `password: pi5@00bUH`).
3. **Uploads**: Ao iniciar na sua hospedagem real ou ambiente Linux de teste, assegure que a pasta `uploads/` possua as devidas permissões do usuário do seu nginx, apache ou lighttpd (Ex: `chmod 775 uploads` ou `777`).

## Funcionalidades da Aplicação

### 1. Dashboard de Monitoramento
A aplicação conta com um painel central na raiz que permite visualizar e monitorar os acessos em tempo real.
- **URL**: `https://wearable.surflog.com.br/`
- **Descrição**: Desenvolvido em PHP cruzando as tabelas e exibindo em HTML os últimos 50 registros recebidos. Permite ver nome do cliente, visualizar miniaturas das imagens coletadas, checar o status e interagir com as coordenadas abrindo-as diretamente no Google Maps.

### 2. Documentação em HTML
Os endpoints de integração contam com uma documentação web viva formatada em HTML.
- **URL**: `https://wearable.surflog.com.br/doc/index.php`

### 3. Diagrama do Banco de Dados
A estrutura de MER está desenhada como código na pasta de documentação, podendo ser verificada [neste arquivo do diagrama Mermaid](diagrama_bd.md).

---

## Especificação de Rotas API

### `POST /api/register_hardware.php`

Endpoint administrativo para gerar o token e cadastrar a placa no banco de dados de maneira automatizada. Ele recebe o endereço MAC da placa e gera um token único e aleatório para a segurança da mesma.

#### Parâmetros (Form Data ou JSON com suporte configurado)

| Campo         | Tipo     | Obrigatório | Descrição                                               |
| ------------- | -------- | ----------- | ------------------------------------------------------- |
| `mac`         | String   | Sim*        | Endereço MAC de fábrica da placa ESP32 (Ex: `AA:BB:CC:DD:EE:FF`) |

#### Exemplo cURL:

```bash
curl -X POST https://wearable.surflog.com.br/api/register_hardware.php \
  -F "mac=AA:BB:CC:DD:EE:FF"
```
**Retorno de Sucesso:**
```json
{
  "message": "Hardware registrado com sucesso.",
  "mac": "AA:BB:CC:DD:EE:FF",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
}
```

---

### `POST /api/monitoring.php`

Esta rota aceita um corpo via `multipart/form-data`, permitindo de antemão um upload livre da imagem sem sobrecarga e conversões via strings (como base64).

#### Parâmetros (Form Data)

| Campo         | Tipo     | Obrigatório | Descrição                                               |
| ------------- | -------- | ----------- | ------------------------------------------------------- |
| `mac`         | String   | Sim*        | Endereço MAC de fábrica da placa ESP32 enviada na rede  |
| `token`       | String   | Sim*        | Chave de segurança cadastrada no banco de dados para a placa |
| `data_hora`   | String   | Não         | Data e hora do registro (Ex: `2026-05-02 14:30:00`). Se não enviado, usa o horário do servidor. |
| `latitude`    | Decimal  | Não         | Coordenada de latitude (Ex: `-23.550520`)               |
| `longitude`   | Decimal  | Não         | Coordenada de longitude (Ex: `-46.633308`)              |
| `status_cam`  | Enum     | Não         | Status da câmera da placa, sendo `on` ou `off`          |
| `status_gps`  | Enum     | Não         | Status do GPS da placa, sendo `on` ou `off`             |
| `imagem`      | File     | Não         | Arquivo efetivo de foto enviado pela placa da ESP32     |

> * **Nota**: No script é obrigatório haver a amarração de origem, então o envio do campo `mac` e seu respectivo `token` de segurança são cruciais. A API usará o MAC para localizar a placa e comparará o `token` enviado com o token cadastrado antes de permitir gravação.

#### Testando com cURL

No terminal, o comportamento seria simulado com a flag form `-F`:

```bash
curl -X POST https://wearable.surflog.com.br/api/monitoring.php \
  -F "mac=AA:BB:CC:DD:EE:FF" \
  -F "token=seu_token_secreto_da_placa" \
  -F "data_hora=2026-05-02 14:30:00" \
  -F "latitude=-23.550520" \
  -F "longitude=-46.633308" \
  -F "status_cam=on" \
  -F "status_gps=on" \
  -F "imagem=@/caminho/completo/para/sua/imagem.jpg"
```

#### Testando com o Postman

1. Crie uma requisição com o método **POST**.
2. Digite a URL para: `https://wearable.surflog.com.br/api/monitoring.php` (no caso de local modifique o host, por ex `http://localhost/pi-wearable/api/monitoring.php`).
3. Altere a seleção do tipo do corpo da requisição `Body` clicando e escolhendo **form-data**.
4. Crie uma chave nova `imagem`, modifique o `tipo` escondido dentro do input Key na sua direita trocando de Text para **File**, agora surgirá a opção de selecionar arquivo.
5. Crie as chaves convencionais passivos (Ex: `mac`, `token`, `data_hora`, `latitude`, `longitude`) declarando seu value.
6. Pressione enviar/Send e veja no log um `201 Created` retornando em um elegante JSON e vá conferir a sua pasta de uploads com o nome do hash novo da foto!
