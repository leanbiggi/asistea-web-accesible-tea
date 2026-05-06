<?php
include("conexion.php");


// Obtener el ID de la actividad de la URL
$id_actividad_url = $_GET['id'] ?? '';
// Convertir el ID de la URL a un entero para seguridad y uso en la consulta
// Si no es un número válido, intval() lo convertirá a 0.
$id_actividad = intval($id_actividad_url);

if ($id_actividad > 0) { // Asegurarse de que el ID es un número válido y positivo
    $consulta_actividades=mysqli_query($conexion,"DELETE FROM actividades WHERE id_actividad=$id_actividad"); //este id me llegó por url

    // Redirección con el parámetro 'estado' para éxito
    header("Location: ../panel_padres.php?seccion=actividades&estado=exito_eliminar_actividad");
} else {
    // Si hubo un error en la base de datos, redirigimos con un mensaje de error
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_eliminar_actividad");
}
?>