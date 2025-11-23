<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// Verifica se o método da requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido. Use GET.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// obtém o id do produto procurado
// busca o id na URL (query string)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// se o id for null (não fornecido)
// ou false (não é um inteiro válido)
if ($id === null || $id === false) {
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'ID do produto inválido ou ausente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// tenta buscar o produto no banco de dados
try {
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'type' => 'error',
        'message'=> 'Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage())
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se o produto não foi encontrado
if (!$product) {
    http_response_code(404);
    $response = [
        'type' => 'error',
        'message' => 'Produto não encontrado.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// retorna o produto no formato JSON
$response = [
    'type' => 'success',
    'message'=> 'Produto encontrado com sucesso.',
    'product' => $product
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);
