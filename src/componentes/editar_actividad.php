<?php
//session_start(); // Asegúrate de que la sesión esté iniciada

// Asegúrate de que la ruta a conexion.php es correcta desde la ubicación de este archivo.
// Si este archivo está en 'componentes/' y 'conexion.php' también, entonces es 'conexion.php'.
// Si 'conexion.php' está en la raíz, entonces es '../conexion.php'.
include("conexion.php"); 


// --- LÓGICA PARA CARGAR DATOS EN EL FORMULARIO (MÉTODO GET) ---


// Una solución más robusta:
if (!isset($conexion) || !mysqli_ping($conexion)) { // Verificar si la conexión existe y es activa
    // Si la conexión no está activa (ej. se cerró en el POST o es el primer GET), re-incluirla
    include("conexion.php"); 
}

// Obtener el ID de la Actividad de la URL (para la carga inicial del formulario)
$id_actividad = $_GET['id'] ?? 0;
$id_actividad = intval($id_actividad);

// Redirigir si el ID no es válido
if ($id_actividad <= 0) {
    header("Location: panel_padres.php?seccion=actividades&mensaje=no_id_valido");
    exit();
}

// Verificar sesión del padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: panel_padres.php?seccion=actividades&mensaje=error_datos_invalidos");
    exit();
}
$id_padre_sesion = $_SESSION['id_padre'];

// Obtener datos de la actividad para rellenar el formulario
$sql_select = "SELECT id_actividad, nombre_actividad, descripcion, foto_actividad, texto_alternativo FROM actividades WHERE id_actividad = ? AND id_padre = ?";
$stmt_select = mysqli_prepare($conexion, $sql_select);

if ($stmt_select === false) {
    die("Error en la preparación de la consulta de selección: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt_select, "ii", $id_actividad, $id_padre_sesion);
mysqli_stmt_execute($stmt_select);
$resultado_select = mysqli_stmt_get_result($stmt_select);
$actividad_datos_select = mysqli_fetch_assoc($resultado_select);

// Si la actividad no se encuentra o no pertenece al padre
if (!$actividad_datos_select) {
    header("Location: panel_padres.php?seccion=actividades&mensaje=actividad_no_encontrada_o_no_autorizada");
    exit();
}

// Asignar los datos a variables para rellenar el formulario HTML
$nombre_actividad_form = htmlspecialchars($actividad_datos_select['nombre_actividad']);
$descripcion_actividad_form = htmlspecialchars($actividad_datos_select['descripcion']);
$imagen_actual_ruta_form = htmlspecialchars($actividad_datos_select['foto_actividad']);
$texto_alternativo_form = htmlspecialchars($actividad_datos_select['texto_alternativo'] ?? '');

mysqli_stmt_close($stmt_select);
mysqli_close($conexion); // Cierra la conexión después de obtener los datos para el formulario y antes de que se rinda el HTML.
?>



<section class="seccion-panel" id="contenedor_form_editar_act" aria-labelledby="edicion-de-actividad">
    <h2 id="edicion-de-actividad">Editar datos de Actividad: </h2>
    <form action="panel_padres.php?seccion=actividades&accion=editar&id=<?php echo $id_actividad; ?>&action=editar_actividad" method="POST" enctype="multipart/form-data">
        <!-- el parametro action=editar_actividad es para que entre en el switch que maneja las logicas php para formulario con metodo POST -->
        <input type="hidden" name="id_actividad" value="<?php echo $id_actividad; ?>">

        
        <label for="nombre_actividad" class="form_label">Título: </label>
        <input type="text" class="form-control" id="nombre_actividad" name="nombre_actividad" value="<?php echo $nombre_actividad_form; ?>" required>
    
        <label for="descripcion" class="form_label">Descripción: </label>
        <textarea class="form-control" id="descripcion" name="descripcion_actividad" rows="5" required><?php echo $descripcion_actividad_form; ?></textarea>
    
        <p class="form_label">Imagen Actual: </p>
        <?php if (!empty($imagen_actual_ruta_form)): ?>
            <img src="<?php echo $imagen_actual_ruta_form; ?>" alt="<?php echo $texto_alternativo_form ?: 'Imagen actual de la actividad'; ?>" class="imagen-miniatura">
            <p class="form-control" style="display: none;">Ruta: <?php echo $imagen_actual_ruta_form; ?></p>
            <input type="hidden" name="imagen_actual_db" value="<?php echo $imagen_actual_ruta_form; ?>">
        <?php else: ?>
            <p class="text-muted">No hay imagen actual.</p>
            <input type="hidden" name="imagen_actual_db" value="">
        <?php endif; ?>

        <fieldset class="opciones-imagen" style="margin-top: 20px;">
            <legend class="form-label-inline">Cambiar Imagen:</legend>
            
            <input type="radio" id="opcion_mantener_img_edit" name="imagen_opcion_edit" value="mantener" checked>
            <label for="opcion_mantener_img_edit">Mantener imagen actual</label>

            <input type="radio" id="opcion_subir_img_edit" name="imagen_opcion_edit" value="subir">
            <label for="opcion_subir_img_edit">Subir nuevo archivo</label>

            <input type="radio" id="opcion_camara_edit" name="imagen_opcion_edit" value="camara">
            <label for="opcion_camara_edit">Usar cámara</label>
            
        </fieldset>

        <div id="contenedor_subir_imagen_edit" style="display: none; margin-top: 10px;">
            <label for="imagen_actividad_file" class="form_label">Seleccionar nuevo archivo:</label>
            <p id="instrucciones_imagen_edit" class="form-text">Tamaño máximo 1MB. Formatos permitidos: jpeg, png, gif, webp.</p>
            <input id="imagen_actividad_file" type="file" name="imagen_actividad_file" accept="image/*" class="form-control" aria-describedby="instrucciones_imagen_edit">
        </div>

        <div id="contenedor_camara_edit" style="display: none; margin-top: 10px;">
            <p>Previsualización de la cámara:</p>
            <video id="video_camara_edit" width="320" height="240" autoplay></video>
            <button type="button" id="boton_tomar_foto_edit" class="boton-accion">Tomar Foto</button>
            <canvas id="canvas_foto_edit" style="display: none;"></canvas>
            <p id="estado_camara_edit" class="mensaje-grabacion"></p>
            <input type="hidden" id="imagen_tomada_edit" name="imagen_tomada_edit">
        </div>
        
        <label for="alt_texto_actividad" class="form_label">Texto Alternativo para la Imagen (opcional):</label>
        <input type="text" id="alt_texto_actividad" name="alt_texto_actividad" class="form-control" placeholder="Describe la imagen 
        para accesibilidad" value="<?php echo $texto_alternativo_form; ?>">
    
        <button type="submit" role="button">Guardar Cambios</button>
        <a href="panel_padres.php?seccion=actividades&accion=listar">Cancelar y volver a la lista de actividades</a>
        <!-- si quiero mantener el boton Cancelar, puedo poner 
         <a href="panel_padres.php?seccion=actividades&accion=listar" aria-label="Cancelar y volver a la lista de actividades">Cancelar</a>
          -->
        
    </form>
</section>
