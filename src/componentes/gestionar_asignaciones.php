<section id="contenedor_asignaciones" class="seccion-panel contenedor_asignaciones" aria-labelledby="estado-de-asignaciones-de-actividades" tabindex="-1">
    <h2 id="estado-de-asignaciones-de-actividades">Estado de Asignaciones de Actividades</h2>

    <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1"><!-- -- tabindex="-1" permite que javascript mueva el foco aqui -->
        <?php if (!empty($mensaje_general)) { ?>
            <p class="mensaje_form <?php echo htmlspecialchars($clase_general); ?>">
                <?php echo htmlspecialchars($mensaje_general); ?>
            </p>
        <?php } ?>
    </div>

    <section class="contenedor-filtros" aria-labelledby="filtrar-asignaciones">
        <form method="GET" action="panel_padres.php" class="filtros-asignaciones">
            <input type="hidden" name="seccion" value="asignaciones">
        <!-- le quito el ancla #contenedor-filtros al action porque haceconflicto con el javascript para hacer el foco luego de filtrar -->
<!-- Estos labels tiene su propio estilo. NO PONERLE class="form-control"   porque estos inputs van uno al lado de otro. -->
        <h3 id="filtrar-asignaciones">Filtrar Asignaciones</h3>

        <div class="filtro-grupo">
            <label for="filtro_nino">Nombre del Niño:</label>
            <input type="text" id="filtro_nino" name="filtro_nino"
                value="<?php echo htmlspecialchars($_GET['filtro_nino'] ?? ''); ?>" 
                placeholder="Ingrese nombre del niño">
        </div>

        <div class="filtro-grupo">
            <label for="filtro_actividad">Actividad:</label>
            <input type="text" id="filtro_actividad" name="filtro_actividad"
                value="<?php echo htmlspecialchars($_GET['filtro_actividad'] ?? ''); ?>" 
                placeholder="Ingrese nombre de actividad">
        </div>

        <div class="filtro-grupo">
            <label for="filtro_estado">Estado:</label>
            <select id="filtro_estado" name="filtro_estado">
                <option value="">Todos</option>
                <option value="pendiente" <?php echo ($_GET['filtro_estado'] ?? '') === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                <option value="completada" <?php echo ($_GET['filtro_estado'] ?? '') === 'completada' ? 'selected' : ''; ?>>Completada</option>
                <option value="en_progreso" <?php echo ($_GET['filtro_estado'] ?? '') === 'en_progreso' ? 'selected' : ''; ?>>En progreso</option>
                <option value="saltada" <?php echo ($_GET['filtro_estado'] ?? '') === 'saltada' ? 'selected' : ''; ?>>Saltada</option>
            </select>
        </div>

        <div class="filtro-grupo">
            <label for="filtro_recurrencia_select">Recurrencia:</label>
            <select id="filtro_recurrencia_select" name="filtro_recurrencia">
                <option value="">Todas</option> <option value="1" <?php echo (($filtro_recurrencia ?? '') === '1') ? 'selected' : ''; ?>>Solo Recurrentes</option>
                <option value="0" <?php echo (($filtro_recurrencia ?? '') === '0') ? 'selected' : ''; ?>>Solo No Recurrentes</option>
            </select>
        </div>
