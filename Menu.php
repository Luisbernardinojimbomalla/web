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

if (isset($_POST['saveData'])) {
    // Aquí tu código para guardar los datos en la base de datos
    $correo_admin = $_POST['correo_admin'];
    $usuario1 = $_POST['usuario1'];
    $usuario2 = $_POST['usuario2'];
    $usuario3 = $_POST['usuario3'];
    $usuario4 = $_POST['usuario4'];
    $usuario5 = $_POST['usuario5'];

    // Realiza la inserción en la base de datos
    $sql = "INSERT INTO correos (correo_admin, usuario1, usuario2, usuario3, usuario4, usuario5) 
            VALUES ('$correo_admin', '$usuario1', '$usuario2', '$usuario3', '$usuario4', '$usuario5')";

    if (mysqli_query($enlace, $sql)) {
        // Redirigir a la misma página para evitar reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($enlace);
    }
}

// Modificar los datos
if (isset($_POST['updateData'])) {
    $id = $_POST['id'];
    $correo_admin = $_POST['correo_admin'];
    $usuario1 = $_POST['usuario1'];
    $usuario2 = $_POST['usuario2'];
    $usuario3 = $_POST['usuario3'];
    $usuario4 = $_POST['usuario4'];
    $usuario5 = $_POST['usuario5'];

    // Realiza la actualización en la base de datos
    $sql = "UPDATE correos SET 
                correo_admin='$correo_admin', 
                usuario1='$usuario1', 
                usuario2='$usuario2', 
                usuario3='$usuario3', 
                usuario4='$usuario4', 
                usuario5='$usuario5' 
            WHERE id=$id";
            if (mysqli_query($enlace, $sql)) {
                // Redirigir a la misma página para evitar reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($enlace);
            }
}

// Obtener todos los correos registrados
$resultado = mysqli_query($enlace, "SELECT * FROM correos");
$correos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Ventas</title>
    <style>
       /* Estilos generales */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fb;
    margin: 0;
    padding: 0;
}

.container {
    display: flex;
    height: 100vh;
}

.sidebar {
    background-color: rgb(0, 0, 0);
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
    background-color: #34495e;
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
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    grid-gap: 20px;
    width: 100%;
}

.grid .item {
    background: #4caf50;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 60px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    font-size: 14px;
    text-align: center;
}

.grid .item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Estilo del formulario */
#addEmailForm {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 350px;  /* Ancho del formulario */
    margin: 20px auto;
    display: none;
    transition: all 0.3s ease-in-out;
}

#addEmailForm label {
    font-size: 14px;  /* Tamaño de texto más pequeño */
    font-weight: bold;
    margin-bottom: 6px;
    color: #333;
    display: block;
}

#addEmailForm input {
    width: 100%;  /* Asegura que los inputs no se estiren demasiado */
    max-width: 327px;  /* Definir un ancho máximo para que no se expanda demasiado */
    padding: 8px;  /* Reducido padding para mayor ajuste */
    margin-bottom: 12px;  /* Reducir margen entre campos */
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background-color: #f9f9f9;
}

#addEmailForm input[type="email"], 
#addEmailForm input[type="text"] {
    padding: 8px;  /* Asegura que todos los campos tengan el mismo tamaño */
    font-size: 14px;  /* Tamaño de fuente consistente */
    border-radius: 6px;
}

#addEmailForm input[type="email"]:focus,
#addEmailForm input[type="text"]:focus {
    border-color: #4CAF50;  /* Color verde para el enfoque */
    outline: none;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.4);  /* Sombra verde */
    background-color: #fff;
}

#addEmailForm button {
    background-color: #4CAF50;  /* Mismo verde que el enfoque */
    color: white;
    padding: 12px;  /* Menor padding */
    border: none;
    border-radius: 6px;  /* Borde más pequeño */
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);  /* Sombra verde */
}

#addEmailForm button:hover {
    background-color: #45a049;  /* Un verde más oscuro al hacer hover */
    transform: translateY(-2px);
}

#addEmailForm button:active {
    background-color: #45a049;  /* Un verde más oscuro al hacer click */
    transform: translateY(0);
}

/* Ajustar el estilo de los campos */
#addEmailForm input[type="email"], 
#addEmailForm input[type="text"] {
    border-radius: 6px;  /* Borde más pequeño */
}

/* Efecto de mostrar el formulario */
#addEmailForm.show {
    display: block;
    transform: scale(1.05);
    opacity: 1;
}

/* Estilo para la tabla de lista de correos */
#listEmails table {
    width: 100%;
    border-collapse: collapse;
}

#listEmails th, #listEmails td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

#listEmails th {
    background-color: #4CAF50;  /* Color verde en el encabezado */
    color: white;
}

