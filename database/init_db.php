<?php
require_once __DIR__ . '/connection.php';

try {
    $db = getConnection();
    // Ler o conteúdo do create_db.sql
    $sql = file_get_contents(__DIR__ . '/create_db.sql');
    // Executar o script completo para criar as tabelas
    $db->exec($sql);
    echo "Banco de dados criado/populado com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao criar/popular o banco: " . $e->getMessage();
}
