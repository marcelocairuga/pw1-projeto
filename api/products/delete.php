<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// verifica se o método HTTP é DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido. Use DELETE.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// obtém o id do produto a ser excluído
// busca o id na URL (query string)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// se o id for null (não fornecido) 
// ou false (não é um inteiro válido)
if ($id === null || $id === false) {
    // retorna erro 400 Bad Request
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'ID do produto inválido ou ausente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// tenta excluir o produto no banco de dados
try {
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
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

// se nenhuma linha foi afetada pelo DELETE
// significa que o id fornecido não existia no banco
if ($stmt->rowCount() === 0) {
    // retorna erro 404 Not Found
    http_response_code(404);
    $response = [
        'type' => 'error',
        'message' => 'Produto não encontrado.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se chegou aqui, deu tudo certo
// envia a mensagem confirmando
http_response_code(200);
$response = [
    'type' => 'success',
    'message' => 'Produto excluído com sucesso.'
];
echo json_encode($response,  JSON_UNESCAPED_UNICODE);
