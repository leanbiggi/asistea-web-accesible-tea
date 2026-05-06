<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Establecer la zona horaria a Argentina (Buenos Aires) ---
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once 'componentes/conexion.php'; 



// *********************** Verificar si el padre no ha iniciado sesión
if(!isset($_SESSION['padre'])){  //si la sesion padre no está activa, muestro form de login y form de registrarse
    //este if ENCIERRA TODA LA PARTE DEL LOGIN Y REGISTRO DEL PADRE ***********************


//LOGICA PARA LOS MENSAJE DE EXITO Y ERROR QUE DEVUELVE LOS FORMULARIOS  
$mensaje_general = '';
$clase_general = '';

    // Se verifica si el parámetro 'estado' está presente en la URL.
    if (isset($_GET['estado'])) {
        // Si existe, se usa un solo switch para manejar todos los posibles estados.
        switch ($_GET['estado']) {
            // Casos para cuando se elimina cuenta de padre exitosamente
            case 'cuenta_eliminada':
                $mensaje_general = "Tu cuenta y todos los datos asociados han sido eliminados exitosamente.";
                $clase_general = "ok";
                break;
            //casos para registrar_padre.php
            case 'ok_registro_cuenta_padre';
                $mensaje_general = "Usted se registró correctamente.";
                $clase_general = "ok";
                break;
            case 'correo_existente';
                $mensaje_general = "El correo electrónico ya está registrado.";
                $clase_general = "error";
                break;
            //casos para validar_padre.php
            case 'error_ingreso_cuenta':
                $mensaje_general = "Error en la validación de cuenta.";
                $clase_general = "error";
                break;
            }
    }


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso/Registro adulto responsable</title>
    <link href="estilos/estilos_login_padres.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- libreria para icono de boton volver -->
</head>
<body>
    <?php
    include('componentes/enlace-saltar-a-contenido-principal.php');
    ?>
     
    <header role="banner" class="header-login">
        <a href="asistea.php" class="boton-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <h1>Bienvenido/a </h1>
        <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1">
            <?php if (!empty($mensaje_general)) { ?>
                <p class="mensaje_form <?php echo htmlspecialchars($clase_general); ?>">
                    <?php echo htmlspecialchars($mensaje_general); ?>
                </p>
            <?php } ?>
        </div>
    </header>
    <main class="formularios-padres-container" role="main" id="contenido-principal">
        <section class="caja-formulario" aria-labelledby="login-adulto-responsable"> <!-- El lector de pantalla
            leerá "seccion login adulto responsable porq el aria-labelledby esta asociado al h2 -->
            <h2 id="login-adulto-responsable">Login adulto responsable</h2>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const notificacionActividades = document.getElementById('notificacion_actividades');
                    const notificacionForm = document.getElementById('notificacion_aria');

                    // Mueve el foco al mensaje de 'Mis actividades' si existe y tiene contenido
                    if (notificacionActividades && notificacionActividades.innerText.trim() !== '') {
                        setTimeout(() => {
                            notificacionActividades.focus();
                        }, 100);
                    }

                    // Mueve el foco al mensaje del formulario si existe y tiene contenido
                    //Podria poner un segundo div con aria-live en el formulario para las notificaciones especificas del formulario,
                    //  pero: En el switch de panel_padres.php, tendría que definir dos variables diferentes ($mensaje_general y, por ejemplo, 
                    // $mensaje_form_crear_act), cada una con su propia clase.
                    if (notificacionForm && notificacionForm.innerText.trim() !== '') {
                        setTimeout(() => {
                            notificacionForm.focus();
                        }, 100);
                    }
                });
            </script>
            <form action="componentes/validar_padre.php" method="POST">
                <label for="correo_ele" class="form_label">Correo electrónico: </label> 
                <input id="correo_ele" type="email" placeholder="Ingrese correo electrónico" name="correo" required>
