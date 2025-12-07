<?php
include 'database/conexion.php';

$mensaje_estado = "Acceso no permitido o datos incompletos.";
$tipo_alerta = "danger";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_ruta = filter_input(INPUT_POST, 'id_ruta', FILTER_VALIDATE_INT);
    $nombre_cliente = filter_input(INPUT_POST, 'nombre_cliente');
    $cantidad_asientos = filter_input(INPUT_POST, 'cantidad_asientos', FILTER_VALIDATE_INT);

    if ($id_ruta && !empty($nombre_cliente) && $cantidad_asientos > 0) {
        $sql_insert = "INSERT INTO reservas (id_ruta, nombre_cliente, cantidad_asientos_reservados, estado) VALUES (?, ?, ?, 'ACTIVA')";

        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("isi", $id_ruta, $nombre_cliente, $cantidad_asientos);

            if ($stmt_insert->execute()) {
                $tipo_alerta = "success";
                $mensaje_estado = "Reserva confirmada. Se han reservado {$cantidad_asientos} asientos a nombre de {$nombre_cliente}.";
            } else {
                $mensaje_estado = "Error al guardar la reserva en la base de datos: " . $conn->error;
            }
            $stmt_insert->close();
        } else {
            $mensaje_estado = "Error al preparar la consulta de inserción.";
        }
    } else {
        $mensaje_estado = "Error: Faltan datos esenciales (ID de ruta, nombre o cantidad de asientos).";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-red {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn-red:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;">
            <div class="card-body text-center">
                <h3 class="card-title mb-4">Resultado de la Reserva</h3>

                <div class="alert alert-<?php echo $tipo_alerta; ?>" role="alert">
                    <?php echo $mensaje_estado; ?>
                </div>

                <a href="index.php" class="btn btn-secondary mt-3">Volver al Inicio</a>
                <?php if ($tipo_alerta == "danger"): ?>
                    <a href="javascript:history.back()" class="btn btn-red mt-3">Intentar de Nuevo</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>