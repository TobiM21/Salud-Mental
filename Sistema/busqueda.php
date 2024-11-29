<?php
// busqueda.php
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

// Obtener obras sociales para el select
try {
    $stmt = $conn->query("SELECT * FROM obra_social WHERE estado = 'Activo'");
    $obras_sociales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener obras sociales: " . $e->getMessage();
}

// Obtener escuelas para el select
try {
    $stmt = $conn->query("SELECT * FROM escolaridad");
    $escuelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener escuelas: " . $e->getMessage();
}

$resultados = [];
$busquedaRealizada = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "SELECT DISTINCT 
                    p.idPersona,
                    p.apeynom,
                    p.direccion,
                    p.cuil,
                    p.num_celular,
                    os.obra_descripcion,
                    e.nombre_escuela,
                    cc.estado_paciente,
                    cc.grupo_sanguineo,
                    cc.alergias,
                    t.tipo_problema,
                    t.estado_tratamiento
                FROM persona_paciente p
                LEFT JOIN obra_social_x_persona_paciente ospp ON p.idPersona = ospp.persona_paciente_idPersona
                LEFT JOIN obra_social os ON ospp.obra_social_id_obra = os.id_obra
                LEFT JOIN seguimiento_consulta sc ON p.idPersona = sc.persona_paciente_idPersona
                LEFT JOIN escolaridad e ON sc.escolaridad_id_escolaridad = e.id_escolaridad
                LEFT JOIN consulta_clinica_paciente cc ON sc.id_seguimiento = cc.seguimiento_consulta_id_seguimiento
                LEFT JOIN tratamiento t ON sc.tratamiento_idtratamiento = t.idtratamiento
                WHERE 1=1";

        $params = [];

        if (!empty($_POST['nombre'])) {
            $sql .= " AND p.apeynom LIKE :nombre";
            $params[':nombre'] = '%' . $_POST['nombre'] . '%';
        }

        if (!empty($_POST['cuil'])) {
            $sql .= " AND p.cuil LIKE :cuil";
            $params[':cuil'] = '%' . $_POST['cuil'] . '%';
        }

        if (!empty($_POST['obra_social'])) {
            $sql .= " AND os.id_obra = :obra_social";
            $params[':obra_social'] = $_POST['obra_social'];
        }

        if (!empty($_POST['escuela'])) {
            $sql .= " AND e.id_escolaridad = :escuela";
            $params[':escuela'] = $_POST['escuela'];
        }

        if (!empty($_POST['estado_paciente'])) {
            $sql .= " AND cc.estado_paciente = :estado_paciente";
            $params[':estado_paciente'] = $_POST['estado_paciente'];
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $busquedaRealizada = true;

    } catch(PDOException $e) {
        echo "Error en la búsqueda: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Pacientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Formulario de Búsqueda -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-6">Búsqueda de Pacientes</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre y Apellido
                        </label>
                        <input type="text" name="nombre" 
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                               class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            CUIL
                        </label>
                        <input type="text" name="cuil"
                               value="<?php echo isset($_POST['cuil']) ? htmlspecialchars($_POST['cuil']) : ''; ?>"
                               class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Obra Social
                        </label>
                        <select name="obra_social" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Todas</option>
                            <?php foreach ($obras_sociales as $obra): ?>
                                <option value="<?php echo $obra['id_obra']; ?>"
                                    <?php echo (isset($_POST['obra_social']) && $_POST['obra_social'] == $obra['id_obra']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($obra['obra_descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Escuela
                        </label>
                        <select name="escuela" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Todas</option>
                            <?php foreach ($escuelas as $escuela): ?>
                                <option value="<?php echo $escuela['id_escolaridad']; ?>"
                                    <?php echo (isset($_POST['escuela']) && $_POST['escuela'] == $escuela['id_escolaridad']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($escuela['nombre_escuela']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Estado del Paciente
                        </label>
                        <select name="estado_paciente" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Todos</option>
                            <option value="Activo" <?php echo (isset($_POST['estado_paciente']) && $_POST['estado_paciente'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="Inactivo" <?php echo (isset($_POST['estado_paciente']) && $_POST['estado_paciente'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Resultados de la Búsqueda -->
        <?php if ($busquedaRealizada): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-6">Resultados de la Búsqueda</h2>
                <?php if (empty($resultados)): ?>
                    <p class="text-gray-500">No se encontraron resultados.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Nombre</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">CUIL</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Obra Social</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Escuela</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Estado</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Grupo Sanguíneo</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Tratamiento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($resultados as $resultado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($resultado['apeynom']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['cuil']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['obra_descripcion']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['nombre_escuela']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['estado_paciente']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['grupo_sanguineo']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['tipo_problema']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>