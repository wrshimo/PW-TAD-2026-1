<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';
require_once __DIR__ . '/../models/Pedido.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    json_response(['error' => 'Acesso negado. Faça login.'], 401);
}

$pedidoModel = new Pedido($pdo);

try {
    if ($method === 'GET') {
        $pedidos = $pedidoModel->getAll();
        json_response($pedidos);
    } elseif ($method === 'POST') {
        $data = get_request_body_params();
        
        try {
            $pedidoId = $pedidoModel->create($data);
            json_response(['message' => 'Pedido #'.$pedidoId.' finalizado com sucesso!', 'id' => $pedidoId], 201);
        } catch (InvalidArgumentException $e) {
            json_response(['error' => $e->getMessage()], 422);
        }

    } else {
        json_response(['error' => 'Método não suportado'], 405);
    }
} catch (Exception $e) {
    json_response(['error' => 'Falha no servidor: ' . $e->getMessage()], 500);
}
