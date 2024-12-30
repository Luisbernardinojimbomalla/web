<?php
// Definir las variables de conexión
$servidor = "localhost";     // Dirección del servidor (usualmente localhost en desarrollo local)
$usuario = "root";           // Nombre de usuario (por defecto es 'root' en XAMPP)
$clave = "";                 // Contraseña (en XAMPP, por defecto no tiene contraseña)
$baseDeDatos = "ejemplo";    // Nombre de la base de datos

// Crear la conexión con MySQLi
$conn = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

// Verificar si la conexión fue exitosa
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

try {
    // Crear la conexión PDO con las mismas variables definidas anteriormente
    $connPDO = new PDO("mysql:host=$servidor;dbname=$baseDeDatos;charset=utf8", $usuario, $clave);
    $connPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener los clientes de la base de datos
    $queryClientes = "SELECT id, nombre, apellido, telefono FROM clientes";
    $stmtClientes = $connPDO->prepare($queryClientes);
    $stmtClientes->execute();
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

// Verificar si se ha solicitado obtener los datos de un cliente y una fecha
if (isset($_GET['id_cliente']) && isset($_GET['fecha'])) {
    $clienteId = $_GET['id_cliente'];
    $fecha = $_GET['fecha']; // Fecha seleccionada

    // Obtener los datos del cliente
    $queryCliente = "SELECT nombre, apellido, telefono FROM clientes WHERE id = :id";
    $stmtCliente = $connPDO->prepare($queryCliente);
    $stmtCliente->bindParam(':id', $clienteId);
    $stmtCliente->execute();
    $clienteData = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    // Obtener los datos de la tabla `datos` para ese cliente y la fecha seleccionada
    $queryDatosCliente = "
        SELECT d.producto, d.perfil, d.pin, d.fecha AS fecha_finalizacion
        FROM datos d
        WHERE d.usuario = :usuario AND DATE(d.fecha) = :fecha
    ";
    $stmtDatosCliente = $connPDO->prepare($queryDatosCliente);
    $stmtDatosCliente->bindParam(':usuario', $clienteId);
    $stmtDatosCliente->bindParam(':fecha', $fecha); // Filtrar por fecha
    $stmtDatosCliente->execute();
    $datosCliente = $stmtDatosCliente->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver los datos del cliente y los productos filtrados por fecha
    echo json_encode(['cliente' => $clienteData, 'productos' => $datosCliente]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.21/jspdf.plugin.autotable.min.js"></script>



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
    /* Estilos para tablas */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 12px 20px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #4caf50;
        color: #fff;
    }

    td {
        background-color: #fff;
    }

    tr:nth-child(even) td {
        background-color: #f9f9f9;
    }

    tr:hover td {
        background-color: #f1f1f1;
    }

    .search-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .search-bar input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .search-bar select {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .search-bar button {
        padding: 10px 15px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-bar button:hover {
        background-color: #0056b3;
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

    /* Modificaciones para alinear la factura a la derecha */
    .factura-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #fff;
        padding: 10px 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .text-info {
        flex: 1;
    }

    .logo {
        margin-left: auto; /* Alinea el logo a la derecha */
    }

    .logo img {
        width: 500px; /* Tamaño más grande para el logo */
    }

    .text-info {
        text-align: right; /* Alinea el texto a la derecha */
    }

    .total {
        text-align: right; /* Alinea el total a la derecha */
    }

    .información-pago {
        text-align: right; /* Alinea la sección de información de pago a la derecha */
    }

    /* Alinear condiciones de la garantía y la descripción a la izquierda */
    .descripcion, .garantia {
        text-align: left; /* Alinea el texto a la izquierda */
    }

    .descripcion p, .garantia p {
        margin: 5px 0;
    }
/* Contenedor del formulario */
/* Contenedor del formulario */
.table-form-container {
    background-color: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    margin: 20px auto;
}

/* Estilo para las filas de los formularios */
.form-row {
    display: block; /* Cambiado de flex a block para apilar los elementos */
    margin-bottom: 15px;
}

/* Estilo para cada campo del formulario */
.form-field {
    margin-bottom: 10px; /* Añadir espacio entre los campos */
}

/* Etiquetas del formulario */
label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

/* Estilo para los inputs y selects */
input[type="text"],
input[type="date"],
select {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background-color: #f9f9f9;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

/* Efecto de foco para los inputs y selects */
input[type="text"]:focus,
input[type="date"]:focus,
select:focus {
    border-color: #007bff;
    background-color: #e6f7ff;
    outline: none;
}
.boton-container {
    text-align: right;  /* Alinea el contenido del div a la derecha */
    margin-top: 20px;    /* Espacio encima del botón, puedes ajustarlo según tus necesidades */
}

.boton-accion {
    padding: 10px 20px;   /* Espaciado del botón */
    font-size: 16px;       /* Tamaño del texto */
    background-color: #4CAF50; /* Color de fondo */
    color: white;          /* Color del texto */
    border: none;          /* Elimina el borde */
    border-radius: 5px;    /* Bordes redondeados */
    cursor: pointer;      /* Cambia el cursor cuando se pasa sobre el botón */
    transition: background-color 0.3s ease;  /* Transición para el cambio de color al pasar el mouse */
}

.boton-accion:hover {
    background-color: #45a049;  /* Color de fondo al pasar el mouse */
}

    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <!-- Aquí se coloca la imagen con la URL correcta -->
            <img src="http://localhost/ejemplo/FACTURA/LOGOBLANCO.PNG" alt="Logo" width="150" class="logo-img">
            <ul>
                <li><a href="http://localhost/ejemplo/menu.php"><span>🏠</span> Inicio</a></li>
                <li><a href="http://localhost/ejemplo/clientes.php"><span>👥</span> Clientes</a></li>
                <li><a href="http://localhost/ejemplo/ventas.php"><span>🛒</span> Ventas</a></li>
                <li><a href="http://localhost/ejemplo/FACTURA/facturacion.php"><span>💳</span> Facturación</a></li>
            </ul>
        </nav>

        <main class="content">
            <header class="header">
                <div class="title">Facturación</div>
                <div class="user" onclick="toggleDropdown(event)">
                    <span>👤 Usuario</span>
                    <div class="dropdown" id="dropdownMenu">
                    <button onclick="window.location.href='http://localhost/ejemplo/index.php';">Cerrar sesión</button>
                    </div>
                </div>
            </header>
            <section class="factura">
                <div class="factura-header">
                    <div class="logo">
                        <!-- Logo con ruta directa para ejemplo -->
                        <img src="http://localhost/ejemplo/FACTURA/LOGO.PNG" alt="Logo" width="150">
                        </div>
                    <div class="text-info">
                        <h1>Factura Electrónica</h1>
                        <p class="emisor">Emisor: SpaceXpert</p>
                        <p class="emisor">Teléfono: 0968122887</p>
                        <p class="emisor">Fecha de Emisión: <span id="fecha-emision"></span></p>
                    </div>
                </div>
    <!-- Datos del Cliente -->
<div class="table-form-container">
    <h2>Datos del Cliente:</h2>

    <div class="form-row">
        <div class="form-field">
            <label for="cliente">Seleccionar Cliente:</label>
            <select id="cliente" name="cliente" onchange="obtenerDatosCliente()">
                <option value="">Seleccione un cliente</option>
                <!-- Los clientes se llenarán con PHP -->
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>"><?= $cliente['nombre'] . ' ' . $cliente['apellido'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label for="fecha">Seleccionar Fecha:</label>
            <input type="date" id="fecha" name="fecha" onchange="obtenerDatosCliente()">
        </div>
    </div>

    <div class="form-row">
        <div class="form-field">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre del cliente" readonly>
        </div>

        <div class="form-field">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" placeholder="Apellido del cliente" readonly>
        </div>

        <div class="form-field">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" placeholder="Teléfono del cliente" readonly>
        </div>
    </div>
</div>


                <!-- Tabla para mostrar los productos y datos del cliente -->
                <div class="datos">
                    <h2>Datos del Cliente y sus Productos:</h2>
                    <table id="tabla-datos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Perfil</th>
                                <th>PIN</th>
                                <th>Fecha de Finalización</th>
                                <th>Precio</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="productos-cliente">
                            <!-- Aquí se llenarán los productos automáticamente -->
                        </tbody>
                    </table>

                    <!-- Total -->
                    <div class="total">
                        <p>Subtotal: <span id="subtotal">$0.00</span></p>
                        <p>IVA 0%: <span id="iva">$0.00</span></p>
                        <strong>Total a pagar: <span id="total">$0.00</span></strong>
                    </div>

                   <!-- Información de Pago -->
<!-- Información de Pago -->
<div class="garantia">
<h3>AVISO IMPORTANTE</h3>
        <p><strong>Este documento es únicamente para fines internos y no tiene validez oficial ni fiscal.</strong></p>
        <p>No puede ser utilizado para declarar impuestos ni como comprobante ante ninguna autoridad.</p>
        <p>Su uso está limitado exclusivamente a las operaciones internas de SpaceXpert.</p>
    </div>

    <!-- Botón Descargar PDF -->
    <div class="boton-container">
        <button id="download-pdf" class="boton-accion">Descargar PDF</button>
    </div>
</div>
</section>
</main>
</div>

<script>
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
    // Función para obtener los datos del cliente y llenar los campos automáticamente
    function obtenerDatosCliente() {
        document.getElementById('download-pdf').addEventListener('click', generarPDF);
        var clienteId = document.getElementById('cliente').value;
        var fecha = document.getElementById('fecha').value; // Obtener la fecha seleccionada

        if (clienteId && fecha) {
            fetch('facturacion.php?id_cliente=' + clienteId + '&fecha=' + fecha)
                .then(response => response.json())
                .then(data => {
                    // Llenar los datos del cliente
                    document.getElementById('nombre').value = data.cliente.nombre;
                    document.getElementById('apellido').value = data.cliente.apellido;
                    document.getElementById('telefono').value = data.cliente.telefono;
                    
                    // Limpiar la tabla antes de agregar nuevos datos
                    var tablaProductos = document.getElementById('productos-cliente');
                    tablaProductos.innerHTML = ''; 

                    let subtotal = 0; // Para acumular el subtotal

                    // Verificar si hay productos
                    if (data.productos.length > 0) {
                        // Si hay productos, llenarlos en la tabla
                        data.productos.forEach(dato => {
                            var row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${dato.producto}</td>
                                <td>${dato.perfil}</td>
                                <td>${dato.pin}</td>
                                <td>${dato.fecha_finalizacion}</td>
                                <td><input type="number" class="precio" value="0" oninput="calcularTotal()" /></td>
                                <td><span class="total-producto">$0.00</span></td>
                            `;
                            tablaProductos.appendChild(row);
                        });
                    } else {
                        // Si no hay productos, mostrar el mensaje en una nueva fila dentro de la tabla
                        var row = document.createElement('tr');
                        row.innerHTML = `
                            <td colspan="6" style="text-align:center; color:red;">No compró nada en esta fecha</td>
                        `;
                        tablaProductos.appendChild(row);
                    }

                    // Calcular el subtotal, IVA y total a pagar
                    calcularTotal();
                })
                .catch(error => console.error('Error al obtener los datos del cliente:', error));
        } else {
            // Limpiar los campos si no se selecciona un cliente o una fecha
            document.getElementById('nombre').value = '';
            document.getElementById('apellido').value = '';
            document.getElementById('telefono').value = '';
            document.getElementById('productos-cliente').innerHTML = '';
        }
    }

    // Función para calcular el total
    function calcularTotal() {
        let subtotal = 0;

        // Sumar los precios ingresados
        document.querySelectorAll('.precio').forEach(function(input, index) {
            const precio = parseFloat(input.value) || 0;
            const totalProducto = precio; // Total por producto (solo el precio en este caso)

            // Mostrar el total por producto
            document.querySelectorAll('.total-producto')[index].textContent = `$${totalProducto.toFixed(2)}`;

            // Acumular el subtotal
            subtotal += totalProducto;
        });

        // Calcular IVA (supuesto 0%)
        const iva = subtotal * 0; // Cambia el porcentaje si necesitas IVA

        // Calcular el total a pagar
        const total = subtotal + iva;

        // Mostrar subtotal, IVA y total en la página
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('iva').textContent = `$${iva.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;
    }

    // Mostrar la fecha actual en el campo de fecha de emisión
    function mostrarFechaEmision() {
        const fechaEmision = new Date();
        const dia = String(fechaEmision.getDate()).padStart(2, '0');
        const mes = String(fechaEmision.getMonth() + 1).padStart(2, '0'); // Mes es base 0
        const año = fechaEmision.getFullYear();
        document.getElementById('fecha-emision').textContent = `${dia}/${mes}/${año}`;
    }

    // Llamar a la función para mostrar la fecha de emisión al cargar la página
    mostrarFechaEmision();

    // Función para generar el PDF con los datos correctos
function generarPDF() {
    console.log('Generando PDF...');

    // Crear el documento PDF
    const { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    // Establecer márgenes
    const margenIzquierda = 15;
    const margenSuperior = 20;
    const margenDerecha = 15;
    const anchoContenido = 180; // Ancho de las líneas horizontales ajustado a los bordes del contenido

    // Cambiar la fuente a Times New Roman
    doc.setFont('times', 'normal'); // Fuente Times New Roman

    // Añadir el logo
    var logo = document.querySelector('.logo img');
    var logoUrl = logo ? logo.src : ''; // Obtenemos la URL de la imagen del logo
    if (logoUrl) {
        doc.addImage(logoUrl, 'PNG', margenIzquierda, margenSuperior, 80, 16); // Altura del logo ajustada a 16px
    }

    // Añadir el título "Factura Electrónica"
    const xRightAlign = 190; // Coordenada X para la alineación a la derecha
    doc.setFontSize(18); // Tamaño ajustado
    doc.setFont('times', 'bold'); // Fuente Times, negrita
    doc.text("Comprobante Electronico", xRightAlign, margenSuperior + 10, null, null, 'right');

    // Añadir la información del emisor y la fecha justo debajo del título
    doc.setFontSize(12);
    doc.setFont('times', 'normal'); // Fuente Times, normal
    doc.text("Emisor: SpaceXpert", xRightAlign, margenSuperior + 18, null, null, 'right');
    doc.text("Teléfono: 0968122887", xRightAlign, margenSuperior + 26, null, null, 'right');
    const fechaEmision = document.getElementById('fecha-emision').textContent || 'Fecha de emisión no disponible';
    doc.text("Fecha de emisión: " + fechaEmision, xRightAlign, margenSuperior + 34, null, null, 'right');

    // Añadir "Datos de Cliente" en negrillas
    const xCliente = margenIzquierda; // Coordenada X para los datos del cliente
    doc.setFontSize(14);
    doc.setFont('times', 'bold'); // Fuente Times, negrita
    doc.text("Datos de Cliente", xCliente, margenSuperior + 50);

    // Dibujar una línea delgada debajo de "Datos de Cliente"
    doc.setLineWidth(0.5); // Línea más gruesa
    doc.line(margenIzquierda, margenSuperior + 52, margenIzquierda + anchoContenido, margenSuperior + 52);

    // Mover los datos del cliente (nombre y teléfono) justo debajo de "Datos de Cliente"
    doc.setFontSize(12);
    doc.setFont('times', 'normal'); // Fuente Times, normal
    doc.text(`Nombre: ${document.getElementById('nombre').value} ${document.getElementById('apellido').value}`, xCliente, margenSuperior + 60);
    doc.text(`Teléfono: ${document.getElementById('telefono').value}`, xCliente, margenSuperior + 70);

    // Añadir "Servicios" en negrillas antes de la tabla
    doc.setFontSize(14);
    doc.setFont('times', 'bold'); // Fuente Times, negrita
    doc.text("Servicios", margenIzquierda, margenSuperior + 80); // Reducido el espacio entre "Teléfono" y "Servicios"

    // Dibujar una línea delgada debajo de "Servicios"
    doc.setLineWidth(0.5); // Línea más gruesa
    doc.line(margenIzquierda, margenSuperior + 82, margenIzquierda + anchoContenido, margenSuperior + 82);

    // Añadir tabla de productos
    var tableColumn = ["Producto", "Perfil", "PIN", "Fecha Finalización", "Precio", "Total"];
    var tableRows = [];

    // Recoger datos de productos desde la tabla en el formulario
    document.querySelectorAll('#productos-cliente tr').forEach(row => {
        var cells = row.querySelectorAll('td');
        if (cells.length) {
            var producto = cells[0].innerText;
            var perfil = cells[1].innerText;
            var pin = cells[2].innerText;
            var fechaFinalizacion = cells[3].innerText;
            var precio = cells[4].querySelector('input') ? cells[4].querySelector('input').value : 0;
            var total = cells[5].querySelector('.total-producto') ? cells[5].querySelector('.total-producto').innerText.replace('$', '') : 0;

            tableRows.push([producto, perfil, pin, fechaFinalizacion, precio, total]);
        }
    });

    // Color morado suave para la tabla
    const colorMorado = [128, 0, 128]; // RGB para un morado suave

    // Añadir la tabla de productos al PDF con color de fondo morado suave
    doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: margenSuperior + 87, // Ajuste para que la tabla empiece después de "Servicios"
        theme: 'grid',
        margin: { left: margenIzquierda, top: margenSuperior }, // Márgenes para la tabla
        headStyles: {
            fillColor: colorMorado, // Tono morado para los encabezados de la tabla
            textColor: [255, 255, 255] // Color de texto blanco para que contraste
        },
        bodyStyles: {
            fillColor: [245, 245, 245] // Color de fondo suave para las filas del cuerpo
        }
    });

    // Añadir total final alineado con la tabla y alineado a la derecha
    const xRightTotals = 190; // Coordenada X para alineado a la derecha
    var subtotal = document.getElementById('subtotal').textContent.replace('$', '');
    var iva = document.getElementById('iva').textContent.replace('$', '');
    var total = document.getElementById('total').textContent.replace('$', '');

    doc.text(`Subtotal: $${subtotal}`, xRightTotals, doc.lastAutoTable.finalY + 10, null, null, 'right');
    doc.text(`IVA: $${iva}`, xRightTotals, doc.lastAutoTable.finalY + 20, null, null, 'right');
    doc.text(`Total: $${total}`, xRightTotals, doc.lastAutoTable.finalY + 30, null, null, 'right');

    // Añadir espacio adicional (3 líneas) antes de "AVISO IMPORTANTE"
    const espacioAviso = 50; // Espacio incrementado para separar más el aviso importante del total
    doc.setFontSize(10); // Tamaño de fuente para "AVISO IMPORTANTE"
    doc.text("AVISO IMPORTANTE", margenIzquierda, doc.lastAutoTable.finalY + espacioAviso);

    // Cambiar el tamaño de la fuente para el texto del aviso importante
    doc.setFontSize(8); // Fuente para el texto del aviso importante (más pequeño)
    // Eliminar negritas en el texto de abajo de "AVISO IMPORTANTE" y añadirlo
    doc.setFont('times', 'normal'); // Establecer fuente normal para el texto del aviso
    const textoAviso = [
        "Este documento es únicamente para fines internos y no tiene validez oficial ni fiscal. No puede ser utilizado para declarar impuestos ni como comprobante ante ninguna autoridad. Su uso está limitado exclusivamente a las operaciones internas de SpaceXpert."
    ];

    // Añadir todo el texto en un solo párrafo justo después del título "AVISO IMPORTANTE"
    const yAvisoImportante = doc.lastAutoTable.finalY + espacioAviso + 6;
    doc.text(textoAviso[0], margenIzquierda, yAvisoImportante, { maxWidth: 180, align: 'justify' });

    // Ajuste de la posición para "GARANTÍA" debajo de "AVISO IMPORTANTE"
    const espacioGarantia = 10; // Espacio reducido entre "AVISO IMPORTANTE" y "GARANTÍA"
    doc.setFontSize(10); // Tamaño de fuente para "GARANTÍA"
    doc.setFont('times', 'bold'); // Fuente negrita
    doc.text("GARANTÍA", margenIzquierda, yAvisoImportante + espacioGarantia);

    // Añadir los puntos de la garantía
    doc.setFontSize(8); // Fuente de tamaño pequeño para los puntos (más pequeño)
    doc.setFont('times', 'normal'); // Fuente normal para los puntos

    const garantiaText = [
        "1. Aseguramos que recibirá una cuenta activa y completamente funcional al momento de la entrega.",
        "2. La garantía no cubre suspensiones por uso indebido o violación de términos de las plataformas.",
        "3. El día en que el cliente reporte el fallo será considerado como el inicio del inconveniente.",
        "4. No se aceptarán reclamos retroactivos indicando que el problema ocurrió varios días antes del reporte."
    ];

    // Ajuste de las posiciones de los puntos de la garantía con poco interlineado
    const startingY = yAvisoImportante + espacioGarantia + 5; // Justo después de "GARANTÍA"
    garantiaText.forEach((line, index) => {
        doc.text(line, margenIzquierda, startingY + (index * 4)); // Espacio de 6 unidades para interlineado pequeño
    });

    // Guardar el archivo PDF
    doc.save('factura.pdf');
}

// Asegúrate de añadir el evento de descarga al hacer clic en el botón
document.getElementById('download-pdf').addEventListener('click', generarPDF);


</script>
</body>
</html>
