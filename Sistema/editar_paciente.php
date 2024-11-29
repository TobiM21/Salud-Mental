<?php
session_start();
require_once 'conexion.php';
require_once 'PacienteController.php';

$controller = new PacienteController($conexion);
$id = $_GET['id'] ?? null;
$paciente = $controller->obtenerPorId($id);

if (!$paciente) {
    header('Location: formulario_paciente.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Paciente</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #85C7F2;
            --secondary-color: #636363;
            --background-color: #D1D1D1;
            --light-gray: #DBDBDB;
            --dark-gray: #4C4C4C;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 2rem;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 2rem;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            background-color: var(--light-gray);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--secondary-color);
            background-color: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(133, 199, 242, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--dark-gray));
        }

        .btn-secondary {
            background: var(--secondary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: var(--dark-gray);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat me-2"></i>
                SGP
            </a>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Paciente
                </h4>
            </div>
            <div class="modal-body">
    <form id="editarPacienteForm">
        <input type="hidden" id="edit_id_paciente" name="id_paciente">
        <input type="hidden" name="action" value="actualizar">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Apellido y Nombre</label>
                <input type="text" id="edit_apeynom" name="apeynom" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">DNI/CUIL</label>
                <input type="text" id="edit_dni" name="dni" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Dirección</label>
                <input type="text" id="edit_direccion" name="direccion" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" id="edit_fec_nac" name="fec_nac" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Género</label>
                <select id="edit_genero" name="genero" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Número de Celular</label>
                <input type="tel" id="edit_num_celular" name="num_celular" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Estado del Paciente</label>
                <select id="edit_estado" name="estado" class="form-select" required>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>
        </div>
    </form>
</div>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>