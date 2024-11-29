<?php
$conn = new mysqli("localhost", "root", "", "mydbproyectos2");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Obtener pacientes y tipos de turno para los selectores
$pacientes = $conn->query("SELECT idPersona, apeynom FROM persona_paciente ORDER BY apeynom");
$tipos_turno = $conn->query("SELECT id_tipo, tipo_turno FROM tipo_turno");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paciente_id = $_POST['paciente'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $tipo = $_POST['tipo'];
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Combinar fecha y hora
        $fecha_hora = date('Y-m-d H:i:s', strtotime("$fecha $hora"));
        
        // 1. Insertar el turno
        $sql_turno = "INSERT INTO turno (fecha_turno, estado_turno, tipo_turno_id_tipo) 
                      VALUES ('$fecha_hora', 'Pendiente', $tipo)";
        $conn->query($sql_turno);
        $turno_id = $conn->insert_id;
        
        // 2. Crear seguimiento de consulta directamente vinculado al paciente
        $sql_seguimiento = "INSERT INTO seguimiento_consulta 
                           (persona_paciente_idPersona, id_seguimiento, 
                            Num_sesion, Fecha, objetivo_consulta) 
                           VALUES ($paciente_id, $turno_id, 
                                   '1', '$fecha', 'Primera consulta')";
        $conn->query($sql_seguimiento);
        
        // Si todo salió bien, confirmar los cambios
        $conn->commit();
        header("Location: gestionar_turnos.php");
        exit;
        
    } catch (Exception $e) {
        // Si algo salió mal, deshacer todos los cambios
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Turno</title>
    
    <style>
        body { 
            background-color: #f5f5f5; 
            font-family: system-ui; 
            padding: 20px; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #85C7F2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover { background: #6bb8e8; }

        .header-buttons {
            margin-bottom: 20px;
        }
        
        .btn-volver {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-volver:hover {
            background-color: #5a6268;
        }

        .time-group {
            display: flex;
            gap: 10px;
        }

        .time-group .form-group {
            flex: 1;
        }

        .select2-container {
            width: 100% !important;
        }

        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>
    <!-- Agregar Select2 para mejor experiencia en selección -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<div class="container">
    <div class="header-buttons">
        <a href="gestionar_turnos.php" class="btn btn-volver">
            <i class="fas fa-arrow-left"></i> Volver al módulo de turnos
        </a>
    </div>
    <h1>Nuevo Turno</h1>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="paciente">Paciente:</label>
            <select name="paciente" id="paciente" required>
                <option value="">Seleccione un paciente</option>
                <?php while($paciente = $pacientes->fetch_assoc()): ?>
                    <option value="<?php echo $paciente['idPersona']; ?>">
                        <?php echo htmlspecialchars($paciente['apeynom']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="time-group">
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" id="fecha" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="hora">Hora:</label>
                <input type="time" name="hora" id="hora" required 
                       min="08:00" max="18:00" 
                       step="1800"> <!-- Step de 30 minutos -->
            </div>
        </div>

        <div class="form-group">
            <label for="tipo">Tipo de Turno:</label>
            <select name="tipo" id="tipo" required>
                <option value="">Seleccione el tipo</option>
                <?php while($tipo = $tipos_turno->fetch_assoc()): ?>
                    <option value="<?php echo $tipo['id_tipo']; ?>">
                        <?php echo htmlspecialchars($tipo['tipo_turno']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Guardar Turno</button>
            <a href="gestionar_turnos.php" class="btn" style="margin-left: 10px">Cancelar</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Inicializar Select2 para el selector de pacientes
    $('#paciente').select2({
        placeholder: "Buscar paciente...",
        allowClear: true
    });

    // Inicializar Select2 para el selector de tipo de turno
    $('#tipo').select2({
        placeholder: "Seleccione el tipo de turno",
        allowClear: true
    });

    // Configurar fecha mínima y máxima para el input de hora
    const horaInput = document.querySelector('input[type="time"]');
    horaInput.addEventListener('change', function() {
        const hora = this.value;
        if (hora < "08:00" || hora > "18:00") {
            alert("Por favor seleccione un horario entre las 8:00 y las 18:00");
            this.value = "08:00";
        }
    });

    // Configurar fecha mínima para el input de fecha
    const fechaInput = document.querySelector('input[type="date"]');
    fechaInput.min = new Date().toISOString().split('T')[0];
});
</script>

</body>
</html>