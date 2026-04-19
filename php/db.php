<?php
// Adiciona o parâmetro $dbName à função e remove a variável $db estática
function getPdo(): PDO
{
    // Database connection details
    $host = '127.0.0.1';
    $dbName = 'mysql';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    // Data Source Name (DSN) - usa o parâmetro $dbName
    $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";

    // PDO options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Lança a exceção para que o código que chama possa tratá-la
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}
?>
