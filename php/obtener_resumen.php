<?php
include('conexion.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];
$asesores = isset($_POST['asesor']) ? $_POST['asesor'] : [];
$sedes = isset($_POST['sede']) ? $_POST['sede'] : [];
$categorias = isset($_POST['categoria']) ? $_POST['categoria'] : [];

if (!is_array($asesores)) {
    $asesores = [$asesores];
}
if (!is_array($sedes)) {
    $sedes = [$sedes];
}
if (!is_array($categorias)) {
    $categorias = [$categorias];
}

if (empty($fechaInicio) || empty($fechaFin)) {
    echo json_encode([
        'sesiones' => 0,
        'totalHorasAlumnos' => 0,
        'duracionMediaSesion' => 0,
        'totalHorasTalent' => 0,
        'profesores' => 0
    ]);
    exit;
}

$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

$sql = "SELECT asesoria.ID, asesoria.Duracion, asesoria.Correo, COUNT(asesoria_asesor.id_Asesor) AS TotalAsesores
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

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

$conn->close();
