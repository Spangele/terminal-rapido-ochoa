<?php
include 'database/conexion.php';
include("header.php");

$status_message = '';
$status_type = 'success';
$filtro_estado = $_GET['estado'] ?? 'ACTIVA';
$filtro_nombre = $_GET['nombre'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'cancelar') {
    $id_reserva = filter_input(INPUT_POST, 'id_reserva', FILTER_VALIDATE_INT);

    if ($id_reserva) {
        $sql = "UPDATE reservas SET estado = 'CANCELADA' WHERE id_reserva = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id_reserva);
            if ($stmt->execute()) {
                $status_message = "Reserva #{$id_reserva} ha sido CANCELADA exitosamente.";
                $status_type = 'warning';
            } else {
                $status_message = "Error al cancelar la reserva: " . $conn->error;
                $status_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$where_clauses = [];
$bind_types = '';
$bind_params = [];

if (!empty($filtro_estado) && $filtro_estado != 'TODAS') {
    $where_clauses[] = "res.estado = ?";
    $bind_types .= 's';
    $bind_params[] = $filtro_estado;
}

if (!empty($filtro_nombre)) {
    $where_clauses[] = "res.nombre_cliente LIKE ?";
    $bind_types .= 's';
    $bind_params[] = "%" . $filtro_nombre . "%";
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$sql_reservas = "
    SELECT
        res.id_reserva, res.nombre_cliente, res.cantidad_asientos_reservados, res.estado,
        r.ciudad_origen, r.ciudad_destino, r.fecha, r.hora,
        v.placa,
        e.nombre AS nombre_empresa
    FROM
        reservas res
    JOIN
        rutas r ON res.id_ruta = r.id_ruta
    JOIN
        vehiculos v ON r.id_vehiculo = v.id_vehiculo
    JOIN
        empresas e ON v.id_empresa = e.id_empresa
    {$where_sql}
    ORDER BY
        res.id_reserva DESC
";

$stmt_reservas = $conn->prepare($sql_reservas);

if ($stmt_reservas === FALSE) {
    $status_message = $status_message ?: "Error al preparar la consulta: " . $conn->error;
    $status_type = 'danger';
    $resultado_reservas = null;
} else {
    if ($bind_types) {
        $params_referencias = array();
        array_unshift($bind_params, $bind_types);
        foreach ($bind_params as $key => $value) {
            $params_referencias[$key] = &$bind_params[$key];
        }
        call_user_func_array([$stmt_reservas, 'bind_param'], $params_referencias);
    }

    $stmt_reservas->execute();
    $resultado_reservas = $stmt_reservas->get_result();

    if ($resultado_reservas === FALSE) {
        $status_message = $status_message ?: "Error al cargar el listado de reservas: " . $conn->error;
        $status_type = 'danger';
        $resultado_reservas = null;
    }
}
?>

<div class="container my-5">
    <h1 class="text-red fw-bold mb-5">Reservas</h1>

    <?php if (!empty($status_message)): ?>
        <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $status_message; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title text-red">Filtros de Búsqueda</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="filtro_nombre" class="form-label small">Nombre del Cliente</label>
                    <input type="text" class="form-control" id="filtro_nombre" name="nombre" value="<?php echo htmlspecialchars($filtro_nombre); ?>" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-4">
                    <label for="filtro_estado" class="form-label small">Estado de la Reserva</label>
                    <select class="form-select" id="filtro_estado" name="estado">
                        <option value="ACTIVA" <?php echo $filtro_estado == 'ACTIVA' ? 'selected' : ''; ?>>ACTIVA</option>
                        <option value="CANCELADA" <?php echo $filtro_estado == 'CANCELADA' ? 'selected' : ''; ?>>CANCELADA</option>
                        <option value="TODAS" <?php echo $filtro_estado == 'TODAS' ? 'selected' : ''; ?>>TODAS</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
    <?php if ($resultado_reservas && $resultado_reservas->num_rows > 0): ?>
        <div class="table-responsive shadow-lg rounded">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-red text-white">
                    <tr>
                        <th class="p-3">Cliente</th>
                        <th class="p-3">Asientos</th>
                        <th class="p-3">Ruta</th>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Empresa (Placa)</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reserva = $resultado_reservas->fetch_assoc()): ?>
                        <tr class="<?php echo $reserva['estado'] == 'CANCELADA' ? 'table-secondary text-muted' : ''; ?>">
                            <td class="fw-bold"><?php echo htmlspecialchars($reserva['nombre_cliente']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($reserva['cantidad_asientos_reservados']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['ciudad_origen']) . ' a ' . htmlspecialchars($reserva['ciudad_destino']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['fecha']) . ' ' . substr($reserva['hora'], 0, 5); ?></td>
                            <td><?php echo htmlspecialchars($reserva['nombre_empresa']) . ' (' . htmlspecialchars($reserva['placa']) . ')'; ?></td>
                            <td>
                                <?php if ($reserva['estado'] == 'ACTIVA'): ?>
                                    <span class="badge bg-success">ACTIVA</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">CANCELADA</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($reserva['estado'] == 'ACTIVA'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('ADVERTENCIA: ¿Confirma la cancelación de la reserva #<?php echo $reserva['id_reserva']; ?>?');">
                                        <input type="hidden" name="action" value="cancelar">
                                        <input type="hidden" name="id_reserva" value="<?php echo $reserva['id_reserva']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Cancelar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            No se encontraron reservas que coincidan con los filtros.
        </div>
    <?php endif; ?>

</div>

<?php
$conn->close();
include("footer.php");
?>