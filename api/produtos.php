<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';
require_once __DIR__ . '/../business/ProdutoBusiness.php';
require_once __DIR__ . '/../models/Produto.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        http_response_code(401);
        echo json_encode(['erro' => 'Autenticação necessária']);
        exit;
    }
}

$produtoModel = new Produto($pdo);
$produtoBusiness = new ProdutoBusiness($produtoModel);

try {
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $produto = $produtoBusiness->getById($id);
            if (!$produto) {
                json_response(['error' => 'Produto não encontrado'], 404);
            }
            json_response($produto);
        } else {
            $produtos = $produtoBusiness->getAll();
            json_response($produtos);
        }
    }

    if ($method === 'POST') {
        $data = $_POST;
        $id = $produtoBusiness->create($data);
        json_response(['message' => 'Produto criado', 'id' => $id], 201);
    }

    if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data = get_request_body_params();

        if ($produtoBusiness->update($id, $data)) {
            json_response(['message' => 'Produto atualizado com sucesso', 'id' => $id]);
        } else {
            json_response(['message' => 'Nenhuma alteração detectada', 'id' => $id]);
        }
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($produtoBusiness->delete($id)) {
            json_response(['message' => 'Produto excluído com sucesso']);
        } else {
            json_response(['error' => 'Produto não encontrado para exclusão'], 404);
        }
    }

} catch (InvalidArgumentException $e) {
    json_response(['error' => $e->getMessage()], 422);
} catch (PDOException $e) {
    error_log('API erro: ' . $e->getMessage());
    json_response(['error' => 'Erro interno ao acessar o banco'], 500);
} catch (Exception $e) {
    json_response(['error' => 'Erro inesperado: ' . $e->getMessage()], 500);
}
