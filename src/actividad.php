<?php 
session_start();
require_once 'componentes/conexion.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Función para obtener el nombre del día en español (puede que ya la tengas en un include)
if (!function_exists('getDayNameSpanish')) {
    function getDayNameSpanish($englishDay) {
        $days = [
            'Monday'    => 'lunes',
            'Tuesday'   => 'martes',
            'Wednesday' => 'miercoles',
            'Thursday'  => 'jueves',
            'Friday'    => 'viernes',
            'Saturday'  => 'sabado',
            'Sunday'    => 'domingo',
        ];
        return $days[$englishDay] ?? '';
    }
}


// ------------------- LÓGICA PRINCIPAL PARA INICIAR/CONTINUAR ASIGNACIÓN -------------------
$id_asignacion = null;
$id_nino_sesion = null;
$current_assignment = null; // Inicializar para evitar errores si no se encuentra la asignación

if (isset($_GET['id_asignacion']) && isset($_SESSION['nino_id'])) {
    $id_asignacion = mysqli_real_escape_string($conexion, $_GET['id_asignacion']);
    $id_nino_sesion = mysqli_real_escape_string($conexion, $_SESSION['nino_id']);

    // Primero, verifica el estado actual, si es recurrente y la fecha de completado
    $check_status_query = "SELECT id_actividad, estado, es_recurrente, fecha_completado FROM asignaciones_actividades WHERE id_asignacion = '$id_asignacion' AND id_nino = '$id_nino_sesion'";
    $result_status = mysqli_query($conexion, $check_status_query);
    $current_assignment = mysqli_fetch_assoc($result_status);

    $can_start_or_continue = false; // Bandera para determinar si se puede iniciar/continuar

    if ($current_assignment) {
        // Si está pendiente o en progreso, siempre se puede iniciar/continuar
        if ($current_assignment['estado'] === 'pendiente' || $current_assignment['estado'] === 'en_progreso') {
            $can_start_or_continue = true;
        }
        // Si está completada y es recurrente, verificar si se puede reiniciar
        elseif ($current_assignment['estado'] === 'completada' && $current_assignment['es_recurrente'] == 1 && !empty($current_assignment['fecha_completado'])) {
            
            // Determinar cuántos días a la semana está configurada esta recurrencia
            $num_recurrent_days = 0;
            $count_days_query = "SELECT COUNT(*) AS num_days FROM asignacion_recurrencia_dias WHERE id_asignacion = '$id_asignacion'";
            $result_count = mysqli_query($conexion, $count_days_query);
            if ($result_count && $row_count = mysqli_fetch_assoc($result_count)) {
                $num_recurrent_days = (int)$row_count['num_days'];
            }

            if ($num_recurrent_days == 7) {
                // Lógica para asignaciones recurrentes DIARIAS (7 días a la semana)
                // Se puede reiniciar si la fecha de completado es anterior al día de hoy
                $fecha_completado_date_only = date('Y-m-d', strtotime($current_assignment['fecha_completado']));
                $hoy_date_only = date('Y-m-d');

                if ($fecha_completado_date_only < $hoy_date_only) {
                    $can_start_or_continue = true;
                }
            } else {
                // Lógica para asignaciones recurrentes SEMANALES (menos de 7 días a la semana)
                // Se puede reiniciar si hoy es uno de los días asignados Y se completó en un día anterior a hoy
                
                $today_day_name_english = date('l'); // Ejemplo: 'Monday'
                $today_day_name_spanish = getDayNameSpanish($today_day_name_english); // 'lunes'
                
                // Verificar si HOY es uno de los días configurados para esta asignación recurrente
                $check_today_assigned_query = "SELECT COUNT(*) FROM asignacion_recurrencia_dias WHERE id_asignacion = '$id_asignacion' AND dia_semana = '$today_day_name_spanish'";
                $result_check_today = mysqli_query($conexion, $check_today_assigned_query);
                $is_today_assigned = false;
                if ($result_check_today && mysqli_fetch_row($result_check_today)[0] > 0) {
                    $is_today_assigned = true;
                }

                if ($is_today_assigned) {
                    // Si hoy es un día asignado para esta recurrencia
                    $fecha_completado_date_only = date('Y-m-d', strtotime($current_assignment['fecha_completado']));
                    $hoy_date_only = date('Y-m-d');
                    
                    // Se puede iniciar si se completó en un día anterior a hoy
                    if ($fecha_completado_date_only < $hoy_date_only) {
                        $can_start_or_continue = true;
                    }
                }
                // Si hoy NO es un día asignado, $can_start_or_continue permanece false.
                // Si se completó HOY, $can_start_or_continue también permanece false (no se puede completar dos veces el mismo día).
            }
        }
    }

    // Si se puede iniciar (o reiniciar/continuar), actualiza el estado a 'en_progreso' en la base de datos
    if ($can_start_or_continue) {
        $update_query = "
            UPDATE asignaciones_actividades
            SET estado = 'en_progreso'
            WHERE id_asignacion = '$id_asignacion' AND id_nino = '$id_nino_sesion'
        ";
        if (mysqli_query($conexion, $update_query)) {
            // El estado se actualizó con éxito. Ahora recupera los detalles de la actividad para mostrarla.
            $id_actividad = $current_assignment['id_actividad']; // Usamos el id_actividad que obtuvimos antes
            // ... (el resto de tu código para obtener los datos de la actividad y sus pasos) ...
            // Este es el punto donde debería continuar el resto de tu archivo actividad.php
            // que obtiene los detalles de la actividad y sus pasos.

        } else {
            // Manejar error de actualización
            error_log("Error al actualizar estado a 'en_progreso' en actividad.php: " . mysqli_error($conexion));
            header('Location: panel_nino.php?error=no_actualizado_actividad');
            exit;
        }
    } else {
        // Si no se cumple la condición para iniciar/continuar la actividad, redirige al panel
        header('Location: panel_nino.php?error=actividad_no_disponible');
        exit;
    }
} else {
    // Si no hay id_asignacion en GET o nino_id en sesión, redirige al panel de niño
    header('Location: panel_nino.php');
    exit;
}


