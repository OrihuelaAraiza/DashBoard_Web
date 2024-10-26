<?php
include('conexion.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$asesores = $_POST['asesor'] ?? [];
$sedes = $_POST['sede'] ?? [];
$categorias = $_POST['categoria'] ?? [];


$asesores = is_array($asesores) ? $asesores : [$asesores];
$sedes = is_array($sedes) ? $sedes : [$sedes];
$categorias = is_array($categorias) ? $categorias : [$categorias];


$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);


$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';


$sql = "SELECT asesoria.ID, asesoria.Duracion, asesoria.Correo, COUNT(asesoria_asesor.id_Asesor) AS TotalAsesores
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        WHERE 1=1";

if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
} elseif (!empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha <= '$fechaFin'";
}

if (!empty($asesoresList)) {
    $sql .= " AND asesoria_asesor.id_Asesor IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sql .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sql .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$sql .= " GROUP BY asesoria.ID";

$result = $conn->query($sql);

$sesiones = 0;
$totalDuracion = 0;
$totalHorasTalent = 0;
$alumnos = [];

if ($result && $result->num_rows > 0) {
    $sesiones = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $duracionSesionHoras = $row['Duracion'] / 60;
        $totalDuracion += $row['Duracion'];
        $totalHorasTalent += $duracionSesionHoras * $row['TotalAsesores'];
        $alumnos[] = $row['Correo'];
    }
}

$duracionMediaSesion = $sesiones > 0 ? ($totalDuracion / $sesiones) : 0;

$totalHorasAlumnos = $totalDuracion / 60;

$alumnosUnicos = count(array_unique($alumnos));

$resumenData = [
    'sesiones' => $sesiones,
    'totalHorasAlumnos' => $totalHorasAlumnos,
    'duracionMediaSesion' => $duracionMediaSesion,
    'totalHorasTalent' => $totalHorasTalent,
    'profesores' => $alumnosUnicos
];

header('Content-Type: application/json');
echo json_encode($resumenData);

if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
} elseif (!empty($fechaInicio)) {
    $sql .= " AND asesoria.Fecha >= '$fechaInicio'";
} elseif (!empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha <= '$fechaFin'";
}

$conn->close();
