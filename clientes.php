<?php
// Definir las variables de conexión
$servidor = "localhost";     // Dirección del servidor (usualmente localhost en desarrollo local)
$usuario = "root";           // Nombre de usuario (por defecto es 'root' en XAMPP)
$clave = "";                 // Contraseña (en XAMPP, por defecto no tiene contraseña)
$baseDeDatos = "ejemplo";    // Nombre de la base de datos

// Crear la conexión
$conn = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

// Verificar si la conexión fue exitosa
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}


// Registrar los datos si el formulario es enviado
// Registrar los datos si el formulario es enviado
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "add") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    // Usar INSERT IGNORE para evitar el error de duplicado
    $sql_insert = "INSERT IGNORE INTO clientes (nombre, apellido, email, telefono, direccion, fecha_nacimiento) 
                   VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', '$fecha_nacimiento')";

    $conn->query($sql_insert);
}


// Eliminar registro si se envía la acción de eliminar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "delete") {
    $id = $_POST['id'];
    $sql_delete = "DELETE FROM clientes WHERE id = $id";
    $conn->query($sql_delete);
}

// Consulta SQL para obtener los datos de los clientes
$sql = "SELECT id, nombre, apellido, email, telefono, direccion, fecha_nacimiento FROM clientes";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <style>
        body {
            margin: 0;
            font-family: sans-serif;
            background-color: #f4f6f9;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            background-color:rgb(0, 0, 0);
            color: #fff;
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: start;
        }
        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3c4858;
        }
        .sidebar a span {
            font-size: 16px;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .header .title {
            font-size: 24px;
            font-weight: bold;
        }
        .header .user {
            position: relative;
        }
        .header .user span {
            cursor: pointer;
        }
        .header .dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            z-index: 1000;
        }
        .header .dropdown button {
            background: none;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            text-align: left;
            width: 100%;
        }
        .header .dropdown button:hover {
            background-color: #f1f1f1;
        }
        .logo-img {
    width: 250px; /* Cambia este valor para aumentar o disminuir el ancho */
    height: auto; /* Esto asegura que mantenga sus proporciones */
    margin-bottom: 20px; /* Opcional: un margen para separarlo de otros elementos */
}
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);  /* Dos botones por fila */
            grid-gap: 20px;
            width: 100%;
            margin-top: 20px;
        }
        .grid .item {
            background: linear-gradient(to top left, #4caf50, #81c784);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 60px;  /* Ajuste del tamaño del botón */
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 16px;  /* Ajuste de tamaño de texto */
            text-align: center;
        }
        .grid .item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }
        /* Estilo para el formulario */
        .form-container {
            display: flex;
            flex-direction: column;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 500px; /* Reducción del tamaño del formulario */
            margin-bottom: 20px;
            margin-top: 30px; /* Espacio adicional en la parte superior */
            margin-left: auto;
            margin-right: auto;
        }
        .form-container input,
        .form-container textarea,
        .form-container select {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
        }
        .form-container button {
            padding: 8px 16px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
        }
        .form-container button.cancel {
            background-color: #f44336;
        }
        /* Estilo para la tabla de clientes */
        #clientesTable {
            margin-top: 40px; /* Espacio adicional para separar la tabla de los botones */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        /* Estilo para el formulario */
.form-container {
    display: flex;
    flex-direction: column;
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 500px; /* Tamaño fijo del formulario */
    margin-bottom: 20px;
    margin-top: 30px;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box; /* Evita que el padding desborde el contenedor */
}

.form-container input,
.form-container textarea,
.form-container select {
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box; /* Asegura que el ancho incluya padding y bordes */
    max-width: 100%; /* Evita que el contenido sobresalga del contenedor */
    overflow: hidden; /* Impide desbordes visuales */
}

.form-container textarea {
    resize: vertical; /* Solo permite redimensionar verticalmente */
    max-height: 150px; /* Altura máxima para evitar que el textarea crezca demasiado */
}

.form-container button {
    padding: 8px 16px;
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
    font-size: 14px;
}

.form-container button.cancel {
    background-color: #f44336;
}
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
        <img src="http://localhost/ejemplo/FACTURA/LOGOBLANCO.PNG" alt="Logo" width="150" class="logo-img">
            <ul>
                <li><a href="Menu.php"><span>🏠</span> Inicio</a></li>
                <li><a href="clientes.php"><span>👥</span> Clientes</a></li>
                <li><a href="ventas.php"><span>🛒</span> Ventas</a></li>
                <li><a href="http://localhost/ejemplo/FACTURA/facturacion.php"><span>💳</span> Facturación</a></li>
            </ul>
        </div>
        <div class="content">
            <div class="header">
                <div class="title">Clientes</div>
                <div class="user" onclick="toggleDropdown(event)">
                    <span>👤 Usuario</span>
                    <div class="dropdown" id="dropdownMenu">
                    <button onclick="window.location.href='http://localhost/ejemplo/index.php';">Cerrar sesión</button>
                    </div>
                </div>
            </div>

            <!-- Grid de opciones -->
            <div class="grid">
                <div class="item" onclick="toggleView('form')">Nuevo Cliente</div>
                <div class="item" onclick="toggleView('table')">Historial de Clientes</div>
            </div>

            <!-- Formulario de Cliente -->
            <div class="form-container" id="formContainer" style="display: block;">
            <form method="POST" action="">
    <input type="hidden" name="action" value="add">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido" placeholder="Apellido" required>
    <input type="email" name="email" placeholder="Correo Electrónico" required>
    <input type="text" name="telefono" placeholder="Teléfono" required>
    <textarea name="direccion" placeholder="Dirección" required></textarea>
    <input type="date" name="fecha_nacimiento" required>
    <button type="submit">Guardar</button>
    <button type="button" class="cancel" onclick="clearForm()">Cancelar</button>
</form>
            </div>

            <!-- Tabla de Clientes -->
            <div id="clientesTable" style="display: none;">
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Fecha de Nacimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . $row['nombre'] . "</td>
                        <td>" . $row['apellido'] . "</td>
                        <td>" . $row['email'] . "</td>
                        <td>" . $row['telefono'] . "</td>
                        <td>" . $row['direccion'] . "</td>
                        <td>" . $row['fecha_nacimiento'] . "</td>
                        <td>
                            <form method='POST' action='' style='display:inline-block;'>
                                <input type='hidden' name='id' value='" . $row['id'] . "'>
                                <input type='hidden' name='action' value='delete'>
                                <button type='submit' style='background-color:#f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;'>Eliminar</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay clientes registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
        </div>
    </div>

    <script>
        function toggleDropdown(event) {
            var dropdown = document.getElementById("dropdownMenu");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            event.stopPropagation();
        }

        function toggleView(view) {
            if (view === 'form') {
                document.getElementById('formContainer').style.display = 'block';
                document.getElementById('clientesTable').style.display = 'none';
            } else if (view === 'table') {
                document.getElementById('formContainer').style.display = 'none';
                document.getElementById('clientesTable').style.display = 'block';
            }
        }

        function clearForm() {
            document.querySelector('form').reset();
        }

        // Cerrar el dropdown si se hace clic fuera de él
        document.addEventListener('click', function(event) {
            var dropdown = document.getElementById("dropdownMenu");
            if (!dropdown.contains(event.target) && !event.target.matches('.user span')) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>s