// Obtener el ID de la actividad de la URL
$id_actividad_url = $_GET['id'] ?? '';
// Obtener el ID de la ASIGNACIÓN de la URL (¡NUEVO!)
$id_asignacion_url = $_GET['id_asignacion'] ?? '';

$id_actividad = intval($id_actividad_url);
$id_asignacion = intval($id_asignacion_url); // Convertir a entero

$nombre_del_nino=$_SESSION['nino_nombre']; //guardo el nombre del niño de la session
// Convertir el ID de la URL a un entero para seguridad y uso en la consulta
// Si no es un número válido, intval() lo convertirá a 0.
$id_actividad = intval($id_actividad_url);

// Variables para almacenar los datos
$actividad = null;
$pasos = [];
$total_pasos = 0;
$primer_paso_datos = null;


// Lógica para consultar la base de datos, obtener la actividad y sus pasos
if ($id_actividad > 0) { // Asegurarse de que el ID es un número válido y positivo
    $consulta_actividades=mysqli_query($conexion,"SELECT * FROM actividades WHERE id_actividad=$id_actividad"); //este id me llegó por url
    
    if ($consulta_actividades && mysqli_num_rows($consulta_actividades) == 1) { //si devuelve una fila
        $actividad = mysqli_fetch_assoc($consulta_actividades);
        

        // Consulta para obtener TODOS los pasos de esa actividad
        $sql_pasos = "SELECT id_paso, numero_paso, texto_paso, imagen_paso, audio_paso FROM pasos WHERE id_actividad = " . $id_actividad . " ORDER BY numero_paso ASC";
        $consulta_pasos = mysqli_query($conexion, $sql_pasos);

        if ($consulta_pasos && mysqli_num_rows($consulta_pasos) > 0) { //tiene 1 fila o mas
            // Recorrer todos los resultados y almacenarlos en el array $pasos
            while ($fila = mysqli_fetch_assoc($consulta_pasos)) {
                // --- INICIO DE LA MODIFICACIÓN ---
                // Añadir el prefijo de la carpeta a las rutas de imagen y audio si existen
                if (!empty($fila['imagen_paso'])) {
                    // Asegurarse de que no se añada el prefijo si ya existe
                    if (strpos($fila['imagen_paso'], 'imagenes/pasos/') === false && strpos($fila['imagen_paso'], 'http') === false) {
                        $fila['imagen_paso'] = 'imagenes/pasos/' . $fila['imagen_paso'];
                    }
                }
                if (!empty($fila['audio_paso'])) {
                    // Asegurarse de que no se añada el prefijo si ya existe
                    if (strpos($fila['audio_paso'], 'audios/pasos/') === false && strpos($fila['audio_paso'], 'http') === false) {
                        $fila['audio_paso'] = 'audios/pasos/' . $fila['audio_paso'];
                    }
                }
                // --- FIN DE LA MODIFICACIÓN ---
                $pasos[] = $fila;
            }
            $total_pasos = count($pasos);
            $primer_paso_datos = $pasos[0]; // El primer paso es el elemento 0 del array
        } else {
            // No se encontraron pasos para esta actividad
            echo "<h1>No se encontraron pasos para la actividad '";
            echo htmlspecialchars($actividad['nombre_actividad']); // Muestra el nombre de la actividad
            echo "'.</h1>";
            // Puedes limpiar las variables para evitar que se muestre contenido incompleto
            $actividad = null;
            $pasos = [];
        }
    } else {
        // No se encontró la actividad con ese ID
        echo "<h1>Actividad no encontrada.</h1>";
        // Puedes limpiar las variables para evitar errores en el HTML
        $actividad = null;
        $pasos = [];
    }
} else {
    // No se especificó un ID de actividad válido en la URL
    echo "<h1>No se especificó un ID de actividad válido.</h1>";
    $actividad = null;
    $pasos = [];
}