<!-- a cada  imput agregar el id="" para que el label lo tome correctamente. -->
                <label for="pass_login" class="form_label">Contraseña: </label>
                <input id="pass_login" type="password" placeholder="Ingrese contraseña" name="pass" required>
                <input type="submit" value="Ingresar">
            </form>
            <?php
            if(isset($_GET['error_registro'])){
                echo "<p class='mensaje-error'>Error al intentar iniciar sesión. Verifique sus credenciales.</p>";
            }
            ?>
        </section>
        <section class="caja-formulario" aria-labelledby="registro-adulto-responsable">
            <h2 id="registro-adulto-responsable">Registro Adulto responsable</h2>
            <p>Si aún no está registrado, puede hacerlo aquí.</p>
            <form action="componentes/registrar_padre.php" method="POST">
                <label for="nombre" class="form_label">Nombre: </label>
                <input id="nombre" type="text" name="nombre" placeholder="Ingrese nombre" required>
                <label for="apellido" class="form_label">Apellido: </label>
                <input id="apellido" type="text" name="apellido" placeholder="Ingrese apellido">
                <label for="correo" class="form_label">Correo electrónico: </label>
                <input id="correo" type="email" name="correo" placeholder="Ingrese Correo electrónico" required>
                <label for="pass" class="form_label">Contraseña: </label>
                <input id="pass" type="password" name="pass" placeholder="Ingrese contraseña" required>
                <input type="submit" value="Registrarse">
            </form>
            <?php
            //BORRAR ESTO despues de añadirlo al manejo de mensajes 
            /*
            if(isset($_GET['ok_registro'])){
                echo "<p class='mensaje-ok-registro'>Usted se registró correctamente.</p>";
            }
            if(isset($_GET['correo_existente'])){
                echo "<p class='mensaje-error'>El correo ya existe.</p>";
            }
                */
            ?>
        </section>
    </main>
</body>
</html>

<?php
    exit(); // Detener la ejecución del resto del script si no está logueado
} 







// Si la sesión de padre está activa, continua con el panel
//
//




$nombre_del_padre = $_SESSION['nombre_padre'] ?? 'Padre'; // Asegúrate de que esta variable esté definida en tu sesión
$id_padre_sesion = $_SESSION['id_padre'] ?? null; // Asegúrate de que esta variable esté definida en tu sesión



//LOGICA PARA LOS MENSAJE DE EXITO Y ERROR QUE DEVUELVE LOS FORMULARIOS  
// Se definen variables para los mensajes que se usarán en toda la página.
$seccion_actual = $_GET['seccion'] ?? 'asignaciones'; // Valor por defecto
$mensaje_general = '';
$clase_general = '';

