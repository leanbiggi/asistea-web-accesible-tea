<?php
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../login.php?mensaje=sesion_expirada");
    exit();
}
$id_padre_sesion = $_SESSION['id_padre'];
$id_actividad = filter_input(INPUT_GET, 'id_actividad', FILTER_VALIDATE_INT);

if (!$id_actividad) {
    header("Location: ../panel_padres.php?mensaje=no_id_actividad_valido");
    exit();
}

$stmt_check_act = mysqli_prepare($conexion, "SELECT nombre_actividad FROM actividades WHERE id_actividad = ? AND id_padre = ?");
mysqli_stmt_bind_param($stmt_check_act, "ii", $id_actividad, $id_padre_sesion);
mysqli_stmt_execute($stmt_check_act);
$res_check_act = mysqli_stmt_get_result($stmt_check_act);
$actividad_info = mysqli_fetch_assoc($res_check_act);
mysqli_stmt_close($stmt_check_act);

if (!$actividad_info) {
    header("Location: ../panel_padres.php?mensaje=actividad_no_encontrada");
    exit();
}
$nombre_actividad = $actividad_info['nombre_actividad'];

$pasos = [];
$stmt_pasos = mysqli_prepare($conexion, "SELECT id_paso, texto_paso, imagen_paso, audio_paso FROM pasos WHERE id_actividad = ? ORDER BY numero_paso ASC");
mysqli_stmt_bind_param($stmt_pasos, "i", $id_actividad);
mysqli_stmt_execute($stmt_pasos);
$res_pasos = mysqli_stmt_get_result($stmt_pasos);
while ($fila_paso = mysqli_fetch_assoc($res_pasos)) {
    $pasos[] = $fila_paso;
}
mysqli_stmt_close($stmt_pasos);

// Crea una versión segura del nombre de la actividad para el ID
// Reemplaza espacios y caracteres especiales con guiones bajos y convierte a minúsculas
$nombre_actividad_id = strtolower(str_replace(' ', '_', $nombre_actividad));
$nombre_actividad_id = preg_replace('/[^a-z0-9_-]/', '', $nombre_actividad_id);
?>

<section class="caja-titulo-gestionar-pasos" aria-labelledby="gestionar-pasos-para-<?php echo htmlspecialchars($nombre_actividad_id); ?>">
    <h2 id="gestionar-pasos-para-<?php echo htmlspecialchars($nombre_actividad_id); ?>">Gestionar Pasos para: <?php echo htmlspecialchars($nombre_actividad); ?></h2>
    <p>Instrucciones: aquí puede Agregar un nuevo paso, editar un paso y reordenar los pasos arrastrandolos de arriba hacia abajo.</p>

    <div id="notificacion_pasos" role="status" aria-live="polite" tabindex="-1">
    <?php if (!empty($mensaje_pasos)) { ?>
        <p class="mensaje_form <?php echo htmlspecialchars($clase_pasos); ?>">
            <?php echo htmlspecialchars($mensaje_pasos); ?>
        </p>
    <?php } ?>
    </div>
    
    <p><a href="panel_padres.php?seccion=actividades">Volver a Gestionar Actividades</a></p>

<section id="contenedor_form_agregar_nuevo_paso" class="seccion-panel" aria-labelledby="agregar-nuevo-paso">
    <h3 id="agregar-nuevo-paso">Agregar nuevo paso</h3>
    <form action="panel_padres.php?seccion=actividades&action=agregar_paso" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_actividad" value="<?php echo htmlspecialchars($id_actividad); ?>">

        <label for="texto_paso" class="form_label">Texto del Paso: </label>
        <textarea id="texto_paso" name="texto_paso" rows="3" required class="form-control"></textarea>

<!--        <div class="opciones-imagen">
            <label class="form-label-inline">Elige una opción para la imagen:</label>
            <input type="radio" id="opcion_subir_img" name="imagen_opcion" value="subir" checked>
            <label for="opcion_subir_img">Subir archivo</label>
            <input type="radio" id="opcion_camara" name="imagen_opcion" value="camara">
            <label for="opcion_camara">Usar cámara</label>
        </div>
    -->
        <fieldset class="opciones-imagen">  <!-- Aqui agrego fieldset y legend para agrupar los radio buton: los 2 para imagen y los otros 2 
            para el sonido. Si no uso estas etiquetas
            me toma los 4 radio como un conjunto. -->
            <legend class="form-label-inline">Opciones para subir la imagen:</legend>
            <input type="radio" id="opcion_subir_img" name="imagen_opcion" value="subir" checked>
            <label for="opcion_subir_img">Subir archivo</label>
            
            <input type="radio" id="opcion_camara" name="imagen_opcion" value="camara">
            <label for="opcion_camara">Usar cámara</label>
        </fieldset>
        <div id="contenedor_subir_imagen">
            <label for="imagen_paso" class="form_label">Subir imagen: </label>
            <p id="instrucciones_imagen" class="form-text">Tamaño máximo 1MB. Formatos permitidos: jpeg, png, gif, webp.</p>
            <input type="file" id="imagen_paso" name="imagen_paso" accept="image/*" class="form-control" required aria-describedby="instrucciones_imagen">
        </div>

        <div id="contenedor_camara" style="display: none;">
            <p>Previsualización de la cámara:</p>
            <video id="video_camara" width="320" height="240" autoplay></video>
            <button type="button" id="boton_tomar_foto" class="boton-accion">Tomar Foto</button>
            <canvas id="canvas_foto" style="display: none;"></canvas>
            <p id="estado_camara" class="mensaje-grabacion"></p>
            <input type="hidden" id="imagen_tomada_paso" name="imagen_tomada_paso">
        </div>

