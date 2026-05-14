<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';

// api/produtos.php
// GET  /api/produtos.php            -> lista
// GET  /api/produtos.php?id=1       -> detalhe
// POST /api/produtos.php            -> cria (campos via $_POST)
// PUT  /api/produtos.php?id=1       -> atualiza (campos via body)

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
      $stmt = $pdo->prepare('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos WHERE id = :id');
      $stmt->execute([':id' => $id]);
      $produto = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$produto) {
        json_response(['error' => 'Produto não encontrado'], 404);
      }

      json_response($produto);
    }

    $stmt = $pdo->query('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos ORDER BY id DESC');
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($produtos);
  }

  if ($method === 'POST') {
    // Para cumprir a competência: processar dados de formulário via POST
    $data = $_POST;

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    $stmt = $pdo->prepare(
      'INSERT INTO produtos (nome, descricao, categoria, preco, imagem)
       VALUES (:nome, :descricao, :categoria, :preco, :imagem)'
    );

    $stmt->execute([
      ':nome' => trim($data['nome']),
      ':descricao' => trim($data['descricao']),
      ':categoria' => trim($data['categoria']),
      ':preco' => (float)$data['preco'],
      ':imagem' => trim($data['imagem']),
    ]);

    $id = (int)$pdo->lastInsertId();
    json_response(['message' => 'Produto criado', 'id' => $id], 201);
  }

  if ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro id é obrigatório'], 400);
    }

    // Para PUT, PHP não preenche $_POST. Precisamos ler php://input.
    $data = get_request_body_params();

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    $stmt = $pdo->prepare(
      'UPDATE produtos
         SET nome = :nome,
             descricao = :descricao,
             categoria = :categoria,
             preco = :preco,
             imagem = :imagem
       WHERE id = :id'
    );

    $stmt->execute([
      ':nome' => trim($data['nome']),
      ':descricao' => trim($data['descricao']),
      ':categoria' => trim($data['categoria']),
      ':preco' => (float)$data['preco'],
      ':imagem' => trim($data['imagem']),
      ':id' => $id,
    ]);

    json_response(['message' => 'Produto atualizado', 'id' => $id]);
  }

  json_response(['error' => 'Método não suportado'], 405);
} catch (PDOException $e) {
  error_log('API erro: ' . $e->getMessage());
  json_response(['error' => 'Erro interno ao acessar o banco'], 500);
}