<?php
session_start();
require_once 'conexion.php';
require_once 'PacienteController.php';

$controller = new PacienteController($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                if ($controller->crear($_POST)) {
                    $_SESSION['mensaje'] = "Paciente registrado exitosamente";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al registrar el paciente";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                break;
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
        if ($controller->eliminar($_GET['id'])) {
            $_SESSION['mensaje'] = "Paciente eliminado exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar el paciente";
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
}

header('Location: formulario_paciente.php');
exit;
?>