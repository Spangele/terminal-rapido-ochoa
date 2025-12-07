<?php

include 'database/conexion.php';
include("header.php");

$status_message = '';
$status_type = 'success';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['action']) && $_POST['action'] == 'eliminar' && isset($_POST['id_ruta'])) {
        $id_ruta = filter_input(INPUT_POST, 'id_ruta', FILTER_VALIDATE_INT);

        if ($id_ruta) {
            $sql = "DELETE FROM rutas WHERE id_ruta = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $id_ruta);
                if ($stmt->execute()) {
                    $status_message = "Ruta eliminada correctamente. Las reservas asociadas han sido canceladas.";
                } else {
                    $status_message = "Error al eliminar la ruta: " . $conn->error;
                    $status_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && ($_POST['action'] == 'agregar' || $_POST['action'] == 'actualizar')) {
        $id_vehiculo = filter_input(INPUT_POST, 'id_vehiculo', FILTER_VALIDATE_INT);
        $origen = filter_input(INPUT_POST, 'ciudad_origen');
        $destino = filter_input(INPUT_POST, 'ciudad_destino');
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $fecha = filter_input(INPUT_POST, 'fecha');
        $hora = filter_input(INPUT_POST, 'hora');

        if (!$id_vehiculo || empty($origen) || empty($destino) || !$precio || empty($fecha) || empty($hora)) {
            $status_message = "Error: Faltan datos obligatorios o son inválidos.";
            $status_type = 'danger';
        } else {
            if ($_POST['action'] == 'agregar') {
                $sql = "INSERT INTO rutas (id_vehiculo, ciudad_origen, ciudad_destino, precio, fecha, hora) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issdss", $id_vehiculo, $origen, $destino, $precio, $fecha, $hora);
            } elseif ($_POST['action'] == 'actualizar') {
                $id_ruta = filter_input(INPUT_POST, 'id_ruta', FILTER_VALIDATE_INT);
                if ($id_ruta) {
                    $sql = "UPDATE rutas SET id_vehiculo = ?, ciudad_origen = ?, ciudad_destino = ?, precio = ?, fecha = ?, hora = ? WHERE id_ruta = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issdssi", $id_vehiculo, $origen, $destino, $precio, $fecha, $hora, $id_ruta);
                }
            }

            if ($stmt && $stmt->execute()) {
                $status_message = ($_POST['action'] == 'agregar') ? "Ruta agregada correctamente." : "Ruta actualizada correctamente.";
            } elseif ($stmt) {
                $status_message = "Error en la operación: " . $stmt->error;
                $status_type = 'danger';
            }
            if (isset($stmt)) $stmt->close();
        }
    }
}


$sql_rutas = "
    SELECT
        r.*,
        v.placa,
        v.capacidad,
        e.nombre AS nombre_empresa
    FROM
        rutas r
    JOIN
        vehiculos v ON r.id_vehiculo = v.id_vehiculo
    JOIN
        empresas e ON v.id_empresa = e.id_empresa
    ORDER BY
        r.fecha DESC, r.hora ASC
";
$resultado_rutas = $conn->query($sql_rutas);

$sql_vehiculos_list = "
    SELECT
        v.id_vehiculo,
        v.placa,
        v.capacidad,
        e.nombre AS nombre_empresa
    FROM
        vehiculos v
    JOIN
        empresas e ON v.id_empresa = e.id_empresa
    ORDER BY
        e.nombre, v.placa
";
$resultado_vehiculos_list = $conn->query($sql_vehiculos_list);
?>