// Cerrar la conexión a la base de datos al final del script
mysqli_close($conexion); // Usar mysqli_close con la variable $conexion
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad: <?php echo htmlspecialchars($actividad['nombre_actividad'] ?? 'Cargando...'); ?></title>
    <link rel="stylesheet" href="estilos/estilos_actividad.css">
    <link rel="stylesheet" href="estilos_actividad.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="encabezado-principal">
        <div class="seccion-izquierda-encabezado">
            <a href="panel_nino.php" class="boton-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div class="identidad-actividad">
                <img src="<?php echo htmlspecialchars($actividad['foto_actividad'] ?? 'imagenes/placeholder_activity.png'); ?>" alt="<?php echo $actividad['texto_alternativo']; ?>" class="logo-actividad">
                <h1 class="nombre-actividad"><?php echo htmlspecialchars($actividad['nombre_actividad'] ?? 'Actividad no disponible'); ?></h1>
            </div>
        </div>
        
        <div class="seccion-derecha-encabezado">
            <p class="saludo">¡Hola, <?php echo htmlspecialchars($nombre_del_nino); ?>!</p>
        </div>
    </header>
    
    <main class="contenido-principal">
        <div id="anuncio-navegacion-pasos" role="status" aria-live="polite" class="sr-only">
        </div>
        <section class="contenedor-paso-actividad"> 
            <button id="anterior" class="boton-navegacion boton-previo" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div id="principal" class="visualizador-paso">
                <?php if ($actividad && $primer_paso_datos): ?>
                
                <div class="caja-texto-paso">
                    <p id="texto" class="texto-paso"><?php echo htmlspecialchars($primer_paso_datos['texto_paso']); ?></p>
                    <div id="caja_avance"> 
                        <div id="progress-bar">
                        </div>
                    </div> 
                </div>
                <div class="caja-imagen-paso">
                    <img id="imagen_paso" src="<?php echo htmlspecialchars($primer_paso_datos['imagen_paso']); ?>" alt="<?php echo htmlspecialchars($primer_paso_datos['texto_paso']); ?>" class="imagen-paso">
                </div>
                <div class="contenedor-audio-paso">
                    <audio id="audio-paso" class="audio-paso" src="<?php echo htmlspecialchars($primer_paso_datos['audio_paso']); ?>" controls></audio>
                </div>
                <?php else: ?>
                    <p class="mensaje-sin-pasos">No se pudo cargar el paso de la actividad.</p>
                <?php endif; ?>
            </div>

            <button id="siguiente" class="boton-navegacion boton-siguiente" aria-label="Siguiente"> 
                <i class="fas fa-chevron-right"></i>
            </button>
        </section>

        <div class="botones-navegacion-movil">
            <button id="anterior_movil" class="boton-navegacion boton-previo-movil" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i> Anterior
            </button>
            <button id="siguiente_movil" class="boton-navegacion boton-siguiente-movil" aria-label="Siguiente">
                Siguiente <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </main>


    <script>
        // Paso crucial: Pasar todos los datos de los pasos desde PHP a JavaScript
        const allSteps = <?php echo json_encode($pasos); ?>;
        let currentStepIndex = 0; // El índice del paso actual (empieza en 0 para el primer paso)

        // Elementos del DOM donde mostraremos la información
        const textoPaso = document.getElementById('texto');
        const imagenPaso = document.getElementById('imagen_paso');  
        const audioPaso = document.getElementById('audio-paso');
        
        const progressBar = document.getElementById('progress-bar'); // Elemento de la barra de progreso

        const btnAnterior = document.getElementById('anterior');
        const btnSiguiente = document.getElementById('siguiente');
        
        const btnAnteriorMovil = document.getElementById('anterior_movil'); // Botones móviles
        const btnSiguienteMovil = document.getElementById('siguiente_movil');
        
        // Agregamos una variable para el div de anuncios de navegación
        const anuncioNavegacion = document.getElementById('anuncio-navegacion-pasos');

        // Función para actualizar el contenido del paso en la interfaz
        function updateStepContent() {
            const principal = document.getElementById('principal');

            // Oculta (fade out)
            principal.classList.add('fade-out');

            // Espera a que se desvanezca, cambia el contenido, y vuelve a aparecer (fade in)
            setTimeout(() => {
                mostrarContenidoPaso();

                principal.classList.remove('fade-out');
                principal.classList.add('fade-in');

                // Limpia la clase después de que termine la animación
                setTimeout(() => {
                    principal.classList.remove('fade-in');
                }, 500); // tiempo igual a la duración del CSS
            }, 300); // pequeño retardo para que se note la salida antes del cambio
        }

        function mostrarContenidoPaso() {
            const currentStep = allSteps[currentStepIndex];
            textoPaso.textContent = currentStep.texto_paso;
            imagenPaso.src = currentStep.imagen_paso + '?' + new Date().getTime();
            imagenPaso.alt = currentStep.texto_paso; // Ahora toma el texto del paso
            audioPaso.src = currentStep.audio_paso + '?' + new Date().getTime();
            
            audioPaso.load();

            // Actualiza el ancho de la barra de progreso
            const progressPercentage = ((currentStepIndex + 1) / allSteps.length) * 100;
            progressBar.style.width = progressPercentage + '%';
        }

        // Función para actualizar el estado de los botones de navegación
        function updateNavigationButtons() {
            if (btnAnterior) { 
                btnAnterior.disabled = currentStepIndex === 0;
                btnAnterior.classList.toggle('disabled', currentStepIndex === 0);
            }
            if (btnAnteriorMovil) {
                btnAnteriorMovil.disabled = currentStepIndex === 0;
                btnAnteriorMovil.classList.toggle('disabled', currentStepIndex === 0);
            }
            // MODIFICACIÓN CRUCIAL: El botón Siguiente ya no se deshabilita en el último paso.
            // Solo se deshabilita si el array está vacío.
            if (btnSiguiente) {
                btnSiguiente.disabled = allSteps.length === 0;
                btnSiguiente.classList.toggle('disabled', allSteps.length === 0);
            }
            if (btnSiguienteMovil) {
                btnSiguienteMovil.disabled = allSteps.length === 0;
                btnSiguienteMovil.classList.toggle('disabled', allSteps.length === 0);
            }
        }

        function avanzarPaso() {
            // Lógica corregida para diferenciar el último paso de los demás
            if (currentStepIndex < allSteps.length - 1) {
                // Si NO estamos en el último paso, avanza normalmente
                currentStepIndex++;
                updateStepContent();
                updateNavigationButtons();
                // ANUNCIO PARA NVDA: Anuncia el cambio de paso
                anuncioNavegacion.textContent = `Se ha avanzado al paso ${currentStepIndex + 1} de ${allSteps.length}. El nuevo paso es: ${allSteps[currentStepIndex].texto_paso}`;
            } else if (currentStepIndex === allSteps.length - 1) {
                // Si estamos en el último paso, esta es la acción final
                // 1. Mostrar el modal de felicitación
                const modalFelicitacion = document.getElementById('modalFelicitacion');
                const audioTriunfo = document.getElementById('audioTriunfo'); // obtengo el elemento de audio

                // 2. Reproducir el audio de triunfo
                if (audioTriunfo) {
                    audioTriunfo.play().catch(e => console.error("Error al intentar reproducir el audio:", e)); // 👈 REPRODUCIMOS EL AUDIO
                }

                // 3. Anunciar a los lectores de pantalla que la actividad ha terminado y un modal se ha abierto
                if (anuncioNavegacion) {
                    anuncioNavegacion.textContent = `¡Felicitaciones! Has completado la actividad. Se ha abierto una ventana emergente de felicitación.`;
                }

                if (modalFelicitacion) {
                    modalFelicitacion.style.display = 'block'; 
                    modalFelicitacion.focus(); // Mueve el foco al modal
                }

                // 4. Preparar y enviar la petición para marcar la actividad como completada
                const idAsignacion = <?php echo json_encode($id_asignacion); ?>; 

                if (idAsignacion > 0) {
                    const formData = new FormData();
                    formData.append('id_asignacion', idAsignacion);

                    fetch('componentes/marcar_actividad_completada.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Actividad marcada como completada exitosamente.');
                        } else {
                            console.error('Error al marcar la actividad como completada:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error en la llamada AJAX:', error);
                    });
                }
                
                // 5. Deshabilita los botones para evitar que el niño siga "avanzando" una actividad ya terminada.
                if (btnSiguiente) { btnSiguiente.disabled = true; btnSiguiente.classList.add('disabled'); }
                if (btnAnterior) { btnAnterior.disabled = true; btnAnterior.classList.add('disabled'); }
                if (btnSiguienteMovil) { btnSiguienteMovil.disabled = true; btnSiguienteMovil.classList.add('disabled'); }
                if (btnAnteriorMovil) { btnAnteriorMovil.disabled = true; btnAnteriorMovil.classList.add('disabled'); }
            }
        }


        function retrocederPaso() {
            if (currentStepIndex > 0) {
                currentStepIndex--;
                updateStepContent();
                updateNavigationButtons();
                // ANUNCIO PARA NVDA: Anuncia el cambio de paso
                anuncioNavegacion.textContent = `Se ha retrocedido al paso ${currentStepIndex + 1} de ${allSteps.length}. El nuevo paso es: ${allSteps[currentStepIndex].texto_paso}`;
            } else {
                return;
            }
        }


        // --- Event Listeners para los botones Anterior y Siguiente ---
        if (btnAnterior) { 
            btnAnterior.addEventListener('click', retrocederPaso);
        }
        if (btnSiguiente) { 
            btnSiguiente.addEventListener('click', avanzarPaso);
        }
        // --- Event Listeners para los botones Anterior y Siguiente (versión móvil) ---
        if (btnAnteriorMovil) {
            btnAnteriorMovil.addEventListener('click', retrocederPaso);
        }
        if (btnSiguienteMovil) {
            btnSiguienteMovil.addEventListener('click', avanzarPaso);
        }

        // Llamar a la función al cargar la página para mostrar el primer paso.
        if (allSteps.length > 0) {
            updateStepContent(); 
            updateNavigationButtons();
            // ANUNCIO PARA NVDA al cargar la página
            anuncioNavegacion.textContent = `Actividad cargada. Estás en el paso 1 de ${allSteps.length}.`;
        } else {
            // Si no hay pasos, deshabilitar botones
            if (btnAnterior) btnAnterior.disabled = true;
            if (btnSiguiente) btnSiguiente.disabled = true;
            if (btnAnteriorMovil) btnAnteriorMovil.disabled = true;
            if (btnSiguienteMovil) btnSiguienteMovil.disabled = true;
            if (progressBar) progressBar.style.width = '0%';
            // ANUNCIO PARA NVDA si no hay pasos
            if (anuncioNavegacion) {
                anuncioNavegacion.textContent = 'No se encontraron pasos para esta actividad.';
            }
        }

        // --- Control con teclado (flechas izquierda y derecha) ---
        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight') {
                avanzarPaso();
            } else if (event.key === 'ArrowLeft') {
                retrocederPaso();
            }
        });

        // --- Soporte táctil para móviles (Swipe) ---
        let touchStartX = 0;
        let touchEndX = 0;

        // Elemento que captura los gestos (puede ser todo el body o solo el contenedor)
        const gestureZone = document.getElementById('principal');

        gestureZone.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches[0].screenX;
        }, false);

        gestureZone.addEventListener('touchend', function(event) {
            touchEndX = event.changedTouches[0].screenX;
            handleGesture();
        }, false);

        function handleGesture() {
            const swipeDistance = touchStartX - touchEndX;

            if (Math.abs(swipeDistance) > 50) {
                if (swipeDistance > 0) {
                    avanzarPaso();
                } else {
                    retrocederPaso();
                }
            }
        }
    </script>

    <div id="modalFelicitacion" class="modal-contenedor"
    role="dialog"
    aria-modal="true"
    tabindex="-1">
    <div class="modal-contenido">
        <h2 class="modal-titulo">¡Felicitaciones!</h2>
        <p class="modal-texto">¡Has completado esta actividad!</p>
        
        <audio id="audioTriunfo">
            <source src="audios/success-1-6297.mp3" type="audio/mpeg">
            Tu navegador no soporta el elemento de audio.
        </audio>
        
        <button onclick="window.location.href='panel_nino.php'" class="boton-volver-modal">Volver a mis actividades</button>
    </div>
</div>
</body>
</html>