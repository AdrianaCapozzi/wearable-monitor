<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// MODO MÃO NA MASSA: LOG DE DEBUG
// ==========================================
$arquivo_log = '../uploads/debug_esp32.txt'; // Aproveitando a pasta que já tem permissão

$log_data  = "=== NOVA REQUISIÇÃO [" . date('Y-m-d H:i:s') . "] ===\n";
$log_data .= "MÉTODO: " . $_SERVER['REQUEST_METHOD'] . "\n";

// Pega os cabeçalhos (ajuda a ver se o Token e o MAC estão passando por aqui)
$log_data .= "--- HEADERS ---\n";
if (function_exists('apache_request_headers')) {
    $log_data .= print_r(apache_request_headers(), true);
} else {
    // Fallback caso a Locaweb bloqueie a função acima
    $log_data .= print_r($_SERVER, true); 
}

// Pega os campos de texto (Latitude, Longitude, etc)
$log_data .= "\n--- DADOS POST ---\n";
$log_data .= print_r($_POST, true);

// Pega os metadados do arquivo enviado (A foto)
$log_data .= "\n--- ARQUIVOS (FILES) ---\n";
$log_data .= print_r($_FILES, true);

// Se vier algo fora do padrão (Raw Body), a gente pega também
$raw_input = file_get_contents('php://input');
if(!empty($raw_input)){
    $log_data .= "\n--- RAW INPUT (Primeiros 100 caracteres) ---\n";
    $log_data .= substr($raw_input, 0, 100) . "...\n"; 
}

$log_data .= "=================================================\n\n";

// Escreve no arquivo adicionando no final (FILE_APPEND)
file_put_contents($arquivo_log, $log_data, FILE_APPEND);
// ==========================================

?>

<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

// Verifica se é um método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido. Utilize POST."]);
    exit();
}

$mac = isset($_POST['mac']) ? $_POST['mac'] : null; 
$token = isset($_POST['token']) ? $_POST['token'] : null;
$latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
$status_cam = isset($_POST['status_cam']) && in_array(strtolower($_POST['status_cam']), ['on', 'off']) ? strtolower($_POST['status_cam']) : 'off';
$status_gps = isset($_POST['status_gps']) && in_array(strtolower($_POST['status_gps']), ['on', 'off']) ? strtolower($_POST['status_gps']) : 'off';
$data_hora = isset($_POST['data_hora']) ? $_POST['data_hora'] : date('Y-m-d H:i:s');

if (!$mac || !$token) {
    http_response_code(400);
    echo json_encode(["message" => "O campos mac e token são obrigatórios."]);
    exit();
}

// Busca o ID do hardware baseado no MAC enviado pela placa
$id_hardware = null;
$query_hw = "SELECT id, token FROM hardware WHERE mac = :mac LIMIT 1";
$stmt_hw = $conn->prepare($query_hw);
$stmt_hw->bindParam(":mac", $mac);
$stmt_hw->execute();

if ($stmt_hw->rowCount() > 0) {
    $row = $stmt_hw->fetch(PDO::FETCH_ASSOC);
    
    // Verifica se o token enviado confere com o gerado/cadastrado no banco
    if ($row['token'] !== $token) {
        http_response_code(401);
        echo json_encode(["message" => "Token de segurança inválido. Acesso negado."]);
        exit();
    }
    
    $id_hardware = $row['id'];
} else {
    http_response_code(404);
    echo json_encode(["message" => "Hardware com MAC não encontrado no sistema. Acesso negado."]);
    exit();
}

$image_url = null;

// Lógica de Upload de Imagem
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
    $target_dir = "../uploads/";
    
    // Cria o diretório de uploads se não existir
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
    // Cria um nome único usando uniqid()
    $new_filename = uniqid('img_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
        $image_url = $new_filename; // Salvamos apenas o nome único
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao fazer upload da imagem."]);
        exit();
    }
}

// Inserir os dados na tabela monitoring
$query = "INSERT INTO monitoring 
          (id_hardware, data_hora, latitude, longitude, image_url, status_cam, status_gps) 
          VALUES (:id_hardware, :data_hora, :latitude, :longitude, :image_url, :status_cam, :status_gps)";

$stmt = $conn->prepare($query);

$stmt->bindParam(":id_hardware", $id_hardware);
$stmt->bindParam(":data_hora", $data_hora);
$stmt->bindParam(":latitude", $latitude);
$stmt->bindParam(":longitude", $longitude);
$stmt->bindParam(":image_url", $image_url);
$stmt->bindParam(":status_cam", $status_cam);
$stmt->bindParam(":status_gps", $status_gps);

try {
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "message" => "Dados registrados com sucesso.",
            "image" => $image_url ? $image_url : "Nenhuma imagem enviada."
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Não foi possível registrar os dados."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>