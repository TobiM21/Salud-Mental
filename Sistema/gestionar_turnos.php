<?php
$conn = new mysqli("localhost", "root", "", "mydbproyectos2");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Eliminar turno si se solicita
if(isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conn->query("DELETE FROM turno WHERE idTurno = $id");
}

$sql = "SELECT 
    t.idTurno,
    t.fecha_turno,
    t.estado_turno,
    tt.tipo_turno,
    pp.apeynom
FROM turno t
LEFT JOIN tipo_turno tt ON t.tipo_turno_id_tipo = tt.id_tipo
LEFT JOIN usuario u ON u.turno_idTurno = t.idTurno
LEFT JOIN seguimiento_consulta sc ON u.id_seguimiento = sc.id_seguimiento
LEFT JOIN persona_paciente pp ON sc.persona_paciente_idPersona = pp.idPersona";

$turnos = $conn->query($sql);
if (!$turnos) {
    die("Error en la consulta: " . $conn->error);
}

$turnos = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background-color: #f5f5f5; 
            font-family: system-ui; 
            padding: 20px; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            background: #85C7F2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #6bb8e8; }
        .tabla-turnos {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-turnos th, .tabla-turnos td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tabla-turnos th { background: #85C7F2; color: white; }
        .btn-eliminar {
            color: #dc3545;
            cursor: pointer;
        }
        .estado-pendiente { color: #ffc107; }
        .estado-completado { color: #28a745; }
        .estado-cancelado { color: #dc3545; }

         
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-volver {
            background-color: #6c757d;
        }
        
        .btn-volver:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-buttons">
            <a href="http://localhost:3000/Fran%20y%20Tobi/index.html" class="btn btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>
        <h1>Gestión de Turnos</h1>
        <a href="agregar_turno.php" class="btn">
            <i class="fas fa-plus"></i> Nuevo Turno
        </a>
    </div>

        <table class="tabla-turnos">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Fecha y Hora</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($turno = $turnos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($turno['apeynom']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($turno['fecha_turno'])); ?></td>
                        <td><?php echo htmlspecialchars($turno['tipo_turno']); ?></td>
                        <td>
                            <span class="estado-<?php echo strtolower($turno['estado_turno']); ?>">
                                <?php echo htmlspecialchars($turno['estado_turno']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?eliminar=<?php echo $turno['idTurno']; ?>" 
                               class="btn-eliminar"
                               onclick="return confirm('¿Está seguro de eliminar este turno?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
