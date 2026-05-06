<section id="caja_ver_actividades" class="seccion-panel" aria-labelledby="mis-actividades">
    <h2 id="mis-actividades">Mis actividades</h2>

    <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1"><!-- -- tabindex="-1" permite que javascript mueva el foco aqui -->
        <?php if (!empty($mensaje_general)) { ?>
            <p class="mensaje_form <?php echo htmlspecialchars($clase_general); ?>">
                <?php echo htmlspecialchars($mensaje_general); ?>
            </p>
        <?php } ?>
    </div>

    <?php
    $id_padre_sesion = $_SESSION['id_padre'];
    $consulta = mysqli_query($conexion, "SELECT * FROM actividades WHERE id_padre='$id_padre_sesion'");

    if (mysqli_num_rows($consulta) > 0) {
    ?>
    <ul class="contenedor_actividades_flex">
    <?php
    while ($listar_act = mysqli_fetch_assoc($consulta)) {
    ?>
        <li class="caja_actividad_para_repetir">
            <section aria-labelledby="titulo-actividad-<?php echo htmlspecialchars($listar_act['id_actividad']); ?>">
                <h3 class="nom_actividad" id="titulo-actividad-<?php echo htmlspecialchars($listar_act['id_actividad']); ?>">
                    Nombre de actividad: <?php echo htmlspecialchars($listar_act['nombre_actividad']); ?>
                </h3>
                <div class="caja_foto_actividad">
                    <img class="img_act" src="<?php echo htmlspecialchars($listar_act['foto_actividad']); ?>" alt="<?php echo htmlspecialchars($listar_act['texto_alternativo']); ?>">
                </div>
                <p class="descripcion">Descripción: <?php echo htmlspecialchars($listar_act['descripcion']); ?></p>
                <?php
                $id_act = $listar_act['id_actividad'];
                $consulta_paso = mysqli_query($conexion, "SELECT * FROM pasos WHERE id_actividad='$id_act'");
                $cant_pasos =  0;
                if ($consulta_paso) {
                    $cant_pasos = mysqli_num_rows($consulta_paso);
                }
                ?>
                <p>Cantidad de pasos: <?php echo $cant_pasos; ?></p>
                <ul class="lista-acciones-actividad">
                    <li>
                    <p><a href="panel_padres.php?seccion=actividades&accion=editar&id=<?php echo htmlspecialchars($listar_act['id_actividad']); ?>" aria-label="Editar actividad: <?php echo htmlspecialchars($listar_act['nombre_actividad']); ?>">Editar actividad</a></p>
                    </li>
                    <li>
                    <p><a href="panel_padres.php?seccion=actividades&accion=pasos&id_actividad=<?php echo htmlspecialchars($listar_act['id_actividad']); ?>" aria-label="Gestionar pasos de la actividad: <?php echo htmlspecialchars($listar_act['nombre_actividad']); ?>">Gestionar pasos</a></p>
                </li>
                <li>
                    <p><a href="componentes/eliminar_actividad.php?id=<?php echo htmlspecialchars($listar_act['id_actividad']); ?>" 
                    aria-label="Eliminar actividad: <?php echo htmlspecialchars($listar_act['nombre_actividad']); ?>" role="button">Eliminar actividad</a></p>
                </li>
                </ul>
            </section>
        </li>
    <?php } ?>
    </ul>

    <?php
    } else {
    ?>
    <p class="mensaje_resaltado">Aún no tiene actividades creadas. Use el formulario de 'Crear actividad' para crear una nueva actividad.</p>
    <?php
    }
    ?>
</section>