<!--        <div class="opciones-audio">
            <label class="form-label-inline">Elige una opción para el audio (opcional):</label>
            <input type="radio" id="opcion_subir" name="audio_opcion" value="subir" checked>
            <label for="opcion_subir">Subir archivo</label>
            <input type="radio" id="opcion_grabar" name="audio_opcion" value="grabar">
            <label for="opcion_grabar">Grabar audio</label>
        </div>
    -->
        <fieldset class="opciones-audio">
            <legend class="form-label-inline">Opciones para el audio (opcional):</legend>
            <input type="radio" id="opcion_subir" name="audio_opcion" value="subir" checked>
            <label for="opcion_subir">Subir archivo</label>
            
            <input type="radio" id="opcion_grabar" name="audio_opcion" value="grabar">
            <label for="opcion_grabar">Grabar audio</label>
        </fieldset>
        <div id="contenedor_subir_audio">
            <label for="audio_paso" class="form_label">Audio: </label>
            <input type="file" id="audio_paso" name="audio_paso" accept="audio/*" class="form-control">
        </div>

        <div id="contenedor_grabar_audio" style="display: none;">
            <p>O, grabar audio directamente:</p>
            <button type="button" id="boton_grabar" class="boton-accion">Grabar</button>
            <button type="button" id="boton_detener" class="boton-accion" disabled>Detener</button>
            <button type="button" id="boton_reproducir" class="boton-accion" disabled>Reproducir</button>
            <p id="estado_grabacion" class="mensaje-grabacion"></p>
            <audio id="vista_previa_audio" controls></audio>
            <input type="hidden" id="audio_grabado_paso" name="audio_grabado_paso">
        </div>

        <input type="submit" value="Agregar paso">
    </form>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Lógica para mostrar/ocultar los contenedores de AUDIO ---
        const opcionSubirAudio = document.getElementById('opcion_subir');
        const opcionGrabarAudio = document.getElementById('opcion_grabar');
        const contenedorSubirAudio = document.getElementById('contenedor_subir_audio');
        const contenedorGrabarAudio = document.getElementById('contenedor_grabar_audio');

        function manejarOpcionesAudio() {
            if (opcionSubirAudio.checked) {
                contenedorSubirAudio.style.display = 'block';
                contenedorGrabarAudio.style.display = 'none';
            } else {
                contenedorSubirAudio.style.display = 'none';
                contenedorGrabarAudio.style.display = 'block';
            }
        }
        opcionSubirAudio.addEventListener('change', manejarOpcionesAudio);
        opcionGrabarAudio.addEventListener('change', manejarOpcionesAudio);
        manejarOpcionesAudio(); // Llama al inicio para establecer el estado inicial

        // --- Lógica de grabación de AUDIO (la que ya tenías) ---
        const botonGrabar = document.getElementById('boton_grabar');
        const botonDetener = document.getElementById('boton_detener');
        const botonReproducir = document.getElementById('boton_reproducir');
        const estadoGrabacion = document.getElementById('estado_grabacion');
        const vistaPreviaAudio = document.getElementById('vista_previa_audio');
        const inputAudioGrabado = document.getElementById('audio_grabado_paso');
        let mediaRecorder;
        let audioChunks = [];

        botonGrabar.onclick = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                vistaPreviaAudio.src = URL.createObjectURL(audioBlob);
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = function() {
                    inputAudioGrabado.value = reader.result;
                };
                audioChunks = [];
            };
            mediaRecorder.start();
            estadoGrabacion.textContent = 'Grabando...';
            botonGrabar.disabled = true;
            botonDetener.disabled = false;
            botonReproducir.disabled = true;
        };

        botonDetener.onclick = () => {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            estadoGrabacion.textContent = 'Grabación finalizada.';
            botonGrabar.disabled = false;
            botonDetener.disabled = true;
            botonReproducir.disabled = false;
        };
        
        botonReproducir.onclick = () => {
            vistaPreviaAudio.play();
        };

        // --- Lógica para mostrar/ocultar los contenedores de IMAGEN y VALIDACIÓN ---
        const formAgregarPaso = document.querySelector('#contenedor_form_agregar_nuevo_paso form');
        const opcionSubirImg = document.getElementById('opcion_subir_img');
        const opcionCamara = document.getElementById('opcion_camara');
        const contenedorSubirImg = document.getElementById('contenedor_subir_imagen');
        const contenedorCamara = document.getElementById('contenedor_camara');
        const videoCamara = document.getElementById('video_camara');
        const botonTomarFoto = document.getElementById('boton_tomar_foto');
        const canvasFoto = document.getElementById('canvas_foto');
        const inputImagenTomada = document.getElementById('imagen_tomada_paso');
        const inputImagenPaso = document.getElementById('imagen_paso');
        const estadoCamara = document.getElementById('estado_camara');

        let stream;

        function manejarOpcionesImagen() {
            if (opcionSubirImg.checked) {
                contenedorSubirImg.style.display = 'block';
                contenedorCamara.style.display = 'none';
                inputImagenPaso.required = true; // El campo de subida es requerido
                inputImagenTomada.value = ''; // Limpia el valor de la cámara
                inputImagenTomada.removeAttribute('required'); // Asegúrate de que no sea requerido
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            } else {
                contenedorSubirImg.style.display = 'none';
                contenedorCamara.style.display = 'block';
                inputImagenPaso.removeAttribute('required'); // El campo de subida ya no es requerido
                inputImagenTomada.required = true; // El campo de la cámara es requerido
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(s => {
                        stream = s;
                        videoCamara.srcObject = stream;
                        videoCamara.play();
                    })
                    .catch(err => {
                        console.error("Error al acceder a la cámara:", err);
                        estadoCamara.textContent = "No se pudo acceder a la cámara. Por favor, asegúrese de que esté conectada y de haber dado los permisos.";
                        alert("No se pudo acceder a la cámara. Por favor, asegúrese de que esté conectada y de haber dado los permisos.");
                    });
            }
        }
        opcionSubirImg.addEventListener('change', manejarOpcionesImagen);
        opcionCamara.addEventListener('change', manejarOpcionesImagen);
        manejarOpcionesImagen();

        // --- Lógica para tomar la foto ---
        botonTomarFoto.onclick = () => {
            const context = canvasFoto.getContext('2d');
            canvasFoto.width = videoCamara.videoWidth;
            canvasFoto.height = videoCamara.videoHeight;
            context.drawImage(videoCamara, 0, 0, canvasFoto.width, canvasFoto.height);
            const dataUrl = canvasFoto.toDataURL('image/jpeg', 0.8); // 0.8 es la calidad de la imagen
            inputImagenTomada.value = dataUrl;
            estadoCamara.textContent = '¡Foto tomada! Puedes enviarla ahora.';
        };
        
        // --- Lógica para mostrar/ocultar formulario de edición y el reordenamiento (la que ya tenías) ---
        // (El resto de tu código jQuery para la edición y reordenamiento iría aquí)
        // ...
    // --- Lógica para Formularios de EDICIÓN de Pasos (Audio y Grabación) ---
