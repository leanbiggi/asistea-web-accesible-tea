<?php
session_start();
require_once 'conexion.php';

// 1. Verificar si el usuario ha iniciado sesión.
if (!isset($_SESSION['id_padre'])) {
    header('Location: ../panel_padres.php');
    exit;
}

// 2. Obtener los datos del formulario de forma segura.
$id_padre = $_SESSION['id_padre'];
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_nueva = $_POST['password'] ?? '';

// 3. Validar los datos mínimos
if (empty($nombre) || empty($apellido) || empty($email)) {
    header("Location: ../panel_padres.php?seccion=cuenta&estado=error_actualizar_datos");
    exit();
}

// 4. Construir la consulta SQL dinámicamente para manejar la contraseña opcionalmente
if (!empty($password_nueva)) {
    // Si se proporciona una nueva contraseña, la incluimos sin cifrar.
    $sql = "UPDATE padres SET nombre = ?, apellido = ?, email = ?, contrasena = ? WHERE id_padre = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $apellido, $email, $password_nueva, $id_padre);
} else {
    // Si no se proporciona una nueva contraseña, solo actualizamos los otros campos.
    $sql = "UPDATE padres SET nombre = ?, apellido = ?, email = ? WHERE id_padre = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $nombre, $apellido, $email, $id_padre);
}

// 5. Ejecutar la consulta y manejar el resultado.
if ($stmt === false) {
    error_log("Error al preparar la actualización de datos del padre: " . mysqli_error($conexion));
    header("Location: ../panel_padres.php?seccion=cuenta&estado=error_actualizar_datos");
    exit();
}

if (mysqli_stmt_execute($stmt)) {
    // Si la actualización fue exitosa, también actualizamos la sesión
    $_SESSION['nombre_padre'] = $nombre;
    $_SESSION['apellido_padre'] = $apellido;
    $_SESSION['padre'] = $email;

    mysqli_stmt_close($stmt);
    header("Location: ../panel_padres.php?seccion=cuenta&estado=ok_actualizar_datos");
    exit();
} else {
    error_log("Error al ejecutar la actualización de datos del padre: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    header("Location: ../panel_padres.php?seccion=cuenta&estado=error_actualizar_datos");
    exit();
}

mysqli_close($conexion);
?>
