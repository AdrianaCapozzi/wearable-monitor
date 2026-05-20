<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido. Utilize POST."]);
    exit();
}

$mac = isset($_POST['mac']) ? $_POST['mac'] : null;

if (!$mac) {
    http_response_code(400);
    echo json_encode(["message" => "O campo mac é obrigatório para registrar a placa."]);
    exit();
}

// Verifica se o MAC já existe
$query_check = "SELECT id, token FROM hardware WHERE mac = :mac LIMIT 1";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bindParam(":mac", $mac);
$stmt_check->execute();

if ($stmt_check->rowCount() > 0) {
    $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode([
        "message" => "Placa já cadastrada.",
        "mac" => $mac,
        "token_existente" => $row['token']
    ]);
    exit();
}

// Gera um token seguro de 32 caracteres
$token = bin2hex(random_bytes(16));

// Insere a nova placa com o token gerado
$query_insert = "INSERT INTO hardware (mac, token) VALUES (:mac, :token)";
$stmt_insert = $conn->prepare($query_insert);
$stmt_insert->bindParam(":mac", $mac);
$stmt_insert->bindParam(":token", $token);

if ($stmt_insert->execute()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Hardware registrado com sucesso.",
        "mac" => $mac,
        "token" => $token
    ]);
} else {
    http_response_code(503);
    echo json_encode(["message" => "Não foi possível registrar o hardware."]);
}
?>