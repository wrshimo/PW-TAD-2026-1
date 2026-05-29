<?php

class Produto {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        return $produto ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produtos (nome, descricao, categoria, preco, imagem)
             VALUES (:nome, :descricao, :categoria, :preco, :imagem)'
        );

        $stmt->execute([
            ':nome' => $data['nome'],
            ':descricao' => $data['descricao'],
            ':categoria' => $data['categoria'],
            ':preco' => $data['preco'],
            ':imagem' => $data['imagem'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos
               SET nome = :nome,
                   descricao = :descricao,
                   categoria = :categoria,
                   preco = :preco,
                   imagem = :imagem
             WHERE id = :id'
        );

        $stmt->execute([
            ':nome' => $data['nome'],
            ':descricao' => $data['descricao'],
            ':categoria' => $data['categoria'],
            ':preco' => $data['preco'],
            ':imagem' => $data['imagem'],
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
