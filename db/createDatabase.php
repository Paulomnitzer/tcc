<?php
$servername = "localhost:3306";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // set the PDO error mode exeception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE IF NOT EXISTS banco 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;";
    // use exec() because no results are returned
    $conn->exec($sql);
    echo "Banco de Dados criado com sucesso<br>";
} catch(PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
}

$conn = null;
?>