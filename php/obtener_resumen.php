<?php
include('conexion.php');

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener datos del formulario
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$asesores = $_POST['asesor'] ?? [];
$sedes = $_POST['sede'] ?? [];
$categorias = $_POST['categoria'] ?? [];

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
$sql = "SELECT asesoria.ID, asesoria.Duracion, asesoria.Correo, COUNT(asesoria_asesor.id_Asesor) AS TotalAsesores
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        WHERE 1=1"; // Iniciar con una condición siempre verdadera

// Agregar condiciones de fecha solo si las fechas no están vacías
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
} elseif (!empty($fechaInicio)) {
    $sql .= " AND asesoria.Fecha >= '$fechaInicio'";
} elseif (!empty($fechaFin)) {
    $sql .= " AND asesoria.Fecha <= '$fechaFin'";
}

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
