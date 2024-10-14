<?php
include('conexion.php');

$fechaInicio = $_GET['fechaInicio'];
$fechaFin = $_GET['fechaFin'];
$asesor = $_GET['asesor'];
$sede = $_GET['sede'];
$categoria = $_GET['categoria'];

// Query para obtener datos según filtros
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

// Variables para cinta de resumen
$totalSesiones = 0;
$totalHorasAlumnos = 0;
$totalHorasTalent = 0;
$totalDuracion = 0;
$profesoresUnicos = [];

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['ID']}</td><td>{$row['Correo']}</td><td>{$row['Fecha']}</td><td>{$row['Duracion']}</td><td>{$row['Categoria']}</td><td>{$row['Asesor']}</td></tr>";

        // Acumular datos para resumen
        $totalSesiones++;
        $totalDuracion += $row['Duracion'];
        if (!in_array($row['Correo'], $profesoresUnicos)) {
            $profesoresUnicos[] = $row['Correo'];
        }
        // Se asume 1 hora por asesor por sesión para simplificar
        $totalHorasTalent += $row['Duracion'] / 60;
    }

    echo "</table>";
}

// Calcular duración media de sesiones
$duracionMedia = $totalDuracion / $totalSesiones;
$totalHorasAlumnos = $totalDuracion / 60;

echo "<div class='resumen'>
        <p>Sesiones: $totalSesiones</p>
        <p>Total Hrs. Alumnos: $totalHorasAlumnos</p>
        <p>Duración media de sesión: $duracionMedia</p>
        <p>Total Hrs. Talent: $totalHorasTalent</p>
        <p>Profesores (Alumnos): " . count($profesoresUnicos) . "</p>
      </div>";

$conn->close();
