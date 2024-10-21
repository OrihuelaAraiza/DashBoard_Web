<?php
include('conexion.php');

// Habilitar reporte de errores (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener datos del formulario
$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];
$asesores = isset($_POST['asesor']) ? $_POST['asesor'] : [];
$sedes = isset($_POST['sede']) ? $_POST['sede'] : [];
$categorias = isset($_POST['categoria']) ? $_POST['categoria'] : [];

// Validar que las fechas no estén vacías
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

// Sanitizar las entradas
$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

// Convertimos los arrays a cadenas para la cláusula IN de SQL
$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

// Construir la consulta base
$sql = "SELECT asesoria.ID, asesoria.Duracion, asesoria.Correo, COUNT(asesoria_asesor.id_Asesor) AS TotalAsesores
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

// Aplicar filtros si existen
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

// Ejecutar la consulta
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

// Calcular duración media de sesión en minutos
$duracionMediaSesion = $sesiones > 0 ? ($totalDuracion / $sesiones) : 0;

// Calcular total de horas de alumnos
$totalHorasAlumnos = $totalDuracion / 60;

// Contar alumnos únicos
$alumnosUnicos = count(array_unique($alumnos));

// Preparar datos para JSON
$resumenData = [
    'sesiones' => $sesiones,
    'totalHorasAlumnos' => $totalHorasAlumnos,
    'duracionMediaSesion' => $duracionMediaSesion,
    'totalHorasTalent' => $totalHorasTalent,
    'profesores' => $alumnosUnicos
];

// Enviar respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($resumenData);

$conn->close();
