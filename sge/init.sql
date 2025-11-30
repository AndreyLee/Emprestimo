CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    senha VARCHAR(255),
    setor VARCHAR(100),
    perfil VARCHAR(20),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `quantidade` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data DATE,
    documento VARCHAR(50),
    item_id INT NOT NULL,
    quantidade INT NOT NULL,
    nome VARCHAR(100),
    status VARCHAR(50),
    retirada_com VARCHAR(100),
    devolvido_com VARCHAR(100),
    data_devolucao DATETIME,
    FOREIGN KEY (item_id) REFERENCES itens(id)
);

INSERT INTO funcionarios (nome, senha, setor, perfil) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TI', 'admin');