// Se verifica si el parámetro 'estado' está presente en la URL.
if (isset($_GET['estado'])) {
    // Si existe, se usa un solo switch para manejar todos los posibles estados.
    switch ($_GET['estado']) {
        // Casos para las actividades
        case 'exito_crear_actividad':
            $mensaje_general = "Actividad creada exitosamente.";
            $clase_general = "ok";
            break;
        case 'exito_editar_actividad':
            $mensaje_general = "Actividad editada exitosamente.";
            $clase_general = "ok";
            break;
        case 'exito_eliminar_actividad':
            $mensaje_general = "Actividad eliminada exitosamente.";
            $clase_general = "ok";
            break;
        case 'error_eliminar_actividad':
            $mensaje_general = "Hubo un error al eliminar la actividad.";
            $clase_general = "error";
            break;
        // Casos para CREAR ACTIVIDAD (NUEVOS ERRORES DE CÁMARA)
        case 'error_no_imagen_capturada':
            $mensaje_general = "Error: Ha seleccionado la cámara, pero no se ha tomado la foto.";
            $clase_general = "error";
            break;
        case 'error_decodificacion_imagen':
            $mensaje_general = "Error interno al procesar la imagen de la cámara. Intente subir un archivo.";
            $clase_general = "error";
            break;
        case 'error_guardar_imagen_camara':
            $mensaje_general = "Error al guardar la imagen capturada en el servidor.";
            $clase_general = "error";
            break;
        // Casos para los pasos (dentro de las actividades)
        case 'exito_crear_paso':
            $mensaje_pasos = "Paso creado exitosamente.";
            $clase_pasos = "ok";
            break;
        case 'exito_editar_paso':
            $mensaje_pasos = "Paso editado correctamente.";
            $clase_pasos = "ok";
            break;
        case 'exito_eliminar_paso':
            $mensaje_pasos = "Paso eliminado correctamente.";
            $clase_pasos = "ok";
            break;
        case 'exito_reordenar_pasos':
            $mensaje_pasos = "Pasos reordenados correctamente.";
            $clase_pasos = "ok";
            break;
        case 'error_paso':
            $mensaje_pasos = "Ocurrió un error al procesar el paso.";
            $clase_pasos = "error";
            break;
        
        // Casos para los perfiles
        case 'exito_crear_perfil':
            $mensaje_general = "Perfil creado exitosamente.";
            $clase_general = "ok";
            break;
        case 'exito_eliminar_perfil':
            $mensaje_general = "Perfil eliminado exitosamente.";
            $clase_general = "ok";
            break;
        case 'error_eliminar_perfil':
            $mensaje_general = "Hubo un error al eliminar el perfil.";
            $clase_general = "error";
            break;
        case 'exito_editar_perfil':
            $mensaje_general = "Perfil editado exitosamente.";
            $clase_general = "ok";
            break;
        case 'error_consulta_db':
            $mensaje_general = "Error en la consulta a la base de datos.";
            $clase_general = "error";
            break;
        case 'error_db':
            $mensaje_general = "Error en la preparación de la consulta.";
            $clase_general = "error";
            break;
        case 'datos_invalidos_':
            $mensaje_general = "Error en los datos.";
            $clase_general = "error";
            break;
        case 'nino_no_encontrado_o_sin_permisos':
            $mensaje_general = "Perfil niño/a no encontrado o sin permisos.";
            $clase_general = "error";
            break;
        //casos para asignaciones:
        case 'exito_en_la_asignacion':
            $mensaje_general = "Exito en la asignación.";
            $clase_general = "ok";
            break;
            //agregar aqui los otros errores de gestionar_asignaciones.php
        case 'eliminar_asignacion_exitosa':
            $mensaje_general = "Eliminación de asignación exitosa.";
            $clase_general = "ok";
            break;
        // Casos para eliminar_cuenta_padre.php
        case 'error_eliminar_cuenta':
            $mensaje_general = "Hubo un error al intentar eliminar tu cuenta. Por favor, inténtalo de nuevo.";
            $clase_general = "error";
            break;
        //casos para actualizar_cuenta.php
        case 'ok_actualizar_datos':
            $mensaje_general = "Tus datos han sido actualizados exitosamente.";
            $clase_general = "ok";
            break;
        case 'error_actualizar_datos':
            $mensaje_general = "Hubo un error al actualizar tus datos. Inténtalo de nuevo.";
            $clase_general = "error";
            break;
        // Otros casos de error genéricos
        case 'error_actualizar_db':
            $mensaje_general = "Error al actualizar la base de datos.";
            $clase_general = "error";
            break;
        case 'error_general_subida':
            $mensaje_general = "Hubo un error general con la subida de archivos.";
            $clase_general = "error";
            break;
    }
}




