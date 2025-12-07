INSERT INTO usuarios (usuario, clave)
VALUES
('admin', MD5('salma54321'));

INSERT INTO empresas (nombre, telefono)
VALUES
('Rápido Ochoa', '3001234567'),
('Berlinas', '3019876543'),
('Brasilia', '3019876544');

INSERT INTO vehiculos (id_empresa, placa, capacidad, tipo_vehiculo, tipo_servicio)
VALUES
(1, 'ABC123', 40, 'Bus', 'CONVENCIONAL'),
(2, 'XYZ789', 15, 'MiniBus', 'LUJO'),
(2, 'JKL456', 25, 'Van', 'PREFERENCIAL'),
(3, 'OKL457', 15, 'MiniBus', 'LUJO');

INSERT INTO rutas (id_vehiculo, ciudad_origen, ciudad_destino, precio, fecha, hora)
VALUES
(1, 'Santa Marta', 'Riohacha', 30000, '2025-11-07', '07:00'),
(2, 'Cartagena', 'Barranquilla', 50000, '2025-11-05', '08:30'),
(3, 'Bucaramanga', 'Bogotá', 110000, '2025-11-06', '09:45'),
(3, 'Barranquilla', 'Medellín', 160000, '2025-11-07', '11:00');

INSERT INTO reservas (id_ruta, nombre_cliente, cantidad_asientos_reservados, estado)
VALUES
(1, 'Salma Angel', 2, 'ACTIVA'),
(2, 'Jeffrey Espinel', 5, 'ACTIVA');

-- Horarios para Rápido Ochoa (id_empresa = 1)
INSERT INTO horarios (id_empresa, dia_semana, hora_inicio, hora_fin) VALUES
(1, 'Lunes', '07:00', '20:00'),
(1, 'Martes', '07:00', '20:00'),
(1, 'Miércoles', '07:00', '20:00'),
(1, 'Jueves', '07:00', '20:00'),
(1, 'Viernes', '07:00', '20:00'),
(1, 'Sábado', '08:00', '15:00'),
(1, 'Domingo', '09:00', '13:00');


-- Horarios para Berlinas (id_empresa = 2)
INSERT INTO horarios (id_empresa, dia_semana, hora_inicio, hora_fin) VALUES
(2, 'Lunes', '00:00', '23:59'),
(2, 'Martes', '00:00', '23:59'),
(2, 'Miércoles', '00:00', '23:59'),
(2, 'Jueves', '00:00', '23:59'),
(2, 'Viernes', '00:00', '23:59'),
(2, 'Sábado', '00:00', '23:59'),
(2, 'Domingo', '00:00', '23:59');


-- Horarios para Brasilia (id_empresa = 3)
INSERT INTO horarios (id_empresa, dia_semana, hora_inicio, hora_fin) VALUES
(3, 'Lunes', '06:00', '21:00'),
(3, 'Martes', '06:00', '21:00'),
(3, 'Miércoles', '06:00', '21:00'),
(3, 'Jueves', '06:00', '21:00'),
(3, 'Viernes', '06:00', '21:00'),
(3, 'Sábado', '06:00', '21:00'),
(3, 'Domingo', '08:00', '18:00');