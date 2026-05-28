# Roteiro de Aula Prática: Finalização do E-commerce
**Instrutor:** Walderson Shimokawa  
**Tópico:** Otimização, Persistência de Pedidos e Demonstração Final.

---

## 1. Visão Geral
Nesta aula, concluiremos o ciclo de venda do nosso e-commerce. Transformaremos os itens temporários do `LocalStorage` em registros permanentes no banco de dados MariaDB, utilizando PHP com PDO e Fetch API.

### Competências a desenvolver:
- **Conhecer**: Performance Web e Minificação.
- **Fazer**: Implementar Checkout REST e refatorar código.
- **Ser**: Orientado à qualidade e experiência do usuário.

---

## 2. Passo 1: Preparação do Banco de Dados
Precisamos criar as tabelas que suportarão a transação. Execute o script SQL abaixo no seu cliente de banco de dados (ex: Adminer ou DBeaver).

```sql
USE loja;

-- 1. Tabela de Clientes (Para seleção no Checkout)
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais para teste
INSERT INTO `clientes` (nome, email, cidade) VALUES 
('Ana Souza', 'ana@email.com', 'Cuiabá'),
('Bruno Lima', 'bruno@email.com', 'Várzea Grande'),
('Carla Dias', 'carla@email.com', 'Rondonópolis'),
('Daniel Oliveira', 'daniel@email.com', 'Cáceres');

-- 2. Tabela de Pedidos (Cabeçalho da Venda)
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `data_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pendente','Pago','Enviado','Cancelado') DEFAULT 'Pendente',
  `total` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Itens do Pedido (Relacional)
CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_item_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `fk_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Passo 2: Implementação do Backend (API REST)
Crie o arquivo `api/pedidos.php`. Ele lidará com a listagem de clientes e a gravação atômica do pedido.

```php
<?php
/**
 * api/pedidos.php
 * Endpoint para gestão de vendas
 */
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

    // POST: Grava o pedido e os itens (Transação Atômica)
    if ($method === 'POST') {
        $data = get_request_body_params();

        if (!isset($data['cliente_id']) || empty($data['items'])) {
            json_response(['error' => 'Dados do pedido incompletos'], 422);
        }

        $pdo->beginTransaction();

        // 1. Grava Cabeçalho
        $stmt = $pdo->prepare('INSERT INTO pedidos (cliente_id, total) VALUES (:c, :t)');
        $stmt->execute([
            ':c' => (int)$data['cliente_id'],
            ':t' => (float)$data['total']
        ]);
        $pedidoId = $pdo->lastInsertId();

        // 2. Grava Itens em Loop
        $stmtItem = $pdo->prepare('
            INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) 
            VALUES (:pid, :prod, :qty, :pre)
        ');

        foreach ($data['items'] as $item) {
            $stmtItem->execute([
                ':pid'  => $pedidoId,
                ':prod' => $item['id'],
                ':qty'  => $item['qty'],
                ':pre'  => $item['preco']
            ]);
        }

        $pdo->commit();
        json_response(['message' => 'Pedido #'.$pedidoId.' finalizado com sucesso!', 'id' => $pedidoId], 201);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['error' => 'Falha no servidor: ' . $e->getMessage()], 500);
}
```

---

## 4. Passo 3: Frontend - Interface do Checkout
No arquivo `index.php`, adicione o modal do Bootstrap antes do fechamento da tag `</body>`.

```html
<!-- Modal Checkout -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-cart-check"></i> Finalizar Pedido</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Selecione o Cliente:</label>
          <select id="select-cliente" class="form-select">
            <option value="">Carregando clientes...</option>
          </select>
        </div>
        <div class="alert alert-info py-2">
            <strong>Total da Venda:</strong> <span id="checkout-total">R$ 0,00</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="btn-confirmar-pedido" class="btn btn-success">Confirmar e Gravar</button>
      </div>
    </div>
  </div>
</div>
```

---

## 5. Passo 4: Frontend - Lógica de Integração
No arquivo `app.js`, adicione as funções para carregar clientes e enviar o payload.

### 5.1 Adicionar botão de Checkout no Popover
Altere sua função `renderCartPopoverHtml()` para incluir o botão:

```javascript
function renderCartPopoverHtml() {
    // ... código de mapeamento de itens existente ...
    return `
        <div>
            ${itemsHtml}
            <hr/>
            <div class="d-flex justify-content-between fw-bold mb-3">
                <span>Total</span><span>${formatPriceBRL(cartTotal())}</span>
            </div>
            <button id="go-to-checkout" class="btn btn-primary w-100">Finalizar Compra</button>
        </div>
    `;
}
```

### 5.2 Lógica de Acionamento e Envio (Final do app.js)
```javascript
// Instanciação do Modal
const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));

// Event Delegation para o botão de checkout
document.body.addEventListener('click', (e) => {
    if (e.target.id === 'go-to-checkout') {
        if (cartCount() === 0) return alert('Carrinho vazio!');
        document.getElementById('checkout-total').textContent = formatPriceBRL(cartTotal());
        loadClientes();
        checkoutModal.show();
    }
});

// Função para buscar clientes
function loadClientes() {
    fetch('/api/pedidos.php')
        .then(r => r.json())
        .then(clientes => {
            const select = document.getElementById('select-cliente');
            select.innerHTML = '<option value="">Selecione um cliente...</option>' + 
                clientes.map(c => `<option value="${c.id}">${c.nome}</option>`).join('');
        })
        .catch(() => alert('Erro ao carregar clientes. Verifique se está logado.'));
}

// Clique no botão Confirmar
document.getElementById('btn-confirmar-pedido').addEventListener('click', () => {
    const clienteId = document.getElementById('select-cliente').value;
    if (!clienteId) return alert('Por favor, selecione um cliente.');

    const payload = {
        cliente_id: clienteId,
        total: cartTotal(),
        items: cart.items
    };

    fetch('/api/pedidos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.error) throw new Error(res.error);
        alert(res.message);
        clearCart(); // Função existente que limpa LS e atualiza UI
        checkoutModal.hide();
    })
    .catch(err => alert('Falha ao gravar pedido: ' + err.message));
});
```

---

## 6. Passo 5: Otimização para Produção
Como última etapa, realize a minificação manual do seu código:
1. Copie o conteúdo de `app.js`.
2. Acesse [javascript-minifier.com](https://javascript-minifier.com/).
3. Cole, gere o código minificado e salve-o como `app.min.js`.
4. Altere a chamada no `index.php` para apontar para o arquivo minificado.

---

## Checklist Final
- [ ] O script SQL foi executado sem erros?
- [ ] O arquivo `api/pedidos.php` possui a lógica de `beginTransaction`?
- [ ] O modal abre apenas se houver itens no carrinho?
- [ ] O banco de dados foi populado após clicar em "Confirmar"?
- [ ] O LocalStorage foi limpo após o sucesso?
