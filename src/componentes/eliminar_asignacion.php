<?php
include("conexion.php");


// Obtener el ID de la asignacion de la URL
$id_asignacion_url = $_GET['id'] ?? '';
// Convertir el ID de la URL a un entero para seguridad y uso en la consulta
// Si no es un número válido, intval() lo convertirá a 0.
$id_asignacion = intval($id_asignacion_url);

if ($id_asignacion > 0) { // Asegurarse de que el ID es un número válido y positivo
    $consulta_asignaciones=mysqli_query($conexion,"DELETE FROM asignaciones_actividades WHERE id_asignacion=$id_asignacion"); //este id me llegó por url

    header("Location: ../panel_padres.php?estado=eliminar_asignacion_exitosa#contenedor_asignaciones");
}
?>