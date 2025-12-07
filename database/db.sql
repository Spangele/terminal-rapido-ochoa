CREATE DATABASE IF NOT EXISTS terminal_rapido_ochoa_db;

USE terminal_rapido_ochoa_db;

CREATE TABLE
    usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(100) NOT NULL UNIQUE,
        clave VARCHAR(255) NOT NULL
    );

CREATE TABLE
    empresas (
        id_empresa INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        telefono VARCHAR(20)
    );

CREATE TABLE
    horarios (
        id_horario INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        dia_semana ENUM (
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
            'Domingo'
        ) NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fin TIME NOT NULL,
        FOREIGN KEY (id_empresa) REFERENCES empresas (id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
    );

CREATE TABLE
    vehiculos (
        id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        placa VARCHAR(10) NOT NULL UNIQUE,
        capacidad INT NOT NULL,
        tipo_vehiculo VARCHAR(50) NOT NULL,
        tipo_servicio ENUM ('LUJO', 'CONVENCIONAL', 'PREFERENCIAL') NOT NULL,
        FOREIGN KEY (id_empresa) REFERENCES empresas (id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
    );

CREATE TABLE
    rutas (
        id_ruta INT AUTO_INCREMENT PRIMARY KEY,
        id_vehiculo INT NOT NULL,
        ciudad_origen VARCHAR(100) NOT NULL,
        ciudad_destino VARCHAR(100) NOT NULL,
        precio DECIMAL(10, 2) NOT NULL,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        FOREIGN KEY (id_vehiculo) REFERENCES vehiculos (id_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE
    );

CREATE TABLE
    reservas (
        id_reserva INT AUTO_INCREMENT PRIMARY KEY,
        id_ruta INT NOT NULL,
        nombre_cliente VARCHAR(200) NOT NULL,
        cantidad_asientos_reservados INT NOT NULL,
        estado ENUM ('ACTIVA', 'CANCELADA') DEFAULT 'ACTIVA',
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta) ON DELETE CASCADE ON UPDATE CASCADE
    );

CREATE TABLE
    mensajes (
        id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
        nombre_remitente VARCHAR(500) NOT NULL,
        correo_remitente VARCHAR(200) NOT NULL,
        mensaje VARCHAR(2000) NOT NULL,
        tipo_mensaje ENUM (
            'MENSAJE',
            'PETICION',
            'QUEJA',
            'RECLAMO',
            'SUGERENCIA'
        ) NOT NULL DEFAULT 'MENSAJE'
    );