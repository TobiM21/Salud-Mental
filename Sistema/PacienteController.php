<?php
class PacienteController {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    public function crear($datos) {
        try {
            $this->conexion->begin_transaction();
            
            // Insertar en persona_paciente
            $sql = "INSERT INTO persona_paciente (
                apeynom, 
                cuil, 
                direccion,
                fec_nac,
                genero,
                num_celular
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conexion->error);
            }
            
            $stmt->bind_param("sssssi", 
                $datos['apeynom'],
                $datos['dni'],
                $datos['direccion'],
                $datos['fec_nac'],
                $datos['genero'],
                $datos['num_celular']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error insertando persona_paciente: " . $stmt->error);
            }
            
            $idPersona = $this->conexion->insert_id;
            
            // Insertar en consulta_clinica_paciente
            $sql = "INSERT INTO consulta_clinica_paciente (
                id_paciente,
                estado_paciente,
                fecha_alta
            ) VALUES (?, ?, CURRENT_DATE())";
            
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conexion->error);
            }
            
            $stmt->bind_param("is", 
                $idPersona,
                $datos['estado']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error insertando consulta_clinica: " . $stmt->error);
            }
            
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function listar() {
        try {
            $sql = "SELECT 
                    pp.idPersona as id,
                    pp.apeynom,
                    pp.cuil,
                    pp.direccion,
                    pp.fec_nac,
                    pp.genero,
                    pp.num_celular
                FROM persona_paciente pp";  // Simplificamos la consulta primero
            
            $resultado = $this->conexion->query($sql);
            
            if (!$resultado) {
                throw new Exception("Error listando pacientes: " . $this->conexion->error);
            }
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function obtener($id) {
        try {
            $sql = "SELECT 
                    pp.idPersona as id,
                    pp.apeynom,
                    pp.cuil as dni,
                    pp.direccion,
                    pp.fec_nac,
                    pp.genero,
                    pp.num_celular,
                    ccp.estado_paciente as estado
                FROM persona_paciente pp
                LEFT JOIN consulta_clinica_paciente ccp ON ccp.id_paciente = pp.idPersona
                WHERE pp.idPersona = ?";
                
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conexion->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error obteniendo paciente: " . $stmt->error);
            }
            
            $resultado = $stmt->get_result();
            return $resultado->fetch_assoc();
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    public function eliminar($id) {
        try {
            $this->conexion->begin_transaction();
            
            // Primero eliminamos de consulta_clinica_paciente
            $sql = "DELETE FROM consulta_clinica_paciente WHERE id_paciente = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Luego eliminamos de persona_paciente
            $sql = "DELETE FROM persona_paciente WHERE idPersona = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log($e->getMessage());
            return false;
        }
    }
}
?>