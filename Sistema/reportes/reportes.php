<?php
// reportes.php
$reportes = [
    [
        'id' => 'historial',
        'titulo' => 'Reporte de Historiales Clínicos',
        'descripcion' => 'Visualizar historiales clínicos completos de los pacientes, incluyendo diagnósticos, objetivos terapéuticos y plan de tratamiento.',
        'url' => 'reporte_historial.php',
        'icono' => 'fa-file-medical' // Ícono específico para historial médico
    ],
    [
        'id' => 'escolar',
        'titulo' => 'Reporte de Pacientes por Escuela',
        'descripcion' => 'Analizar la distribución de pacientes por escuela, incluyendo estadísticas y gráficos de diagnósticos.',
        'url' => 'reporte_escolar.php',
        'icono' => 'fa-school' // Ícono específico para reportes escolares
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reportes - SGP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #F5F7FA;
            --text-primary: #2D3748;
            --text-secondary: #4A5568;
            --hover-color: #357ABD;
        }

        body { 
            background-color: var(--secondary-color); 
            padding: 20px; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 2rem; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 
                       0 1px 3px rgba(0, 0, 0, 0.1); 
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        h1 {
            color: var(--text-primary);
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0;
            padding: 1rem 0;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .reporte-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            transition: all 0.3s ease;
            border: 1px solid #E2E8F0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .reporte-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }

        .reporte-card h2 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .reporte-card i {
            color: var(--primary-color);
            font-size: 1.75rem;
        }

        .reporte-card p {
            color: var(--text-secondary);
            margin: 1rem 0;
            line-height: 1.6;
            flex-grow: 1;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            width: 100%;
            margin-top: auto;
        }

        .btn:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }

        .btn-volver {
            background: #64748B;
            padding: 0.75rem 1.25rem;
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-volver:hover {
            background: #475569;
        }

        .actions {
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .reportes-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 1.75rem;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="actions">
            <a href="../index.html" class="btn btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>

        <div class="header">
            <h1>Sistema de Reportes</h1>
            <p class="subtitle">Seleccione el tipo de reporte que desea generar</p>
        </div>

        <div class="reportes-grid">
            <?php foreach ($reportes as $reporte): ?>
                <div class="reporte-card">
                    <h2>
                        <i class="fas <?php echo htmlspecialchars($reporte['icono']); ?>"></i>
                        <?php echo htmlspecialchars($reporte['titulo']); ?>
                    </h2>
                    <p><?php echo htmlspecialchars($reporte['descripcion']); ?></p>
                    <a href="<?php echo htmlspecialchars($reporte['url']); ?>" class="btn">
                        <i class="fas fa-chart-line"></i>
                        Generar Reporte
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>