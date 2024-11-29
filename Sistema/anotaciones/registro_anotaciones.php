<?php
// registro_anotaciones.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydbproyectos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener lista de pacientes para el select
$sql_pacientes = "SELECT idPersona, apeynom FROM persona_paciente ORDER BY apeynom";
$resultado_pacientes = $conn->query($sql_pacientes);

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_paciente = $_POST['paciente'];
    $anotaciones = $_POST['anotaciones'];
    $fecha = date('Y-m-d');
    $hora_inicio = date('H:i:s');
    $hora_fin = date('H:i:s');

    try {
        $sql = "INSERT INTO seguimiento_consulta (
                    Num_sesion,
                    Fecha,
                    Hora_inicio,
                    Hora_fin,
                    Objetivo_consulta,
                    Resumen_sesion,
                    Plan_intervencion,
                    Avance_paciente,
                    persona_paciente_idPersona
                ) VALUES (
                    (SELECT COALESCE(MAX(Num_sesion), 0) + 1 
                     FROM (SELECT Num_sesion FROM seguimiento_consulta 
                           WHERE persona_paciente_idPersona = ?) AS t),
                    ?, ?, ?, ?, ?, ?, ?, ?
                )";

        $stmt = $conn->prepare($sql);
        
        // Bind exactamente 9 parámetros
        $stmt->bind_param(
            "isssssssi", 
            $id_paciente,    // Para el WHERE de la subconsulta
            $fecha,          // Fecha
            $hora_inicio,    // Hora_inicio
            $hora_fin,       // Hora_fin
            $anotaciones,    // Objetivo_consulta
            $anotaciones,    // Resumen_sesion
            $anotaciones,    // Plan_intervencion
            $anotaciones,    // Avance_paciente
            $id_paciente     // persona_paciente_idPersona
        );

        if ($stmt->execute()) {
            $mensaje = "Anotaciones guardadas exitosamente.";
            $tipo_mensaje = "success";
        } else {
            throw new Exception("Error en execute(): " . $stmt->error);
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Registro de Anotaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            background-color: #D1D1D1;
            color: #4C4C4C;
            min-height: 100vh;
            padding-top: 90px;
        }

        .header {
            background-color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(76, 76, 76, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            justify-content: space-between;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 1.8rem;
            color: #85C7F2;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            text-decoration: none;
        }
        
        .logo-img {
            height: 60px;
            width: auto;
            transition: transform 0.3s ease;
        }
        
        .main-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(76, 76, 76, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }

        h1 {
            color: #4C4C4C;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4C4C4C;
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D1D1;
            border-radius: 8px;
            font-size: 1rem;
            color: #4C4C4C;
            background-color: white;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: #85C7F2;
            box-shadow: 0 0 0 3px rgba(133, 199, 242, 0.2);
        }

        textarea {
            min-height: 200px;
            resize: vertical;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1.05rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #85C7F2;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(133, 199, 242, 0.3);
        }

        .btn-secondary {
            background-color: #D1D1D1;
            color: #4C4C4C;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 76, 76, 0.2);
        }

        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .mensaje.success {
            background-color: #e8f7f0;
            color: #0f6848;
            border: 1px solid #b7e4d3;
        }

        .mensaje.error {
            background-color: #fdf0f0;
            color: #c02b2b;
            border: 1px solid #f5c6c6;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
                padding: 10px;
            }

            .logo {
                margin-bottom: 15px;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .main-content {
                padding: 10px;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="../index.html" class="logo">
                <img src="../logo.png" alt="Logo SGP" class="logo-img">
                <span>Sistema de Gestión de Pacientes</span>
            </a>
        </div>
    </header>

    <main class="main-content">
        <div class="card">
            <h1>Registro de Anotaciones de Sesión</h1>

            <?php if (isset($mensaje)): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="paciente">Seleccionar Paciente:</label>
                    <select name="paciente" id="paciente" required>
                        <option value="">Seleccione un paciente</option>
                        <?php while($paciente = $resultado_pacientes->fetch_assoc()): ?>
                            <option value="<?php echo $paciente['idPersona']; ?>">
                                <?php echo htmlspecialchars($paciente['apeynom']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="anotaciones">Anotaciones de la Sesión:</label>
                    <textarea 
                        name="anotaciones" 
                        id="anotaciones" 
                        required
                        placeholder="Escribe aquí las notas de la sesión..."
                    ></textarea>
                </div>

                <div class="buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Anotaciones
                    </button>
                    <a href="../index.html" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Limpiar mensaje después de 5 segundos
        setTimeout(() => {
            const mensaje = document.querySelector('.mensaje');
            if (mensaje) {
                mensaje.style.transition = 'opacity 0.5s ease';
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>

<?php
$conn->close();
?>