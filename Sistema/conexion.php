<?php
$conexion = new mysqli("localhost", "root", "", "mydbproyectos2");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>