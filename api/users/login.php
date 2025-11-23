<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// lê os dados enviados no corpo da requisição
$body = file_get_contents('php://input');

// decodifica os dados que devem estar em JSON
$data = json_decode($body, true);

// não há uma validação complexa aqui, 
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// se email ou senha estiverem vazios
if (empty($email) || empty($password)) {
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'Credenciais inválidas.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// tenta buscar o usuário no banco de dados
try {
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();      
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'type' => 'error',
        'message'=> 'Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage())
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se o usuário não foi encontrado ou a senha não confere
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    $response = [
        'type' => 'error',
        'message' => 'Credenciais inválidas.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// remove a senha do array antes de retornar os dados do usuário
// importante para segurança, nunca envie senhas em respostas
unset($user['password']); 

// retorna o usuário no formato JSON
$response = [
    'type' => 'success',
    'message'=> 'Login realizado com sucesso.',
    'user' => $user
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);
