<?php
session_start();
include("conexion.php");
// Asegúrate de que estas variables estén sanitizadas adecuadamente.
// No uses $_POST['titulo'] y $_POST['texto'] directamente en la consulta mysqli_query()
$titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
$texto = mysqli_real_escape_string($conexion, $_POST['texto']);  


$imagen_ruta_para_db = ''; // Inicializamos la ruta que se guardará en la BD

// 2. Procesar la subida de la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_original_archivo = $_FILES['imagen']['name'];
    $ubicacion_temporal_archivo = $_FILES['imagen']['tmp_name'];

    // --- RUTA PARA PHP (donde move_uploaded_file guardará el archivo) ---
    // Esta ruta debe ser relativa al script PHP (cargar_accion.php)
    // Si cargar_accion.php está en 'componentes/', y 'imagenes_de_acciones/' está en la raíz,
    // necesitamos 'subir un nivel' para acceder a ella.
    $directorio_destino_php = '../imagenes_de_acciones/'; 

    // Crear el directorio si no existe
    if (!is_dir($directorio_destino_php)) {
        mkdir($directorio_destino_php, 0777, true); 
    }

    // Generar un nombre único para el archivo
    $extension_archivo = pathinfo($nombre_original_archivo, PATHINFO_EXTENSION);
    $nombre_unico_archivo = uniqid('accion_', true) . '.' . $extension_archivo; 
    
    // Ruta completa en el servidor donde se guardará el archivo (usada por move_uploaded_file)
    $ruta_completa_servidor_php = $directorio_destino_php . $nombre_unico_archivo;

    // Mover el archivo subido
    if (move_uploaded_file($ubicacion_temporal_archivo, $ruta_completa_servidor_php)) {
        // --- RUTA PARA LA BASE DE DATOS (la que usará el navegador en la etiqueta <img>) ---
        // Esta ruta debe ser relativa a la raíz web de tu sitio.
        // Si 'imagenes_de_acciones' está en la raíz de tu dominio, esta es la forma correcta.
        $imagen_ruta_para_db = 'imagenes_de_acciones/' . $nombre_unico_archivo; 
    } else {
        // Si no se pudo mover el archivo (ej. permisos incorrectos)
        echo "Error al mover el archivo subido al servidor.<br>";
        // Puedes añadir un registro de error o un mensaje al administrador
    }
}
// else: Si no se subió ninguna imagen o hubo un error, $imagen_ruta_para_db quedará vacía.

// 3. Insertar los datos en la base de datos
// ¡¡¡IMPORTANTE!!! Utiliza Prepared Statements para mayor seguridad contra inyección SQL.
// No uses los valores de $_POST directamente en la consulta.
$query = "INSERT INTO acciones (titulo, texto, imagen_ruta) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conexion, $query);

// 'sss' indica que los tres parámetros son de tipo string.
mysqli_stmt_bind_param($stmt, "sss", $titulo, $texto, $imagen_ruta_para_db);

if (mysqli_stmt_execute($stmt)) {
    // Éxito al guardar
    header("Location: ../admin_panel.php?ok_carga#sect1");
    exit();
} else {
    // Error al guardar en la BD
    echo "Error al guardar la acción en la base de datos: " . mysqli_error($conexion);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>