#listEmails td {
    background-color: white;  /* Fondo blanco para las celdas */
    color: #333;  /* Texto negro */
}

#listEmails tr:hover {
    background-color: #f1f1f1;
}

/* Estilos para el formulario de modificar correo */
#modifyForm {
    background-color: #f9f9f9;
    padding: 8px; /* Reducción adicional del padding */
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 250px; /* Ancho aún más pequeño */
    margin-left: 10px; /* Alineación a la izquierda */
    display: none; /* Inicialmente oculto */
}

#modifyForm h3 {
    text-align: center;
    font-size: 14px; /* Tamaño de fuente más pequeño */
    margin-bottom: 8px;
    color: #333;
}

#modifyForm label {
    display: block;
    font-weight: bold;
    margin: 4px 0; /* Reducir el espacio entre etiquetas */
    color: #444;
}

#modifyForm input[type="email"],
#modifyForm input[type="text"] {
    width: 100%;
    padding: 5px; /* Padding reducido aún más */
    font-size: 12px; /* Fuente más pequeña */
    margin-bottom: 6px; /* Reducir el espacio entre los campos */
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

#modifyForm input[type="email"]:focus,
#modifyForm input[type="text"]:focus {
    border-color: #66afe9;
    outline: none;
}

#modifyForm button {
    background-color: #4CAF50;
    color: white;
    padding: 5px 10px; /* Padding reducido en el botón */
    font-size: 12px; /* Fuente más pequeña */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}

#modifyForm button:hover {
    background-color: #45a049;
}

#modifyForm button:active {
    background-color: #388e3c;
}
/* Estilos para la tabla de lista de correos */
#listEmails table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff; /* Color de fondo blanco para las filas */
    color: black; /* Texto negro para el cuerpo de la tabla */
    margin-top: 20px;
    border: 1px solid #ddd; /* Borde de la tabla */
}

#listEmails th {
    background-color: #28a745; /* Color verde para el encabezado */
    color: white; /* Texto blanco en el encabezado */
    padding: 10px;
    text-align: center;
    border: 1px solid #ffffff; /* Borde blanco para los encabezados */
}

#listEmails td {
    background-color: #ffffff; /* Color blanco para las celdas */
    color: black; /* Texto negro en las celdas */
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd; /* Borde gris claro para las celdas */
}

/* Estilo para el botón Modificar */
#listEmails .action-btn {
    background-color: #28a745; /* Color verde */
    color: white; /* Texto en blanco */
    border: none;
    padding: 8px 15px;
    border-radius: 12px; /* Bordes redondeados */
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease; /* Transición suave para los cambios */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
    text-align: center;
    text-decoration: none;
}

#listEmails .action-btn:hover {
    background-color: #218838; /* Color más oscuro al pasar el mouse */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2); /* Sombra más pronunciada al pasar el mouse */
    transform: translateY(-2px); /* Eleva el botón ligeramente al pasar el mouse */
}

#listEmails .action-btn:active {
    background-color: #1e7e34; /* Color aún más oscuro al hacer clic */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra menos pronunciada al hacer clic */
    transform: translateY(1px); /* Reduce la elevación cuando se hace clic */
}

.logo-img {
    width: 250px; /* Cambia este valor para aumentar o disminuir el ancho */
    height: auto; /* Esto asegura que mantenga sus proporciones */
    margin-bottom: 20px; /* Opcional: un margen para separarlo de otros elementos */
}
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="http://localhost/ejemplo/FACTURA/LOGOBLANCO.PNG" alt="Logo" width="150" class="logo-img">
            <ul>
                <li><a href="menu.php"><span>🏠</span> Inicio</a></li>
                <li><a href="clientes.php"><span>👥</span> Clientes</a></li>
                <li><a href="ventas.php"><span>🛒</span> Ventas</a></li>
                <li><a href="http://localhost/ejemplo/FACTURA/facturacion.php"><span>💳</span> Facturación</a></li>
            </ul>
        </div>

        <div class="content">
            <div class="header">
                <div class="title">Menu</div>
                <div class="user" onclick="toggleDropdown(event)">
                    <span>👤 Usuario</span>
                    <div class="dropdown" id="dropdownMenu">
                    <button onclick="window.location.href='http://localhost/ejemplo/index.php';">Cerrar sesión</button>
                    </div>
                </div>
            </div>

            <div class="grid">
    <div class="item" onclick="showForm()">Añadir correo</div>
    <div class="item" onclick="showList()">Lista de correos</div>
</div>

