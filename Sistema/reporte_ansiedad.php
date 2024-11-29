<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydbproyectos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Habilitar reporte de errores de MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Consulta SQL con mejor manejo de errores
try {
    $sql = "SELECT 
                pp.apeynom,
                pp.fec_nac,
                e.nombre_escuela,
                hc.fecha_creacion,
                hc.motivo_consulta,
                hc.diagnostico,
                hc.objetivos_terapeuticos,
                hc.plan_intervencion,
                hc.antecedentes_escolares,
                hc.observaciones_generales
            FROM 
                historial_clinico hc
                INNER JOIN persona_paciente pp ON hc.id_persona = pp.idPersona
                INNER JOIN escolaridad e ON hc.escolaridad_id_escolaridad = e.id_escolaridad
            WHERE 
                TIMESTAMPDIFF(YEAR, pp.fec_nac, CURDATE()) BETWEEN 6 AND 8
                AND hc.diagnostico LIKE '%ansiedad%'
                AND e.nombre_escuela IS NOT NULL
            ORDER BY 
                pp.apeynom, hc.fecha_creacion DESC";

    // Ejecutar la consulta y verificar errores
    $resultado = $conn->query($sql);
    
    if ($resultado === false) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    // Debug: Imprimir la consulta SQL
    // echo "<pre>$sql</pre>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Historias Clínicas - SGP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #4C4C4C;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4C4C4C;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2rem;
        }

        .tabla-reporte {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla-reporte th, 
        .tabla-reporte td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .tabla-reporte th {
            background-color: #85C7F2;
            color: white;
            font-weight: 500;
        }

        .tabla-reporte tr:hover {
            background-color: #f8f9fa;
        }

        .btn-exportar {
            background-color: #85C7F2;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-exportar:hover {
            background-color: #6bb8e8;
        }

        .sin-resultados {
            text-align: center;
            padding: 40px;
            font-size: 1.1rem;
            color: #6c757d;
        }

        .error-message {
            background-color: #fee;
            color: #c00;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        @media print {
            .btn-exportar,
            .filtros {
                display: none;
            }
            
            .tabla-reporte {
                font-size: 10pt;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historias Clínicas - Pacientes Escolarizados (6-8 años) con Ansiedad</h1>
        
        <button class="btn-exportar" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> Exportar a PDF
        </button>

        <?php
        // Verificar si hay resultados
        if ($resultado && $resultado->num_rows > 0): ?>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Edad</th>
                        <th>Escuela</th>
                        <th>Fecha Consulta</th>
                        <th>Motivo Consulta</th>
                        <th>Diagnóstico</th>
                        <th>Objetivos Terapéuticos</th>
                        <th>Plan de Intervención</th>
                        <th>Antecedentes Escolares</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <?php 
                            $fecha_nac = new DateTime($fila['fec_nac']);
                            $hoy = new DateTime();
                            $edad = $fecha_nac->diff($hoy)->y;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['apeynom']); ?></td>
                            <td><?php echo $edad; ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre_escuela']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($fila['fecha_creacion']))); ?></td>
                            <td><?php echo htmlspecialchars($fila['motivo_consulta']); ?></td>
                            <td><?php echo htmlspecialchars($fila['diagnostico']); ?></td>
                            <td><?php echo htmlspecialchars($fila['objetivos_terapeuticos']); ?></td>
                            <td><?php echo htmlspecialchars($fila['plan_intervencion']); ?></td>
                            <td><?php echo htmlspecialchars($fila['antecedentes_escolares']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="sin-resultados">
                <i class="fas fa-info-circle"></i>
                <p>No se encontraron resultados que coincidan con los criterios especificados.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>