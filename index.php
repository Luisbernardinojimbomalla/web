<?php
// Definir las variables de conexión
$servidor = "localhost";     // Dirección del servidor (usualmente localhost en desarrollo local)
$usuario = "root";           // Nombre de usuario (por defecto es 'root' en XAMPP)
$clave = "";                 // Contraseña (en XAMPP, por defecto no tiene contraseña) 
$baseDeDatos = "ejemplo";    // Nombre de la base de datos

// Crear la conexión
$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

// Verificar si la conexión fue exitosa
if (!$enlace) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lógica de registro de usuario
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Verificar que no haya campos vacíos
        if (empty($username) || empty($email) || empty($password)) {
            $message = "Por favor, completa todos los campos.";
            $alert_type = "error";
        } else {
            // Verificar si el nombre de usuario ya existe
            $query = "SELECT * FROM usuarios WHERE username = ?";
            $stmt = $enlace->prepare($query);  // Usar $enlace aquí, no $conn
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // El nombre de usuario ya está registrado
                $message = "El nombre de usuario ya está registrado.";
                $alert_type = "error";
            } else {
                // Insertar el nuevo usuario
                $query = "INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)";
                $stmt = $enlace->prepare($query);  // Usar $enlace aquí también
                $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Encriptar la contraseña
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    // Registro exitoso
                    $message = "Usuario registrado exitosamente.";
                    $alert_type = "success";
                } else {
                    // Error al registrar usuario
                    $message = "Error al registrar el usuario: " . $stmt->error;
                    $alert_type = "error";
                }
                $stmt->close();
            }
        }
    }

    // Lógica de inicio de sesión de usuario
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Verificar que no haya campos vacíos
        if (empty($username) || empty($password)) {
            $message = "Por favor, completa todos los campos.";
            $alert_type = "error";
        } else {
            // Verificar si el usuario existe
            $query = "SELECT * FROM usuarios WHERE username = ?";
            $stmt = $enlace->prepare($query);  // Usar $enlace aquí también
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Verificar la contraseña
                if (password_verify($password, $user['password'])) {
                    // Inicio de sesión exitoso
                    $message = "Inicio de sesión exitoso.";
                    $alert_type = "success";
                    // Redirigir al menú
                    header("Location: Menu.php");
                    exit;  // Asegurarse de que el script se detenga después de la redirección
                } else {
                    // Contraseña incorrecta
                    $message = "Contraseña incorrecta.";
                    $alert_type = "error";
                }
            } else {
                // El nombre de usuario no existe
                $message = "El nombre de usuario no existe.";
                $alert_type = "error";
            }

            $stmt->close();
        }
    }
}

// Verificar si la conexión aún está activa antes de cerrarla
if ($enlace) {
    mysqli_close($enlace);  // Usar $enlace aquí
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión y Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #fa824c, #f0f3f4);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .alert {
            width: 100%;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            border-radius: 5px;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            display: none;
        }

        .alert.success {
            background-color: #28a745;
            color: white;
        }

        .alert.error {
            background-color: #dc3545;
            color: white;
        }

        .container {
            background: #ffffff;
            width: 350px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            padding: 30px 20px;
        }

        .header {
            background: #22313f;
            color: white;
            padding: 20px 0;
            border-radius: 15px 15px 0 0;
        }

        .header h1 {
            font-size: 24px;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            margin-top: -30px;
            margin-bottom: 10px;
            border-radius: 50%;
            border: 3px solid #ffffff;
        }

        .form {
            margin-top: 20px;
        }

        .form input {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .form button {
            width: 100%;
            padding: 10px;
            background: #fa824c;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form button:hover {
            background: #ff7043;
        }

        .toggle-link {
            color: #fa824c;
            font-size: 14px;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
   <!-- Mostrar alerta si hay un mensaje -->
    <?php if (isset($message)): ?>
        <div class="alert <?php echo $alert_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="container" id="login-container">
        <div class="header">
            <h1>Mi Cuenta</h1>
        </div>
        <img src="https://via.placeholder.com/60" alt="Icono de Perfil" class="profile-img">
        <div class="form">
            <form action="index.php" method="POST">
                <input type="text" placeholder="Nombre de usuario" name="username" required>
                <input type="password" placeholder="Contraseña" name="password" required>
                <button type="submit" name="login">Iniciar sesión</button>
            </form>
            <p class="toggle-link" onclick="showRegisterForm()">¿No tienes una cuenta? Regístrate</p>
        </div>
    </div>

    <div class="container" id="register-container" style="display: none;">
        <div class="header">
            <h1>Regístrate</h1>
        </div>
        <form action="index.php" method="POST" class="form">
            <input type="text" placeholder="Nombre de usuario" name="username" required>
            <input type="email" placeholder="Correo electrónico" name="email" required>
            <input type="password" placeholder="Contraseña" name="password" required>
            <button type="submit" name="register">Registrarse</button>
            <p class="toggle-link" onclick="showLoginForm()">¿Ya tienes una cuenta? Inicia sesión</p>
        </form>
    </div>

    <script>
        // Mostrar alerta si hay un mensaje
        <?php if (isset($message)): ?>
            document.querySelector('.alert').style.display = 'block';
            setTimeout(function() {
                document.querySelector('.alert').style.display = 'none';
            }, 5000); // La alerta desaparece después de 5 segundos
        <?php endif; ?>

        function showRegisterForm() {
            document.getElementById("login-container").style.display = "none";
            document.getElementById("register-container").style.display = "block";
        }

        function showLoginForm() {
            document.getElementById("login-container").style.display = "block";
            document.getElementById("register-container").style.display = "none";
        }

        // Deshabilitar tanto el botón de retroceso como el de avance en el navegador
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };

        // Deshabilitar el avance del historial al intentar navegar hacia adelante
        window.onload = function() {
            setInterval(function() {
                window.history.pushState(null, "", window.location.href);
            }, 100);
        };
    </script>
</body>
</html>