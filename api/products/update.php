<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// verifica se o método HTTP é PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido. Use PUT.'
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
        'type' => 'error',
        'message' => 'ID do produto inválido ou ausente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// lê os dados enviados no corpo da requisição
$body = file_get_contents('php://input');

// decodifica os dados que devem estar em JSON
$data = json_decode($body, true);

// valida os dados recebidos campo a campo
// array para armazenar eventuais mensagens de erro
$errors = [];

// se o campo 'name' não estiver presente ou vazio
$name = trim($data['name'] ?? '');
if (!$name) {
    $errors['name'] = 'O nome do produto é obrigatório.';
}

// se o campo 'stock' não estiver presente 
// ou não for um número inteiro ou for negativo
$stock = filter_var($data['stock'] ?? '', FILTER_VALIDATE_INT);
if ($stock === false || $stock < 0) {
    $errors['stock'] = 'O estoque do produto deve ser maior ou igual a zero.';
} 

// se o campo 'price' não estiver presente 
// ou não for numérico ou for negativo
$price = filter_var($data['price'] ?? '', FILTER_VALIDATE_FLOAT);
if ($price === false || $price < 0) {
    $errors['price'] = 'O preço do produto deve ser maior ou igual a zero.';
}

// se o campo 'active' não estiver presente 
// ou não for um número inteiro
$active = filter_var($data['active'] ?? '', FILTER_VALIDATE_INT);
if ($active === false || ($active !== 0 && $active !== 1)) {
    $errors['active'] = 'O campo ativo deve ser 0 ou 1.';
} 

// se o campo 'userId' não estiver presente 
// ou não for um número inteiro
$userId = filter_var($data['userId'] ?? '', FILTER_VALIDATE_INT);
if ($userId === false) {
    $errors['userId'] = 'O id do usuário deve ser um número inteiro.';
}

// se houver erros de validação, 
// ou seja, se o array $errors não estiver vazio
if (!empty($errors)) {
    // retorna erro 400 Bad Request
    // com as mensagens de erro
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'Falha ao validar os dados do produto.',
        'errors' => $errors
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se chegou aqui, os dados são válidos
// e já estão no formato correto para inserção no banco de dados

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

    // verifica se o usuário existe
    $sql = "SELECT * FROM users WHERE id = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    // se o usuário não foi encontrado
    if (!$user) {
        http_response_code(404);
        $response = [
            'type' => 'error',
            'message' => 'Usuário não encontrado.'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // se produto e usuário existem, atualiza o produto no banco de dados
    $sql = "UPDATE products
            SET name = :name,
                stock = :stock,
                price = :price,
                active = :active
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
    $stmt->bindValue(':price', $price);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
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

// se chegou aqui, deu tudo certo
// monta o array com os dados do produto atualizado
$product = [
    'id' => $id,
    'name' => $name,
    'stock' => $stock,
    'price' => $price,
    'active' => $active,
    'userId' => $userId
];

// envia resposta 200 (OK) com a mensagem confirmando
// e os dados do produto atualizado
$response = [
    'type' => 'success',
    'message' => 'Produto atualizado com sucesso.',
    'product' => $product
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);