<?php

include 'database/conexion.php';

$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$fecha_str = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
$fecha_db = $fecha_str;

if (empty($origen) || empty($destino) || !$fecha_db) {
    die("Por favor, ingrese un Origen, Destino y una Fecha válidos (formato aaaaa/mm/dd).");
}

$sql = "
    SELECT
        r.id_ruta,
        e.nombre AS nombre_empresa,
        r.ciudad_origen,
        r.ciudad_destino,
        r.precio,
        r.fecha,
         TIME_FORMAT(r.hora, '%h:%i %p') hora,
        v.capacidad AS capacidad_total,
        v.tipo_servicio,
        COALESCE(SUM(CASE WHEN res.estado = 'ACTIVA' THEN res.cantidad_asientos_reservados ELSE 0 END), 0) AS asientos_reservados,
        (v.capacidad - COALESCE(SUM(CASE WHEN res.estado = 'ACTIVA' THEN res.cantidad_asientos_reservados ELSE 0 END), 0)) AS asientos_disponibles
    FROM
        rutas r
    JOIN
        vehiculos v ON r.id_vehiculo = v.id_vehiculo
    JOIN
        empresas e ON v.id_empresa = e.id_empresa
    LEFT JOIN
        reservas res ON r.id_ruta = res.id_ruta
    WHERE
        r.ciudad_origen LIKE ? AND
        r.ciudad_destino LIKE ? AND
        r.fecha = ?
    GROUP BY
        r.id_ruta, e.nombre, r.ciudad_origen, r.ciudad_destino, r.precio, r.fecha, v.capacidad, v.tipo_servicio
    HAVING
        asientos_disponibles > 0  -- Opcional: solo mostrar rutas con disponibilidad
    ORDER BY
        e.nombre, r.precio
";

if ($stmt = $conn->prepare($sql)) {
    $origen_like = "%$origen%";
    $destino_like = "%$destino%";

    $stmt->bind_param("sss", $origen_like, $destino_like, $fecha_db);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    die("Error al preparar la consulta: " . $conn->error);
}

?>
<?php include("header.php"); ?>


<div class="container my-5">
    <h2 class="mb-4 text-center">🚌 Rutas Disponibles de <?php echo htmlspecialchars($origen) . ' a ' . htmlspecialchars($destino) . ' el ' . htmlspecialchars($fecha_str); ?></h2>

    <?php if ($resultado->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-custom-header">
                    <tr>
                        <th>Empresa</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Servicio</th>
                        <th>Precio</th>
                        <th>Capacidad Total</th>
                        <th>Asientos Disponibles</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['nombre_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($fila['ciudad_origen']); ?></td>
                            <td><?php echo htmlspecialchars($fila['ciudad_destino']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($fila['fecha']))); ?></td>
                            <td><?php echo htmlspecialchars($fila['hora']); ?></td>
                            <td><?php echo htmlspecialchars($fila['tipo_servicio']); ?></td>
                            <td>$<?php echo number_format($fila['precio'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($fila['capacidad_total']); ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?php echo htmlspecialchars($fila['asientos_disponibles']); ?>
                                </span>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-red btn-reserva"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalReserva"
                                    data-idruta="<?php echo $fila['id_ruta']; ?>"
                                    data-capacidad="<?php echo $fila['asientos_disponibles']; ?>"
                                    data-fecha="<?php echo htmlspecialchars(date('d/m/Y', strtotime($fila['fecha']))) . ' ' . htmlspecialchars($fila['hora']); ?>"
                                    data-ruta="<?php echo htmlspecialchars($fila['ciudad_origen']) . ' a ' . htmlspecialchars($fila['ciudad_destino']) . ' (' . htmlspecialchars($fila['nombre_empresa']) . ')'; ?>">
                                    Reservar
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            ¡Lo sentimos! No se encontraron rutas disponibles con los criterios de búsqueda.
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">Nueva Búsqueda</a>
    </div>
</div>

<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalReservaLabel">🔒 Realizar Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="reservar.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_ruta" id="idRutaInput">

                    <p>Ruta: <strong id="rutaInfo"></strong></p>
                    <p>Fecha: <strong id="fechaInfo"></strong></p>
                    <p>Asientos disponibles: <strong id="capacidadInfo"></strong></p>

                    <div class="mb-3">
                        <label for="nombreCliente" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombreCliente" name="nombre_cliente" required>
                    </div>

                    <div class="mb-3">
                        <label for="cantidadAsientos" class="form-label">Cantidad de Asientos</label>
                        <input type="number" class="form-control" id="cantidadAsientos" name="cantidad_asientos" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-red">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="javascript/rutas.js"></script>

<?php
$stmt->close();
$conn->close();
?>