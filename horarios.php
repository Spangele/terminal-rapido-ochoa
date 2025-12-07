<?php
include 'database/conexion.php';

$sql = "
    SELECT
        e.nombre AS nombre_empresa,
        e.telefono,
        h.dia_semana,
        TIME_FORMAT(h.hora_inicio, '%h:%i %p') AS hora_inicio,
        TIME_FORMAT(h.hora_fin, '%h:%i %p') AS hora_fin
    FROM
        empresas e
    JOIN
        horarios h ON e.id_empresa = h.id_empresa
    ORDER BY
        e.nombre, FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')
";

$resultado = $conn->query($sql);
if (!$resultado) {
    die("Error en la consulta de horarios: " . $conn->error);
}

$horarios_agrupados = [];
while ($fila = $resultado->fetch_assoc()) {
    $nombre = $fila['nombre_empresa'];

    if (!isset($horarios_agrupados[$nombre])) {
        $horarios_agrupados[$nombre] = [
            'telefono' => $fila['telefono'],
            'horarios' => []
        ];
    }

    $horarios_agrupados[$nombre]['horarios'][] = [
        'dia' => $fila['dia_semana'],
        'inicio' => $fila['hora_inicio'],
        'fin' => $fila['hora_fin']
    ];
}

$conn->close();
?>
<?php include("header.php"); ?>

<main>
    <div class="container my-5">
        <h2 class="text-center mb-5">⏰ Horarios de Atención de Empresas de Transporte</h2>

        <?php if (empty($horarios_agrupados)): ?>
            <div class="alert alert-warning text-center">No hay información de horarios disponible.</div>
        <?php else: ?>

            <?php foreach ($horarios_agrupados as $nombre_empresa => $data): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h3 class="h5 mb-0"><?php echo htmlspecialchars($nombre_empresa); ?></h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            📞 Teléfono de contacto: <?php echo htmlspecialchars($data['telefono'] ?? 'N/A'); ?>
                        </p>
                        <h4 class="h6 mt-3">Horario de Atención:</h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($data['horarios'] as $horario): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo htmlspecialchars($horario['dia']); ?></span>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($horario['inicio']); ?> - <?php echo htmlspecialchars($horario['fin']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

</main>
<?php
include("footer.php");
?>