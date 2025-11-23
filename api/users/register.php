<?php

// importa a conexão com o banco de dados
require_once __DIR__ . '/../connection.php';

// define que o conteúdo da resposta será em JSON e UTF-8
header('Content-Type: application/json; charset=UTF-8');

// verifica se o método HTTP é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // se não for, retorna erro 405 Method Not Allowed
    http_response_code(405);
    $response = [
        'type' => 'error',
        'message' => 'Método não permitido. Use POST.'
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

// se o campo 'name' não estiver presente ou estiver vazio 
$name = trim($data['name'] ?? '');
if (!$name) {
    $errors['name'] = 'O nome do usuário é inválido.';
}

// se o campo 'email' não estiver presente 
// ou não for um email valido
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    $errors['email'] = 'O e-mail do usuário é inválido.';
}

// se o campo 'password' não estiver presente 
$password = $data['password'] ?? '';
if (!$password || strlen($password) < 5) {
    $errors['password'] = 'A senha do usuário deve ter pelo menos 5 caracteres.';
}

// se houver erros de validação, 
// ou seja, se o array $errors não estiver vazio
if (!empty($errors)) {
    // retorna erro 400 Bad Request
    // com as mensagens de erro
    http_response_code(400);
    $response = [
        'type' => 'error',
        'message' => 'Falha ao validar os dados do usuário.',
        'errors' => $errors
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// se chegou aqui, os dados são válidos
// só falta criptografar a senha
$password = password_hash($password, PASSWORD_DEFAULT);

// tenta inserir o novo usuário no banco de dados
try {
    // verifica se já existe um usuário com o mesmo email
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        $errors['email'] = 'Já existe um usuário cadastrado com este e-mail.';
        http_response_code(409);
        $response = [
            'type' => 'error',
            'message' => 'O e-mail informado já está em uso.'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // insere o novo usuário
    $sql = "INSERT INTO users (name, email, password) 
            VALUES (:name, :email, :password)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':password', $password);
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

// monta o array com os dados do usuário criado
// repare que a senha não é incluída aqui, por segurança
$user = [
    'id' => $conn->lastInsertId(),
    'name' => $name,
    'email' => $email
];

// retorna resposta 201 Created
// com os dados do usuário criado
http_response_code(201);
$response = [
    'type'=> 'success',
    'message'=> 'Usuário registrado com sucesso.',
    'user' => $user
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);