<?php
include 'database/conexion.php';
include("header.php");

$status_message = '';
$status_type = 'success';

$filtro_tipo = $_GET['tipo'] ?? 'TODOS';
$filtro_nombre = $_GET['nombre'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id_mensaje = filter_input(INPUT_POST, 'id_mensaje', FILTER_VALIDATE_INT);

    if ($id_mensaje) {
        $sql = "DELETE FROM mensajes WHERE id_mensaje = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id_mensaje);
            if ($stmt->execute()) {
                $status_message = "Mensaje #{$id_mensaje} eliminado correctamente.";
            } else {
                $status_message = "Error al eliminar el mensaje: " . $conn->error;
                $status_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$where_clauses = [];
$bind_types = '';
$bind_params = [];

if (!empty($filtro_tipo) && $filtro_tipo != 'TODOS') {
    $where_clauses[] = "tipo_mensaje = ?";
    $bind_types .= 's';
    $bind_params[] = $filtro_tipo;
}

if (!empty($filtro_nombre)) {
    $where_clauses[] = "nombre_remitente LIKE ?";
    $bind_types .= 's';
    $bind_params[] = "%" . $filtro_nombre . "%";
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$sql_mensajes = "
    SELECT
        id_mensaje, nombre_remitente, correo_remitente, mensaje, tipo_mensaje
    FROM
        mensajes
    {$where_sql}
    ORDER BY
        id_mensaje DESC
";

$stmt_mensajes = $conn->prepare($sql_mensajes);

if ($stmt_mensajes === FALSE) {
    $status_message = "Error al preparar la consulta: " . $conn->error;
    $status_type = 'danger';
    $resultado_mensajes = null;
} else {
    if ($bind_types) {
        array_unshift($bind_params, $bind_types);

        $params_referencias = array();
        foreach ($bind_params as $key => $value) {
            $params_referencias[$key] = &$bind_params[$key];
        }
        call_user_func_array([$stmt_mensajes, 'bind_param'], $params_referencias);
    }

    $stmt_mensajes->execute();
    $resultado_mensajes = $stmt_mensajes->get_result();
}

$mensaje_seleccionado = null;

if (isset($_GET['ver']) && filter_input(INPUT_GET, 'ver', FILTER_VALIDATE_INT)) {
    $id_ver = filter_input(INPUT_GET, 'ver', FILTER_VALIDATE_INT);

    $sql_detalle = "SELECT nombre_remitente, correo_remitente, mensaje, tipo_mensaje FROM mensajes WHERE id_mensaje = ?";
    if ($stmt_detalle = $conn->prepare($sql_detalle)) {
        $stmt_detalle->bind_param("i", $id_ver);
        $stmt_detalle->execute();
        $resultado_detalle = $stmt_detalle->get_result();

        if ($resultado_detalle->num_rows == 1) {
            $mensaje_seleccionado = $resultado_detalle->fetch_assoc();
            echo '<script>document.addEventListener("DOMContentLoaded", function() { new bootstrap.Modal(document.getElementById("verMensajeModal")).show(); });</script>';
        }
        $stmt_detalle->close();
    }
}
?>

<div class="container my-5">
    <h1 class="text-red fw-bold mb-5">Mensajes y PQRS</h1>

    <?php if (!empty($status_message)): ?>
        <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $status_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title text-red">Filtros de Búsqueda</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="filtro_nombre" class="form-label small">Nombre del Remitente</label>
                    <input type="text" class="form-control" id="filtro_nombre" name="nombre" value="<?php echo htmlspecialchars($filtro_nombre); ?>" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-4">
                    <label for="filtro_tipo" class="form-label small">Tipo</label>
                    <select class="form-select" id="filtro_tipo" name="tipo">
                        <option value="TODOS" <?php echo $filtro_tipo == 'TODOS' ? 'selected' : ''; ?>>TODOS</option>
                        <option value="MENSAJE" <?php echo $filtro_tipo == 'MENSAJE' ? 'selected' : ''; ?>>MENSAJE</option>
                        <option value="PETICION" <?php echo $filtro_tipo == 'PETICION' ? 'selected' : ''; ?>>PETICIÓN</option>
                        <option value="QUEJA" <?php echo $filtro_tipo == 'QUEJA' ? 'selected' : ''; ?>>QUEJA</option>
                        <option value="RECLAMO" <?php echo $filtro_tipo == 'RECLAMO' ? 'selected' : ''; ?>>RECLAMO</option>
                        <option value="SUGERENCIA" <?php echo $filtro_tipo == 'SUGERENCIA' ? 'selected' : ''; ?>>SUGERENCIA</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
    <?php if ($resultado_mensajes && $resultado_mensajes->num_rows > 0): ?>
        <div class="table-responsive shadow-lg rounded">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-red text-white">
                    <tr>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Remitente</th>
                        <th class="p-3">Correo</th>
                        <th class="p-3">Extracto del Mensaje</th>
                        <th class="p-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mensaje = $resultado_mensajes->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold">
                                <?php
                                $badge_color = 'primary';
                                if ($mensaje['tipo_mensaje'] == 'QUEJA' || $mensaje['tipo_mensaje'] == 'RECLAMO') {
                                    $badge_color = 'danger';
                                } elseif ($mensaje['tipo_mensaje'] == 'PETICION') {
                                    $badge_color = 'warning';
                                }
                                ?>
                                <span class="badge bg-<?php echo $badge_color; ?>"><?php echo htmlspecialchars($mensaje['tipo_mensaje']); ?></span>
                            </td>
                            <td class="fw-bold"><?php echo htmlspecialchars($mensaje['nombre_remitente']); ?></td>
                            <td><?php echo htmlspecialchars($mensaje['correo_remitente']); ?></td>
                            <td>
                                <?php
                                echo htmlspecialchars(substr($mensaje['mensaje'], 0, 50)) .
                                    (strlen($mensaje['mensaje']) > 50 ? '...' : '');
                                ?>
                            </td>
                            <td class="text-center">
                                <a
                                    href="?ver=<?php echo $mensaje['id_mensaje']; ?>"
                                    class="btn btn-sm btn-outline-primary me-2">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            No se encontraron mensajes que coincidan con los filtros.
        </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="verMensajeModal" tabindex="-1" aria-labelledby="verMensajeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="verMensajeModalLabel">Detalle del Mensaje</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($mensaje_seleccionado): ?>
                    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($mensaje_seleccionado['tipo_mensaje'] ?? 'MENSAJE'); ?></p>
                    <p><strong>Remitente:</strong> <?php echo htmlspecialchars($mensaje_seleccionado['nombre_remitente']); ?></p>
                    <p><strong>Correo:</strong> <a href="mailto:<?php echo htmlspecialchars($mensaje_seleccionado['correo_remitente']); ?>"><?php echo htmlspecialchars($mensaje_seleccionado['correo_remitente']); ?></a></p>
                    <hr>
                    <p class="fw-bold">Mensaje:</p>
                    <div class="p-3 border rounded bg-light">
                        <?php echo nl2br(htmlspecialchars($mensaje_seleccionado['mensaje'])); ?>
                    </div>
                <?php else: ?>
                    <p>Error: No se pudo cargar el mensaje.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
if ($stmt_mensajes) $stmt_mensajes->close();
$conn->close();
include("footer.php");
?>