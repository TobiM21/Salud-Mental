<?php
// conexion.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydbproyectos2";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

// Eliminar paciente
if (isset($_POST['eliminar'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM persona_paciente WHERE idPersona = ?");
        $stmt->execute([$_POST['eliminar']]);
        $mensaje = "Paciente eliminado exitosamente";
    } catch(PDOException $e) {
        $error = "Error al eliminar paciente: " . $e->getMessage();
    }
}

// Cargar datos para editar
if (isset($_GET['editar'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM persona_paciente WHERE idPersona = ?");
        $stmt->execute([$_GET['editar']]);
        $pacienteEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error al cargar paciente: " . $e->getMessage();
    }
}

// Procesar actualización
if (isset($_POST['actualizar'])) {
    try {
        $sql = "UPDATE persona_paciente SET 
                apeynom = :apeynom,
                direccion = :direccion,
                cuil = :cuil,
                fec_nac = :fec_nac,
                genero = :genero,
                num_celular = :num_celular
                WHERE idPersona = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':apeynom' => $_POST['apeynom'],
            ':direccion' => $_POST['direccion'],
            ':cuil' => $_POST['cuil'],
            ':fec_nac' => $_POST['fec_nac'],
            ':genero' => $_POST['genero'],
            ':num_celular' => $_POST['num_celular'],
            ':id' => $_POST['id']
        ]);
        
        $mensaje = "Paciente actualizado exitosamente";
        header("Location: formulario_paciente.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error al actualizar paciente: " . $e->getMessage();
    }
}

// Insertar nuevo paciente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar'])) {
    try {
        $sql = "INSERT INTO persona_paciente (apeynom, direccion, cuil, fec_nac, genero, num_celular) 
                VALUES (:apeynom, :direccion, :cuil, :fec_nac, :genero, :num_celular)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':apeynom' => $_POST['apeynom'],
            ':direccion' => $_POST['direccion'],
            ':cuil' => $_POST['cuil'],
            ':fec_nac' => $_POST['fec_nac'],
            ':genero' => $_POST['genero'],
            ':num_celular' => $_POST['num_celular']
        ]);
        
        $mensaje = "Paciente registrado exitosamente";
    } catch(PDOException $e) {
        $error = "Error al registrar paciente: " . $e->getMessage();
    }
}

// Obtener lista de pacientes
try {
    $stmt = $conn->query("SELECT * FROM persona_paciente");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener pacientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro de que desea eliminar este paciente?')) {
                document.getElementById('form-eliminar-' + id).submit();
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Formulario de Registro/Edición -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-6">
                <?php echo isset($pacienteEditar) ? 'Editar Paciente' : 'Registro de Paciente'; ?>
            </h2>
            
            <?php if (isset($mensaje)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?php if (isset($pacienteEditar)): ?>
                    <input type="hidden" name="id" value="<?php echo $pacienteEditar['idPersona']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Apellido y Nombre
                        </label>
                        <input type="text" name="apeynom" required
                            value="<?php echo isset($pacienteEditar) ? $pacienteEditar['apeynom'] : ''; ?>"
                            class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            CUIL
                        </label>
                        <input type="text" name="cuil" required
                            value="<?php echo isset($pacienteEditar) ? $pacienteEditar['cuil'] : ''; ?>"
                            class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección
                        </label>
                        <input type="text" name="direccion"
                            value="<?php echo isset($pacienteEditar) ? $pacienteEditar['direccion'] : ''; ?>"
                            class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" name="fec_nac"
                            value="<?php echo isset($pacienteEditar) ? $pacienteEditar['fec_nac'] : ''; ?>"
                            class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Género
                        </label>
                        <select name="genero" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Seleccione género</option>
                            <option value="Masculino" <?php echo (isset($pacienteEditar) && $pacienteEditar['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Femenino" <?php echo (isset($pacienteEditar) && $pacienteEditar['genero'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                            <option value="Otro" <?php echo (isset($pacienteEditar) && $pacienteEditar['genero'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Celular
                        </label>
                        <input type="tel" name="num_celular"
                            value="<?php echo isset($pacienteEditar) ? $pacienteEditar['num_celular'] : ''; ?>"
                            class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="reset"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </button>
                    <button type="submit" name="<?php echo isset($pacienteEditar) ? 'actualizar' : 'guardar'; ?>"
                        class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <?php echo isset($pacienteEditar) ? 'Actualizar' : 'Guardar'; ?> Paciente
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Pacientes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-6">Lista de Pacientes</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Nombre y Apellido</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">CUIL</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Dirección</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Género</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">N° Celular</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($pacientes as $paciente): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($paciente['idPersona']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($paciente['apeynom']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($paciente['cuil']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($paciente['direccion']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($paciente['genero']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($paciente['num_celular']); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-3">
                                        <a href="?editar=<?php echo $paciente['idPersona']; ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form id="form-eliminar-<?php echo $paciente['idPersona']; ?>" method="POST" class="inline">
                                            <input type="hidden" name="eliminar" value="<?php echo $paciente['idPersona']; ?>">
                                            <button type="button" onclick="confirmarEliminacion(<?php echo $paciente['idPersona']; ?>)"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>