// --- LÓGICA GENERAL PARA PROCESAR FORMULARIOS ENVIADOS (MÉTODO POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Es crucial obtener la 'action' desde $_GET (URL) ya que los formularios la envían allí.
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'editar_actividad':
            // --- INICIO LÓGICA PARA EDITAR ACTIVIDAD con nuevas opciones de imagen ---

            // 1. Validar y Sanitizar los datos recibidos
            $id_actividad_edit = filter_input(INPUT_POST, 'id_actividad', FILTER_VALIDATE_INT);
            $nombre_actividad_edit = $_POST['nombre_actividad'] ?? '';
            $descripcion_actividad_edit = $_POST['descripcion_actividad'] ?? '';
            $texto_alternativo_edit = $_POST['alt_texto_actividad'] ?? '';

            // --- Nuevos campos de imagen ---
            $imagen_opcion_edit = $_POST['imagen_opcion_edit'] ?? 'mantener'; // 'mantener', 'subir', o 'camara'
            $imagen_tomada_edit = $_POST['imagen_tomada_edit'] ?? ''; // Datos Base64 de la cámara
            $imagen_actual_db = $_POST['imagen_actual_db'] ?? ''; // Ruta de la imagen actual

            // Variables de control
            $ruta_imagen_db_edit = $imagen_actual_db; // Por defecto, mantiene la ruta actual
            $eliminar_imagen_antigua = false;
            $directorio_imagenes = 'imagenes/actividades/'; // Directorio relativo a panel_padres.php

            // Validaciones básicas
            if (!$id_actividad_edit || empty($nombre_actividad_edit) || empty($descripcion_actividad_edit)) {
                header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_campos_vacios");
                exit();
            }

            // 2. Verificar que la actividad pertenece al padre logueado (doble check de seguridad)
            if (!isset($_SESSION['id_padre'])) {
                header("Location: panel_padres.php?seccion=actividades&mensaje=error_sesion_expirada");
                exit();
            }
            $id_padre_sesion = $_SESSION['id_padre'];

            $stmt_check = mysqli_prepare($conexion, "SELECT id_padre FROM actividades WHERE id_actividad = ?");
            if ($stmt_check === false) {
                die("Error al preparar la verificación de ID de padre: " . mysqli_error($conexion));
            }
            mysqli_stmt_bind_param($stmt_check, "i", $id_actividad_edit);
            mysqli_stmt_execute($stmt_check);
            $res_check = mysqli_stmt_get_result($stmt_check);
            $row_check = mysqli_fetch_assoc($res_check);
            mysqli_stmt_close($stmt_check);

            if (!$row_check || $row_check['id_padre'] != $id_padre_sesion) {
                header("Location: panel_padres.php?seccion=actividades&mensaje=error_no_autorizado");
                exit();
            }

            // =========================================================================
            // 3. Lógica para procesar la NUEVA IMAGEN según la opción
            // =========================================================================

            if ($imagen_opcion_edit === 'subir') {
                // A. Opción: Subir nuevo archivo (Input: 'imagen_actividad_file')
                if (isset($_FILES['imagen_actividad_file']) && $_FILES['imagen_actividad_file']['error'] === UPLOAD_ERR_OK) {
                    
                    $file_tmp_name = $_FILES['imagen_actividad_file']['tmp_name'];
                    $file_name = $_FILES['imagen_actividad_file']['name'];
                    $file_size = $_FILES['imagen_actividad_file']['size'];
                    $file_type = $_FILES['imagen_actividad_file']['type'];

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $max_file_size = 1 * 1024 * 1024; // 1 MB
                    
                    // Validación de tipo y tamaño
                    if (!in_array($file_type, $allowed_types) || $file_size > $max_file_size) {
                        header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_validar_nueva_imagen");
                        exit();
                    }

                    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $nombre_nueva_imagen = uniqid('act_', true) . '.' . $extension;
                    $upload_path = $directorio_imagenes . $nombre_nueva_imagen;

                    if (move_uploaded_file($file_tmp_name, $upload_path)) {
                        $ruta_imagen_db_edit = 'imagenes/actividades/' . $nombre_nueva_imagen;
                        $eliminar_imagen_antigua = true; // Se debe eliminar la imagen anterior
                    } else {
                        header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_upload_error");
                        exit();
                    }
                } else {
                    // Se seleccionó subir pero el archivo no se subió correctamente
                    header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_no_nueva_imagen");
                    exit();
                }

            } elseif ($imagen_opcion_edit === 'camara') {
                // B. Opción: Imagen tomada por la cámara (Base64)
                
                if (empty($imagen_tomada_edit)) {
                    // No se tomó foto
                    header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_no_imagen_capturada");
                    exit();
                }
                
                // Limpiar el Base64 y decodificar
                $data_parts = explode(',', $imagen_tomada_edit);
                $base64_img = end($data_parts);
                $imagen_decodificada = base64_decode($base64_img);
                
                if ($imagen_decodificada === false) {
                    header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_decodificacion_imagen");
                    exit();
                }
                
                // Guardar el archivo
                $nombre_nueva_imagen = uniqid('cam_', true) . '.jpeg';
                $upload_path = $directorio_imagenes . $nombre_nueva_imagen;
                
                if (file_put_contents($upload_path, $imagen_decodificada) !== false) {
                    $ruta_imagen_db_edit = 'imagenes/actividades/' . $nombre_nueva_imagen;
                    $eliminar_imagen_antigua = true; // Se debe eliminar la imagen anterior
                } else {
                    // Error al escribir el archivo
                    header("Location: panel_padres.php?seccion=actividades&accion=editar&id=$id_actividad_edit&estado=error_guardar_imagen_camara");
                    exit();
                }

            } 
            // Si la opción es 'mantener', la ruta sigue siendo la actual: $ruta_imagen_db_edit = $imagen_actual_db

            // 4. Lógica para ELIMINAR la imagen antigua si se subió/capturó una nueva y había una previa
            // Importante: La ruta en $imagen_actual_db es relativa a panel_padres.php (ej. 'imagenes/actividades/...')
            if ($eliminar_imagen_antigua && !empty($imagen_actual_db) && file_exists($imagen_actual_db)) {
                unlink($imagen_actual_db);
            }


            // 5. Actualizar la base de datos
            $sql_update = "UPDATE actividades SET nombre_actividad = ?, descripcion = ?, foto_actividad = ?, texto_alternativo = ? WHERE id_actividad = ? AND id_padre = ?";
            $stmt_update = mysqli_prepare($conexion, $sql_update);

            if ($stmt_update === false) {
                error_log("Error en la preparación de la consulta de actualización: " . mysqli_error($conexion));
                header("Location: panel_padres.php?seccion=actividades&estado=error_db");
                exit();
            }
            
            // Vinculamos los parámetros: 4 strings (s) y 2 enteros (ii)
            mysqli_stmt_bind_param($stmt_update, "ssssii", $nombre_actividad_edit, $descripcion_actividad_edit, $ruta_imagen_db_edit, $texto_alternativo_edit, $id_actividad_edit, $id_padre_sesion);

            if (mysqli_stmt_execute($stmt_update)) {
                header("Location: panel_padres.php?seccion=actividades&accion=listar&estado=exito_editar_actividad");
                exit();
            } else {
                error_log("Error al actualizar actividad ID $id_actividad_edit: " . mysqli_error($conexion));
                
                // Si falla la DB, borramos la NUEVA imagen subida/capturada si se intentó subir
                if ($eliminar_imagen_antigua && !empty($ruta_imagen_db_edit) && file_exists($ruta_imagen_db_edit)) {
                     unlink($ruta_imagen_db_edit);
                }
                header("Location: panel_padres.php?seccion=actividades&estado=error_db");
                exit();
            }
            mysqli_stmt_close($stmt_update);
            // --- FIN LÓGICA PARA EDITAR ACTIVIDAD ---
            break; // Fin del case 'editar_actividad'

        case 'agregar_paso':
            // 1. Validar datos
            $id_actividad = filter_input(INPUT_POST, 'id_actividad', FILTER_VALIDATE_INT);
            $texto_paso = $_POST['texto_paso'] ?? '';

            if (!$id_actividad) {
                // Redirección CORRECTA si no se proporciona un ID válido
                $_SESSION['mensaje_error'] = "No se proporcionó un ID de actividad válido.";
                header("Location: panel_padres.php?seccion=actividades&accion=listar&estado=error_paso");
                exit();
            }

            // Obtener el número de paso más alto para esta actividad y añadir 1
            $sql_max_orden = "SELECT MAX(numero_paso) AS max_orden FROM pasos WHERE id_actividad = ?";
            $stmt_max_orden = mysqli_prepare($conexion, $sql_max_orden);
            if ($stmt_max_orden === false) {
                $_SESSION['mensaje_error'] = "Error interno del servidor al obtener el orden del paso.";
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                exit();
            }
            mysqli_stmt_bind_param($stmt_max_orden, "i", $id_actividad);
            mysqli_stmt_execute($stmt_max_orden);
            $res_max_orden = mysqli_stmt_get_result($stmt_max_orden);
            $fila_max_orden = mysqli_fetch_assoc($res_max_orden);
            $siguiente_numero_paso = ($fila_max_orden['max_orden'] !== null) ? $fila_max_orden['max_orden'] + 1 : 1;
            mysqli_stmt_close($stmt_max_orden);

            $nombre_imagen = null;
            $nombre_audio = null;
            $upload_dir_imagenes = __DIR__ . '/imagenes/pasos/';
            $upload_dir_audios = __DIR__ . '/audios/pasos/';

            // Crear los directorios si no existen
            if (!is_dir($upload_dir_imagenes) && !mkdir($upload_dir_imagenes, 0777, true)) {
                $_SESSION['mensaje_error'] = "Error al preparar el directorio de subida de imágenes.";
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                exit();
            }
            if (!is_dir($upload_dir_audios) && !mkdir($upload_dir_audios, 0777, true)) {
                $_SESSION['mensaje_error'] = "Error al preparar el directorio de subida de audios.";
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                exit();
            }

            // Procesar imagen subida o tomada con la cámara
            if (isset($_FILES['imagen_paso']) && $_FILES['imagen_paso']['error'] === UPLOAD_ERR_OK) {
                $file_tmp_name = $_FILES['imagen_paso']['tmp_name'];
                $file_ext = pathinfo($_FILES['imagen_paso']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = uniqid('img_') . '.' . $file_ext;
                $target_file = $upload_dir_imagenes . $nombre_imagen;
                if (!move_uploaded_file($file_tmp_name, $target_file)) {
                    $_SESSION['mensaje_error'] = "Error al subir la imagen. Inténtelo de nuevo.";
                    header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                    exit();
                }
            } elseif (isset($_POST['imagen_tomada_paso']) && !empty($_POST['imagen_tomada_paso'])) {
                $imagen_data = $_POST['imagen_tomada_paso'];
                list($type, $imagen_data) = explode(';', $imagen_data);
                list(, $imagen_data) = explode(',', $imagen_data);
                $imagen_data = base64_decode($imagen_data);
                $file_mime_type = explode(':', $type)[1];
                $file_ext = ($file_mime_type === 'image/png') ? 'png' : 'jpg';
                $nombre_imagen = uniqid('img_camara_') . '.' . $file_ext;
                $ruta_guardado_imagen = $upload_dir_imagenes . $nombre_imagen;
                if (file_put_contents($ruta_guardado_imagen, $imagen_data) === false) {
                    $_SESSION['mensaje_error'] = "Error al guardar la imagen de la cámara.";
                    header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                    exit();
                }
            }

            // Procesar audio subido o grabado
            if (isset($_FILES['audio_paso']) && $_FILES['audio_paso']['error'] === UPLOAD_ERR_OK) {
                $file_tmp_name = $_FILES['audio_paso']['tmp_name'];
                $file_ext = pathinfo($_FILES['audio_paso']['name'], PATHINFO_EXTENSION);
                $nombre_audio = uniqid('audio_') . '.' . $file_ext;
                $target_file = $upload_dir_audios . $nombre_audio;
                if (!move_uploaded_file($file_tmp_name, $target_file)) {
                    $_SESSION['mensaje_error'] = "Error al subir el audio. Inténtelo de nuevo.";
                    header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                    exit();
                }
            } elseif (isset($_POST['audio_grabado_paso']) && !empty($_POST['audio_grabado_paso'])) {
                $audio_data = $_POST['audio_grabado_paso'];
                list($type, $audio_data) = explode(';', $audio_data);
                list(, $audio_data) = explode(',', $audio_data);
                $audio_data = base64_decode($audio_data);
                $nombre_audio = uniqid('audio_grabado_') . '.webm';
                $ruta_guardado_audio = $upload_dir_audios . $nombre_audio;
                if (file_put_contents($ruta_guardado_audio, $audio_data) === false) {
                    $_SESSION['mensaje_error'] = "Error al guardar el audio grabado.";
                    header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                    exit();
                }
            }

            // Insertar el nuevo paso en la base de datos
            $sql_insert_paso = "INSERT INTO pasos (id_actividad, numero_paso, texto_paso, imagen_paso, audio_paso) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql_insert_paso);
            if ($stmt === false) {
                $_SESSION['mensaje_error'] = "Error interno del servidor al preparar el paso.";
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad);
                exit();
            }
            mysqli_stmt_bind_param($stmt, "iisss", $id_actividad, $siguiente_numero_paso, $texto_paso, $nombre_imagen, $nombre_audio);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                    // Redirección de ÉXITO: AHORA INCLUYE el parámetro 'estado'
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad . "&estado=exito_crear_paso");
                exit();
            } else {
                mysqli_stmt_close($stmt);
                // Redirección de ERROR: Usar el parámetro 'estado' para el error
                header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad . "&estado=error_paso");
                exit();
            }
            break; // Fin del case 'agregar_paso'
        case 'reordenar_pasos':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_actividad = $_POST['id_actividad'] ?? null;
                $ordenado = $_POST['pasos_ordenados'] ?? '';

                if ($id_actividad && $ordenado) {
                    $ids = explode(',', $ordenado);
                    foreach ($ids as $indice => $id_paso) {
                        // Es importante que el índice empiece en 1, no en 0, para que el número de paso sea correcto
                        $nuevo_numero_paso = $indice + 1; 
                        $stmt = mysqli_prepare($conexion, "UPDATE pasos SET numero_paso = ? WHERE id_paso = ? AND id_actividad = ?");
                        mysqli_stmt_bind_param($stmt, "iii", $nuevo_numero_paso, $id_paso, $id_actividad);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    // 💡 CAMBIO AQUI: Redirección para mostrar el mensaje de éxito 
                    header("Location: panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad . "&estado=exito_reordenar_pasos");
                    exit(); // Es crucial usar exit() después de una redirección
                }
            }
            // Si no hay POST, el código simplemente continuará.
            // En este caso, no hay que redirigir.
            break;
        default:
            // Opcional: Manejar un POST sin una acción reconocida
            // error_log("DEBUG: Petición POST sin acción reconocida.");
            break;
    }
}

