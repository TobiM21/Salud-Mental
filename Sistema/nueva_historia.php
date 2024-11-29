<?php
// nueva_historia.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydbproyectos2";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener lista de pacientes para el select
try {
    $stmt = $conn->query("SELECT idPersona, apeynom FROM persona_paciente ORDER BY apeynom");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener pacientes: " . $e->getMessage();
}

// Obtener lista de escuelas para el select
try {
    $stmt = $conn->query("SELECT id_escolaridad, nombre_escuela FROM escolaridad");
    $escuelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener escuelas: " . $e->getMessage();
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO historial_clinico (
                    fecha_creacion,
                    evaluacion_inicial,
                    motivo_consulta,
                    diagnostico,
                    objetivos_terapeuticos,
                    plan_tratamiento,
                    antecedentes_escolares,
                    observaciones_generales,
                    fecha_ultima_actualizacion_hc,
                    id_persona,
                    escolaridad_id_escolaridad
                ) VALUES (
                    :fecha_creacion,
                    :evaluacion_inicial,
                    :motivo_consulta,
                    :diagnostico,
                    :objetivos_terapeuticos,
                    :plan_tratamiento,
                    :antecedentes_escolares,
                    :observaciones_generales,
                    :fecha_ultima_actualizacion_hc,
                    :id_persona,
                    :escolaridad_id_escolaridad
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':fecha_creacion' => $_POST['fecha_historia'],
            ':evaluacion_inicial' => $_POST['evaluacion_inicial'],
            ':motivo_consulta' => $_POST['motivo_consulta'],
            ':diagnostico' => $_POST['diagnostico'],
            ':objetivos_terapeuticos' => $_POST['objetivos_terapeuticos'],
            ':plan_tratamiento' => $_POST['plan_tratamiento'],
            ':antecedentes_escolares' => $_POST['antecedentes_escolares'],
            ':observaciones_generales' => $_POST['observaciones_generales'],
            ':fecha_ultima_actualizacion_hc' => date('Y-m-d'),
            ':id_persona' => $_POST['id_paciente'],
            ':escolaridad_id_escolaridad' => $_POST['escolaridad']
        ]);

        $mensaje = "Historia clínica guardada exitosamente";
        $tipo_mensaje = "success";
    } catch(PDOException $e) {
        $mensaje = "Error al guardar la historia clínica: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nueva Historia Clínica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Aquí va todo el CSS que proporcionaste */
        /* ... */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #D1D1D1;
            color: #4C4C4C;
            line-height: 1.5;
        }

        .form-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(76, 76, 76, 0.1);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #DBDBDB;
        }

        .form-title {
            color: #636363;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .status-badge {
            background-color: #85C7F2;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-section {
            background-color: #DBDBDB;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: #4C4C4C;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #85C7F2;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #636363;
            font-size: 0.875rem;
        }

        .required::after {
            content: "*";
            color: #85C7F2;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #DBDBDB;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #85C7F2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(133, 199, 242, 0.2);
        }

        .form-control:hover {
            border-color: #85C7F2;
        }

        .btn-primary {
            background: #85C7F2;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #636363;
        }

        .btn-secondary {
            background: white;
            color: #636363;
            border: 1px solid #DBDBDB;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #DBDBDB;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            background-color: #85C7F2;
            color: white;
            border: none;
        }

        .character-count {
            font-size: 0.75rem;
            color: #636363;
            text-align: right;
            margin-top: 0.25rem;
        }

        .form-help {
            font-size: 0.75rem;
            color: #636363;
            margin-top: 0.25rem;
        }

        .tooltip-text {
            background-color: #4C4C4C;
            color: white;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #DBDBDB;
        }

        .alert-success {
            background-color: #4CAF50;
            color: white;
        }
        
        .alert-error {
            background-color: #f44336;
            color: white;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Historia Clínica</h2>
            <span class="status-badge">Nuevo Registro</span>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php else: ?>
            <div class="alert">
                Complete todos los campos marcados con * para guardar la historia clínica.
            </div>
        <?php endif; ?>

        <form id="historiaClinicaForm" method="POST" onsubmit="return validateForm(event)">
            <!-- Información Básica -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información Básica
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Fecha de Historia</label>
                        <input 
                            type="datetime-local" 
                            class="form-control" 
                            name="fecha_historia" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Paciente</label>
                        <select name="id_paciente" class="form-control" required>
                            <option value="">Seleccione un paciente</option>
                            <?php foreach ($pacientes as $paciente): ?>
                                <option value="<?php echo $paciente['idPersona']; ?>">
                                    <?php echo htmlspecialchars($paciente['apeynom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Escuela</label>
                        <select name="escolaridad" class="form-control" required>
                            <option value="">Seleccione una escuela</option>
                            <?php foreach ($escuelas as $escuela): ?>
                                <option value="<?php echo $escuela['id_escolaridad']; ?>">
                                    <?php echo htmlspecialchars($escuela['nombre_escuela']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Evaluación Clínica -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-stethoscope"></i>
                    Evaluación Clínica
                </h3>

                <div class="form-group">
                    <label class="form-label required">Evaluación Inicial</label>
                    <textarea 
                        class="form-control" 
                        name="evaluacion_inicial" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'evaluacion-count')"
                    ></textarea>
                    <div class="character-count" id="evaluacion-count">0/1000 caracteres</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Motivo de Consulta</label>
                    <textarea 
                        class="form-control" 
                        name="motivo_consulta" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'motivo-count')"
                    ></textarea>
                    <div class="character-count" id="motivo-count">0/1000 caracteres</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Antecedentes Escolares</label>
                    <textarea 
                        class="form-control" 
                        name="antecedentes_escolares" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'antecedentes-count')"
                    ></textarea>
                    <div class="character-count" id="antecedentes-count">0/1000 caracteres</div>
                </div>
            </div>

            <!-- Plan Terapéutico -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Plan Terapéutico
                </h3>

                <div class="form-group">
                    <label class="form-label required">Objetivos Terapéuticos</label>
                    <textarea 
                        class="form-control" 
                        name="objetivos_terapeuticos" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'objetivos-count')"
                    ></textarea>
                    <div class="character-count" id="objetivos-count">0/1000 caracteres</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Diagnóstico</label>
                    <textarea 
                        class="form-control" 
                        name="diagnostico" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'diagnostico-count')"
                    ></textarea>
                    <div class="character-count" id="diagnostico-count">0/1000 caracteres</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Plan de Tratamiento</label>
                    <textarea 
                        class="form-control" 
                        name="plan_tratamiento" 
                        maxlength="1000" 
                        required
                        oninput="updateCharCount(this, 'plan-count')"
                    ></textarea>
                    <div class="character-count" id="plan-count">0/1000 caracteres</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones Generales</label>
                    <textarea 
                        class="form-control" 
                        name="observaciones_generales" 
                        maxlength="1000"
                        oninput="updateCharCount(this, 'observaciones-count')"
                    ></textarea>
                    <div class="character-count" id="observaciones-count">0/1000 caracteres</div>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    💾 Guardar Historia Clínica
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    ↺ Limpiar Formulario
                </button>
            </div>
        </form>
    </div>

    <script>
        // El mismo JavaScript que proporcionaste, con algunas modificaciones
        function updateCharCount(textarea, counterId) {
            const maxLength = textarea.maxLength;
            const currentLength = textarea.value.length;
            document.getElementById(counterId).textContent = 
                `${currentLength}/${maxLength} caracteres`;
        }

        function validateForm(event) {
            // La validación básica la maneja HTML5 con required
            return true;
        }

        function resetForm() {
            if (confirm('¿Está seguro de que desea limpiar el formulario? Se perderán todos los datos ingresados.')) {
                document.getElementById('historiaClinicaForm').reset();
                // Resetear todos los contadores
                const counters = [
                    'evaluacion-count', 'motivo-count', 'antecedentes-count',
                    'objetivos-count', 'diagnostico-count', 'plan-count',
                    'observaciones-count'
                ];
                counters.forEach(counter => {
                    document.getElementById(counter).textContent = '0/1000 caracteres';
                });
            }
        }

        // Establecer fecha actual como valor por defecto
        window.onload = function() {
            const now = new Date();
            const offset = now.getTimezoneOffset();
            now.setMinutes(now.getMinutes() - offset);
            document.querySelector('input[name="fecha_historia"]').value = now.toISOString().slice(0, 16);
        }
    </script>
</body>
</html>