// =========================================================================

// Objeto para mantener el estado del MediaRecorder y los chunks para cada paso
const audioRecorders = {};

// Función utilitaria para obtener los elementos específicos de un paso (por su ID)
function getAudioElementsEdit(pasoId) {
    return {
        // Elementos de opciones y contenedores
        contenedorSubir: document.getElementById('contenedor_subir_audio_edit_' + pasoId),
        contenedorGrabar: document.getElementById('contenedor_grabar_audio_edit_' + pasoId),
        
        // Elementos de grabación
        botonGrabar: document.querySelector('.boton-grabar-edit[data-id-paso="' + pasoId + '"]'),
        botonDetener: document.querySelector('.boton-detener-edit[data-id-paso="' + pasoId + '"]'),
        botonReproducir: document.querySelector('.boton-reproducir-edit[data-id-paso="' + pasoId + '"]'),
        estadoGrabacion: document.getElementById('estado_grabacion_edit_' + pasoId),
        vistaPreviaAudio: document.getElementById('vista_previa_audio_edit_' + pasoId),
        inputAudioGrabado: document.getElementById('audio_grabado_paso_edit_' + pasoId)
    };
}


// 1. Lógica para mostrar/ocultar los contenedores de AUDIO en EDICIÓN
document.querySelectorAll('input[name^="audio_opcion_edit"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Extrae el ID del paso de la ID del radio button (ej: 'opcion_subir_audio_edit_123' -> 123)
        const pasoId = this.id.match(/\d+$/)[0]; 
        const elements = getAudioElementsEdit(pasoId);
        
        // Ocultar ambos contenedores primero
        if (elements.contenedorSubir) elements.contenedorSubir.style.display = 'none';
        if (elements.contenedorGrabar) elements.contenedorGrabar.style.display = 'none';

        // Lógica de visualización
        if (this.value === 'subir') {
            if (elements.contenedorSubir) elements.contenedorSubir.style.display = 'block';
        } else if (this.value === 'grabar') {
            if (elements.contenedorGrabar) elements.contenedorGrabar.style.display = 'block';
        }
    });
});

