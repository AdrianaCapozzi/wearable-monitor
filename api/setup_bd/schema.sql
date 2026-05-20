CREATE DATABASE IF NOT EXISTS wearable;
USE wearable;

CREATE TABLE IF NOT EXISTS hardware (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac VARCHAR(50) NOT NULL UNIQUE,
    status VARCHAR(20) DEFAULT 'ativo',
    token VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    data_nascimento DATE,
    sexo VARCHAR(20),
    cidade VARCHAR(100),
    estado VARCHAR(50),
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    email VARCHAR(255),
    status VARCHAR(20) DEFAULT 'ativo'
);

CREATE TABLE IF NOT EXISTS cliente_hardware (
    id_cliente INT NOT NULL,
    id_hardware INT NOT NULL,
    data_inicial DATE,
    data_final DATE,
    status VARCHAR(20) DEFAULT 'ativo',
    PRIMARY KEY (id_cliente, id_hardware),
    FOREIGN KEY (id_cliente) REFERENCES cliente(id) ON DELETE CASCADE,
    FOREIGN KEY (id_hardware) REFERENCES hardware(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hardware INT NOT NULL,
    data_hora DATETIME NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image_url VARCHAR(255),
    observacao TEXT,
    status VARCHAR(20) DEFAULT 'ativo',
    status_cam ENUM('on', 'off') DEFAULT 'off',
    status_gps ENUM('on', 'off') DEFAULT 'off',
    FOREIGN KEY (id_hardware) REFERENCES hardware(id) ON DELETE CASCADE
);
