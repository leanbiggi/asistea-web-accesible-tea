<?php
session_start();

require_once 'conexion.php';

// 1. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../panel_padres.php?seccion=actividades");
    exit();
}

// 2. VERIFICAR QUE EL PADRE ESTÉ LOGUEADO Y TENGA UN ID VÁLIDO EN SESIÓN
if (!isset($_SESSION['id_padre']) || !is_numeric($_SESSION['id_padre']) || $_SESSION['id_padre'] <= 0) {
    // CAMBIO: Usamos un parámetro más genérico para el estado
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_no_sesion_padre");
    exit();
}

// Ahora sí, obtenemos el ID del padre logueado
$id_padre = intval($_SESSION['id_padre']);

$ruta_foto_db = '';

// Recibo y SANEAMOS los datos del formulario (¡IMPORTANTE!)
// Ya no usamos mysqli_real_escape_string aquí, ya que usaremos sentencias preparadas
$nombre_actividad = $_POST['nombre_actividad'] ?? '';
$descripcion_actividad = $_POST['descripcion_actividad'] ?? '';
$texto_alternativo = $_POST['alt_texto_actividad'] ?? '';

// =========================================================================
// ↓↓↓ NUEVOS CAMPOS A RECIBIR ↓↓↓
$imagen_opcion_actividad = $_POST['imagen_opcion_actividad'] ?? ''; // 'subir' o 'camara'
$imagen_tomada_actividad = $_POST['imagen_tomada_actividad'] ?? ''; // Datos Base64 de la cámara
// =========================================================================


// Validación básica de los campos obligatorios del formulario
if (empty($nombre_actividad) || empty($descripcion_actividad)) {
    // CAMBIO: Parámetro 'estado' consistente
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_campos_vacios");
    exit();
}


// =========================================================================
// 3. Procesar la imagen (Subida de archivo o Cámara) - REEMPLAZO DE LA SECCIÓN
// =========================================================================
$upload_dir = '../imagenes/actividades/';

if ($imagen_opcion_actividad === 'subir') {
    // A. Opción: Subir archivo ($_FILES)
    if (!isset($_FILES['imagen_actividad']) || $_FILES['imagen_actividad']['error'] !== UPLOAD_ERR_OK) {
        // La opción es subir, pero no se subió ningún archivo (o hubo error)
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_no_imagen_seleccionada");
        exit();
    }

    $file_tmp_name = $_FILES['imagen_actividad']['tmp_name'];
    $file_name = $_FILES['imagen_actividad']['name'];
    $file_size = $_FILES['imagen_actividad']['size'];
    $file_type = $_FILES['imagen_actividad']['type'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_file_size = 1 * 1024 * 1024; // 1 MB

    // Validación de tipo y tamaño (mantenemos la lógica existente)
    if (!in_array($file_type, $allowed_types)) {
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_tipo_imagen_invalido");
        exit();
    }
    if ($file_size > $max_file_size) {
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_tamano_imagen_excedido");
        exit();
    }

    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $nuevo_nombre_imagen = uniqid('act_', true) . '.' . $extension;
    $upload_path = $upload_dir . $nuevo_nombre_imagen;

    if (move_uploaded_file($file_tmp_name, $upload_path)) {
        $ruta_foto_db = 'imagenes/actividades/' . $nuevo_nombre_imagen;
    } else {
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_upload_error");
        exit();
    }

} elseif ($imagen_opcion_actividad === 'camara') {
    // B. Opción: Imagen tomada por la cámara (Base64)
    
    if (empty($imagen_tomada_actividad)) {
        // La opción es cámara, pero el campo Base64 está vacío (el usuario no tomó foto)
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_no_imagen_capturada");
        exit();
    }
    
    // 1. Limpiar el prefijo Base64 (ej: data:image/jpeg;base64,)
    $data_parts = explode(',', $imagen_tomada_actividad);
    $base64_img = end($data_parts);
    
    $imagen_decodificada = base64_decode($base64_img);
    
    if ($imagen_decodificada === false) {
        // Error de decodificación
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_decodificacion_imagen");
        exit();
    }
    
    // 2. Generar nombre de archivo y ruta (usamos .jpeg ya que el JS lo genera así)
    $nuevo_nombre_imagen = uniqid('cam_', true) . '.jpeg';
    $upload_path = $upload_dir . $nuevo_nombre_imagen;
    
    // 3. Guardar el archivo
    if (file_put_contents($upload_path, $imagen_decodificada) !== false) {
        $ruta_foto_db = 'imagenes/actividades/' . $nuevo_nombre_imagen;
    } else {
        // Error al escribir el archivo
        header("Location: ../panel_padres.php?seccion=actividades&estado=error_guardar_imagen_camara");
        exit();
    }
    
} else {
    // Si no se selecciona ninguna opción válida (como medida de seguridad)
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_no_imagen_seleccionada");
    exit();
}
// =========================================================================


// 4. Insertar la actividad en la base de datos usando SENTENCIAS PREPARADAS
// Esta sección no cambia ya que la variable $ruta_foto_db ya tiene el valor final.
$sql_insert = "INSERT INTO actividades (id_padre, nombre_actividad, descripcion, foto_actividad, texto_alternativo, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";

// Preparamos la consulta
$stmt = mysqli_prepare($conexion, $sql_insert);

// Verificamos si la preparación fue exitosa
if ($stmt === false) {
    error_log("Error en la preparación de la consulta: " . mysqli_error($conexion));
    // CAMBIO: Parámetro 'estado' consistente
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_db");
    exit();
}

// Vinculamos los parámetros
// "issss" para el id (entero) y 4 strings
mysqli_stmt_bind_param($stmt, "issss", $id_padre, $nombre_actividad, $descripcion_actividad, $ruta_foto_db, $texto_alternativo);

// Ejecutamos la consulta
if (mysqli_stmt_execute($stmt)) {
    // CAMBIO: Parámetro 'estado' para éxito
    header("Location: ../panel_padres.php?seccion=actividades&estado=exito_crear_actividad");
    exit();
} else {
    error_log("Error al crear actividad para padre ID $id_padre: " . mysqli_error($conexion));
    
    // Si falla la inserción en la DB, borramos la imagen que ya habíamos subido
    if (!empty($ruta_foto_db) && file_exists('../' . $ruta_foto_db)) {
        unlink('../' . $ruta_foto_db);
    }
    
    // CAMBIO: Parámetro 'estado' consistente
    header("Location: ../panel_padres.php?seccion=actividades&estado=error_db");
    exit();
}

// Cerramos la sentencia preparada
mysqli_stmt_close($stmt);

mysqli_close($conexion);
?>