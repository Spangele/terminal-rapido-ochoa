<?php
include 'database/conexion.php';
include("header.php");

$status_message = '';
$status_type = '';
$tipo_mensaje = 'MENSAJE';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = filter_input(INPUT_POST, 'nombre_remitente');
    $correo = filter_input(INPUT_POST, 'correo_remitente', FILTER_SANITIZE_EMAIL);
    $cuerpo_mensaje = filter_input(INPUT_POST, 'mensaje',);
    $tipo_mensaje = filter_input(INPUT_POST, 'tipo_mensaje') ?? 'MENSAJE';

    if (empty($nombre) || empty($correo) || empty($cuerpo_mensaje)) {
        $status_message = "Error: Todos los campos son obligatorios.";
        $status_type = 'danger';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $status_message = "Error: El formato del correo electrónico no es válido.";
        $status_type = 'danger';
    } else {
        $sql = "INSERT INTO mensajes (nombre_remitente, correo_remitente, mensaje, tipo_mensaje) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $nombre, $correo, $cuerpo_mensaje, $tipo_mensaje);

            if ($stmt->execute()) {
                $status_message = "¡Mensaje enviado con éxito! Tipo: " . htmlspecialchars($tipo_mensaje);
                $status_type = 'success';

                $nombre = $correo = $cuerpo_mensaje = '';
                $tipo_mensaje = 'MENSAJE';
            } else {
                $status_message = "Error al guardar el mensaje: " . $stmt->error;
                $status_type = 'danger';
            }
            $stmt->close();
        } else {
            $status_message = "Error interno del servidor al preparar la consulta.";
            $status_type = 'danger';
        }
    }
}
?>

<main>
    <section class="container my-5">
        <h2 class="text-red fw-bold mb-3">Oficina Principal</h2>
        <p><strong>Terminal de Transporte de Cartagena</strong></p>
        <p>Carrera 71 # 31-250, Cartagena, Bolívar</p>
        <p>Tel: +57 (605) 653 0000</p>
        <p>Email: contacto@rapidoochoa.com</p>
        <img
            src="imagenes/map.png"
            alt="Mapa Terminal Cartagena"
            class="img-fluid border border-danger" />
    </section>

    <section class="container my-5">
        <h2 class="text-red fw-bold mb-3">Oficinas Regionales</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card border-danger shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold text-red">Medellín</h5>
                        <p>Terminal del Sur, Local 25</p>
                        <p>Tel: +57 (604) 444 1234</p>
                        <p>Email: medellin@rapidoochoa.com</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-danger shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold text-red">Bogotá</h5>
                        <p>Terminal Salitre, Módulo 3</p>
                        <p>Tel: +57 (601) 320 4567</p>
                        <p>Email: bogota@rapidoochoa.com</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="text-red fw-bold mb-4">Teléfonos Regionales</h2>
            <div class="row">
                <div class="col-md-4">
                    <p>
                        <strong>Caribe:</strong><br />Cartagena, Barranquilla,
                        Sincelejo<br />+57 (605) 655 0000
                    </p>
                </div>
                <div class="col-md-4">
                    <p>
                        <strong>Centro:</strong><br />Bogotá, Medellín<br />+57 (601) 310
                        0000
                    </p>
                </div>
                <div class="col-md-4">
                    <p>
                        <strong>Costa Norte:</strong><br />Montería, Caucasia<br />+57
                        (604) 780 0000
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <h2 class="text-red fw-bold mb-3">Envíanos un Mensaje o PQRS</h2>
        <?php if (!empty($status_message)): ?>
            <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $status_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input
                        type="text"
                        class="form-control border-danger"
                        placeholder="Tu nombre"
                        name="nombre_remitente"
                        value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
                        required />
                </div>
                <div class="col-md-6">
                    <input
                        type="email"
                        class="form-control border-danger"
                        placeholder="Tu correo"
                        name="correo_remitente"
                        value="<?php echo htmlspecialchars($correo ?? ''); ?>"
                        required />
                </div>
            </div>

            <div class="mb-3">
                <label for="tipo_mensaje" class="form-label small fw-bold">Tipo de Consulta</label>
                <select class="form-select border-danger" id="tipo_mensaje" name="tipo_mensaje" required>
                    <option value="MENSAJE" <?php echo ($tipo_mensaje == 'MENSAJE' ? 'selected' : ''); ?>>Mensaje (General)</option>
                    <option value="PETICION" <?php echo ($tipo_mensaje == 'PETICION' ? 'selected' : ''); ?>>Petición</option>
                    <option value="QUEJA" <?php echo ($tipo_mensaje == 'QUEJA' ? 'selected' : ''); ?>>Queja</option>
                    <option value="RECLAMO" <?php echo ($tipo_mensaje == 'RECLAMO' ? 'selected' : ''); ?>>Reclamo</option>
                    <option value="SUGERENCIA" <?php echo ($tipo_mensaje == 'SUGERENCIA' ? 'selected' : ''); ?>>Sugerencia</option>
                </select>
            </div>

            <div class="mb-3">
                <textarea
                    class="form-control border-danger"
                    rows="5"
                    placeholder="Escribe tu mensaje"
                    name="mensaje"
                    required><?php echo htmlspecialchars($cuerpo_mensaje ?? ''); ?></textarea>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-red btn-lg">
                    <i class="bi bi-send-fill me-2"></i> Enviar Mensaje
                </button>
            </div>
        </form>
    </section>
</main>

<?php
$conn->close();
include("footer.php");
?>