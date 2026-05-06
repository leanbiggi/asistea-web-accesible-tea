<?php
session_start();

require_once 'conexion.php'; 

// 1. Verificar que la solicitud sea POST y que el usuario sea administrador
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin_panel.php");
    exit();
}

if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_panel.php");
    exit();
}

// 2. Obtener y sanitizar los datos del formulario
$id_accion = intval($_POST['id_accion'] ?? 0);
$titulo = mysqli_real_escape_string($conexion, $_POST['titulo'] ?? '');
$texto = mysqli_real_escape_string($conexion, $_POST['texto'] ?? '');
$imagen_actual_ruta = mysqli_real_escape_string($conexion, $_POST['imagen_actual_ruta'] ?? '');

// Validación básica
if ($id_accion <= 0 || empty($titulo) || empty($texto)) {
    header("Location: modificar_accion.php?id=" . $id_accion . "&error=campos_vacios");
    exit();
}

$nueva_imagen_subida = false;
$ruta_imagen_db = $imagen_actual_ruta; // Por defecto, mantiene la imagen actual

// 3. Procesar la nueva imagen si se subió una
if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['nueva_imagen']['tmp_name'];
    $file_name = $_FILES['nueva_imagen']['name'];
    $file_size = $_FILES['nueva_imagen']['size'];
    $file_type = $_FILES['nueva_imagen']['type'];

    $upload_dir = '../imagenes/'; // Ruta donde se guardarán las imágenes (sube un nivel, luego entra en 'imagenes/')
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    // Validar tipo y tamaño
    if (!in_array($file_type, $allowed_types)) {
        header("Location: modificar_accion.php?id=" . $id_accion . "&error=tipo_imagen_invalido");
        exit();
    }
    if ($file_size > $max_file_size) {
        header("Location: modificar_accion.php?id=" . $id_accion . "&error=tamano_imagen_excedido");
        exit();
    }

    // Generar un nombre único para la nueva imagen
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $nuevo_nombre_imagen = uniqid('img_', true) . '.' . $extension;
    $upload_path = $upload_dir . $nuevo_nombre_imagen;

    if (move_uploaded_file($file_tmp_name, $upload_path)) {
        $nueva_imagen_subida = true;
        $ruta_imagen_db = 'imagenes/' . $nuevo_nombre_imagen; // Ruta a guardar en la DB

        // Eliminar la imagen antigua si existe y es diferente a la nueva
        if (!empty($imagen_actual_ruta) && file_exists('../' . $imagen_actual_ruta) && is_file('../' . $imagen_actual_ruta)) {
            // Asegúrate de que la ruta antigua no sea la misma que la nueva si el nombre es igual por casualidad
            if ('../' . $imagen_actual_ruta !== $upload_path) {
                if (!unlink('../' . $imagen_actual_ruta)) {
                    error_log("Error al eliminar la imagen antigua: " . '../' . $imagen_actual_ruta);
                    // Podrías decidir si quieres redirigir aquí o simplemente loggear el error
                }
            }
        }
    } else {
        header("Location: modificar_accion.php?id=" . $id_accion . "&error=upload_error");
        exit();
    }
}

// 4. Actualizar la base de datos
$sql_update = "UPDATE acciones SET titulo = ?, texto = ?, imagen_ruta = ? WHERE id_accion = ?";
$stmt_update = mysqli_prepare($conexion, $sql_update);

if ($stmt_update) {
    mysqli_stmt_bind_param($stmt_update, "sssi", $titulo, $texto, $ruta_imagen_db, $id_accion);
    
    if (mysqli_stmt_execute($stmt_update)) {
        // Éxito al actualizar
        header("Location: ../admin_panel.php?ok_modificar=true");
        exit();
    } else {
        // Error al ejecutar el update
        error_log("Error al actualizar la acción en DB (ID: $id_accion): " . mysqli_error($conexion));
        header("Location: modificar_accion.php?id=" . $id_accion . "&error=db_error_update");
        exit();
    }
    mysqli_stmt_close($stmt_update);
} else {
    // Error al preparar el update
    error_log("Error al preparar el update de acción: " . mysqli_error($conexion));
    header("Location: modificar_accion.php?id=" . $id_accion . "&error=db_error_prepare");
    exit();
}

mysqli_close($conexion);
?>