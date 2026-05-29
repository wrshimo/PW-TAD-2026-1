INSERT INTO `clientes` (nome, email, cidade) VALUES 
('Ana Souza', 'ana@email.com', 'Cuiabá'),
('Bruno Lima', 'bruno@email.com', 'Várzea Grande'),
('Carla Dias', 'carla@email.com', 'Rondonópolis'),
('Daniel Oliveira', 'daniel@email.com', 'Cáceres');

-- Inserção de dados na tabela `produtos`
INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria`) VALUES
(1, 'Smartphone Samsung Galaxy S23', 'Eletrônicos: Smartphone Samsung Galaxy S23. Produto demonstrativo para o laboratório de DOM e eventos.', 3599.00, 'https://placehold.co/400x400?text=Smartphone', 'Eletrônicos'),
(2, 'Fone de Ouvido Sony WH-1000XM5', 'Eletrônicos: Fone de Ouvido Sony WH-1000XM5. Produto demonstrativo para o laboratório de DOM e eventos.', 2499.00, 'https://placehold.co/400x400?text=Fone', 'Eletrônicos'),
(3, 'Smartwatch Apple Watch Series 9', 'Eletrônicos: Smartwatch Apple Watch Series 9. Produto demonstrativo para o laboratório de DOM e eventos.', 4199.00, 'https://placehold.co/400x400?text=Smartwatch', 'Eletrônicos'),
(4, 'Notebook Dell Inspiron 15', 'Eletrônicos: Notebook Dell Inspiron 15. Produto demonstrativo para o laboratório de DOM e eventos.', 3899.00, 'https://placehold.co/400x400?text=Notebook', 'Eletrônicos'),
(5, 'Teclado Mecânico Gamer Logitech', 'Eletrônicos: Teclado Mecânico Gamer Logitech. Produto demonstrativo para o laboratório de DOM e eventos.', 699.90, 'https://placehold.co/400x400?text=Teclado', 'Eletrônicos'),
(6, 'Camiseta Básica de Algodão', 'Roupas: Camiseta Básica de Algodão. Produto demonstrativo para o laboratório de DOM e eventos.', 49.90, 'https://placehold.co/400x400?text=Camiseta', 'Roupas'),
(7, 'Calça Jeans Masculina Slim', 'Roupas: Calça Jeans Masculina Slim. Produto demonstrativo para o laboratório de DOM e eventos.', 149.90, 'https://placehold.co/400x400?text=Calca', 'Roupas'),
(8, 'Jaqueta de Couro Sintético Feminina', 'Roupas: Jaqueta de Couro Sintético Feminina. Produto demonstrativo para o laboratório de DOM e eventos.', 249.90, 'https://placehold.co/400x400?text=Jaqueta', 'Roupas'),
(9, 'Tênis Esportivo Corrida Unissex', 'Roupas: Tênis Esportivo Corrida Unissex. Produto demonstrativo para o laboratório de DOM e eventos.', 349.90, 'https://placehold.co/400x400?text=Tenis', 'Roupas'),
(10, 'Vestido Midi Floral', 'Roupas: Vestido Midi Floral. Produto demonstrativo para o laboratório de DOM e eventos.', 199.90, 'https://placehold.co/400x400?text=Vestido', 'Roupas'),
(11, 'Livro: O Senhor dos Anéis - Box Completo', 'Livros: Livro: O Senhor dos Anéis - Box Completo. Produto demonstrativo para o laboratório de DOM e eventos.', 189.90, 'https://placehold.co/400x400?text=Livro', 'Livros'),
(12, 'Livro: 1984 - George Orwell', 'Livros: Livro: 1984 - George Orwell. Produto demonstrativo para o laboratório de DOM e eventos.', 39.90, 'https://placehold.co/400x400?text=Livro', 'Livros'),
(13, 'Livro: O Pequeno Príncipe', 'Livros: Livro: O Pequeno Príncipe. Produto demonstrativo para o laboratório de DOM e eventos.', 29.90, 'https://placehold.co/400x400?text=Livro', 'Livros'),
(14, 'Kit Ferramentas de Jardinagem 10 Peças', 'Casa e Jardim: Kit Ferramentas de Jardinagem 10 Peças. Produto demonstrativo para o laboratório de DOM e eventos.', 129.90, 'https://placehold.co/400x400?text=Kit', 'Casa e Jardim'),
(15, 'Cadeira de Escritório Ergonômica', 'Casa e Jardim: Cadeira de Escritório Ergonômica. Produto demonstrativo para o laboratório de DOM e eventos.', 899.90, 'https://placehold.co/400x400?text=Cadeira', 'Casa e Jardim'),
(16, 'Aspirador de Pó Robô Inteligente', 'Casa e Jardim: Aspirador de Pó Robô Inteligente. Produto demonstrativo para o laboratório de DOM e eventos.', 1299.00, 'https://placehold.co/400x400?text=Aspirador', 'Casa e Jardim'),
(17, 'Mouse Gamer RGB 12000 DPI', 'Eletrônicos: Mouse Gamer RGB 12000 DPI. Produto demonstrativo para o laboratório de DOM e eventos.', 259.90, 'https://placehold.co/400x400?text=Mouse', 'Eletrônicos'),
(18, 'Cafeteira Elétrica Programável', 'Casa e Jardim: Cafeteira Elétrica Programável. Produto demonstrativo para o laboratório de DOM e eventos.', 449.00, 'https://placehold.co/400x400?text=Cafeteira', 'Casa e Jardim'),
(19, 'Moletom Canguru com Capuz', 'Roupas: Moletom Canguru com Capuz. Produto demonstrativo para o laboratório de DOM e eventos.', 129.90, 'https://placehold.co/400x400?text=Moletom', 'Roupas');

INSERT INTO usuarios (nome, usuario, senha)
VALUES (
  'Administrador',
  'admin',
  '$2y$10$LR1AP8ImaWXgVpYbVKkeJ.VC4ae/UI6iBaXb31htSJGNJE8cdCN2K'
);