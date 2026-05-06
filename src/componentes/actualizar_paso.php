<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que esta ruta sea correcta para tu archivo de conexión

// 1. Verificar que la solicitud sea POST y que los datos esenciales estén presentes
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_paso'], $_POST['id_actividad'], $_POST['texto_paso'])) {
    // Si no es una solicitud POST o faltan datos esenciales, redirigir
    header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . intval($_POST['id_actividad'] ?? 0) . "&estado=error_paso");
    exit();
}

$id_paso = intval($_POST['id_paso']);
$id_actividad_redireccion = intval($_POST['id_actividad']);
$texto_paso = $_POST['texto_paso'];
$imagen_paso_actual_db = $_POST['imagen_paso_actual'] ?? ''; // Nombre de la imagen actual en DB
$audio_paso_actual_db = $_POST['audio_paso_actual'] ?? ''; // Nombre del audio actual en DB

$nombre_imagen_db = $imagen_paso_actual_db; // Por defecto, mantiene la imagen actual
$nombre_audio_db = $audio_paso_actual_db; // Por defecto, mantiene el audio actual

// NUEVOS CAMPOS: Opciones de imagen y datos de la cámara
$imagen_opcion_edit = $_POST['imagen_opcion_edit'] ?? 'mantener'; // 'mantener', 'subir', o 'camara'
$imagen_tomada_paso_edit = $_POST['imagen_tomada_paso_edit'] ?? ''; // Datos Base64 de la cámara

// =========================================================================
// MODIFICACIÓN 1: Declaración de variables de audio
// =========================================================================
$audio_opcion_edit = $_POST['audio_opcion_edit'] ?? 'mantener'; // 'mantener', 'subir', o 'grabar'
$audio_grabado_paso_edit = $_POST['audio_grabado_paso_edit'] ?? ''; // Datos Base64 del audio grabado
// =========================================================================

// 2. Procesar la subida de la NUEVA IMAGEN (Archivo o Cámara)
$directorio_imagenes = '../imagenes/pasos/'; // Ruta relativa desde componentes/ a imagenes/pasos/
$eliminar_imagen_antigua = false;
$nombre_nueva_imagen = '';

if ($imagen_opcion_edit === 'subir') {
    // A. Subir archivo (lógica de $_FILES)
    if (isset($_FILES['imagen_paso']) && $_FILES['imagen_paso']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['imagen_paso']['name'], PATHINFO_EXTENSION);
        $nombre_nueva_imagen = uniqid('img_', true) . '.' . $extension;
        $ruta_destino_imagen = $directorio_imagenes . $nombre_nueva_imagen;

        if (move_uploaded_file($_FILES['imagen_paso']['tmp_name'], $ruta_destino_imagen)) {
            $nombre_imagen_db = $nombre_nueva_imagen;
            $eliminar_imagen_antigua = true; // Hay una nueva imagen, se debe eliminar la antigua
        }
    }
} elseif ($imagen_opcion_edit === 'camara' && !empty($imagen_tomada_paso_edit)) {
    // B. Usar cámara (manejo de Base64)
    
    // 1. Limpiar el Base64 (eliminar el "data:image/jpeg;base64,")
    $data_parts = explode(',', $imagen_tomada_paso_edit);
    $base64_img = end($data_parts);
    
    $imagen_decodificada = base64_decode($base64_img);
    
    // 2. Definir nombre de archivo y ruta
    $nombre_nueva_imagen = uniqid('cam_', true) . '.jpeg'; // Usamos .jpeg por consistencia con el JS
    $ruta_destino_imagen = $directorio_imagenes . $nombre_nueva_imagen;
    
    // 3. Guardar el archivo
    if ($imagen_decodificada !== false && file_put_contents($ruta_destino_imagen, $imagen_decodificada) !== false) {
        $nombre_imagen_db = $nombre_nueva_imagen;
        $eliminar_imagen_antigua = true; // Hay una nueva imagen, se debe eliminar la antigua
    }
} elseif ($imagen_opcion_edit === 'mantener') {
    // C. Mantener la imagen actual (comportamiento por defecto)
    $nombre_imagen_db = $imagen_paso_actual_db;
    // Si la opción es 'mantener', la variable $eliminar_imagen_antigua sigue en false.
}


