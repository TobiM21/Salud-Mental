<?php
require_once 'conexion.php';
require_once 'PacienteController.php';

if (isset($_GET['id'])) {
    $controller = new PacienteController($conexion);
    $paciente = $controller->obtenerPorId($_GET['id']);
    
    if ($paciente) {
        header('Content-Type: application/json');
        echo json_encode($paciente);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Paciente no encontrado']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
}
?>