// --- FIN LÓGICA POST GLOBAL ---
    
$seccion_actual = $_GET['seccion'] ?? 'asignaciones'; // Asignaciones como sección por defecto. este es un if(isset($_GET['seccion'])) { ...}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Adulto responsable - <?php echo htmlspecialchars($nombre_del_padre); ?></title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="estilos/estilos_panel_padres.css" rel="stylesheet"> <!-- MIS ESTILOS PARA PANEL PADRES -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    
</head>
<body class="body-login-page">
    <!-- Librerías jQuery UI necesarias -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<?php
include('componentes/enlace-saltar-a-contenido-principal.php');
?>

<header>
    <div class="cabecera">
        <h1>¡Hola, <?php echo htmlspecialchars($nombre_del_padre); ?>!</h1>
        <p>
            <a href="panel_padres.php?seccion=cuenta">Mi Cuenta</a> | <a href="componentes/salir_padre.php">Cerrar sesión</a></p>
<!-- Agrego un enlace Micuenta para que me cargue una seccion nueva que permite "Editar datos de cuenta" y "ELIMINAR Cuenta" -->
    </div>

    <nav class="nav-principal" aria-label="Navegación principal">
        <a href="panel_padres.php?seccion=actividades">Actividades</a>
        <a href="panel_padres.php?seccion=ninos">Perfiles Niños/as</a>
        <a href="panel_padres.php?seccion=asignaciones">Asignaciones</a>
    </nav>
