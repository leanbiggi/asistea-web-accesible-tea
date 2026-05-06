<?php
session_start();

/*conecto con base de datos*/
include("conexion.php"); 

/*recibo datos que vienen del formulario*/
$nombre=$_POST['nombre'];
$apellido=$_POST['apellido'];
$correo=$_POST['correo'];
$pass=$_POST['pass'];

$consultar_correo=mysqli_query($conexion,"SELECT email FROM padres WHERE email='$correo'"); /*esta consulta devolverá un registro si el correo existe. Devuelve vacio cuando el usuario no está*/

if(mysqli_num_rows($consultar_correo)==1){  /*mysqli_num_rows() es una funcion que devuelve cantidad de filas de esa consulta*/
/*si es 1*  ya existe*/
    header("Location: ../panel_padres.php?estado=correo_existente"); //ambos header deben redirigir a la parte del login (donde no hay session)

} else { /*no está ese correo en la base de datos*/

    $consulta=mysqli_query($conexion,"INSERT INTO padres VALUES (DEFAULT,'$nombre','$apellido','$correo','$pass')");
    header("Location: ../panel_padres.php?estado=ok_registro_cuenta_padre");
}

?>