<?php
class Pedido {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query(
            'SELECT
                p.id AS pedido_id,
                p.data_pedido,
                p.status,
                p.total,
                c.id AS cliente_id,
                c.nome AS cliente_nome,
                c.email AS cliente_email,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        \'produto_id\', pi.produto_id,
                        \'produto_nome\', pr.nome,
                        \'quantidade\', pi.quantidade,
                        \'preco_unitario\', pi.preco_unitario
                    )
                ) AS itens
            FROM
                pedidos p
            JOIN
                clientes c ON p.cliente_id = c.id
            LEFT JOIN
                pedido_itens pi ON p.id = pi.pedido_id
            LEFT JOIN
                produtos pr ON pi.produto_id = pr.id
            GROUP BY
                p.id
            ORDER BY
                p.data_pedido DESC'
        );
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            if ($pedido['itens']) {
                // A agregação CONCAT + JSON_OBJECT pode retornar um único objeto que não está em um array
                // então garantimos que a saída seja sempre um array de itens.
                $pedido['itens'] = json_decode('[' . $pedido['itens'] . ']');
            } else {
                $pedido['itens'] = [];
            }
        }

        return $pedidos;
    }

    public function create(array $data): int {
        if (!isset($data['cliente_id']) || empty($data['items']) || !isset($data['total'])) {
            throw new InvalidArgumentException('Dados do pedido incompletos');
        }

        $this->pdo->beginTransaction();

        try {
            // 1. Grava Cabeçalho
            $stmt = $this->pdo->prepare('INSERT INTO pedidos (cliente_id, total) VALUES (:c, :t)');
            $stmt->execute([
                ':c' => (int)$data['cliente_id'],
                ':t' => (float)$data['total']
            ]);
            $pedidoId = (int)$this->pdo->lastInsertId();

            // 2. Grava Itens em Loop
            $stmtItem = $this->pdo->prepare(
                'INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) 
                 VALUES (:pid, :prod, :qty, :pre)'
            );

            foreach ($data['items'] as $item) {
                $stmtItem->execute([
                    ':pid'  => $pedidoId,
                    ':prod' => $item['id'],
                    ':qty'  => $item['qty'],
                    ':pre'  => $item['preco']
                ]);
            }

            $this->pdo->commit();
            return $pedidoId;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Re-lança a exceção para ser tratada pela camada da API
            throw $e;
        }
    }
}
