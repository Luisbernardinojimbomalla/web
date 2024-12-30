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

// Verificar si el formulario fue enviado para registrar datos
if (isset($_POST['registro'])) {
    // Obtener los valores del formulario
    $usuario_id = $_POST['usuario'];  // Aquí se obtiene el id del usuario seleccionado
    $producto = $_POST['producto'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $perfil = $_POST['perfil'];
    $pin = $_POST['pin'];
    $fecha = $_POST['fecha'];
    $duracion = $_POST['duracion'];
    
    // Calcular la fecha de vencimiento
    $fecha_vencimiento = calculate_vencimiento($fecha, $duracion);

    // Insertar datos en la base de datos
    $insertarDatos = "INSERT INTO datos (usuario, producto, correo, contrasena, perfil, pin, fecha, duracion, vencimiento) 
    VALUES ('$usuario_id', '$producto', '$correo', '$contrasena', '$perfil', '$pin', '$fecha', '$duracion', '$fecha_vencimiento')";

    $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);

    if ($ejecutarInsertar) {
        echo "<script>alert('Venta registrada con éxito');</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Error al registrar venta');</script>";
    }
}

// Verificar si el formulario fue enviado para eliminar un registro
if (isset($_POST['eliminar'])) {
    // Obtener el ID del registro a eliminar
    $id = $_POST['id'];

    // Consulta para eliminar el registro de la base de datos
    $query = "DELETE FROM datos WHERE id = $id";

    // Ejecutar la consulta de eliminación
    if (mysqli_query($enlace, $query)) {
        echo "<script>alert('Registro eliminado exitosamente');</script>";
        // Redirigir para evitar que el formulario se reenvíe al actualizar la página
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Error al eliminar el registro');</script>";
    }
}

// Función para calcular la fecha de vencimiento
function calculate_vencimiento($fecha_inicio, $duracion) {
    $fecha_inicio = new DateTime($fecha_inicio);
    
    switch ($duracion) {
        case "1 mes":
            $fecha_inicio->modify("+1 month");
            break;
        case "2 meses":
            $fecha_inicio->modify("+2 months");
            break;
        case "3 meses":
            $fecha_inicio->modify("+3 months");
            break;
        case "6 meses":
            $fecha_inicio->modify("+6 months");
            break;
        case "7 meses":
            $fecha_inicio->modify("+7 months");
            break;
        case "12 meses":
            $fecha_inicio->modify("+12 months");
            break;
        case "14 meses":
            $fecha_inicio->modify("+14 months");
            break;
    }
    
    return $fecha_inicio->format("Y-m-d");
}

// Consulta para obtener los datos de la base de datos ordenados por fecha de vencimiento (del más reciente al más antiguo)
$consulta = "
SELECT datos.*, clientes.nombre, clientes.apellido 
FROM datos 
INNER JOIN clientes ON datos.usuario = clientes.id
ORDER BY STR_TO_DATE(datos.vencimiento, '%Y-%m-%d') DESC
";
$resultado = mysqli_query($enlace, $consulta);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            grid-gap: 20px;
            width: 100%;
        }
        .grid .item {
            background: linear-gradient(to top left, #4caf50, #81c784);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 120px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 18px;
            text-align: center;
        }
        .grid .item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            width: 500px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .modal-content h2 {
            margin-top: 0;
        }
        .modal-content form {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .modal-content form td {
            padding: 10px;
            width: 50%;
        }
        .modal-content form input,
        .modal-content form select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .modal-content form .buttons {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .modal-content form button {
            padding: 10px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .modal-content form .save-btn {
            background: #4caf50;
            color: #fff;
        }
        .modal-content form .save-btn:hover {
            background: #45a049;
        }
        .modal-content form .cancel-btn {
            background: #f44336;
            color: #fff;
        }
        .modal-content form .cancel-btn:hover {
            background: #e53935;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            color: #888;
            cursor: pointer;
        }
        .close-btn:hover {
            color: #000;
        }

        /* Estilo para la tabla de historial de ventas */
        #historial-ventas table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        #historial-ventas th, #historial-ventas td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        #historial-ventas th {
            background-color: #4caf50;
            color: white;
        }
        #historial-ventas tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        #historial-ventas tr:hover {
            background-color: #f1f1f1;
        }
        #historial-ventas .eliminar-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
        }
        #historial-ventas .eliminar-btn:hover {
            background-color: #e53935;
        }
        
        #historial-ventas .cancel-btn {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
}

