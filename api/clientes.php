<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Segurança: Apenas usuários logados podem acessar a API de pedidos
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    json_response(['error' => 'Acesso negado. Faça login.'], 401);
}

try {
    // GET: Lista clientes para o select do modal
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome ASC');
        json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    json_response(['error' => 'Método não suportado'], 405);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['error' => 'Falha no servidor: ' . $e->getMessage()], 500);
}