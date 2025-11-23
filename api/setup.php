<?php

require_once "connection.php";

// Apaga se existir e então cria a tabela 'users'
$conn->exec("DROP TABLE IF EXISTS users");
$conn->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    password TEXT NOT NULL
)");
echo "Tabela 'users' criada com sucesso...\n";

$users = [
    ["name" => "Elesbão", "email" => "elesbao@email.com", "password" => password_hash("12345", PASSWORD_DEFAULT)],    
    ["name"=> "Genoveva", "email"=> "genoveva@email.com", "password"=> password_hash("12345", PASSWORD_DEFAULT)],
    ["name"=> "Raoni", "email"=> "raoni@email.com", "password"=> password_hash("12345", PASSWORD_DEFAULT)]
];

// Insere os usuários na tabela
$stmt = $conn->prepare("INSERT INTO users (name, email, password) 
                    VALUES (:name, :email, :password)");
foreach ($users as $user) {
    $stmt->execute($user);
}
echo "Todos os usuários foram criados com sucesso...\n";

// Apaga se existir e então cria a tabela 'products'
$conn->exec("DROP TABLE IF EXISTS products");
$conn->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    stock INTEGER NOT NULL,
    price REAL NOT NULL,
    active INTEGER NOT NULL,
    userId INTEGER NOT NULL,
    FOREIGN KEY (userId) REFERENCES users(id)
)");
echo "Tabela 'products' criada com sucesso...\n";

// Alguns produtos de exemplo
$products = [
    ["name" => "Mouse Gamer RGB",        "stock" => 25, "price" => 149.90, "active" => 1, "userId" => 1],
    ["name" => "Teclado Mecânico",       "stock" => 15, "price" => 299.99, "active" => 1, "userId" => 1],
    ["name" => "Monitor LED 24 pol.",    "stock" => 8,  "price" => 899.90, "active" => 0, "userId" => 1],
    ["name" => "Headset USB Surround",   "stock" => 12, "price" => 249.50, "active" => 1, "userId" => 1],
    ["name" => "Notebook i5 8GB SSD",    "stock" => 5,  "price" => 3499.00,"active" => 0, "userId" => 1],
    ["name" => "Cabo HDMI 2.1 2m",       "stock" => 40, "price" => 49.90,  "active" => 1, "userId" => 1],
    ["name" => "Suporte Articulado Monitor", "stock" => 10, "price" => 189.00, "active" => 1, "userId" => 1],
    ["name" => "Camiseta Básica Algodão", "stock" => 30, "price" => 59.90,  "active" => 1, "userId" => 2],
    ["name" => "Calça Jeans Slim",        "stock" => 20, "price" => 139.90, "active" => 1, "userId" => 2],
    ["name" => "Tênis Casual Branco",     "stock" => 12, "price" => 249.00, "active" => 1, "userId" => 2],
    ["name" => "Jaqueta de Couro Sintético", "stock" => 5, "price" => 399.90, "active" => 1, "userId" => 2],
    ["name" => "Boné Esportivo",          "stock" => 18, "price" => 79.90,  "active" => 0, "userId" => 2],
    ["name" => "Vestido Floral Curto",    "stock" => 10, "price" => 189.00, "active" => 0, "userId" => 2],
    ["name" => "Chinelo de Borracha",     "stock" => 25, "price" => 29.90,  "active" => 1, "userId" => 2],
    ["name" => "Meias Cano Médio (3 pares)", "stock" => 50, "price" => 34.90, "active" => 1, "userId" => 2]
];


// Insere os produtos na tabela
$stmt = $conn->prepare("INSERT INTO products (name, stock, price, active, userId) 
                    VALUES (:name, :stock, :price, :active, :userId)");
foreach ($products as $product) {
    $stmt->execute($product);
}
echo "Todos os produtos foram criados com sucesso...\n";