<!-- Formulario de añadir correo -->
<form id="addEmailForm" method="POST" action="" style="display:none;">
    <label for="correo_admin">Correo Admin:</label>
    <input type="email" name="correo_admin" id="correo_admin" required><br>

    <label for="usuario1">Usuario 1:</label>
    <input type="text" name="usuario1" id="usuario1" required><br>

    <label for="usuario2">Usuario 2:</label>
    <input type="text" name="usuario2" id="usuario2" required><br>

    <label for="usuario3">Usuario 3:</label>
    <input type="text" name="usuario3" id="usuario3" required><br>

    <label for="usuario4">Usuario 4:</label>
    <input type="text" name="usuario4" id="usuario4" required><br>

    <label for="usuario5">Usuario 5:</label>
    <input type="text" name="usuario5" id="usuario5" required><br>

    <button type="submit" name="saveData">Guardar Datos</button>
</form>

<!-- Contenedor para la tabla de correos -->
<div id="listEmails" style="display:none; margin-top: 20px;">
    <table border="1" width="100%" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Correo Admin</th>
                <th>Usuario 1</th>
                <th>Usuario 2</th>
                <th>Usuario 3</th>
                <th>Usuario 4</th>
                <th>Usuario 5</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($correos as $correo) { ?>
                <tr>
                    <td><?php echo $correo['correo_admin']; ?></td>
                    <td><?php echo $correo['usuario1']; ?></td>
                    <td><?php echo $correo['usuario2']; ?></td>
                    <td><?php echo $correo['usuario3']; ?></td>
                    <td><?php echo $correo['usuario4']; ?></td>
                    <td><?php echo $correo['usuario5']; ?></td>
                    <td>
                        <!-- Botón de Modificar -->
                        <button class="action-btn" onclick="modifyEmail(<?php echo $correo['id']; ?>, '<?php echo $correo['correo_admin']; ?>', '<?php echo $correo['usuario1']; ?>', '<?php echo $correo['usuario2']; ?>', '<?php echo $correo['usuario3']; ?>', '<?php echo $correo['usuario4']; ?>', '<?php echo $correo['usuario5']; ?>')">Modificar</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Formulario para modificar los datos del correo -->
<div id="modifyForm" style="display:none; margin-top: 20px;">
    <h3>Modificar Correo</h3>
    <form id="modifyEmailForm" method="POST" action="">
        <input type="hidden" name="id" id="modify_id"> <!-- ID del correo que se va a modificar -->
        
        <label for="correo_admin">Correo Admin:</label>
        <input type="email" name="correo_admin" id="modify_correo_admin" required><br>

        <label for="usuario1">Usuario 1:</label>
        <input type="text" name="usuario1" id="modify_usuario1" required><br>

        <label for="usuario2">Usuario 2:</label>
        <input type="text" name="usuario2" id="modify_usuario2" required><br>

        <label for="usuario3">Usuario 3:</label>
        <input type="text" name="usuario3" id="modify_usuario3" required><br>

        <label for="usuario4">Usuario 4:</label>
        <input type="text" name="usuario4" id="modify_usuario4" required><br>

        <label for="usuario5">Usuario 5:</label>
        <input type="text" name="usuario5" id="modify_usuario5" required><br>

        <button type="submit" name="updateData">Actualizar Datos</button>
    </form>
</div>
<script>
    function toggleDropdown(event) {
            var dropdown = document.getElementById("dropdownMenu");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            event.stopPropagation();
        }
    // Función para mostrar y ocultar el formulario de añadir correo
    function showForm() {
        const form = document.getElementById("addEmailForm");
        const list = document.getElementById("listEmails");

        // Alternar la visibilidad del formulario con una animación
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            list.style.display = "none"; // Cerrar la lista de correos
        } else {
            form.style.display = "none";
        }
    }

    // Función para mostrar la lista de correos
    function showList() {
        const form = document.getElementById("addEmailForm");
        const list = document.getElementById("listEmails");

        // Alternar la visibilidad de la tabla y el formulario
        if (list.style.display === "none" || list.style.display === "") {
            list.style.display = "block";
            form.style.display = "none"; // Cerrar el formulario de añadir correo
        } else {
            list.style.display = "none";
            form.style.display = "block"; // Volver a mostrar el formulario de añadir correo
        }
    }
    function modifyEmail(id, correoAdmin, usuario1, usuario2, usuario3, usuario4, usuario5) {
        // Mostrar el formulario de modificación
        document.getElementById("modifyForm").style.display = "block";

        // Llenar los campos del formulario con los valores del correo
        document.getElementById("modify_id").value = id;
        document.getElementById("modify_correo_admin").value = correoAdmin;
        document.getElementById("modify_usuario1").value = usuario1;
        document.getElementById("modify_usuario2").value = usuario2;
        document.getElementById("modify_usuario3").value = usuario3;
        document.getElementById("modify_usuario4").value = usuario4;
        document.getElementById("modify_usuario5").value = usuario5;
    }
</script>

</body>
</html>