</header>
<main  id="contenido-principal" role="main">
    <!-- agrego esta caja para que el enlace de SALTAR A CONT PRINCIPAL venga aqui. Ademas, le puse la etiqueta main para indicar 
region (landmark).Si uso etiqueta div puedo agregar role="main" para indicar landmark -->
    <?php
    switch($seccion_actual){
        case 'actividades':
            $accion_actividades = $_GET['accion'] ?? 'listar'; 

            switch ($accion_actividades) {
                case 'editar':
                    // Si la acción es 'editar' y se ha proporcionado un 'id' válido,
                    // incluimos el formulario de edición de la actividad.
                    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                        include('componentes/editar_actividad.php');
                    } else {
                        // Si no se proporciona un ID válido para editar,
                        // volvemos a mostrar la lista de actividades por defecto.
                        // Puedes añadir aquí un mensaje de error si lo consideras necesario.
                        include('componentes/gestionar_actividades.php'); 
                    }
                    break;
                case 'pasos': // para gestionar los pasos de una actividad
                    if (isset($_GET['id_actividad']) && is_numeric($_GET['id_actividad'])) {
                        include('componentes/gestionar_pasos.php');
                    } else {
                        // Si no se proporciona un ID de actividad válido para gestionar pasos,
                        // volvemos a mostrar la lista de actividades.
                        include('componentes/gestionar_actividades.php'); 
                    }
                    break;
                case 'listar': // Este es el caso por defecto cuando 'accion' no es 'editar'
                default: // Captura cualquier otro valor de 'accion' o si no se especifica
                    // Incluye el archivo que lista las actividades y tiene el formulario para crear nuevas.
                    include('componentes/gestionar_actividades.php'); 
                    break;
            }
            break;
        case 'ninos':
            $accion_ninos = $_GET['accion'] ?? 'listar'; // Por defecto, listar

            switch ($accion_ninos) {
                case 'editar':
                    // Si la acción es 'editar' y se ha proporcionado un 'id_nino' válido,
                    // incluimos el formulario de edición del niño.
                    if (isset($_GET['id_nino']) && is_numeric($_GET['id_nino'])) {
                        include('componentes/editar_nino.php');
                    } else {
                        // Si no se proporciona un ID válido para editar,
                        // volvemos a mostrar la lista de perfiles de niños por defecto.
                        include('componentes/gestionar_perfiles_ninos.php');
                    }
                    break;
                case 'listar': // Este es el caso por defecto
                default: // Captura cualquier otro valor de 'accion' o si no se especifica
                    // Incluye el archivo que lista los perfiles de niños.
                    include('componentes/gestionar_perfiles_ninos.php');
                    break;
            }
            break;
        case 'asignaciones':
            include('componentes/gestionar_asignaciones.php');
            break;
        case 'cuenta': // Nuevo caso para gestionar la cuenta del padre
            include('componentes/gestionar_cuenta.php');
            break;
        default:
            // Si la sección solicitada en la URL no existe o es inválida,
            // cargamos la sección de actividades por defecto.
            include('componentes/gestionar_asignaciones.php');
            break;
    }
    ?>
