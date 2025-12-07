<?php
session_start();
include 'database/conexion.php';

$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if (empty($usuario) || empty($clave)) {
        $mensaje_error = "Por favor, ingrese su usuario y contraseña.";
    } else {
        $clave_md5_ingresada = MD5($clave);

        $sql = "SELECT id_usuario, usuario FROM usuarios WHERE usuario = ? AND clave = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $usuario, $clave_md5_ingresada);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                $fila = $resultado->fetch_assoc();

                $_SESSION["loggedin"] = true;
                $_SESSION["id_usuario"] = $fila['id_usuario'];
                $_SESSION["usuario"] = $fila['usuario'];

                header("location: index.php");
                exit;
            } else {
                $mensaje_error = "Usuario o contraseña incorrectos.";
            }

            $stmt->close();
        } else {
            $mensaje_error = "Error de base de datos al preparar la consulta: " . $conn->error;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-card {
            max-width: 400px;
            padding: 30px;
            border-radius: 10px;
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn-red:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow login-card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4 text-red">Iniciar sesión</h3>

                <?php
                if (!empty($mensaje_error)) {
                    echo '<div class="alert alert-danger text-center">' . $mensaje_error . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="clave" class="form-label">Contraseña</label>
                        <input type="password" name="clave" id="clave" class="form-control" required>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-red">Continuar</button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    <small><a href="index.php">Volver al inicio</a></small>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>