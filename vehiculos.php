<?php

include 'database/conexion.php';
include("header.php");

$status_message = '';
$status_type = 'success';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['action']) && $_POST['action'] == 'eliminar' && isset($_POST['id_vehiculo'])) {
        $id_vehiculo = filter_input(INPUT_POST, 'id_vehiculo', FILTER_VALIDATE_INT);

        if ($id_vehiculo) {
            $sql = "DELETE FROM vehiculos WHERE id_vehiculo = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $id_vehiculo);
                if ($stmt->execute()) {
                    $status_message = "Vehículo eliminado correctamente.";
                } else {
                    $status_message = "Error al eliminar el vehículo (puede tener rutas asociadas): " . $conn->error;
                    $status_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && ($_POST['action'] == 'agregar' || $_POST['action'] == 'actualizar')) {
        $id_empresa = filter_input(INPUT_POST, 'id_empresa', FILTER_VALIDATE_INT);
        $placa = filter_input(INPUT_POST, 'placa');
        $tipo_vehiculo = filter_input(INPUT_POST, 'tipo_vehiculo');
        $capacidad = filter_input(INPUT_POST, 'capacidad', FILTER_VALIDATE_INT);
        $tipo_servicio = filter_input(INPUT_POST, 'tipo_servicio');

        if (!$id_empresa || empty($placa) || empty($tipo_vehiculo) || !$capacidad || empty($tipo_servicio)) {
            $status_message = "Error: Todos los campos son obligatorios y deben ser válidos.";
            $status_type = 'danger';
        } else {
            if ($_POST['action'] == 'agregar') {
                $sql = "INSERT INTO vehiculos (id_empresa, placa, tipo_vehiculo, capacidad, tipo_servicio) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issis", $id_empresa, $placa, $tipo_vehiculo, $capacidad, $tipo_servicio);
            } elseif ($_POST['action'] == 'actualizar') {
                $id_vehiculo = filter_input(INPUT_POST, 'id_vehiculo', FILTER_VALIDATE_INT);
                if ($id_vehiculo) {
                    $sql = "UPDATE vehiculos SET id_empresa = ?, placa = ?, tipo_vehiculo = ?, capacidad = ?, tipo_servicio = ? WHERE id_vehiculo = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issisi", $id_empresa, $placa, $tipo_vehiculo, $capacidad, $tipo_servicio, $id_vehiculo);
                }
            }

            if ($stmt && $stmt->execute()) {
                $status_message = ($_POST['action'] == 'agregar') ? "Vehículo agregado correctamente." : "Vehículo actualizado correctamente.";
            } elseif ($stmt) {
                $status_message = "Error en la operación: " . $stmt->error;
                $status_type = 'danger';
            }
            if (isset($stmt)) $stmt->close();
        }
    }
}

$sql_vehiculos = "
    SELECT
        v.id_vehiculo, v.placa, v.tipo_vehiculo, v.capacidad, v.tipo_servicio,
        e.nombre AS nombre_empresa, e.id_empresa
    FROM
        vehiculos v
    JOIN
        empresas e ON v.id_empresa = e.id_empresa
    ORDER BY
        v.placa ASC
";
$resultado_vehiculos = $conn->query($sql_vehiculos);

$sql_empresas = "SELECT id_empresa, nombre FROM empresas ORDER BY nombre ASC";
$resultado_empresas = $conn->query($sql_empresas);
?>