// Asegurar el estado inicial para todos los formularios al cargar la página (dispara el evento 'change' en el radio button chequeado)
document.querySelectorAll('input[name^="audio_opcion_edit"]:checked').forEach(radio => {
    radio.dispatchEvent(new Event('change'));
});

// 2. Lógica de Grabación (Botón GRABAR)
document.querySelectorAll('.boton-grabar-edit').forEach(boton => {
    boton.addEventListener('click', async function() {
        const pasoId = this.getAttribute('data-id-paso');
        const elements = getAudioElementsEdit(pasoId);
        
        // Si hay una grabación activa para este paso, la detenemos primero
        if (audioRecorders[pasoId] && audioRecorders[pasoId].mediaRecorder.state !== 'inactive') {
             audioRecorders[pasoId].mediaRecorder.stop();
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mediaRecorder = new MediaRecorder(stream);
            let audioChunks = []; // Array limpio para la nueva grabación
            
            audioRecorders[pasoId] = { mediaRecorder, audioChunks, stream };

            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                elements.vistaPreviaAudio.src = URL.createObjectURL(audioBlob);
                
                // Convertir Blob a Base64 para enviarlo en el formulario
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = function() {
                    elements.inputAudioGrabado.value = reader.result;
                };

                elements.estadoGrabacion.textContent = 'Grabación finalizada. Lista para guardar.';
                elements.botonGrabar.disabled = false;
                elements.botonDetener.disabled = true;
                elements.botonReproducir.disabled = false;
                
                // Detener la pista del micrófono
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            elements.estadoGrabacion.textContent = 'Grabando...';
            elements.botonGrabar.disabled = true;
            elements.botonDetener.disabled = false;
            elements.botonReproducir.disabled = true;
            elements.vistaPreviaAudio.src = ''; // Limpiar previsualización anterior
            elements.inputAudioGrabado.value = ''; // Limpiar input hidden anterior
            
        } catch (err) {
            console.error("Error al acceder al micrófono:", err);
            elements.estadoGrabacion.textContent = "Error: No se pudo acceder al micrófono. Verifique los permisos.";
            alert("Error: No se pudo acceder al micrófono. Verifique los permisos.");
            
            // Restablecer el estado de los botones si falla el acceso
            if(elements.botonGrabar) elements.botonGrabar.disabled = false;
            if(elements.botonDetener) elements.botonDetener.disabled = true;
            if(elements.botonReproducir) elements.botonReproducir.disabled = true;
        }
    });
});

// 3. Lógica de Detener Grabación (Botón DETENER)
document.querySelectorAll('.boton-detener-edit').forEach(boton => {
    boton.addEventListener('click', function() {
        const pasoId = this.getAttribute('data-id-paso');
        const recorder = audioRecorders[pasoId];
        if (recorder && recorder.mediaRecorder.state !== 'inactive') {
            recorder.mediaRecorder.stop();
        }
        // La lógica de detener y liberar la pista se ejecuta dentro de mediaRecorder.onstop
    });
});

// 4. Lógica de Reproducir Audio (Botón REPRODUCIR)
document.querySelectorAll('.boton-reproducir-edit').forEach(boton => {
    boton.addEventListener('click', function() {
        const pasoId = this.getAttribute('data-id-paso');
        const elements = getAudioElementsEdit(pasoId);
        if (elements.vistaPreviaAudio && elements.vistaPreviaAudio.src) {
            elements.vistaPreviaAudio.play();
        }
    });
});
    });
</script>


</section>

<section class="seccion-panel" aria-labelledby="lista-de-pasos-cargados:-arrastra-para-reordenar">
<?php if (empty($pasos)): ?>
    <p>No hay pasos definidos para esta actividad.</p>
