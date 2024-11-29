<?php
// reporte_historial.php
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

$where_clause = "1=1"; // Condición base
$params = [];

// Procesar filtros si se enviaron
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['nombre'])) {
        $where_clause .= " AND pp.apeynom LIKE :nombre";
        $params[':nombre'] = '%' . $_POST['nombre'] . '%';
    }
    if (!empty($_POST['fecha_desde'])) {
        $where_clause .= " AND hc.fecha_creacion >= :fecha_desde";
        $params[':fecha_desde'] = $_POST['fecha_desde'];
    }
    if (!empty($_POST['fecha_hasta'])) {
        $where_clause .= " AND hc.fecha_creacion <= :fecha_hasta";
        $params[':fecha_hasta'] = $_POST['fecha_hasta'];
    }
    if (!empty($_POST['escuela'])) {
        $where_clause .= " AND e.id_escolaridad = :escuela";
        $params[':escuela'] = $_POST['escuela'];
    }
}

// Obtener escuelas para el filtro
try {
    $stmt = $conn->query("SELECT * FROM escolaridad");
    $escuelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener escuelas: " . $e->getMessage();
}

// Ejecutar la consulta principal
try {
    $sql = "SELECT 
                pp.idPersona,
                pp.apeynom,
                pp.cuil,
                pp.fec_nac,
                e.nombre_escuela,
                hc.fecha_creacion,
                hc.motivo_consulta,
                hc.diagnostico,
                hc.objetivos_terapeuticos,
                hc.plan_tratamiento,
                hc.antecedentes_escolares,
                hc.observaciones_generales
            FROM 
                persona_paciente pp
                LEFT JOIN historial_clinico hc ON pp.idPersona = hc.id_persona
                LEFT JOIN escolaridad e ON hc.escolaridad_id_escolaridad = e.id_escolaridad
            WHERE " . $where_clause . "
            ORDER BY pp.apeynom, hc.fecha_creacion DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Historiales Clínicos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function imprimirReporte() {
            window.print();
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
            <h2 class="text-xl font-semibold mb-6">Filtros del Reporte</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre del Paciente
                        </label>
                        <input type="text" name="nombre" 
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                               class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha Desde
                        </label>
                        <input type="date" name="fecha_desde"
                               value="<?php echo isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : ''; ?>"
                               class="w-full p-3 border border-gray-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha Hasta
                        </label>
                        <input type="date" name="fecha_hasta"
                               value="<?php echo isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : ''; ?>"
                               class="w-full p-3 border border-gray-200 rounded-lg">
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
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Generar Reporte
                    </button>
                    <button type="button" onclick="imprimirReporte()" 
                            class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Imprimir
                    </button>
                </div>
            </form>
        </div>

        <!-- Reporte -->
        <?php if (!empty($resultados)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Encabezado del Reporte -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold">Reporte de Historiales Clínicos</h1>
                    <p class="text-gray-600">Fecha de generación: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>

                <!-- Contenido del Reporte -->
                <?php foreach ($resultados as $i => $resultado): ?>
                    <?php if ($i > 0) echo '<div class="page-break"></div>'; ?>
                    <div class="mb-8 border-b pb-8">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <h3 class="font-bold text-lg mb-4">Información del Paciente</h3>
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($resultado['apeynom']); ?></p>
                                <p><strong>CUIL:</strong> <?php echo htmlspecialchars($resultado['cuil']); ?></p>
                                <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($resultado['fec_nac'])); ?></p>
                                <p><strong>Escuela:</strong> <?php echo htmlspecialchars($resultado['nombre_escuela'] ?? 'No especificada'); ?></p>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-4">Información del Historial</h3>
                                <p><strong>Fecha de Creación:</strong> <?php echo $resultado['fecha_creacion'] ? date('d/m/Y', strtotime($resultado['fecha_creacion'])) : 'No registrada'; ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold text-gray-700">Motivo de Consulta</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['motivo_consulta'] ?? 'No registrado')); ?></p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700">Diagnóstico</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['diagnostico'] ?? 'No registrado')); ?></p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700">Objetivos Terapéuticos</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['objetivos_terapeuticos'] ?? 'No registrados')); ?></p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700">Plan de Tratamiento</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['plan_tratamiento'] ?? 'No registrado')); ?></p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700">Antecedentes Escolares</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['antecedentes_escolares'] ?? 'No registrados')); ?></p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700">Observaciones Generales</h4>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($resultado['observaciones_generales'] ?? 'No registradas')); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500">No se encontraron registros que coincidan con los criterios de búsqueda.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>