#historial-ventas .cancel-btn:hover {
    background-color:rgb(255, 255, 255);
}
        /* Ocultar la tabla de historial inicialmente */
        #historial-ventas {
            display: none;
        }
       /* Estilo para la tabla de filtro */
#tabla-filtro table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#tabla-filtro th {
    background-color: #4caf50;  /* Fondo verde */
    color: white;  /* Texto blanco */
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

#tabla-filtro td {
    background-color: white;  /* Fondo blanco */
    color: black;  /* Texto negro */
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

#tabla-filtro tr:nth-child(even) {
    background-color: #f9f9f9;  /* Fondo blanco para filas pares */
}

#tabla-filtro tr:hover {
    background-color: #f1f1f1;  /* Fondo ligeramente gris cuando se pasa el ratón por encima */
}

#tabla-filtro .cancel-btn {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
}

#tabla-filtro .cancel-btn:hover {
    background-color:rgb(255, 255, 255);
}
#tabla-filtro .eliminar-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
        }
        #tabla-filtro .eliminar-btn:hover {
            background-color: #e53935;
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
                <div class="title">Ventas</div>
                <div class="user" onclick="toggleDropdown(event)">
                    <span>👤 Usuario</span>
                    <div class="dropdown" id="dropdownMenu">
                        <button onclick="window.location.href='http://localhost/ejemplo/index.php';">Cerrar sesión</button>
                    </div>
                </div>
            </div>
            
            <div class="grid">
                <div class="item" onclick="openModal()">Nueva Venta</div>
                <div class="item" onclick="mostrarHistorial()">Historial de Ventas</div>
                <div class="item" onclick="mostrarFiltro()">Filtro</div>
            </div>

            <!-- Tabla para mostrar los datos filtrados -->
            <div id="tabla-filtro" style="display: none;">
    <h3>Estado de Usuarios</h3>
    <table class="tabla-estilo">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Producto</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Vencimiento</th>
                <th>Duración</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para obtener los datos de la base de datos ordenados por fecha de vencimiento (más antigua primero)
            $consultaEstado = "
            SELECT datos.*, clientes.nombre, clientes.apellido 
            FROM datos 
            INNER JOIN clientes ON datos.usuario = clientes.id
            ORDER BY STR_TO_DATE(datos.vencimiento, '%Y-%m-%d') ASC
            ";
            $resultadoEstado = mysqli_query($enlace, $consultaEstado);

            while ($fila = mysqli_fetch_assoc($resultadoEstado)) {
                $hoy = new DateTime();
                $fechaInicio = new DateTime($fila['fecha']);
                $fechaVencimiento = new DateTime($fila['vencimiento']);
                $estado = "";
                $diferencia = $hoy->diff($fechaVencimiento);
                $diasRestantes = (int)$diferencia->format("%r%a");

                if ($diasRestantes > 0) {
                    $estado = "<span style='color: green;'>Activa ($diasRestantes días restantes)</span>";
                } elseif ($diasRestantes === 0) {
                    $estado = "<span style='color: orange;'>Vence hoy</span>";
                } else {
                    $estado = "<span style='color: red;'>Caducada (" . abs($diasRestantes) . " días de atraso)</span>";
                }

                // Mostrar los datos de la fila ordenados por fecha de vencimiento
                echo "<tr>
                    <td>" . $fila['nombre'] . " " . $fila['apellido'] . "</td>
                    <td>" . $fila['producto'] . "</td>
                    <td>" . $fila['fecha'] . "</td>
                    <td>" . $fila['vencimiento'] . "</td>
                    <td>" . $fila['duracion'] . "</td>
                    <td>" . $estado . "</td>
                <td>
                        <form method='POST'>
                            <input type='hidden' name='id' value='" . $fila['id'] . "'>
                            <button type='submit' name='eliminar' class='eliminar-btn'>Eliminar</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <button class="cancel-btn" onclick="cerrarFiltro()">Cerrar</button>
            </div>

            <!-- Mostrar datos insertados -->
            <div id="historial-ventas">
    <h3>Datos Registrados:</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Producto</th>
                <th>Correo</th>
                <th>Contraseña</th>
                <th>Perfil</th>
                <th>PIN</th>
                <th>Fecha de Inicio</th>
                <th>Duración</th>
                <th>Fecha de Vencimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para obtener los datos de la base de datos ordenados por fecha de vencimiento
            $consulta = "
            SELECT datos.*, clientes.nombre, clientes.apellido 
            FROM datos 
            INNER JOIN clientes ON datos.usuario = clientes.id
            ORDER BY STR_TO_DATE(datos.vencimiento, '%Y-%m-%d') ASC
            ";
            $resultado = mysqli_query($enlace, $consulta);

            while ($fila = mysqli_fetch_assoc($resultado)) {
                echo "<tr>
                    <td>" . $fila['nombre'] . " " . $fila['apellido'] . "</td>
                    <td>" . $fila['producto'] . "</td>
                    <td>" . $fila['correo'] . "</td>
                    <td>" . $fila['contrasena'] . "</td>
                    <td>" . $fila['perfil'] . "</td>
                    <td>" . $fila['pin'] . "</td>
                    <td>" . $fila['fecha'] . "</td>
                    <td>" . $fila['duracion'] . "</td>
                    <td>" . $fila['vencimiento'] . "</td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='id' value='" . $fila['id'] . "'>
                            <button type='submit' name='eliminar' class='eliminar-btn'>Eliminar</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <button class="cancel-btn" onclick="cerrarHistorial()">Cerrar</button>
</div>

        </div>
    </div>

    <!-- Modal para registrar una nueva venta -->
    <div class="modal" id="myModal">
        <div class="modal-content">
            <h2>Registrar Venta</h2>
            <form action="" method="POST">
                <table>
                    <tr>
                        <td><label for="usuario">Usuario</label></td>
                        <td>
                            <select name="usuario" id="usuario" required>
                                <option value="">Seleccionar Usuario</option>
                                <?php
                                $consultaClientes = "SELECT id, nombre, apellido FROM clientes";
                                $resultadoClientes = mysqli_query($enlace, $consultaClientes);

                                while ($cliente = mysqli_fetch_assoc($resultadoClientes)) {
                                    $nombreCompleto = $cliente['nombre'] . " " . $cliente['apellido'];
                                    echo "<option value='" . $cliente['id'] . "'>" . $nombreCompleto . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="producto">Producto</label></td>
                        <td>
                        <select name="producto" id="producto" required>
    <option value="">Seleccionar</option>
    <option value="NETFLIX">NETFLIX</option>
    <option value="SPOTIFY">SPOTIFY</option>
    <option value="DISNEY+">DISNEY+</option>
    <option value="PRIME VIDEO">PRIME VIDEO</option>
    <option value="FLUJO TV">FLUJO TV</option>
    <option value="TELELATINO">TELELATINO</option>
    <option value="IPTV">IPTV</option>
    <option value="YT PREMIUM">YT PREMIUM</option>
    <option value="VIX+">VIX+</option>
    <option value="CRUNCHYROLL">CRUNCHYROLL</option>
    <option value="HBO MAX">HBO MAX</option>
</select>

                        </td>
                    </tr>
                    <tr>
                        <td><label for="correo">Correo</label></td>
                        <td><input type="email" name="correo" id="correo" required></td>
                    </tr>
                    <tr>
                        <td><label for="contrasena">Contraseña</label></td>
                        <td><input type="password" name="contrasena" id="contrasena" required></td>
                    </tr>
                    <tr>
                        <td><label for="perfil">Perfil</label></td>
                        <td><input type="text" name="perfil" id="perfil" required></td>
                    </tr>
                    <tr>
                        <td><label for="pin">PIN</label></td>
                        <td><input type="text" name="pin" id="pin" required></td>
                    </tr>
                    <tr>
                        <td><label for="fecha">Fecha de Inicio</label></td>
                        <td><input type="date" name="fecha" id="fecha" required></td>
                    </tr>
                    <tr>
                        <td><label for="duracion">Duración</label></td>
                        <td>
                           <select name="duracion" id="duracion" onchange="calcularVencimiento()" required>
    <option value="">Seleccionar</option>
    <option value="1 mes">1 mes</option>
    <option value="2 meses">2 meses</option>
    <option value="3 meses">3 meses</option>
    <option value="6 meses">6 meses</option>
    <option value="7 meses">7 meses</option>
    <option value="12 meses">12 meses</option>
    <option value="14 meses">14 meses</option>
</select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="vencimiento">Fecha de Vencimiento</label></td>
                        <td><input type="date" name="vencimiento" id="vencimiento" required readonly></td>
                    </tr>
                </table>
                <div class="buttons">
                    <button type="submit" name="registro" class="save-btn">Guardar</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Funciones existentes con modificaciones
    function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('dropdownMenu');
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';

            // Close dropdown when clicking elsewhere
            document.addEventListener('click', function closeDropdown(e) {
                if (!event.target.closest('.user')) {
                    dropdown.style.display = 'none';
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }

    function openModal() {
        document.getElementById("myModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("myModal").style.display = "none";
    }

    function mostrarHistorial() {
        // Abrir la tabla de historial
        document.getElementById("historial-ventas").style.display = "block";
        // Cerrar la tabla de filtro
        document.getElementById("tabla-filtro").style.display = "none";
    }

    function cerrarHistorial() {
        document.getElementById("historial-ventas").style.display = "none";
    }

    function mostrarFiltro() {
        // Abrir la tabla de filtro
        document.getElementById("tabla-filtro").style.display = "block";
        // Cerrar la tabla de historial
        document.getElementById("historial-ventas").style.display = "none";
    }

    function cerrarFiltro() {
        document.getElementById("tabla-filtro").style.display = "none";
    }

    function calcularVencimiento() {
    var duracion = document.getElementById('duracion').value;
    var vencimiento = new Date(); // fecha actual
    
    if (duracion === "1 mes") {
        vencimiento.setMonth(vencimiento.getMonth() + 1);
    } else if (duracion === "2 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 2);
    } else if (duracion === "3 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 3);
    } else if (duracion === "6 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 6);
    } else if (duracion === "7 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 7);
    } else if (duracion === "12 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 12);
    } else if (duracion === "14 meses") {
        vencimiento.setMonth(vencimiento.getMonth() + 14);
    }

    var vencimientoFormatted = vencimiento.toISOString().split('T')[0];
    document.getElementById("vencimiento").value = vencimientoFormatted;
}


    // Nueva función para eliminar registros
    document.addEventListener("DOMContentLoaded", function () {
        // Agregar el evento a todos los botones de eliminar
        document.querySelectorAll(".eliminar").forEach(function (button) {
            button.addEventListener("click", function () {
                // Obtener el ID del registro a eliminar
                var idRegistro = this.getAttribute("data-id");

                // Confirmación de eliminación
                if (confirm("¿Estás seguro de que deseas eliminar este registro?")) {
                    // Aquí puedes hacer la solicitud a la base de datos (AJAX)
                    // A continuación se simula la eliminación localmente por ahora:
                    var row = this.closest("tr");  // Obtener la fila que contiene el botón
                    row.remove(); // Eliminar la fila de la tabla

                }
            });
        });
    });
</script>
</body>
</html>
