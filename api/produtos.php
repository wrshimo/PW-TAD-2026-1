<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';

// api/produtos.php
// GET  /api/produtos.php            -> lista
// GET  /api/produtos.php?id=1       -> detalhe
// POST /api/produtos.php            -> cria (campos via $_POST)
// PUT  /api/produtos.php?id=1       -> atualiza (campos via body)
// DELETE /api/produtos.php?id=1     -> exclui

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
    $data = $_POST;

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    // 1. Validando o Preço (float obrigatório)
    $precoFiltrado = filter_var($data['preco'], FILTER_VALIDATE_FLOAT);
    if ($precoFiltrado === false) {
      json_response(['error' => 'Preço inválido'], 422);
    }

    // 2. Sanitizando contra XSS (Protegendo textos de código HTML)
    $nome = htmlentities(trim($data['nome']), ENT_QUOTES, 'UTF-8');
    $descricao = htmlentities(trim($data['descricao']), ENT_QUOTES, 'UTF-8');
    $categoria = htmlentities(trim($data['categoria']), ENT_QUOTES, 'UTF-8');

    $stmt = $pdo->prepare(
      'INSERT INTO produtos (nome, descricao, categoria, preco, imagem)
       VALUES (:nome, :descricao, :categoria, :preco, :imagem)'
    );

    $stmt->execute([
      ':nome' => $nome,
      ':descricao' => $descricao,
      ':categoria' => $categoria,
      ':preco' => (float)$precoFiltrado,
      ':imagem' => trim($data['imagem']),
    ]);

    // 3. Confirmando a inserção no banco
    if ($stmt->rowCount() === 0) {
      json_response(['error' => 'Falha ao inserir o produto'], 500);
    }

    $id = (int)$pdo->lastInsertId();
    json_response(['message' => 'Produto criado', 'id' => $id], 201);
  }

  if ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro id é obrigatório'], 400);
    }

    // Para PUT, PHP não preenche $_POST.
    $data = get_request_body_params();

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    // 1. Validando o Preço e URL da imagem
    $precoFiltrado = filter_var($data['preco'], FILTER_VALIDATE_FLOAT);
    if ($precoFiltrado === false) {
      json_response(['error' => 'Preço inválido'], 422);
    }
    $imagemFiltrada = filter_var(trim($data['imagem']), FILTER_VALIDATE_URL);
    if ($imagemFiltrada === false) {
      json_response(['error' => 'URL de imagem inválida'], 422);
    }

    // 2. Sanitizando contra XSS
    $nome = htmlentities(trim($data['nome']), ENT_QUOTES, 'UTF-8');
    $descricao = htmlentities(trim($data['descricao']), ENT_QUOTES, 'UTF-8');
    $categoria = htmlentities(trim($data['categoria']), ENT_QUOTES, 'UTF-8');

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
      ':nome' => $nome,
      ':descricao' => $descricao,
      ':categoria' => $categoria,
      ':preco' => (float)$precoFiltrado,
      ':imagem' => $imagemFiltrada,
      ':id' => $id,
    ]);

    // 3. Confirmando a alteração
    if ($stmt->affectedRows() === 0) {
      // Retorna sucesso para UX pois o usuário pode ter salvo sem mudar valores reais
      json_response(['message' => 'Nenhuma alteração detectada', 'id' => $id]);
    }

    json_response(['message' => 'Produto atualizado com sucesso', 'id' => $id]);
  }

  if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Validação se o ID é razoável para o banco de dados
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro ID é inválido ou ausente'], 400);
    }

    // Operação via PDO Prepared Statements (segurança contra SQLi)
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    // Validando se o produto de fato existia para ser deletado
    if ($stmt->rowCount() === 0) {
      json_response(['error' => 'Produto não encontrado para exclusão'], 404);
    }

    // Resposta HTTP de Sucesso para o Front-end
    json_response(['message' => 'Produto excluído com sucesso']);
  }

  json_response(['error' => 'Método não suportado'], 405);
} catch (PDOException $e) {
  error_log('API erro: ' . $e->getMessage());
  json_response(['error' => 'Erro interno ao acessar o banco'], 500);
}