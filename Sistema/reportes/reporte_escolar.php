<?php
// reporte_escolar.php
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

// Obtener lista de escuelas para el filtro
try {
    $stmt = $conn->query("SELECT * FROM escolaridad");
    $escuelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error al obtener escuelas: " . $e->getMessage();
}

$where_clause = "1=1";
$params = [];
$resultados = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Construir la consulta según los filtros
    if (!empty($_POST['mes'])) {
        $where_clause .= " AND MONTH(hc.fecha_creacion) = :mes";
        $params[':mes'] = $_POST['mes'];
    }
    
    if (!empty($_POST['escuela'])) {
        $where_clause .= " AND e.id_escolaridad = :escuela";
        $params[':escuela'] = $_POST['escuela'];
    }

    try {
        $sql = "SELECT 
                    pp.apeynom,
                    e.nombre_escuela,
                    hc.fecha_creacion,
                    hc.diagnostico,
                    hc.motivo_consulta,
                    COUNT(*) OVER (PARTITION BY e.nombre_escuela) as total_escuela
                FROM persona_paciente pp
                JOIN historial_clinico hc ON pp.idPersona = hc.id_persona
                JOIN escolaridad e ON hc.escolaridad_id_escolaridad = e.id_escolaridad
                WHERE $where_clause
                ORDER BY e.nombre_escuela, pp.apeynom";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pacientes por Escuela</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        function imprimirReporte() {
            window.print();
        }

        // Configuración del gráfico
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            <?php if (!empty($resultados)): ?>
            var data = google.visualization.arrayToDataTable([
                ['Escuela', 'Cantidad de Pacientes'],
                <?php
                $escuelas_contadas = [];
                foreach ($resultados as $resultado) {
                    if (!isset($escuelas_contadas[$resultado['nombre_escuela']])) {
                        echo "['" . addslashes($resultado['nombre_escuela']) . "', " . $resultado['total_escuela'] . "],";
                        $escuelas_contadas[$resultado['nombre_escuela']] = true;
                    }
                }
                ?>
            ]);

            var options = {
                title: 'Distribución de Pacientes por Escuela',
                pieHole: 0.4,
                colors: ['#4299E1', '#48BB78', '#ED8936', '#9F7AEA', '#F56565']
            };

            var chart = new google.visualization.PieChart(document.getElementById('grafico_distribucion'));
            chart.draw(data, options);
            <?php endif; ?>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mes
                        </label>
                        <select name="mes" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Todos los meses</option>
                            <?php
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            foreach ($meses as $num => $nombre): ?>
                                <option value="<?php echo $num; ?>" 
                                    <?php echo (isset($_POST['mes']) && $_POST['mes'] == $num) ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Escuela
                        </label>
                        <select name="escuela" class="w-full p-3 border border-gray-200 rounded-lg">
                            <option value="">Todas las escuelas</option>
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
                    <h1 class="text-2xl font-bold">Reporte de Pacientes por Escuela</h1>
                    <p class="text-gray-600">Fecha de generación: <?php echo date('d/m/Y H:i:s'); ?></p>
                    <?php if (!empty($_POST['mes'])): ?>
                        <p class="text-gray-600">Mes: <?php echo $meses[$_POST['mes']]; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Gráfico -->
                <div id="grafico_distribucion" class="w-full h-96 mb-8"></div>

                <!-- Resumen Estadístico -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Resumen Estadístico</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total de Pacientes</p>
                            <p class="text-2xl font-bold"><?php echo count($resultados); ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total de Escuelas</p>
                            <p class="text-2xl font-bold"><?php echo count($escuelas_contadas); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Resultados -->
                <?php
                $escuela_actual = '';
                foreach ($resultados as $resultado):
                    if ($escuela_actual != $resultado['nombre_escuela']):
                        if ($escuela_actual != '') echo '</tbody></table></div>';
                        $escuela_actual = $resultado['nombre_escuela'];
                ?>
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($resultado['nombre_escuela']); ?></h3>
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Alumno</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Fecha</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Motivo de Consulta</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Diagnóstico</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                <?php endif; ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($resultado['apeynom']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($resultado['fecha_creacion'])); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['motivo_consulta']); ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($resultado['diagnostico']); ?></td>
                                    </tr>
                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500">No se encontraron registros que coincidan con los criterios de búsqueda.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>