</main>
<script>
    // =========================================================================
// --- Lógica para el Formulario de EDITAR ACTIVIDAD (IMAGEN y CÁMARA) ---
// =========================================================================

// Solo ejecuta si los elementos existen (cuando se carga la sección de edición)
if (document.getElementById('opcion_mantener_img_edit')) {
    const opcionMantenerImgEdit = document.getElementById('opcion_mantener_img_edit');
    const opcionSubirImgEdit = document.getElementById('opcion_subir_img_edit');
    const opcionCamaraEdit = document.getElementById('opcion_camara_edit');

    const contenedorSubirImgEdit = document.getElementById('contenedor_subir_imagen_edit');
    const contenedorCamaraEdit = document.getElementById('contenedor_camara_edit');

    const videoCamaraEdit = document.getElementById('video_camara_edit');
    const botonTomarFotoEdit = document.getElementById('boton_tomar_foto_edit');
    const canvasFotoEdit = document.getElementById('canvas_foto_edit');
    const inputImagenTomadaEdit = document.getElementById('imagen_tomada_edit');
    const inputImagenFileEdit = document.getElementById('imagen_actividad_file');
    const estadoCamaraEdit = document.getElementById('estado_camara_edit');

    let streamEdit; // Stream global para la cámara de edición

    function manejarOpcionesImagenEdicion() {
        // Detener el stream anterior si existe
        if (streamEdit) {
            streamEdit.getTracks().forEach(track => track.stop());
            streamEdit = null;
        }

        // Ocultar ambos contenedores por defecto
        contenedorSubirImgEdit.style.display = 'none';
        contenedorCamaraEdit.style.display = 'none';
        
        // Limpiar los campos y requirements
        inputImagenTomadaEdit.value = '';
        inputImagenFileEdit.value = '';
        estadoCamaraEdit.textContent = '';


        if (opcionSubirImgEdit.checked) {
            // Opción: Subir archivo
            contenedorSubirImgEdit.style.display = 'block';

        } else if (opcionCamaraEdit.checked) {
            // Opción: Usar cámara
            contenedorCamaraEdit.style.display = 'block';
            
            // Inicializar la cámara
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(s => {
                    streamEdit = s;
                    videoCamaraEdit.srcObject = streamEdit;
                    videoCamaraEdit.play();
                    estadoCamaraEdit.textContent = 'Cámara lista. Presiona "Tomar Foto".';
                })
                .catch(err => {
                    console.error("Error al acceder a la cámara:", err);
                    estadoCamaraEdit.textContent = "Error: No se pudo acceder a la cámara. Verifique los permisos.";
                    alert("No se pudo acceder a la cámara para la edición. Por favor, asegúrese de haber dado los permisos.");
                    
                    // Si falla la cámara, forzar la opción Mantener
                    opcionMantenerImgEdit.checked = true;
                    manejarOpcionesImagenEdicion();
                });
        }
    }

    // Escuchadores de eventos para los radio buttons de Edición
    opcionMantenerImgEdit.addEventListener('change', manejarOpcionesImagenEdicion);
    opcionSubirImgEdit.addEventListener('change', manejarOpcionesImagenEdicion);
    opcionCamaraEdit.addEventListener('change', manejarOpcionesImagenEdicion);

    // Llama a la función al inicio para establecer el estado inicial (mantener por defecto)
    manejarOpcionesImagenEdicion(); 

    // --- Lógica para tomar la foto en Edición ---
    botonTomarFotoEdit.onclick = () => {
        const context = canvasFotoEdit.getContext('2d');
        // Aseguramos que el canvas tenga el tamaño exacto del video
        canvasFotoEdit.width = videoCamaraEdit.videoWidth;
        canvasFotoEdit.height = videoCamaraEdit.videoHeight;
        context.drawImage(videoCamaraEdit, 0, 0, canvasFotoEdit.width, canvasFotoEdit.height);
        
        // Convertir la imagen del canvas a Base64
        const dataUrl = canvasFotoEdit.toDataURL('image/jpeg', 0.8);
        inputImagenTomadaEdit.value = dataUrl; // Guardar Base64 en el input hidden
        estadoCamaraEdit.textContent = '¡Foto tomada! Lista para guardar con la actividad.';
    };
}
</script>
</body>
</html>