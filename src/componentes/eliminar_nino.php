<?php
include("conexion.php");


// Obtener el ID de la actividad de la URL
$id_nino_url = $_GET['id'] ?? '';
// Convertir el ID de la URL a un entero para seguridad y uso en la consulta
// Si no es un número válido, intval() lo convertirá a 0.
$id_nino = intval($id_nino_url);

if ($id_nino > 0) { // Asegurarse de que el ID es un número válido y positivo
    $consulta_ninos=mysqli_query($conexion,"DELETE FROM ninos WHERE id_nino=$id_nino"); //este id me llegó por url
    // Redirección con el parámetro 'estado' unificado para éxito
    header("Location: ../panel_padres.php?seccion=ninos&estado=exito_eliminar_perfil#caja_ver_lista_ninos");
} else {
    // Redirección con el parámetro 'estado' unificado para error
    header("Location: ../panel_padres.php?seccion=ninos&estado=error_eliminar_perfil#caja_ver_lista_ninos");
}
?>