<div class="container my-5">
    <h1 class="text-red fw-bold mb-5">Vehículos</h1>

    <?php if (!empty($status_message)): ?>
        <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $status_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#agregarModal">
            <i class="bi bi-bus-front me-1"></i> Agregar Nuevo Vehículo
        </button>
    </div>

    <?php if ($resultado_vehiculos && $resultado_vehiculos->num_rows > 0): ?>
        <div class="table-responsive shadow-lg rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-red text-white">
                    <tr>
                        <th class="p-3">Placa</th>
                        <th class="p-3">Empresa</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Capacidad</th>
                        <th class="p-3">Servicio</th>
                        <th class="p-3 text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vehiculo = $resultado_vehiculos->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($vehiculo['placa']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['nombre_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['tipo_vehiculo']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['capacidad']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['tipo_servicio']); ?></td>
                            <td class="text-center">
                                <button
                                    class="btn btn-sm btn-outline-primary me-2 edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarModal"
                                    data-id="<?php echo $vehiculo['id_vehiculo']; ?>"
                                    data-placa="<?php echo htmlspecialchars($vehiculo['placa']); ?>"
                                    data-tipo_vehiculo="<?php echo htmlspecialchars($vehiculo['tipo_vehiculo']); ?>"
                                    data-capacidad="<?php echo htmlspecialchars($vehiculo['capacidad']); ?>"
                                    data-servicio="<?php echo htmlspecialchars($vehiculo['tipo_servicio']); ?>"
                                    data-empresa-id="<?php echo htmlspecialchars($vehiculo['id_empresa']); ?>">
                                    Editar
                                </button>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar la placa <?php echo htmlspecialchars($vehiculo['placa']); ?>? Esto eliminará rutas asociadas.');">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculo['id_vehiculo']; ?>">
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
            No se encontraron vehículos registrados.
        </div>
    <?php endif; ?>

</div>

<?php
if ($resultado_empresas) {
    $resultado_empresas->data_seek(0);
}
?>

<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red text-white">
                <h5 class="modal-title" id="agregarModalLabel">Agregar Nuevo Vehículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="agregar">

                    <div class="mb-3">
                        <label for="id_empresa_agregar" class="form-label">Empresa</label>
                        <select class="form-select border-danger" id="id_empresa_agregar" name="id_empresa" required>
                            <option value="">Seleccione una empresa</option>
                            <?php
                            if ($resultado_empresas && $resultado_empresas->num_rows > 0):
                                while ($emp = $resultado_empresas->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['id_empresa']; ?>">
                                        <?php echo htmlspecialchars($emp['nombre']); ?>
                                    </option>
                            <?php endwhile;
                            endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="placa_agregar" class="form-label">Placa</label>
                        <input type="text" class="form-control border-danger" id="placa_agregar" name="placa" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_vehiculo_agregar" class="form-label">tipo_vehiculo</label>
                        <input type="text" class="form-control border-danger" id="tipo_vehiculo_agregar" name="tipo_vehiculo" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacidad_agregar" class="form-label">Capacidad (Asientos)</label>
                            <input type="number" class="form-control border-danger" id="capacidad_agregar" name="capacidad" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="servicio_agregar" class="form-label">Tipo de Servicio</label>
                            <select class="form-select border-danger" id="servicio_agregar" name="tipo_servicio" required>
                                <option value="">Seleccione...</option>
                                <option value="CONVENCIONAL">CONVENCIONAL</option>
                                <option value="PREFERENCIAL">PREFERENCIAL</option>
                                <option value="LUJO">LUJO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-red">Guardar Vehículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editarModalLabel">Editar Vehículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="actualizar">
                    <input type="hidden" id="id_vehiculo_editar" name="id_vehiculo">

                    <div class="mb-3">
                        <label for="id_empresa_editar" class="form-label">Empresa</label>
                        <select class="form-select border-primary" id="id_empresa_editar" name="id_empresa" required>
                            <option value="">Seleccione una empresa</option>
                            <?php
                            if ($resultado_empresas) {
                                $resultado_empresas->data_seek(0);
                                while ($emp = $resultado_empresas->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['id_empresa']; ?>">
                                        <?php echo htmlspecialchars($emp['nombre']); ?>
                                    </option>
                            <?php endwhile;
                            } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="placa_editar" class="form-label">Placa</label>
                        <input type="text" class="form-control border-primary" id="placa_editar" name="placa" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_vehiculo_editar" class="form-label">tipo_vehiculo</label>
                        <input type="text" class="form-control border-primary" id="tipo_vehiculo_editar" name="tipo_vehiculo" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacidad_editar" class="form-label">Capacidad (Asientos)</label>
                            <input type="number" class="form-control border-primary" id="capacidad_editar" name="capacidad" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="servicio_editar" class="form-label">Tipo de Servicio</label>
                            <select class="form-select border-primary" id="servicio_editar" name="tipo_servicio" required>
                                <option value="CONVENCIONAL">CONVENCIONAL</option>
                                <option value="PREFERENCIAL">PREFERENCIAL</option>
                                <option value="LUJO">LUJO</option>
                            </select>
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

<script src="javascript/vehiculos.js"></script>

<?php
$conn->close();
include("footer.php");
?>