<?php else: ?>
    <h3 id="lista-de-pasos-cargados:-arrastra-para-reordenar">Lista de pasos cargados: (Arrastra para reordenar)</h3>
    <ol class="lista-pasos" id="pasosList"> 
        <?php foreach ($pasos as $index => $paso): ?>
            <li class="paso-individual" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>">
                <div class="paso-header">
                    <strong>PASO <?php echo $index + 1; ?>:</strong>
                    <div class="paso-acciones">
                        <button class="boton-accion boton-editar" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>"
                            aria-label="Editar paso <?php echo $index + 1; ?>: <?php echo htmlspecialchars($paso['texto_paso']); ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>

                        <button class="boton-accion boton-eliminar" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>"
                            data-id-actividad="<?php echo htmlspecialchars($id_actividad); ?>"
                            onclick="eliminarPaso(this);"
                            aria-label="Eliminar paso <?php echo $index + 1; ?>: <?php echo htmlspecialchars($paso['texto_paso']); ?>">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>

                        <script>
                            function eliminarPaso(boton) {
                                const id_paso = boton.getAttribute('data-id-paso');
                                const id_actividad = boton.getAttribute('data-id-actividad');
                                if (confirm('¿Estás seguro de que quieres eliminar este paso? Esta acción es irreversible.')) {
                                    // Construye la URL de redirección
                                    window.location.href = `componentes/eliminar_paso.php?id_paso=${id_paso}&id_actividad=${id_actividad}&seccion=actividades&accion=pasos`;
                                }
                            }
                        </script>
                    </div>
                </div>
                
                <p class="paso-texto" id="mostrar_paso_<?php echo htmlspecialchars($paso['id_paso']); ?>"><?php echo nl2br(htmlspecialchars($paso['texto_paso'])); ?></p>
                
                <?php if (!empty($paso['imagen_paso'])): ?>
                    <div class="paso-media">
                        Imagen: <img src="imagenes/pasos/<?php echo htmlspecialchars($paso['imagen_paso']); ?>" alt="<?php echo htmlspecialchars($paso['texto_paso']); ?>" class="paso-imagen">
                    </div>
                <?php endif; ?>

                <?php if (!empty($paso['audio_paso'])): ?>
                    <div class="paso-media">
                        Audio: <audio controls src="audios/pasos/<?php echo htmlspecialchars($paso['audio_paso']); ?>" class="paso-audio"></audio>
                    </div>
                <?php endif; ?>

                <section class="paso-edit-form" id="edit_form_<?php echo htmlspecialchars($paso['id_paso']); ?>" style="display:none;" tabindex="-1" aria-labelledby="heading-editar-paso-<?php echo htmlspecialchars($paso['id_paso']); ?>">
                    <form id="form-actualizar-paso-<?php echo htmlspecialchars($paso['id_paso']); ?>" action="componentes/actualizar_paso.php" method="POST" enctype="multipart/form-data" class="form-actualizar-paso">
    <h4 id="heading-editar-paso-<?php echo htmlspecialchars($paso['id_paso']); ?>">Editar paso <?php echo $index + 1; ?>: <?php echo htmlspecialchars($paso['texto_paso']); ?></h4>
    <input type="hidden" name="id_paso" value="<?php echo htmlspecialchars($paso['id_paso']); ?>">
    <input type="hidden" name="id_actividad" value="<?php echo htmlspecialchars($id_actividad); ?>">

    <div>
        <label class="form_label" for="texto_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>">Texto del Paso:</label>
        <textarea name="texto_paso" id="texto_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" rows="3" required><?php echo htmlspecialchars($paso['texto_paso']); ?></textarea>
    </div>

    <div>
        <?php if (!empty($paso['imagen_paso'])): ?>
            <p class="form-text">Imagen actual: <br><img src="imagenes/pasos/<?php echo htmlspecialchars($paso['imagen_paso']); ?>" alt="<?php echo htmlspecialchars($paso['texto_paso']); ?>" style="max-width: 100px; max-height: 100px;"></p>
            <input type="hidden" name="imagen_paso_actual" value="<?php echo htmlspecialchars($paso['imagen_paso']); ?>">
        <?php endif; ?>

        <fieldset class="opciones-imagen-edit">
            <legend class="form-label-inline">Opciones de Imagen:</legend>
            <input type="radio" id="opcion_mantener_img_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="imagen_opcion_edit" value="mantener" checked>
            <label for="opcion_mantener_img_<?php echo htmlspecialchars($paso['id_paso']); ?>">Mantener actual</label>
            <input type="radio" id="opcion_subir_img_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="imagen_opcion_edit" value="subir">
            <label for="opcion_subir_img_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>">Subir archivo</label>
            <input type="radio" id="opcion_camara_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="imagen_opcion_edit" value="camara">
            <label for="opcion_camara_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>">Usar cámara</label>
        </fieldset>
        <div id="contenedor_subir_imagen_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" style="display:none; margin-top: 10px;">
            <label for="imagen_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="form_label">Seleccionar nueva imagen: </label>
            <p class="form-text">Tamaño máximo 1MB. Formatos permitidos: jpeg, png, gif, webp.</p>
            <input type="file" name="imagen_paso" id="imagen_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" accept="image/*" class="form-control imagen_paso_edit_file">
        </div>

        <div id="contenedor_camara_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="contenedor_camara_edit" style="display: none; margin-top: 10px;">
            <p>Previsualización de la cámara:</p>
            <video id="video_camara_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" width="320" height="240" autoplay></video>
            <button type="button" class="boton-accion boton-tomar-foto-edit" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>">Tomar Foto</button>
            <canvas id="canvas_foto_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" style="display: none;"></canvas>
            <p id="estado_camara_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="mensaje-grabacion"></p>
            <input type="hidden" id="imagen_tomada_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="imagen_tomada_paso_edit">
        </div>
    </div>


    <div>
    <?php if (!empty($paso['audio_paso'])): ?>
        <p class="form-text">Audio actual: <br><audio controls src="audios/pasos/<?php echo htmlspecialchars($paso['audio_paso']); ?>" class="paso-audio"></audio></p>
        <input type="hidden" name="audio_paso_actual" value="<?php echo htmlspecialchars($paso['audio_paso']); ?>">
    <?php endif; ?>

    <fieldset class="opciones-audio-edit" style="margin-top: 15px;">
        <legend class="form-label-inline">Cambiar Audio: Elige una opción (opcional):</legend>
        
        <input type="radio" id="opcion_mantener_audio_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="audio_opcion_edit" value="mantener" checked>
        <label for="opcion_mantener_audio_<?php echo htmlspecialchars($paso['id_paso']); ?>">Mantener actual</label>
        
        <input type="radio" id="opcion_subir_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="audio_opcion_edit" value="subir">
        <label for="opcion_subir_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>">Subir archivo</label>
        
        <input type="radio" id="opcion_grabar_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="audio_opcion_edit" value="grabar">
        <label for="opcion_grabar_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>">Grabar audio</label>
    </fieldset>
    <div id="contenedor_subir_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" style="display:none; margin-top: 10px;">
        <label for="audio_paso_edit_file_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="form_label">Seleccionar nuevo audio: </label>
        <input type="file" name="audio_paso_file" id="audio_paso_edit_file_<?php echo htmlspecialchars($paso['id_paso']); ?>" accept="audio/*" class="form-control">
    </div>

    <div id="contenedor_grabar_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="contenedor_grabar_audio_edit" style="display: none; margin-top: 10px;">
        <p>Grabar audio directamente:</p>
        <button type="button" class="boton-accion boton-grabar-edit" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>">Grabar</button>
        <button type="button" class="boton-accion boton-detener-edit" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>" disabled>Detener</button>
        <button type="button" class="boton-accion boton-reproducir-edit" data-id-paso="<?php echo htmlspecialchars($paso['id_paso']); ?>" disabled>Reproducir</button>
        <p id="estado_grabacion_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" class="mensaje-grabacion"></p>
        <audio id="vista_previa_audio_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" controls></audio>
        <input type="hidden" id="audio_grabado_paso_edit_<?php echo htmlspecialchars($paso['id_paso']); ?>" name="audio_grabado_paso_edit">
    </div>

