CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','vendedor','orcamentista') NOT NULL DEFAULT 'vendedor',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);

INSERT INTO users (name, email, password, role, created_at)
VALUES ('Administrador', 'admin@autre.local', '$2y$12$RpofxfMKYrlx4iyR/VQ1v.D251XV7GPDdFvWoi6vyuwXQjpIyocqu', 'admin', NOW());

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(50) NULL,
    empresa VARCHAR(150) NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE obras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(180) NOT NULL,
    cliente_id INT NULL,
    endereco VARCHAR(255) NULL,
    cidade VARCHAR(120) NULL,
    estado VARCHAR(60) NULL,
    criado_em DATETIME NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

CREATE TABLE arquitetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(60) NULL,
    empresa VARCHAR(150) NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(60) NULL,
    comissao_percentual DECIMAL(10,2) DEFAULT 0,
    criado_em DATETIME NOT NULL
);

CREATE TABLE projetistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(60) NULL,
    especialidade VARCHAR(150) NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NULL,
    nome VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    codigo_inlumi VARCHAR(120) NULL,
    fornecedor VARCHAR(150) NULL,
    codigo_fornecedor VARCHAR(120) NULL,
    acabamento VARCHAR(120) NULL,
    custo DECIMAL(15,2) NOT NULL DEFAULT 0,
    preco_venda DECIMAL(15,2) NOT NULL DEFAULT 0,
    imagem_path VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

CREATE TABLE orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_ordem VARCHAR(50) NOT NULL,
    cliente_id INT NULL,
    obra_id INT NULL,
    arquiteto_id INT NULL,
    vendedor_id INT NULL,
    projetista_id INT NULL,
    markup DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    frete DECIMAL(15,2) DEFAULT 0,
    total_custo DECIMAL(15,2) DEFAULT 0,
    total_venda DECIMAL(15,2) DEFAULT 0,
    total_final DECIMAL(15,2) DEFAULT 0,
    status ENUM('aberto','fechado') DEFAULT 'aberto',
    pedido_numero VARCHAR(80) NULL,
    observacoes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (obra_id) REFERENCES obras(id) ON DELETE SET NULL,
    FOREIGN KEY (arquiteto_id) REFERENCES arquitetos(id) ON DELETE SET NULL,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE SET NULL,
    FOREIGN KEY (projetista_id) REFERENCES projetistas(id) ON DELETE SET NULL
);

CREATE TABLE orcamento_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orcamento_id INT NOT NULL,
    produto_id INT NULL,
    codigo_produto VARCHAR(120) NULL,
    descricao VARCHAR(255) NULL,
    acabamento VARCHAR(150) NULL,
    custo_unitario DECIMAL(15,2) NOT NULL DEFAULT 0,
    preco_unitario DECIMAL(15,2) NOT NULL DEFAULT 0,
    quantidade DECIMAL(15,2) NOT NULL DEFAULT 1,
    agrupamento VARCHAR(120) NULL,
    imagem_path VARCHAR(255) NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
);

CREATE INDEX idx_orcamentos_status ON orcamentos(status);
CREATE INDEX idx_orcamentos_cliente ON orcamentos(cliente_id);
CREATE INDEX idx_itens_orcamento ON orcamento_itens(orcamento_id);
