<?php

// define o caminho do banco de dados SQLite
$db = __DIR__ . '/db.sqlite';

try {
    // cria a conexão com o banco de dados usando PDO
    $conn = new PDO("sqlite:$db");
    // configura o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // configura o PDO para retornar resultados como arrays associativos por padrão
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // se ocorrer um erro ao conectar ao banco de dados,
    // retorna erro 500 Internal Server Error
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    $response = [
        'type' => 'error',
        'message' => 'Erro ao conectar ao banco de dados: ' . htmlspecialchars($e->getMessage())
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}