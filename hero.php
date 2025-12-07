<?php
$ciudades_disponibles = [
    'Santa Marta',
    'Riohacha',
    'Cartagena',
    'Barranquilla',
    'Bucaramanga',
    'Bogotá',
    'Medellín'
];

sort($ciudades_disponibles);
?>

<header class="bg-blue text-white text-center py-5">
    <div class="container">
        <h1 class="fw-bold mb-3">Compre sus tiquetes de bus en Servicio de Rápido Ochoa</h1>
        <p class="lead mb-4">Ingrese su origen, destino y fecha para consultar disponibilidad y capacidad</p>

        <form action="rutas.php" method="GET" class="row g-2 justify-content-center">
            <div class="col-md-3">
                <select name="origen" class="form-control" required>
                    <option value="" disabled selected>Origen</option>
                    <?php foreach ($ciudades_disponibles as $ciudad): ?>
                        <option value="<?php echo htmlspecialchars($ciudad); ?>">
                            <?php echo htmlspecialchars($ciudad); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="destino" class="form-control" required>
                    <option value="" disabled selected>Destino</option>
                    <?php foreach ($ciudades_disponibles as $ciudad): ?>
                        <option value="<?php echo htmlspecialchars($ciudad); ?>">
                            <?php echo htmlspecialchars($ciudad); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="fecha" class="form-control" required>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-red w-100">Buscar</button>
            </div>
        </form>
    </div>

    <div class="hero-img position-relative mt-4 row justify-content-center h-50">
        <img src="imagenes/banner.png" class="w-100 rounded  col-md-12 banner-img" alt="Banner Hero">
    </div>
</header>