<div class="container my-5">
    <h1 class="text-red fw-bold mb-5">Rutas</h1>

    <?php if (!empty($status_message)): ?>
        <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $status_message; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#agregarModal">
            <i class="bi bi-geo-alt-fill me-1"></i> Agregar Nueva Ruta
        </button>
    </div>

    <?php if ($resultado_rutas && $resultado_rutas->num_rows > 0): ?>
        <div class="table-responsive shadow-lg rounded">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-red text-white">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Origen/Destino</th>
                        <th class="p-3">Empresa</th>
                        <th class="p-3">Vehículo (Placa)</th>
                        <th class="p-3">Precio</th>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Hora</th>
                        <th class="p-3 text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ruta = $resultado_rutas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ruta['id_ruta']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ruta['ciudad_origen']); ?></strong>
                                a
                                <strong><?php echo htmlspecialchars($ruta['ciudad_destino']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($ruta['nombre_empresa']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($ruta['placa']); ?>
                                (Capacidad: <?php echo htmlspecialchars($ruta['capacidad']); ?>)
                            </td>
                            <td>$<?php echo number_format($ruta['precio'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($ruta['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($ruta['hora']); ?></td>
                            <td class="text-center">
                                <button
                                    class="btn btn-sm btn-outline-primary me-2 edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarModal"
                                    data-id="<?php echo $ruta['id_ruta']; ?>"
                                    data-vehiculo-id="<?php echo htmlspecialchars($ruta['id_vehiculo']); ?>"
                                    data-origen="<?php echo htmlspecialchars($ruta['ciudad_origen']); ?>"
                                    data-destino="<?php echo htmlspecialchars($ruta['ciudad_destino']); ?>"
                                    data-precio="<?php echo htmlspecialchars($ruta['precio']); ?>"
                                    data-fecha="<?php echo htmlspecialchars($ruta['fecha']); ?>"
                                    data-hora="<?php echo htmlspecialchars($ruta['hora']); ?>">
                                    Editar
                                </button>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar esta ruta (ID: <?php echo $ruta['id_ruta']; ?>)? Esto eliminará todas las reservas asociadas.');">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id_ruta" value="<?php echo $ruta['id_ruta']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No se encontraron rutas registradas.
        </div>
    <?php endif; ?>

</div>

<?php
if ($resultado_vehiculos_list) {
    $resultado_vehiculos_list->data_seek(0);
}
?>

<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red text-white">
                <h5 class="modal-title" id="agregarModalLabel">Agregar Nueva Ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="agregar">

                    <div class="mb-3">
                        <label for="id_vehiculo_agregar" class="form-label">Vehículo / Empresa</label>
                        <select class="form-select border-danger" id="id_vehiculo_agregar" name="id_vehiculo" required>
                            <option value="">Seleccione un vehículo</option>
                            <?php
                            if ($resultado_vehiculos_list && $resultado_vehiculos_list->num_rows > 0):
                                while ($veh = $resultado_vehiculos_list->fetch_assoc()): ?>
                                    <option value="<?php echo $veh['id_vehiculo']; ?>">
                                        <?php echo htmlspecialchars($veh['placa']); ?> (<?php echo htmlspecialchars($veh['nombre_empresa']); ?> - Capacidad: <?php echo $veh['capacidad']; ?>)
                                    </option>
                            <?php endwhile;
                            endif; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="origen_agregar" class="form-label">Ciudad Origen</label>
                            <input type="text" class="form-control border-danger" id="origen_agregar" name="ciudad_origen" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="destino_agregar" class="form-label">Ciudad Destino</label>
                            <input type="text" class="form-control border-danger" id="destino_agregar" name="ciudad_destino" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="precio_agregar" class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" class="form-control border-danger" id="precio_agregar" name="precio" min="0" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_agregar" class="form-label">Fecha</label>
                            <input type="date" class="form-control border-danger" id="fecha_agregar" name="fecha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hora_agregar" class="form-label">Hora</label>
                            <input type="time" class="form-control border-danger" id="hora_agregar" name="hora" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-red">Guardar Ruta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editarModalLabel">Editar Ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="actualizar">
                    <input type="hidden" id="id_ruta_editar" name="id_ruta">

                    <div class="mb-3">
                        <label for="id_vehiculo_editar" class="form-label">Vehículo / Empresa</label>
                        <select class="form-select border-primary" id="id_vehiculo_editar" name="id_vehiculo" required>
                            <option value="">Seleccione un vehículo</option>
                            <?php
                            if ($resultado_vehiculos_list) {
                                $resultado_vehiculos_list->data_seek(0);
                                while ($veh = $resultado_vehiculos_list->fetch_assoc()): ?>
                                    <option value="<?php echo $veh['id_vehiculo']; ?>">
                                        <?php echo htmlspecialchars($veh['placa']); ?> (<?php echo htmlspecialchars($veh['nombre_empresa']); ?> - Capacidad: <?php echo $veh['capacidad']; ?>)
                                    </option>
                            <?php endwhile;
                            } ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="origen_editar" class="form-label">Ciudad Origen</label>
                            <input type="text" class="form-control border-primary" id="origen_editar" name="ciudad_origen" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="destino_editar" class="form-label">Ciudad Destino</label>
                            <input type="text" class="form-control border-primary" id="destino_editar" name="ciudad_destino" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="precio_editar" class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" class="form-control border-primary" id="precio_editar" name="precio" min="0" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_editar" class="form-label">Fecha</label>
                            <input type="date" class="form-control border-primary" id="fecha_editar" name="fecha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hora_editar" class="form-label">Hora</label>
                            <input type="time" class="form-control border-primary" id="hora_editar" name="hora" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="javascript/rutas_admin.js"></script>

<?php
$conn->close();
include("footer.php");
?>