<section id="contenedor_form_crear_act" class="seccion-panel" aria-labelledby="crear-actividad-nueva">
    <h2 id="crear-actividad-nueva">Crear actividad nueva</h2>

    <form action="componentes/crear_actividad.php" method="POST" enctype="multipart/form-data">
        <label for="nombre_actividad" class="form_label">Nombre de actividad:</label>
        <input id="nombre_actividad" type="text" name="nombre_actividad" placeholder="Ingrese nombre de actividad" class="form-control" required>

        <label for="descripcion_actividad" class="form_label">Descripción de la actividad:</label>
        <textarea id="descripcion_actividad" type="text" name="descripcion_actividad" rows="3" placeholder="Describe brevemente la actividad." class="form-control" required></textarea>

        <fieldset class="opciones-imagen">
            <legend class="form-label-inline">Opciones de Imagen:</legend>
            <input type="radio" id="opcion_subir_img_actividad" name="imagen_opcion_actividad" value="subir" checked>
            <label for="opcion_subir_img_actividad">Subir archivo</label>

            <input type="radio" id="opcion_camara_actividad" name="imagen_opcion_actividad" value="camara">
            <label for="opcion_camara_actividad">Usar cámara</label>
        </fieldset>

        <div id="contenedor_subir_imagen_actividad" style="margin-top: 10px;">
            <label for="imagen_actividad" class="form_label">Seleccionar imagen:</label>
            <p id="instrucciones_imagen_actividad" class="form-text">Tamaño máximo 1MB. Formatos permitidos: jpeg, png, gif, webp.</p>
            <input id="imagen_actividad" type="file" name="imagen_actividad" accept="image/*" class="form-control" required aria-describedby="instrucciones_imagen_actividad">
        </div>

        <div id="contenedor_camara_actividad" style="display: none; margin-top: 10px;">
            <p>Previsualización de la cámara:</p>
            <video id="video_camara_actividad" width="320" height="240" autoplay></video>
            <button type="button" id="boton_tomar_foto_actividad" class="boton-accion">Tomar Foto</button>
            <canvas id="canvas_foto_actividad" style="display: none;"></canvas>
            <p id="estado_camara_actividad" class="mensaje-grabacion"></p>
            <input type="hidden" id="imagen_tomada_actividad" name="imagen_tomada_actividad">
        </div>
        <label for="alt_texto_actividad" class="form_label">Texto Alternativo para la Imagen (opcional):</label>
        <input type="text" id="alt_texto_actividad" name="alt_texto_actividad" class="form-control" placeholder="Describe la imagen para accesibilidad">
        <input type="submit" value="Crear actividad">
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificacionActividades = document.getElementById('notificacion_actividades');
        const notificacionForm = document.getElementById('notificacion_aria');

        // Lógica de foco existente
        if (notificacionActividades && notificacionActividades.innerText.trim() !== '') {
            setTimeout(() => {
                notificacionActividades.focus();
            }, 100);
        }

        if (notificacionForm && notificacionForm.innerText.trim() !== '') {
            setTimeout(() => {
                notificacionForm.focus();
            }, 100);
        }

        // =========================================================================
        // ↓↓↓ INICIO DEL NUEVO CÓDIGO JS PARA CREAR ACTIVIDAD ↓↓↓
        // =========================================================================

        const opcionSubirImgAct = document.getElementById('opcion_subir_img_actividad');
        const opcionCamaraAct = document.getElementById('opcion_camara_actividad');
        const contenedorSubirImgAct = document.getElementById('contenedor_subir_imagen_actividad');
        const contenedorCamaraAct = document.getElementById('contenedor_camara_actividad');
        const videoCamaraAct = document.getElementById('video_camara_actividad');
        const botonTomarFotoAct = document.getElementById('boton_tomar_foto_actividad');
        const canvasFotoAct = document.getElementById('canvas_foto_actividad');
        const inputImagenTomadaAct = document.getElementById('imagen_tomada_actividad');
        const inputImagenAct = document.getElementById('imagen_actividad');
        const estadoCamaraAct = document.getElementById('estado_camara_actividad');

        let streamAct; // Stream global para la cámara de actividad

        function manejarOpcionesImagenActividad() {
            // Detener el stream anterior si existe
            if (streamAct) {
                streamAct.getTracks().forEach(track => track.stop());
                streamAct = null;
            }

            if (opcionSubirImgAct.checked) {
                // Opción: Subir archivo
                contenedorSubirImgAct.style.display = 'block';
                contenedorCamaraAct.style.display = 'none';
                inputImagenAct.required = true; // El campo de subida es requerido
                inputImagenTomadaAct.value = ''; // Limpia el valor de la cámara
                inputImagenTomadaAct.removeAttribute('required'); 
                estadoCamaraAct.textContent = ''; // Limpia mensajes de estado
            } else if (opcionCamaraAct.checked) {
                // Opción: Usar cámara
                contenedorSubirImgAct.style.display = 'none';
                contenedorCamaraAct.style.display = 'block';
                inputImagenAct.removeAttribute('required'); // El campo de subida ya no es requerido
                inputImagenTomadaAct.required = true; // El campo de la cámara es requerido (asumiendo que debe tomar una foto)
                
                // Inicializar la cámara
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(s => {
                        streamAct = s;
                        videoCamaraAct.srcObject = streamAct;
                        videoCamaraAct.play();
                        estadoCamaraAct.textContent = 'Cámara lista. Presiona "Tomar Foto".';
                    })
                    .catch(err => {
                        console.error("Error al acceder a la cámara:", err);
                        estadoCamaraAct.textContent = "Error: No se pudo acceder a la cámara. Verifique los permisos.";
                        alert("No se pudo acceder a la cámara para la actividad. Por favor, asegúrese de haber dado los permisos.");
                        // Vuelve a seleccionar 'Subir archivo' si falla la cámara
                        opcionSubirImgAct.checked = true;
                        manejarOpcionesImagenActividad();
                    });
            }
        }

        // Escuchadores de eventos para los radio buttons
        if (opcionSubirImgAct && opcionCamaraAct) {
            opcionSubirImgAct.addEventListener('change', manejarOpcionesImagenActividad);
            opcionCamaraAct.addEventListener('change', manejarOpcionesImagenActividad);
        }
        
        // Llama a la función al inicio para establecer el estado inicial (Subir archivo es el checked por defecto)
        if (opcionSubirImgAct) {
            manejarOpcionesImagenActividad(); 
        }
        
        // --- Lógica para tomar la foto ---
        if (botonTomarFotoAct) {
            botonTomarFotoAct.onclick = () => {
                const context = canvasFotoAct.getContext('2d');
                // Aseguramos que el canvas tenga el tamaño exacto del video para evitar distorsiones
                canvasFotoAct.width = videoCamaraAct.videoWidth;
                canvasFotoAct.height = videoCamaraAct.videoHeight;
                context.drawImage(videoCamaraAct, 0, 0, canvasFotoAct.width, canvasFotoAct.height);
                
                // Convertir la imagen del canvas a Base64
                const dataUrl = canvasFotoAct.toDataURL('image/jpeg', 0.8); // 0.8 es la calidad
                inputImagenTomadaAct.value = dataUrl;
                estadoCamaraAct.textContent = '¡Foto tomada! Lista para guardar con la actividad.';
            };
        }
        
        // =========================================================================
        // ↑↑↑ FIN DEL NUEVO CÓDIGO JS PARA CREAR ACTIVIDAD ↑↑↑
        // =========================================================================
    });
</script>


