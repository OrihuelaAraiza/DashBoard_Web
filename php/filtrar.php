<?php
include('conexion.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reporte de Asesorías</title>
    <style>
    /* Estilos CSS */
    body {
        font-family: Arial, sans-serif;
        background-color: #1a1a1a;
        color: #ddd;
        margin: 0;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #ffd700;
    }

    .tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .tab-button {
        background-color: #333;
        color: #ffd700;
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        margin: 0 5px;
        border-radius: 5px;
        font-weight: bold;
    }

    .tab-button.active {
        background-color: #ffd700;
        color: #333;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    #resultados, #categorias {
        background-color: #2b2b2b;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #ffd700;
    }

    th, td {
        padding: 12px;
        border: 1px solid #666;
        text-align: left;
    }

    th {
        background-color: #333;
        font-weight: bold;
    }

    tr:nth-child(even) td {
        background-color: #2a2a2a;
    }

    tr:nth-child(odd) td {
        background-color: #222;
    }

    .return-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #444;
        color: #ddd;
        border: 1px solid #666;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s, color 0.3s;
        margin: 20px auto;
    }

    .return-button:hover {
        background-color: #555;
        color: #ffd700;
    }
    </style>

    <script defer>
    function openTab(tabId, element) {
        var tabs = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }

        var buttons = document.getElementsByClassName('tab-button');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
        }

        document.getElementById(tabId).classList.add('active');
        element.classList.add('active');
    }
    </script>
</head>
<body>
    <h1>Reporte de Asesorías</h1>

    <div class='tabs'>
        <button class='tab-button active' onclick='openTab(\"resultadosTab\", this)'>Resultados</button>
        <button class='tab-button' onclick='openTab(\"categoriasTab\", this)'>Categorías</button>
    </div>

    <div id='resultadosTab' class='tab-content active'>
";

$fechaInicio = $_GET['fechaInicio'];
$fechaFin = $_GET['fechaFin'];
$asesores = isset($_GET['asesor']) ? $_GET['asesor'] : [];
$sedes = isset($_GET['sede']) ? $_GET['sede'] : [];
$categorias = isset($_GET['categoria']) ? $_GET['categoria'] : [];

// Convertir los arrays a cadenas separadas por comas
$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

// Consulta SQL para los resultados de asesorías con los filtros aplicados
$sql = "SELECT asesoria.ID, asesoria.Correo, asesoria.Fecha, asesoria.Duracion, categoria.Nombre AS Categoria, asesor.Nombre AS Asesor
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
        JOIN categoria ON asesoria.id_Categoria = categoria.ID
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

// Filtros
if (!empty($asesoresList)) {
    $sql .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sql .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sql .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['ID']}</td><td>{$row['Correo']}</td><td>{$row['Fecha']}</td><td>{$row['Duracion']}</td><td>{$row['Categoria']}</td><td>{$row['Asesor']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No se encontraron resultados para los filtros aplicados.</p>";
}

$conn->close();
?>

<!-- Código para la pestaña Categorías -->
<?php
include('conexion.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fechaInicio = $_GET['fechaInicio'];
$fechaFin = $_GET['fechaFin'];
$asesores = isset($_GET['asesor']) ? $_GET['asesor'] : [];
$sedes = isset($_GET['sede']) ? $_GET['sede'] : [];
$categorias = isset($_GET['categoria']) ? $_GET['categoria'] : [];

$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

// Consulta para la pestaña Categorías
$sqlCategorias = "SELECT 
                    categoria.Llave AS `Key`, 
                    categoria.Nombre AS Nombre, 
                    COUNT(DISTINCT asesoria.ID) AS Sesiones,
                    COUNT(DISTINCT asesoria.Correo) AS Profesores, 
                    SUM(asesoria.Duracion) / 60 AS TotalHorasProf, 
                    SUM(asesoria.Duracion * (SELECT COUNT(*) FROM asesoria_asesor WHERE asesoria_asesor.id_Asesoria = asesoria.ID)) / 60 AS TotalHorasTalent
                FROM asesoria
                JOIN categoria ON asesoria.id_Categoria = categoria.ID
                JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
                JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
                WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

// Filtros
if (!empty($asesoresList)) {
    $sqlCategorias .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sqlCategorias .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sqlCategorias .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$sqlCategorias .= " GROUP BY categoria.ID";

$resultCategorias = $conn->query($sqlCategorias);

if ($resultCategorias->num_rows > 0) {
    echo "<table><tr><th>Key</th><th>Nombre</th><th>Sesiones</th><th>Profesores</th><th>Total Horas Prof</th><th>Total Horas Talent</th><th>Duración Media Prof</th><th>Duración Media Talent</th></tr>";
    while ($row = $resultCategorias->fetch_assoc()) {
        $duracionMediaProf = $row['Sesiones'] > 0 ? $row['TotalHorasProf'] / $row['Sesiones'] : 0;
        $duracionMediaTalent = $row['Sesiones'] > 0 ? $row['TotalHorasTalent'] / $row['Sesiones'] : 0;
        echo "<tr>
                <td>{$row['Key']}</td>
                <td>{$row['Nombre']}</td>
                <td>{$row['Sesiones']}</td>
                <td>{$row['Profesores']}</td>
                <td>" . number_format($row['TotalHorasProf'], 2) . "</td>
                <td>" . number_format($row['TotalHorasTalent'], 2) . "</td>
                <td>" . number_format($duracionMediaProf, 2) . "</td>
                <td>" . number_format($duracionMediaTalent, 2) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No se encontraron resultados para la vista de categorías.</p>";
}

$conn->close();
?>