<?php
session_start();

$correo=$_POST['correo'];
$pass=$_POST['pass'];

include("conexion.php");

$consulta_existe=mysqli_query($conexion,"SELECT id_padre,nombre,apellido,email,contrasena FROM padres WHERE email='$correo' AND contrasena='$pass'");

if(mysqli_num_rows($consulta_existe)==1){ /*si existe: creo la sesion y devuelvo un ok por url*/
    $_SESSION['padre']=$correo;
    
    
    $padre_array = mysqli_fetch_assoc($consulta_existe); /*genera array asociativo a partir de la consulta sql*/
    $_SESSION['id_padre'] = $padre_array['id_padre']; // Guarda el ID del padre
    $_SESSION['nombre_padre']=$padre_array['nombre'];
    $_SESSION['apellido_padre']=$padre_array['apellido'];
    $_SESSION['contrasena_cuenta_padre'] = $padre_array['contrasena'];
    /*tambien, como ya genere el array puedo hacer: */
    /*$_SESSION['padre'] = $padre['email'];*/ //Guarda el email como "nombre de usuario" de la sesión. Esta opcion es mejor porq
    // toma el valor de la BD. Asi, en la variable $_SESSION[] puedo guardar los datos del padre
    header("Location: ../panel_padres.php");
    exit();
} else { /*no está*/
    header("Location: ../panel_padres.php?estado=error_ingreso_cuenta");
    exit();
}
?>