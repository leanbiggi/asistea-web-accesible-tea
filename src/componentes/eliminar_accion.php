<?php
session_start();
include("conexion.php");

// Verificar que el usuario sea un administrador (seguridad esencial)
if (isset($_SESSION['admin'])) {
    $id_accion = $_POST['id_accion_a_eliminar'];

    mysqli_query($conexion,"DELETE FROM acciones  WHERE id_accion= $id_accion");

    header("Location: ../admin_panel.php");
}

mysqli_close($conexion);
?>