<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// verifica se o método HTTP é PATCH
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido. Use PATCH.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// obtém o id do produto a ser atualizado
// busca o id na URL (query string)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// se o id for null (não fornecido)
// ou false (não é um inteiro válido)
if ($id === null || $id === false) {
    http_response_code(400);
    $response = [
        'type'=> 'error',
        'message'=> 'ID do produto inválido ou ausente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// tenta alterar o produto no banco de dados
try {
    // verifica se o produto existe
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    // se o produto não foi encontrado
    if (!$product) {
        // retorna erro 404 Not Found
        http_response_code(404);
        $response = [
            'type' => 'error',
            'message' => 'Produto não encontrado.'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // se encontrou, faz o update
    $sql = "UPDATE products
            SET active = 1 - active
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // busca o produto alterado para retornar na resposta
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();
} catch (Exception $e) {
    // se ocorrer um erro ao acessar o banco de dados,
    // retorna erro 500 Internal Server Error
    http_response_code(500);
    $response = [
        'type' => 'error',
        'message'=> 'Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage())
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se chegou aqui, deu tudo certo
// envia resposta 200 (OK) com a mensagem confirmando
// e os dados do produto ajustado.

http_response_code(200);
$response = [
    'type' => 'success',
    'message' => 'Produto ajustado com sucesso.',
    'product' => $product
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);