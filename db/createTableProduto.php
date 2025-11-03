<?php

$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "banco";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Configura o modo de erro do PDO para exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL para criar a tabela produto
    $sql = "CREATE TABLE produto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        categoria TEXT,
        preco DECIMAL(10,2) NOT NULL,
        estoque INT NOT NULL,
        limite_min INT NOT NULL,
        imagem VARCHAR(255),
        dt_criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    // Executa a criação da tabela
    $conn->exec($sql);
    echo "Tabela Produto criada com sucesso";
} catch(PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
}

// Fecha a conexão
$conn = null;

?>