</div>

    <button type="submit" class="boton-accion guardar-edicion-btn">Guardar</button>
    <button type="button" class="boton-accion cancelar-edicion-btn" data-id="<?php echo htmlspecialchars($paso['id_paso']); ?>">Cancelar</button>
</form>
                </section>
            </li>
        <?php endforeach; ?>
    </ol>
    <input type="hidden" id="pasos_ordenados_input" name="pasos_ordenados">
    <button type="button" id="guardarOrdenBtn" style="display:none;">Guardar Nuevo Orden</button>

<?php endif; ?>
</section>

</section>


<script>
    $(document).ready(function() {
        // --- Lógica para mostrar/ocultar el formulario de edición ---
        $(document).on('click', '.boton-editar', function() {
            var pasoId = $(this).data('id-paso');
            
            $('.paso-edit-form').hide();
            $('.paso-texto').show();
            $('.boton-editar').show();
            
            $('#mostrar_paso_' + pasoId).hide();
            var editForm = $('#edit_form_' + pasoId); // Selecciona el formulario
            editForm.show();
            $(this).hide();
            
            // 1. Mueve el foco a la sección del formulario de edición.
            editForm.focus();

            // 2. Mueve el foco al primer campo del formulario.
            editForm.find('textarea').focus();
        });

        $(document).on('click', '.cancelar-edicion-btn', function() {
            var pasoId = $(this).data('id');
            $('#edit_form_' + pasoId).hide();
            $('#mostrar_paso_' + pasoId).show();
            $('.boton-editar[data-id-paso="' + pasoId + '"]').show();
        });

        // --- Lógica para Arrastrar y Reordenar Pasos (jQuery UI Sortable) ---
        // (Tu código para el reordenamiento sigue aquí, sin cambios)
        $("#pasosList").sortable({
            update: function(event, ui) {
                var pasosOrdenados = $(this).sortable('toArray', {attribute: 'data-id-paso'});
                console.log("Nuevo orden de IDs de pasos:", pasosOrdenados);
                $('#pasos_ordenados_input').val(pasosOrdenados.join(','));
                $('#guardarOrdenBtn').show();
            }
        });

        $("#pasosList").disableSelection();

        $('#guardarOrdenBtn').on('click', function() {
            var idActividad = <?php echo htmlspecialchars($id_actividad); ?>;
            var ordenPasos = $('#pasos_ordenados_input').val();

            if (ordenPasos) {
                $.ajax({
                    url: 'componentes/guardar_orden_pasos.php',
                    type: 'POST',
                    data: {
                        id_actividad: idActividad,
                        orden_pasos: ordenPasos
                    },
                    success: function(response) {
                        window.location.href = `panel_padres.php?
                        seccion=actividades&accion=pasos&id_actividad=${idActividad}&estado=exito_reordenar_pasos`; 
                        /*Agregué esto porque el ajax no dejaba llegar el parametro por url*/ 
                    },
                    error: function() {
                        alert('Error al guardar el nuevo orden de los pasos.');
                    }
                });
            } else {
                alert('No hay pasos para reordenar.');
            }
        });
    });

    // --- Lógica para Múltiples Formularios de Edición (IMAGEN y CÁMARA) ---
