<?php
session_start();
require_once 'conexion.php';
require_once 'PacienteController.php';

class BusquedaController {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    public function buscarPacientes($busqueda = '', $estado = '', $ordenar = '') {
        try {
            $sql = "SELECT p.id_paciente, pp.apeynom, pp.dni, pp.direccion, pp.estado, p.fecha_alta 
                    FROM paciente p 
                    JOIN persona_paciente pp ON p.entidad_idPersona = pp.idPersona 
                    WHERE 1=1";
            $params = [];
            $types = "";
            
            // Agregar condición de búsqueda si existe
            if (!empty($busqueda)) {
                $sql .= " AND (pp.apeynom LIKE ? OR pp.dni LIKE ? OR pp.direccion LIKE ?)";
                $busquedaParam = "%{$busqueda}%";
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
                $types .= "sss";
            }
            
            // Agregar filtro de estado si existe
            if (!empty($estado)) {
                $sql .= " AND pp.estado = ?";
                $params[] = $estado;
                $types .= "s";
            }
            
            // Agregar ordenamiento
            switch ($ordenar) {
                case 'nombre':
                    $sql .= " ORDER BY pp.apeynom";
                    break;
                case 'fecha':
                    $sql .= " ORDER BY p.fecha_alta DESC";
                    break;
                case 'dni':
                    $sql .= " ORDER BY pp.dni";
                    break;
                default:
                    $sql .= " ORDER BY p.id_paciente DESC";
            }
            
            $stmt = $this->conexion->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en la búsqueda: " . $e->getMessage());
            return [];
        }
    }
}

// Procesar la búsqueda
$busquedaController = new BusquedaController($conexion);

// Obtener parámetros de búsqueda
$termino_busqueda = $_GET['buscar'] ?? '';
$estado = $_GET['estado'] ?? '';
$ordenar = $_GET['ordenar'] ?? '';

// Realizar la búsqueda
$pacientes = $busquedaController->buscarPacientes($termino_busqueda, $estado, $ordenar);

// Si es una petición AJAX, devolver resultados en JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($pacientes);
    exit;
}

// Si no es AJAX, continuar con el renderizado normal de la página
?>

<!-- Agregar JavaScript para búsqueda dinámica -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    const estadoSelect = document.querySelector('select[name="estado"]');
    const ordenarSelect = document.querySelector('select[name="ordenar"]');
    
    function realizarBusqueda() {
        const busqueda = searchInput.value;
        const estado = estadoSelect.value;
        const ordenar = ordenarSelect.value;
        
        fetch(`busqueda.php?buscar=${busqueda}&estado=${estado}&ordenar=${ordenar}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('.results-table tbody');
            tbody.innerHTML = '';
            
            data.forEach(paciente => {
                tbody.innerHTML += `
                    <tr>
                        <td>${paciente.apeynom}</td>
                        <td>${paciente.dni}</td>
                        <td>${paciente.direccion}</td>
                        <td>
                            <span class="estado-${paciente.estado.toLowerCase()}">
                                ${paciente.estado}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" onclick="editarPaciente(${paciente.id_paciente})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="eliminarPaciente(${paciente.id_paciente})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Eventos para realizar búsqueda
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        realizarBusqueda();
    });
    
    // Búsqueda en tiempo real mientras se escribe (con debounce)
    let timeoutId;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(realizarBusqueda, 300);
    });
    
    // Realizar búsqueda cuando cambian los filtros
    estadoSelect.addEventListener('change', realizarBusqueda);
    ordenarSelect.addEventListener('change', realizarBusqueda);
});

function editarPaciente(id) {
    window.location.href = `editar_paciente.php?id=${id}`;
}

function eliminarPaciente(id) {
    if (confirm('¿Está seguro de que desea eliminar este paciente?')) {
        window.location.href = `procesar_paciente.php?action=eliminar&id=${id}`;
    }
}
</script>