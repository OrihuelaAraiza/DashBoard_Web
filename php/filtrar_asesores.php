<?php
include('conexion.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener datos del formulario
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$asesores = isset($_POST['asesor']) ? $_POST['asesor'] : [];
$sedes = isset($_POST['sede']) ? $_POST['sede'] : [];
$categorias = isset($_POST['categoria']) ? $_POST['categoria'] : [];

// Asegurarse de que son arrays
$asesores = is_array($asesores) ? $asesores : [$asesores];
$sedes = is_array($sedes) ? $sedes : [$sedes];
$categorias = is_array($categorias) ? $categorias : [$categorias];

// Sanitizar las entradas
$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

// Convertimos los arrays a cadenas para la cláusula IN de SQL
$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

// Construir la consulta base
$sqlAsesores = "SELECT 
    asesor.ID, 
    asesor.Nombre, 
    asesor.Correo, 
    COUNT(DISTINCT asesoria.ID) AS TotalAsesorias,
    SUM(asesoria.Duracion) / 60 AS TotalHorasAsesorias,
    SUM(asesoria.Duracion * (SELECT COUNT(*) FROM asesoria_asesor WHERE asesoria_asesor.id_Asesoria = asesoria.ID)) / 60 AS TotalHorasTalent,
    AVG(asesoria.Duracion) AS DuracionMediaSesion
FROM asesor
JOIN asesoria_asesor ON asesor.ID = asesoria_asesor.id_Asesor
JOIN asesoria ON asesoria_asesor.id_Asesoria = asesoria.ID
WHERE 1=1"; // Iniciar con una condición siempre verdadera

// Agregar condiciones de fecha solo si las fechas no están vacías
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sqlAsesores .= " AND asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
} elseif (!empty($fechaInicio)) {
    $sqlAsesores .= " AND asesoria.Fecha >= '$fechaInicio'";
} elseif (!empty($fechaFin)) {
    $sqlAsesores .= " AND asesoria.Fecha <= '$fechaFin'";
}

// Agregar filtros adicionales
if (!empty($asesoresList)) {
    $sqlAsesores .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sqlAsesores .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sqlAsesores .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$sqlAsesores .= " GROUP BY asesor.ID";

// Ejecutar la consulta
$resultAsesores = $conn->query($sqlAsesores);

if ($resultAsesores) {
    if ($resultAsesores->num_rows > 0) {
        echo "<table><tr><th>ID</th><th>Nombre Completo</th><th>Correo</th><th>Total Asesorías</th><th>Total Horas Asesorías</th><th>Total Horas Talent</th><th>Duración Media Sesión (mins)</th><th>% Horas Prof</th><th>% Horas Talent</th></tr>";
        while ($row = $resultAsesores->fetch_assoc()) {
            $totalHoras = $row['TotalHorasAsesorias'] + $row['TotalHorasTalent'];
            $porcentajeHorasProf = $totalHoras > 0 ? ($row['TotalHorasAsesorias'] / $totalHoras) * 100 : 0;
            $porcentajeHorasTalent = $totalHoras > 0 ? ($row['TotalHorasTalent'] / $totalHoras) * 100 : 0;

            echo "<tr>
                <td>{$row['ID']}</td>
                <td>{$row['Nombre']}</td>
                <td>{$row['Correo']}</td>
                <td>{$row['TotalAsesorias']}</td>
                <td>" . number_format($row['TotalHorasAsesorias'], 2) . "</td>
                <td>" . number_format($row['TotalHorasTalent'], 2) . "</td>
                <td>" . number_format($row['DuracionMediaSesion'], 2) . "</td>
                <td>" . number_format($porcentajeHorasProf, 2) . "%</td>
                <td>" . number_format($porcentajeHorasTalent, 2) . "%</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron resultados para los asesores seleccionados.</p>";
    }
} else {
    echo "Error en la consulta: " . $conn->error;
}

$conn->close();