document.addEventListener('DOMContentLoaded', function() {
    const editForms = document.querySelectorAll('.paso-edit-form');

    editForms.forEach(form => {
        const pasoId = form.querySelector('input[name="id_paso"]').value;
        
        // Elementos del formulario de edición
        const opcionMantenerImg = document.getElementById('opcion_mantener_img_' + pasoId);
        const opcionSubirImgEdit = document.getElementById('opcion_subir_img_edit_' + pasoId);
        const opcionCamaraEdit = document.getElementById('opcion_camara_edit_' + pasoId);
        
        const contenedorSubirImgEdit = document.getElementById('contenedor_subir_imagen_edit_' + pasoId);
        const contenedorCamaraEdit = document.getElementById('contenedor_camara_edit_' + pasoId);
        const videoCamaraEdit = document.getElementById('video_camara_edit_' + pasoId);
        const botonTomarFotoEdit = contenedorCamaraEdit.querySelector('.boton-tomar-foto-edit');
        const canvasFotoEdit = document.getElementById('canvas_foto_edit_' + pasoId);
        const inputImagenTomadaEdit = document.getElementById('imagen_tomada_paso_edit_' + pasoId);
        const inputImagenPasoEdit = document.getElementById('imagen_paso_edit_' + pasoId);
        const estadoCamaraEdit = document.getElementById('estado_camara_edit_' + pasoId);

        let streamEdit;

        function detenerStreamEdit() {
            if (streamEdit) {
                streamEdit.getTracks().forEach(track => track.stop());
                streamEdit = null;
                // Si la cámara está visible, ocultarla cuando se detiene el stream
                if (contenedorCamaraEdit) {
                    contenedorCamaraEdit.style.display = 'none';
                }
            }
        }
        
        function manejarOpcionesImagenEdit() {
            detenerStreamEdit(); // Siempre detiene el stream al cambiar de opción

            // Limpiar campos y requerimientos de subida/cámara
            if(inputImagenPasoEdit) inputImagenPasoEdit.value = '';
            if(inputImagenTomadaEdit) inputImagenTomadaEdit.value = '';
            if(estadoCamaraEdit) estadoCamaraEdit.textContent = '';
            
            if(contenedorSubirImgEdit) contenedorSubirImgEdit.style.display = 'none';
            if(contenedorCamaraEdit) contenedorCamaraEdit.style.display = 'none';

            if (opcionSubirImgEdit && opcionSubirImgEdit.checked) {
                // Opción Subir archivo
                if(contenedorSubirImgEdit) contenedorSubirImgEdit.style.display = 'block';
                // La validación de que debe subir un archivo si selecciona esta opción se hace en el servidor.
            } else if (opcionCamaraEdit && opcionCamaraEdit.checked) {
                // Opción Usar cámara
                if(contenedorCamaraEdit) contenedorCamaraEdit.style.display = 'block';
                // inputImagenTomadaEdit.required = true; // El servidor ya verifica si el campo Base64 está lleno
                
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(s => {
                        streamEdit = s;
                        videoCamaraEdit.srcObject = streamEdit;
                        videoCamaraEdit.play();
                    })
                    .catch(err => {
                        console.error("Error al acceder a la cámara:", err);
                        if(estadoCamaraEdit) {
                            estadoCamaraEdit.textContent = "No se pudo acceder a la cámara. Por favor, revise los permisos.";
                        }
                    });
            }
        }

        // Agregar listeners a los radios del formulario actual
        if(opcionMantenerImg) opcionMantenerImg.addEventListener('change', manejarOpcionesImagenEdit);
        if(opcionSubirImgEdit) opcionSubirImgEdit.addEventListener('change', manejarOpcionesImagenEdit);
        if(opcionCamaraEdit) opcionCamaraEdit.addEventListener('change', manejarOpcionesImagenEdit);
        
        // Lógica para tomar la foto en el formulario de edición
        if(botonTomarFotoEdit) {
            botonTomarFotoEdit.onclick = () => {
                const context = canvasFotoEdit.getContext('2d');
                canvasFotoEdit.width = videoCamaraEdit.videoWidth;
                canvasFotoEdit.height = videoCamaraEdit.videoHeight;
                context.drawImage(videoCamaraEdit, 0, 0, canvasFotoEdit.width, canvasFotoEdit.height);
                const dataUrl = canvasFotoEdit.toDataURL('image/jpeg', 0.8);
                inputImagenTomadaEdit.value = dataUrl;
                if(estadoCamaraEdit) estadoCamaraEdit.textContent = '¡Foto tomada! Lista para guardar.';
                
                // Aseguramos que el campo de subida de archivo esté vacío, ya que se usó la cámara.
                if(inputImagenPasoEdit) inputImagenPasoEdit.value = ''; 
            };
        }

        // Asegurar que el stream de la cámara se detenga al cancelar la edición
        const cancelarBtn = form.closest('.paso-edit-form').querySelector('.cancelar-edicion-btn');
        if(cancelarBtn) {
            cancelarBtn.addEventListener('click', () => {
                detenerStreamEdit();
                // Opcional: reiniciar la opción a "Mantener actual" al cancelar, aunque el formulario se oculta
                if(opcionMantenerImg) opcionMantenerImg.checked = true;
                if(contenedorSubirImgEdit) contenedorSubirImgEdit.style.display = 'none';
                if(contenedorCamaraEdit) contenedorCamaraEdit.style.display = 'none';
            });
        }
    });

    // Sobreescribe/Actualiza la lógica del botón Editar (si la tienes) para detener otras cámaras
    $(document).on('click', '.boton-editar', function() {
        // 1. Detener todas las cámaras abiertas de *otros* formularios
        document.querySelectorAll('.contenedor_camara_edit video').forEach(video => {
            if (video.srcObject) {
                // Detiene el stream de video
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
                // Oculta el contenedor si estaba visible y no es el formulario actual
                const contenedor = video.closest('.contenedor_camara_edit');
                if (contenedor) {
                    contenedor.style.display = 'none';
                    // Restablece el radio button a 'mantener'
                    const form = video.closest('.paso-edit-form');
                    if (form) {
                        const pasoId = form.querySelector('input[name="id_paso"]').value;
                        const opcionMantenerImg = document.getElementById('opcion_mantener_img_' + pasoId);
                        if(opcionMantenerImg) opcionMantenerImg.checked = true;
                        
                        const inputImagenTomadaEdit = document.getElementById('imagen_tomada_paso_edit_' + pasoId);
                        if(inputImagenTomadaEdit) inputImagenTomadaEdit.value = ''; // Limpia el valor base64
                        const inputImagenPasoEdit = document.getElementById('imagen_paso_edit_' + pasoId);
                        if(inputImagenPasoEdit) inputImagenPasoEdit.value = ''; // Limpia el campo de subida
                    }
                }
            }
        });

        // 2. Lógica existente para mostrar/ocultar formularios
        var pasoId = $(this).data('id-paso');
        $('.paso-edit-form').hide();
        $('.paso-texto').show();
        $('.boton-editar').show();
        
        $('#mostrar_paso_' + pasoId).hide();
        var editForm = $('#edit_form_' + pasoId);
        editForm.show();
        $(this).hide();
        
        // 3. Mueve el foco
        editForm.focus();
        editForm.find('textarea').focus();
    });
});

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mensajeDiv = document.getElementById('notificacion_pasos');

        if (mensajeDiv && mensajeDiv.innerText.trim() !== '') {
            setTimeout(() => {
                mensajeDiv.focus();
            }, 100); // 100 milisegundos de retraso para asegurar el foco
        }
    });
</script>