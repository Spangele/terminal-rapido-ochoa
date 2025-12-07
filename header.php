<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaActual = basename($_SERVER['PHP_SELF']);

$seo = [
    "title"       => "Rápido Ochoa S.A. | Transporte Intermunicipal",
    "description" => "Consulta rutas desde Cartagena, tiquetes, horarios de transporte intermunicipal y reserva fácilmente con Rápido Ochoa.",
    "keywords"    => "rutas desde Cartagena, tiquetes Rápido Ochoa, transporte intermunicipal",
    "url"         => "https://rapidoochoa.com/"
];

switch ($paginaActual) {

    case "index.php":
        $seo["title"]       = "Rutas desde Cartagena | Tiquetes y Horarios Rápido Ochoa";
        $seo["description"] = "Consulta rutas desde Cartagena, horarios actualizados y compra tus tiquetes de transporte intermunicipal con Rápido Ochoa.";
        $seo["keywords"]    = "rutas desde Cartagena, horarios de buses, tiquetes en Cartagena";
        $seo["url"]         = "https://tudominio.com/index.php";
        break;

    case "horarios.php":
        $seo["title"]       = "Horarios de Transporte Intermunicipal | Rápido Ochoa";
        $seo["description"] = "Revisa los horarios de salida desde Cartagena hacia diferentes destinos. Información actualizada diariamente.";
        $seo["keywords"]    = "horarios de transporte, buses Cartagena, horarios Rápido Ochoa";
        $seo["url"]         = "https://tudominio.com/horarios.php";
        break;

    case "contacto.php":
        $seo["title"]       = "Contacto y Reservas | Rápido Ochoa";
        $seo["description"] = "Comunícate con nosotros para reservas, información de rutas y atención al cliente.";
        $seo["keywords"]    = "contacto Rápido Ochoa, reservas de buses, transporte Cartagena";
        $seo["url"]         = "https://tudominio.com/contacto.php";
        break;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   
    <title><?= $seo["title"] ?></title>
    <meta name="description" content="<?= $seo["description"] ?>">
    <meta name="keywords" content="<?= $seo["keywords"] ?>">
    <meta name="author" content="Salma Angel">

    <meta property="og:title" content="<?= $seo["title"] ?>">
    <meta property="og:description" content="<?= $seo["description"] ?>">
    <meta property="og:url" content="<?= $seo["url"] ?>">
    <meta property="og:type" content="website">



    <meta name="description" 
          content="Consulta rutas desde Cartagena, tiquetes de Rápido Ochoa, horarios actualizados y transporte intermunicipal. Reserva fácil y rápido.">
    <meta name="keywords" content="rutas desde Cartagena, tiquetes Rápido Ochoa, horarios de buses, transporte intermunicipal">
    <meta name="author" content="Salma Angel">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-red border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Rápido Ochoa S.A.</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual == 'index.php' ? 'active fw-bold' : '' ?> text-dark" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual == 'horarios.php' ? 'active fw-bold' : '' ?> text-dark" href="horarios.php">Horarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual == 'contacto.php' ? 'active fw-bold' : '' ?> text-dark" href="contacto.php">Contacto</a>
                    </li>

                    <?php if (isset($_SESSION['usuario'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-danger" href="#" role="button" data-bs-toggle="dropdown">
                                Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="empresas.php">Empresas</a></li>
                                <li><a class="dropdown-item" href="vehiculos.php">Vehículos</a></li>
                                <li><a class="dropdown-item" href="rutas_admin.php">Rutas</a></li>
                                <li><a class="dropdown-item" href="reservas.php">Reservas</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="mensajes.php">Mensajes</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="login.php">Administrador</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>