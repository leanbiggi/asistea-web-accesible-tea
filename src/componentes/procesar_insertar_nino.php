<?php
session_start();
// --- Lógica para agregar un nuevo niño ---

$nombre_nino = trim($_POST['nombre_nino'] ?? '');
$usuario_nino = trim($_POST['usuario_nino'] ?? ''); 
$contrasena_nino = trim($_POST['contrasena_nino'] ?? ''); 
$id_padre=$_SESSION['id_padre']; /* esto lo traigo de la session del padre */

include("conexion.php");

$consulta_existe=mysqli_query($conexion,"SELECT usuario FROM ninos WHERE usuario='$usuario_nino'");
if(mysqli_num_rows($consulta_existe)==1){ /*si existe*/
    // Redirección con el parámetro 'estado' unificado para error
    header("Location: ../panel_padres.php?seccion=ninos&estado=error_db");
    exit(); // <--- ¡IMPORTANTE! Detener la ejecución
} else {
    mysqli_query($conexion,"INSERT INTO ninos VALUES(DEFAULT, $id_padre, '$nombre_nino', '$usuario_nino', '$contrasena_nino',DEFAULT)");
    // Redirección con el parámetro 'estado' unificado para éxito
    header("Location: ../panel_padres.php?seccion=ninos&estado=exito_crear_perfil");
    exit(); // <--- ¡IMPORTANTE! Detener la ejecución
}

?>