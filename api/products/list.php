<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// verifica se o método HTTP é GET
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

// obtém o id do usuário dono dos produtos
// busca o userId na URL (query string)
$userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);

// se o userId for null (não fornecido)
// ou false (não é um inteiro válido)
if ($userId === null || $userId === false) {
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'ID do usuário inválido ou ausente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}


try {
    // verifica se o usuário existe
    $sql = "SELECT * FROM users WHERE id = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        $response = [
            "type"=> "error",
            "message"=> "Usuário não encontrado"
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "SELECT * FROM products WHERE userId = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':userId', $userId);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    // se ocorrer um erro ao acessar o banco de dados,
    // retorna erro 500 Internal Server Error
    http_response_code(500);
    $response = [
        'type' => 'error',
        'message'=> 'Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage())
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

// retorna os produtos no formato JSON
$response = [
    'type' => 'success',
    'message' => 'Lista de produtos do usuário obtida com sucesso.',
    'products' => $products
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);