// 2.1. Lógica para eliminar la imagen antigua, si se subió/capturó una nueva
if ($eliminar_imagen_antigua && !empty($imagen_paso_actual_db) && file_exists($directorio_imagenes . $imagen_paso_actual_db)) {
    unlink($directorio_imagenes . $imagen_paso_actual_db);
}
// Fin de la lógica de imagen.


// =========================================================================
// MODIFICACIÓN 2: Lógica de Audio Unificada (3 Opciones) - REEMPLAZO COMPLETO
// =========================================================================
// 3. Procesar el AUDIO (Subir archivo, Grabar Base64, o Mantener actual)
$directorio_audios = '../audios/pasos/'; // Ruta relativa desde componentes/ a audios/pasos/
$eliminar_audio_antiguo = false;

if ($audio_opcion_edit === 'subir') {
    // A. Opción: Subir nuevo archivo de audio
    // El input de tipo file tiene name="audio_paso_file" en el HTML
    if (isset($_FILES['audio_paso_file']) && $_FILES['audio_paso_file']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['audio_paso_file']['name'], PATHINFO_EXTENSION);
        $nombre_nuevo_audio = uniqid('aud_', true) . '.' . $extension;
        $ruta_destino_audio = $directorio_audios . $nombre_nuevo_audio;

        if (move_uploaded_file($_FILES['audio_paso_file']['tmp_name'], $ruta_destino_audio)) {
            $nombre_audio_db = $nombre_nuevo_audio;
            $eliminar_audio_antiguo = true; // Hay un nuevo audio, se debe eliminar el antiguo
        }
    } else {
        // Si se selecciona subir pero no se sube archivo, se mantiene el actual
        $nombre_audio_db = $audio_paso_actual_db;
    }
} elseif ($audio_opcion_edit === 'grabar' && !empty($audio_grabado_paso_edit)) {
    // B. Opción: Audio grabado (manejo de Base64)
    
    // 1. Limpiar el Base64 (eliminar el prefijo "data:audio/webm;base64," o similar)
    $data_parts = explode(',', $audio_grabado_paso_edit);
    $base64_audio = end($data_parts);
    
    $audio_decodificado = base64_decode($base64_audio);
    
    // 2. Definir nombre de archivo y ruta (se guarda como .webm o .ogg)
    $nombre_nuevo_audio = uniqid('gra_', true) . '.webm'; 
    $ruta_destino_audio = $directorio_audios . $nombre_nuevo_audio;
    
    // 3. Guardar el archivo
    if ($audio_decodificado !== false && file_put_contents($ruta_destino_audio, $audio_decodificado) !== false) {
        $nombre_audio_db = $nombre_nuevo_audio;
        $eliminar_audio_antiguo = true; // Hay un nuevo audio, se debe eliminar el antiguo
    }
} elseif ($audio_opcion_edit === 'mantener') {
    // C. Opción: Mantener el audio actual
    $nombre_audio_db = $audio_paso_actual_db;
}

// 3.1. Lógica para eliminar el audio antiguo, si se subió/grabó uno nuevo
if ($eliminar_audio_antiguo && !empty($audio_paso_actual_db) && file_exists($directorio_audios . $audio_paso_actual_db)) {
    unlink($directorio_audios . $audio_paso_actual_db);
}
// Fin de la nueva lógica de audio.
// =========================================================================

// 4. Actualizar el paso en la base de datos
$stmt_update = mysqli_prepare($conexion, "UPDATE pasos SET texto_paso = ?, imagen_paso = ?, audio_paso = ? WHERE id_paso = ?");

if ($stmt_update === false) {
    // Si la preparación falla, registra el error y redirige
    error_log("Error al preparar la consulta de actualización de paso: " . mysqli_error($conexion));
    header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
    exit();
}

// Vincular parámetros: 'sss' para tres strings, 'i' para un entero
mysqli_stmt_bind_param($stmt_update, "sssi", $texto_paso, $nombre_imagen_db, $nombre_audio_db, $id_paso);

if (mysqli_stmt_execute($stmt_update)) {
    // Redirección de éxito
    header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=exito_editar_paso");
} else {
    // Si la ejecución falla, registra el error y redirige
    error_log("Error al ejecutar la actualización de paso: " . mysqli_error($conexion));
    header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
}

mysqli_stmt_close($stmt_update);
mysqli_close($conexion);
exit();
?>