<?php
include('conexion.php');
echo "
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 20px;
}

h1 {
    text-align: center;
    color: #333;
}

#filtros {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #fff;
}

#filtros label, #filtros input, #filtros select, #filtros button {
    margin: 5px;
}

.resumen {
    text-align: center;
    background-color: #ffd700;
    padding: 10px;
    margin-top: 20px;
    border-radius: 5px;
    font-weight: bold;
}

#resultados, #categorias {
    margin-top: 20px;
    background-color: #1a1a1a;
    color: #ffd700;
    border-radius: 8px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #666;
}

th {
    background-color: #333;
    color: #ffd700; /* Cambia a blanco o un color más claro si lo prefieres */
    font-weight: bold;
    text-transform: uppercase;
}

tr:nth-child(even) td {
    background-color: #2a2a2a;
}

tr:nth-child(odd) td {
    background-color: #222;
}

tr:hover td {
    background-color: #444;
    color: #ffd700;
}

td {
    color: #ffd700;
}

table, th, td {
    border: 1px solid #666;
}
</style>
";

$fechaInicio = $_GET['fechaInicio'];
$fechaFin = $_GET['fechaFin'];
$asesor = $_GET['asesor'];
$sede = $_GET['sede'];
$categoria = $_GET['categoria'];


$sql = "SELECT asesoria.ID, asesoria.Correo, asesoria.Fecha, asesoria.Duracion, categoria.Nombre AS Categoria, asesor.Nombre AS Asesor
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
        JOIN categoria ON asesoria.id_Categoria = categoria.ID
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' 
        AND asesor.ID = '$asesor' 
        AND asesoria.id_Sede = '$sede' 
        AND asesoria.id_Categoria = '$categoria'";

$result = $conn->query($sql);


if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";


    $totalSesiones = 0;
    $totalHorasAlumnos = 0;
    $totalHorasTalent = 0;
    $totalDuracion = 0;
    $profesoresUnicos = [];

    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['ID']}</td><td>{$row['Correo']}</td><td>{$row['Fecha']}</td><td>{$row['Duracion']}</td><td>{$row['Categoria']}</td><td>{$row['Asesor']}</td></tr>";


        $totalSesiones++;
        $totalDuracion += $row['Duracion'];
        if (!in_array($row['Correo'], $profesoresUnicos)) {
            $profesoresUnicos[] = $row['Correo'];
        }

        $totalHorasTalent += $row['Duracion'] / 60;
    }

    echo "</table>";


    $duracionMedia = $totalSesiones > 0 ? $totalDuracion / $totalSesiones : 0;
    $totalHorasAlumnos = $totalDuracion / 60;

    echo "<div class='resumen'>
            <p>Sesiones: $totalSesiones</p>
            <p>Total Hrs. Alumnos: $totalHorasAlumnos</p>
            <p>Duración media de sesión: $duracionMedia</p>
            <p>Total Hrs. Talent: $totalHorasTalent</p>
            <p>Profesores (Alumnos): " . count($profesoresUnicos) . "</p>
         </div>";
} else {

    echo "<script>alert('No se encontraron resultados para los filtros aplicados.');</script>";
}

$sqlCategorias = "SELECT 
                    categoria.Llave AS Key, 
                    categoria.Nombre AS Nombre, 
                    COUNT(DISTINCT asesoria.ID) AS Sesiones,
                    COUNT(DISTINCT asesoria.Correo) AS Profesores, 
                    SUM(asesoria.Duracion) / 60 AS TotalHorasProf, 
                    SUM(asesoria.Duracion * (SELECT COUNT(*) FROM asesoria_asesor WHERE asesoria_asesor.id_Asesoria = asesoria.ID)) / 60 AS TotalHorasTalent
                FROM asesoria
                JOIN categoria ON asesoria.id_Categoria = categoria.ID
                JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
                WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' 
                AND asesoria.id_Sede = '$sede'
                GROUP BY categoria.ID";

$resultCategorias = $conn->query($sqlCategorias);

if ($resultCategorias->num_rows > 0) {
    echo "<div id='categorias'>";
    echo "<table>";
    echo "<tr><th>Key</th><th>Nombre</th><th>Sesiones</th><th>Profesores</th><th>Total Horas Prof</th><th>Total Horas Talent</th><th>Duración Media Prof</th><th>Duración Media Talent</th></tr>";

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
    echo "</div>";
} else {
    echo "<script>alert('No se encontraron resultados para la vista de categorías.');</script>";
}

$conn->close();
