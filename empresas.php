<?php
include 'database/conexion.php';
include("header.php");

$mensaje = '';
$tipo = 'success';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar_empresa') {
        $nombre = trim($_POST['nombre']);
        $telefono = trim($_POST['telefono']);

        if ($nombre && $telefono) {
            $stmt = $conn->prepare("INSERT INTO empresas (nombre, telefono) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $telefono);
            $mensaje = $stmt->execute() ? "La empresa fue agregada correctamente." : "Error al agregar la empresa.";
            $stmt->close();
        } else {
            $mensaje = "Todos los campos son obligatorios.";
            $tipo = 'warning';
        }
    }

    if ($accion === 'eliminar_empresa' && !empty($_POST['id_empresa'])) {
        $id_empresa = (int)$_POST['id_empresa'];
        $stmt = $conn->prepare("DELETE FROM empresas WHERE id_empresa=?");
        $stmt->bind_param("i", $id_empresa);
        $mensaje = $stmt->execute() ? "La empresa fue eliminada correctamente." : "No se pudo eliminar la empresa.";
        $stmt->close();
    }

    if ($accion === 'agregar_horario') {
        $id_empresa = (int)$_POST['id_empresa'];
        $dia = $_POST['dia'];
        $inicio = $_POST['hora_inicio'];
        $fin = $_POST['hora_fin'];

        if ($id_empresa && $dia && $inicio && $fin) {
            $stmt = $conn->prepare("INSERT INTO horarios (id_empresa, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_empresa, $dia, $inicio, $fin);
            $mensaje = $stmt->execute() ? "El horario fue agregado correctamente." : "Error al agregar el horario.";
            $stmt->close();
        } else {
            $mensaje = "Todos los campos del horario son obligatorios.";
            $tipo = 'warning';
        }
    }

    if ($accion === 'eliminar_horario' && !empty($_POST['id_horario'])) {
        $id_horario = (int)$_POST['id_horario'];
        $stmt = $conn->prepare("DELETE FROM horarios WHERE id_horario=?");
        $stmt->bind_param("i", $id_horario);
        $mensaje = $stmt->execute() ? "El horario fue eliminado correctamente." : "Error al eliminar el horario.";
        $stmt->close();
    }
}

$empresas = $conn->query("SELECT * FROM empresas ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-5">
    <h2 class="text-danger fw-bold mb-5">Empresas y Horarios</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo; ?> text-center"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white">Agregar nueva empresa</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="agregar_empresa">
                <div class="col-md-5">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-danger w-100">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Empresas registradas</div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Horarios</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empresas)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No hay empresas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($empresa['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($empresa['telefono']); ?></td>
                                <td>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM horarios WHERE id_empresa=? ORDER BY FIELD(dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')");
                                    $stmt->bind_param("i", $empresa['id_empresa']);
                                    $stmt->execute();
                                    $horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    $stmt->close();
                                    ?>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Día</th>
                                                <th>Inicio</th>
                                                <th>Fin</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($horarios)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Sin horarios</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($horarios as $h): ?>
                                                    <tr>
                                                        <td><?php echo $h['dia_semana']; ?></td>
                                                        <td><?php echo $h['hora_inicio']; ?></td>
                                                        <td><?php echo $h['hora_fin']; ?></td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="accion" value="eliminar_horario">
                                                                <input type="hidden" name="id_horario" value="<?php echo $h['id_horario']; ?>">
                                                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <tr>
                                                <form method="POST" class="row g-1 align-items-center">
                                                    <input type="hidden" name="accion" value="agregar_horario">
                                                    <input type="hidden" name="id_empresa" value="<?php echo $empresa['id_empresa']; ?>">
                                                    <td>
                                                        <select name="dia" class="form-select form-select-sm" required>
                                                            <option value="">Día</option>
                                                            <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $d): ?>
                                                                <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="time" name="hora_inicio" class="form-control form-control-sm" required></td>
                                                    <td><input type="time" name="hora_fin" class="form-control form-control-sm" required></td>
                                                    <td><button class="btn btn-sm btn-success w-100">Agregar</button></td>
                                                </form>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('¿Eliminar esta empresa y sus horarios asociados?')">
                                        <input type="hidden" name="accion" value="eliminar_empresa">
                                        <input type="hidden" name="id_empresa" value="<?php echo $empresa['id_empresa']; ?>">
                                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
include("footer.php");
?>