<div id="anuncio_dias_filtro" class="oculto" role="status" aria-live="polite"></div> <!-- Para notificar que se ha habilitado los checkboxes para seleccionar dias de recurrencia.  -->
        <div id="contenedor_dias" style="display: <?php echo (($filtro_recurrencia ?? '') === '1') ? 'flex' : 'none'; ?>;">
            
        <fieldset>
            <legend>Filtrar por Días de la Semana:</legend>
            <?php
            $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            $dias_seleccionados = $_GET['filtro_dias'] ?? [];
            foreach ($dias as $dia) {
                $checked = in_array($dia, $dias_seleccionados) ? 'checked' : '';
                echo "<label><input type='checkbox' name='filtro_dias[]' value='$dia' $checked> " . ucfirst($dia) . "</label>";
            }
            ?>
        </fieldset>
        </div>
            <button type="submit">Aplicar Filtros</button>
            <a href="panel_padres.php?seccion=asignaciones" class="boton-limpiar-filtros" role="button">Limpiar filtros</a>
        </form>

    </section> <!-- Cierra la seccion del form de filtro -->

    <?php
    // --- NUEVA LÓGICA PARA ACTUALIZAR ASIGNACIONES A 'SALTADA' AUTOMÁTICAMENTE ---
    $current_datetime = date('Y-m-d H:i:s'); // Obtiene la fecha y hora actual
    $current_date = date('Y-m-d'); // Obtiene solo la fecha actual

    // Esta consulta actualizará cualquier asignación que:
    // 1. Esté en estado 'pendiente'.
    // 2. NO sea recurrente (es_recurrente = 0).
    // 3. Y su 'fecha_asignada' sea anterior a la fecha actual (el día de la asignación ya pasó).
    //tambien se puede hacer que pase a "saltada" teniendo en cuenta la fecha_asignada y hora_asignada 
    $update_query = "
        UPDATE asignaciones_actividades
        SET estado = 'saltada'
        WHERE estado = 'pendiente'
        AND es_recurrente = 0  -- Excluye las tareas recurrentes de esta lógica.
        AND fecha_asignada < '$current_date' -- ¡Modificado para considerar solo la fecha!
    ";

    // Ejecutar la consulta de actualización
    if (!mysqli_query($conexion, $update_query)) {
        error_log("Error al actualizar estados a 'saltada': " . mysqli_error($conexion));
    }
    // --- FIN LÓGICA PARA ACTUALIZAR ASIGNACIONES A 'SALTADA' ---


    // ------------------- Lógica de Filtros  -------------------
    $filtro_nino = $_GET['filtro_nino'] ?? '';
    $filtro_actividad = $_GET['filtro_actividad'] ?? '';
    $filtro_estado = $_GET['filtro_estado'] ?? '';
    $filtro_recurrencia = $_GET['filtro_recurrencia'] ?? '';
    $filtro_dias = $_GET['filtro_dias'] ?? [];

    $where_clauses = ["n.id_padre = '$id_padre_sesion'"];
    $joins = [];

    // Filtro por nombre del niño
    if (!empty($filtro_nino)) {
        $filtro_nino_escaped = mysqli_real_escape_string($conexion, $filtro_nino);
        $where_clauses[] = "n.nombre LIKE '%$filtro_nino_escaped%'";
    }

    // Filtro por nombre de actividad
    if (!empty($filtro_actividad)) {
        $filtro_actividad_escaped = mysqli_real_escape_string($conexion, $filtro_actividad);
        $where_clauses[] = "a.nombre_actividad LIKE '%$filtro_actividad_escaped%'";
    }

    // Filtro por estado
    if (!empty($filtro_estado)) {
        $filtro_estado_escaped = mysqli_real_escape_string($conexion, $filtro_estado);
        $where_clauses[] = "aa.estado = '$filtro_estado_escaped'";
    }

    // Filtro por recurrencia
    if ($filtro_recurrencia === '1') {
        $where_clauses[] = "aa.es_recurrente = 1";

        if (!empty($filtro_dias)) {
            // JOIN necesario para aplicar el filtro de días
            $joins[] = "LEFT JOIN asignacion_recurrencia_dias ard ON aa.id_asignacion = ard.id_asignacion";

            $dias_escapados = array_map(function($dia) use ($conexion) {
                return "'" . mysqli_real_escape_string($conexion, $dia) . "'";
            }, $filtro_dias);

            $dias_str = implode(",", $dias_escapados);
            $where_clauses[] = "ard.dia_semana IN ($dias_str)";
        }
    } elseif ($filtro_recurrencia === '0') {
        $where_clauses[] = "aa.es_recurrente = 0";
    }

    // Armar la cláusula final
    $full_where_clause = "WHERE " . implode(" AND ", $where_clauses);
    $full_joins = implode(" ", $joins);

    // ------------------- Fin Lógica de Filtros -------------------

    // Consulta final con los JOINs incluidos
    $consulta_asignaciones = mysqli_query($conexion, "
        SELECT
            aa.id_asignacion,
            n.nombre AS nombre_nino,
            a.nombre_actividad,
            aa.fecha_creacion,
            aa.fecha_asignada,
            aa.hora_asignada,
            aa.estado,
            aa.fecha_completado,
            aa.es_recurrente
        FROM
            asignaciones_actividades aa
        JOIN ninos n ON aa.id_nino = n.id_nino
        JOIN actividades a ON aa.id_actividad = a.id_actividad
        $full_joins
        $full_where_clause
    ");

    $full_joins = implode(" ", $joins);  // Asegurate de que esto exista
    // Resto del código PHP para procesar resultados y mostrar días recurrentes 
    // 1. Almacenar todos los datos de las asignaciones y recolectar los IDs recurrentes
    $all_asignaciones_data = [];
    $recurrente_ids = [];
    if ($consulta_asignaciones) {
        while ($asignacion_row = mysqli_fetch_assoc($consulta_asignaciones)) {
            $all_asignaciones_data[] = $asignacion_row;
            if (isset($asignacion_row['es_recurrente']) && $asignacion_row['es_recurrente']) {
                $recurrente_ids[] = $asignacion_row['id_asignacion'];
            }
        }
    } else {
        error_log("Error al consultar asignaciones con filtros: " . mysqli_error($conexion));
    }


    // 2. Si hay asignaciones recurrentes, obtener sus días de la tabla asignacion_recurrencia_dias
    $dias_recurrencia_por_asignacion = [];
    if (!empty($recurrente_ids)) {
        $ids_string = implode(',', array_map('intval', $recurrente_ids));

        $consulta_dias_recurrencia = mysqli_query($conexion, "
            SELECT id_asignacion, dia_semana
            FROM asignacion_recurrencia_dias
            WHERE id_asignacion IN ($ids_string)
        ");

        if ($consulta_dias_recurrencia) {
            while ($dia_row = mysqli_fetch_assoc($consulta_dias_recurrencia)) {
                $dias_recurrencia_por_asignacion[$dia_row['id_asignacion']][] = htmlspecialchars($dia_row['dia_semana']);
            }
        } else {
            error_log("Error al consultar días de recurrencia con filtros: " . mysqli_error($conexion));
        }
    }

    // 3. Imprimir las asignaciones usando los datos pre-cargados
    if (!empty($all_asignaciones_data)) {
        // Añade aria-live="polite" directamente al <ul>
        echo "<ul aria-live='polite'>";
        foreach ($all_asignaciones_data as $asignacion) {
            echo "<li>";
            echo "<strong>Niño:</strong> " . htmlspecialchars($asignacion['nombre_nino']) . " - ";
            echo "<strong>Actividad:</strong> " . htmlspecialchars($asignacion['nombre_actividad']) . " - ";
            echo "<strong>Asignada el:</strong> " . date('d/m/Y H:i', strtotime($asignacion['fecha_creacion'])) . " - ";

            echo "<strong>¿Es recurrente?:</strong> " . (isset($asignacion['es_recurrente']) && $asignacion['es_recurrente'] ? "Sí" : "No");

            if (isset($asignacion['es_recurrente']) && $asignacion['es_recurrente'] && isset($dias_recurrencia_por_asignacion[$asignacion['id_asignacion']])) {
                echo " (Días: " . implode(', ', $dias_recurrencia_por_asignacion[$asignacion['id_asignacion']]) . ")";
            }
            echo " - ";

            // Condición para mostrar fecha/hora o solo el texto "Hora que debe asignarse"
            if (isset($asignacion['es_recurrente']) && $asignacion['es_recurrente']) {
                echo "<strong>Hora en que debe realizarse: </strong>" . $asignacion['hora_asignada'];
            } else {
                echo "<strong>Fecha y hora en que debe realizarse: </strong>" . htmlspecialchars($asignacion['fecha_asignada']) ." ". htmlspecialchars($asignacion['hora_asignada']);
            }
            echo " - ";
            
            echo "<strong>Estado:</strong> <span class='estado-" . htmlspecialchars($asignacion['estado']) . "'>";
            if ($asignacion['estado'] === 'en_progreso') {
                echo "en progreso";
            } else {
                echo ucfirst(htmlspecialchars($asignacion['estado']));
            }
            echo "</span>";

            if ($asignacion['estado'] === 'completada' && isset($asignacion['fecha_completado']) && $asignacion['fecha_completado']) {
                // Verifica si la asignación es recurrente
                if (isset($asignacion['es_recurrente']) && $asignacion['es_recurrente']) {
                    // Mensaje para asignaciones recurrentes completadas
                    echo " (Última vez completada el: " . date('d/m/Y H:i', strtotime($asignacion['fecha_completado'])) . ")";
                } else {
                    // Mensaje para asignaciones NO recurrentes completadas
                    echo " (Completada el: " . date('d/m/Y H:i', strtotime($asignacion['fecha_completado'])) . ")";
                }
            }
            ?>
            <p><a href="componentes/eliminar_asignacion.php?id=<?php echo htmlspecialchars($asignacion['id_asignacion']); ?>" role="button" aria-label="Eliminar asignación de <?php echo htmlspecialchars($asignacion['nombre_nino']); ?> para la actividad: <?php echo htmlspecialchars($asignacion['nombre_actividad']); ?>">Eliminar asignacion</a></p>
            <?php
            echo "</li>";
        }
        echo "</ul>";
    } else {
        // También es buena práctica añadirlo al mensaje de "no resultados"
        echo "<p class='mensaje_resaltado' aria-live='polite'>No hay actividades asignadas por el momento.</p>";
    }
    ?>
</section>



<section id="caja_formulario_asignar_actividad" class="seccion-panel" aria-labelledby="asignar-actividad-a-un-niño/a">
    <h3 id="asignar-actividad-a-un-niño/a">Asignar Actividad a un Niño/a</h3>

    <form id="asignarActividadForm" action="componentes/procesar_asignacion.php" method="POST">
        <label for="nino_id" class="form_label">Seleccionar Niño/a:</label>
        <select name="nino_id" id="nino_id" required>
            <?php
            $consulta_ninos = mysqli_query($conexion, "SELECT id_nino, nombre FROM ninos WHERE id_padre = '$id_padre_sesion'");

            if ($consulta_ninos && mysqli_num_rows($consulta_ninos) > 0) {
                while ($nino = mysqli_fetch_assoc($consulta_ninos)) {
                    echo "<option value='{$nino['id_nino']}'>{$nino['nombre']}</option>";
                }
            } else {
                echo "<option value='' disabled selected>No hay niños creados</option>";
            }
            ?>
        </select>

        <label for="actividad_id" class="form_label">Seleccionar Actividad:</label>
        <select name="actividad_id" id="actividad_id" required>
            <?php
            $consulta_actividades = mysqli_query($conexion, "
                SELECT
                    a.id_actividad,
                    a.nombre_actividad
                FROM
                    actividades a
                WHERE
                    a.id_padre = '$id_padre_sesion'
                AND
                    EXISTS (SELECT 1 FROM pasos p WHERE p.id_actividad = a.id_actividad)");

            if ($consulta_actividades && mysqli_num_rows($consulta_actividades) > 0) {
                while ($actividad = mysqli_fetch_assoc($consulta_actividades)) {
                    echo "<option value='{$actividad['id_actividad']}'>" . htmlspecialchars($actividad['nombre_actividad']) . "</option>";
                }
            } else {
                echo "<option value='' disabled selected>No hay actividades disponibles</option>";
            }
            ?>
        </select>

        
        <div class="form-group">
            <label for="es_recurrente" class="form-label-inline">¿Es una actividad recurrente (semanal)?</label>
            <input type="checkbox" name="es_recurrente" id="es_recurrente" value="1">
        </div>
        
        

        <div id="campos_recurrencia" style="display: none;">
            <fieldset> <!-- es para accesibilidad: encierra todos los checkboxes -->
                <legend>Días de la Semana para Repetición:</legend>
                
                <input type="checkbox" name="dias_semana[]" id="dia_lunes" value="lunes"> <label for="dia_lunes">Lunes</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_martes" value="martes"> <label for="dia_martes">Martes</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_miercoles" value="miercoles"> <label for="dia_miercoles">Miércoles</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_jueves" value="jueves"> <label for="dia_jueves">Jueves</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_viernes" value="viernes"> <label for="dia_viernes">Viernes</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_sabado" value="sabado"> <label for="dia_sabado">Sábado</label><br>
                <input type="checkbox" name="dias_semana[]" id="dia_domingo" value="domingo"> <label for="dia_domingo">Domingo</label><br>
                <span id="dias_semana_error" style="color: red; display: none;">Debe seleccionar al menos un día.</span>
            </fieldset>
                
        </div>

        <div id="campos_unica_o_inicio">
            <div id="fecha_asignacion_wrapper"> 
                <label for="fecha_asignada" class="form_label">Fecha de Asignación:</label>
                <?php $hoy = date('Y-m-d'); ?>
                <input type="date" name="fecha_asignada" id="fecha_asignada" class="form-control" required min="<?php echo $hoy; ?>">
            </div>

            <label for="hora_asignada" class="form_label">Hora de Asignación (Campo obligatorio):</label>
            <input type="time" name="hora_asignada" id="hora_asignada" required>
        </div>

        <button type="submit">Asignar Actividad</button>
    </form>
</section>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- LÓGICA DE FOCO (CORREGIDA Y UNIFICADA) ---
        const urlParams = new URLSearchParams(window.location.search);
        const hayMensajeDeEstado = urlParams.has('estado') || urlParams.has('mensaje');
        const notificacionDiv = document.getElementById('notificacion_aria');

        // Mover el foco a la notificación si existe un mensaje
        if (hayMensajeDeEstado && notificacionDiv) {
            setTimeout(() => {
                notificacionDiv.focus();
            }, 300);
        }

        const contenedorResultados = document.getElementById('contenedor_asignaciones');
        const hasFilters = Array.from(urlParams.keys()).some(key => key !== 'seccion');

        // Mover el foco a los resultados después de aplicar filtros, pero solo si no hay un mensaje de estado
        if (urlParams.get('seccion') === 'asignaciones' && contenedorResultados && hasFilters && !hayMensajeDeEstado) {
            setTimeout(() => {
                contenedorResultados.focus();
            }, 1000); // <-- Aumentamos el tiempo de espera aquí para mayor fiabilidad
        }
        // --- FIN LÓGICA DE FOCO ---
        
        // ----------------------------------------------------
        // --- TU CÓDIGO EXISTENTE PARA LOS FILTROS Y FORMULARIO DE ASIGNACIÓN VA AQUÍ ---
        
        const filtroRecurrenciaSelect = document.getElementById('filtro_recurrencia_select');
        const contenedorDias = document.getElementById('contenedor_dias');
        const anuncioDias = document.getElementById('anuncio_dias_filtro');

        function toggleDiasSemanaFiltro() {
            if (filtroRecurrenciaSelect.value === '1') {
                contenedorDias.style.display = 'flex';
                if (anuncioDias) {
                    anuncioDias.textContent = "Se ha habilitado la selección de días de la semana.";
                }
            } else {
                contenedorDias.style.display = 'none';
                if (anuncioDias) {
                    anuncioDias.textContent = "";
                }
                contenedorDias.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
        }
        toggleDiasSemanaFiltro();
        filtroRecurrenciaSelect.addEventListener('change', toggleDiasSemanaFiltro);
        
        const asignarActividadForm = document.getElementById('asignarActividadForm');
        if (asignarActividadForm) {
            const esRecurrenteCheckbox = document.getElementById('es_recurrente');
            const camposRecurrenciaDiv = document.getElementById('campos_recurrencia');
            const checkboxesDias = document.querySelectorAll('#campos_recurrencia input[type="checkbox"]');
            const diasSemanaErrorSpan = document.getElementById('dias_semana_error');
            const fechaAsignadaInput = document.getElementById('fecha_asignada');
            const horaAsignadaInput = document.getElementById('hora_asignada');
            const fechaAsignacionWrapper = document.getElementById('fecha_asignacion_wrapper');

            function toggleCampos() {
                if (esRecurrenteCheckbox.checked) {
                    camposRecurrenciaDiv.style.display = 'block';
                    diasSemanaErrorSpan.style.display = 'none';
                    checkboxesDias.forEach(checkbox => {
                        checkbox.disabled = false;
                    });
                    if (fechaAsignacionWrapper) {
                        fechaAsignacionWrapper.style.display = 'none';
                    }
                    fechaAsignadaInput.required = false;
                } else {
                    camposRecurrenciaDiv.style.display = 'none';
                    diasSemanaErrorSpan.style.display = 'none';
                    checkboxesDias.forEach(checkbox => {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                    });
                    if (fechaAsignacionWrapper) {
                        fechaAsignacionWrapper.style.display = 'block';
                    }
                    fechaAsignadaInput.required = true;
                    horaAsignadaInput.required = true;
                }
            }

            asignarActividadForm.addEventListener('submit', function(event) {
                if (esRecurrenteCheckbox.checked) {
                    let alMenosUnDiaSeleccionado = false;
                    checkboxesDias.forEach(checkbox => {
                        if (checkbox.checked) {
                            alMenosUnDiaSeleccionado = true;
                        }
                    });
                    if (!alMenosUnDiaSeleccionado) {
                        diasSemanaErrorSpan.style.display = 'block';
                        event.preventDefault();
                        alert('Por favor, selecciona al menos un día de la semana para la actividad recurrente.');
                    } else {
                        diasSemanaErrorSpan.style.display = 'none';
                    }
                }
            });
            toggleCampos();
            esRecurrenteCheckbox.addEventListener('change', toggleCampos);
        }
    });
</script>