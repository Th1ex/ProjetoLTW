<?php
declare(strict_types=1); // Opcional, mas ajuda a manter um código mais robusto.

function getConnection(): PDO {
    static $db = null;

    if ($db === null) {
        try {
            // Exemplo: caminho relativo para a base "project.db" na pasta database
            $db = new PDO('sqlite:' . __DIR__ . '/project.db');
            // Define o modo de erro para lançar exceções (opcional, mas recomendado)
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Define o modo de fetch por omissão
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Em produção, não se deve exibir o erro completo ao utilizador.
            // Use logs em vez disso.
            die("Erro na conexão com a base de dados: " . $e->getMessage());
        }
    }

    return $db;
}
