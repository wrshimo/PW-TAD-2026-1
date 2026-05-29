<?php

require_once __DIR__ . '/../models/Produto.php';

class ProdutoBusiness {
    private Produto $produtoModel;

    public function __construct(Produto $produtoModel) {
        $this->produtoModel = $produtoModel;
    }

    public function getAll(): array {
        return $this->produtoModel->getAll();
    }

    public function getById(int $id): ?array {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do produto inválido.');
        }
        return $this->produtoModel->getById($id);
    }

    public function create(array $data): int {
        $this->validateProductData($data);
        $data = $this->sanitizeProductData($data);
        return $this->produtoModel->create($data);
    }

    public function update(int $id, array $data): bool {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do produto inválido.');
        }
        $this->validateProductData($data);
        $data = $this->sanitizeProductData($data);
        return $this->produtoModel->update($id, $data);
    }

    public function delete(int $id): bool {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do produto inválido.');
        }
        return $this->produtoModel->delete($id);
    }

    private function validateProductData(array $data) {
        $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
        if ($missing) {
            throw new InvalidArgumentException('Campos obrigatórios ausentes: ' . implode(', ', $missing));
        }

        if (filter_var($data['preco'], FILTER_VALIDATE_FLOAT) === false) {
            throw new InvalidArgumentException('Preço inválido.');
        }

        if (filter_var(trim($data['imagem']), FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('URL de imagem inválida.');
        }
    }

    private function sanitizeProductData(array $data): array {
        return [
            'nome' => htmlentities(trim($data['nome']), ENT_QUOTES, 'UTF-8'),
            'descricao' => htmlentities(trim($data['descricao']), ENT_QUOTES, 'UTF-8'),
            'categoria' => htmlentities(trim($data['categoria']), ENT_QUOTES, 'UTF-8'),
            'preco' => (float)$data['preco'],
            'imagem' => trim($data['imagem']),
        ];
    }
}

function require_fields(array $data, array $required): array {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}
