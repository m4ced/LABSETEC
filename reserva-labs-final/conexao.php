<?php
$host    = "localhost";
$banco   = "laboratorio";
$usuario = "root";
$senha   = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    $pdo = null;
    $erro_conexao = "Não foi possível conectar ao MySQL ($banco). Detalhes: " . $e->getMessage();
}
?>
