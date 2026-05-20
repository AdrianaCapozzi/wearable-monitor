<?php

$host = 'wearable.mysql.dbaas.com.br';
$db_name = 'wearable';
$username = 'wearable';
$password = 'pi5@00bUH';

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode(array("message" => "Erro de conexão: " . $exception->getMessage()));
    exit();
}
?>
