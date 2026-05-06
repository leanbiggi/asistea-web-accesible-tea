<?php
session_start(); // Inicia la sesión para simular el entorno

// Datos "hardcodeados" de la actividad de ejemplo
$actividad = [
    'nombre_actividad' => 'Preparar un Sándwich',
    'foto_actividad' => 'imagenes/actividades/sandwich_principal.jpg', // Asegúrate de tener esta imagen
    'texto_alternativo' => 'Imagen de un sándwich'
];

// Datos "hardcodeados" de los pasos de la actividad
$pasos = [
    [
        'id_paso' => 1,
        'numero_paso' => 1,
        'texto_paso' => 'Paso 1: Busca los ingredientes. Necesitarás pan, queso, jamón (u otro relleno), y mayonesa o mostaza.',
        'imagen_paso' => 'imagenes/pasos/paso1_ingredientes.jpg', // Asegúrate de tener esta imagen
        'audio_paso' => 'audios/pasos/paso1_ingredientes.mp3' // Asegúrate de tener este audio
    ],
    [
        'id_paso' => 2,
        'numero_paso' => 2,
        'texto_paso' => 'Paso 2: Coloca dos rebanadas de pan en un plato.',
        'imagen_paso' => 'imagenes/pasos/paso2_pan.jpg',
        'audio_paso' => 'audios/pasos/paso2_pan.mp3'
    ],
    [
        'id_paso' => 3,
        'numero_paso' => 3,
        'texto_paso' => 'Paso 3: Si quieres, unta mayonesa o mostaza en una de las rebanadas de pan.',
        'imagen_paso' => 'imagenes/pasos/paso3_mayonesa.jpg',
        'audio_paso' => 'audios/pasos/paso3_mayonesa.mp3'
    ],
    [
        'id_paso' => 4,
        'numero_paso' => 4,
        'texto_paso' => 'Paso 4: Pon el jamón y el queso (o tu relleno favorito) sobre una de las rebanadas de pan.',
        'imagen_paso' => 'imagenes/pasos/paso4_relleno.jpg',
        'audio_paso' => 'audios/pasos/paso4_relleno.mp3'
    ],
    [
        'id_paso' => 5,
        'numero_paso' => 5,
        'texto_paso' => 'Paso 5: Cubre con la otra rebanada de pan.',
        'imagen_paso' => 'imagenes/pasos/paso5_cubrir.jpg',
        'audio_paso' => 'audios/pasos/paso5_cubrir.mp3'
    ],
    [
        'id_paso' => 6,
        'numero_paso' => 6,
        'texto_paso' => 'Paso 6: ¡Disfruta de tu delicioso sándwich!',
        'imagen_paso' => 'imagenes/pasos/paso6_disfrutar.jpg',
        'audio_paso' => 'audios/pasos/paso6_disfrutar.mp3'
    ]
];

$total_pasos = count($pasos);
$primer_paso_datos = $pasos[0];

// Simula una sesión de niño si no existe
if (!isset($_SESSION['nino_nombre'])) {
    $_SESSION['nino_nombre'] = 'Ejemplo Niño'; // Nombre para el saludo
}
$nombre_del_nino = $_SESSION['nino_nombre'];

// No se requiere id_asignacion para el ejemplo, pero se mantiene la variable para que el JS no falle.
// En un ejemplo real, no intentaría marcar como completado.
$id_asignacion = 0; // Se establece en 0 para que la lógica de JS que marca como completado no intente hacer nada.

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad de Ejemplo: <?php echo htmlspecialchars($actividad['nombre_actividad']); ?></title>
    <link rel="stylesheet" href="estilos/estilos_actividad.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php
include('componentes/enlace-saltar-a-contenido-principal.php');
?>
    <header class="encabezado-principal">
        <div class="seccion-izquierda-encabezado">
            <a href="asistea.php" class="boton-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div class="identidad-actividad">
                <img src="<?php echo htmlspecialchars($actividad['foto_actividad']); ?>" alt="<?php echo htmlspecialchars($actividad['texto_alternativo']); ?>" class="logo-actividad">
                <h1 class="nombre-actividad"><?php echo htmlspecialchars($actividad['nombre_actividad']); ?></h1>
            </div>
        </div>
        
        <div class="seccion-derecha-encabezado">
            <p class="saludo">¡Hola, <?php echo htmlspecialchars($nombre_del_nino); ?>!</p>
        </div>
    </header>
    
    <main class="contenido-principal" id="contenido-principal">
        <section class="contenedor-paso-actividad">
            <button id="anterior" class="boton-navegacion boton-previo" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div id="principal" class="visualizador-paso">
                <div class="caja-texto-paso">
                    <p id="texto" class="texto-paso"><?php echo htmlspecialchars($primer_paso_datos['texto_paso']); ?></p>
                    <div id="caja_avance">
                        <div id="progress-bar"></div>
                    </div>
                </div>
                <div class="caja-imagen-paso">
                    <img id="imagen_paso" src="<?php echo htmlspecialchars($primer_paso_datos['imagen_paso']); ?>" alt="<?php echo htmlspecialchars($primer_paso_datos['texto_paso']); ?>" class="imagen-paso">
                </div>
                <div class="contenedor-audio-paso">
                    <audio id="audio-paso" class="audio-paso" src="<?php echo htmlspecialchars($primer_paso_datos['audio_paso']); ?>" controls></audio>
                </div>
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
    
    // Función para actualizar el contenido del paso en la interfaz
    function updateStepContent() {
        const principal = document.getElementById('principal');

        principal.classList.add('fade-out');

        setTimeout(() => {
            mostrarContenidoPaso();

            principal.classList.remove('fade-out');
            principal.classList.add('fade-in');

            setTimeout(() => {
                principal.classList.remove('fade-in');
            }, 500); // tiempo igual a la duración del CSS
        }, 300); // pequeño retardo para que se note la salida antes del cambio
    }

    function mostrarContenidoPaso() {
        const currentStep = allSteps[currentStepIndex];
        textoPaso.textContent = currentStep.texto_paso;
        // Para evitar problemas de caché con las imágenes, añade un timestamp
        imagenPaso.src = currentStep.imagen_paso + '?' + new Date().getTime();
        imagenPaso.alt = currentStep.texto_paso;
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

        if (btnSiguiente) {
            btnSiguiente.disabled = currentStepIndex === allSteps.length - 1;
            btnSiguiente.classList.toggle('disabled', currentStepIndex === allSteps.length - 1);
        }
        if (btnSiguienteMovil) {
            btnSiguienteMovil.disabled = currentStepIndex === allSteps.length - 1;
            btnSiguienteMovil.classList.toggle('disabled', currentStepIndex === allSteps.length - 1);
        }
    }

    function avanzarPaso() {
        if (currentStepIndex < allSteps.length - 1) {
            currentStepIndex++;
            updateStepContent();
            updateNavigationButtons();
        } else {
            // LÓGICA DEL ÚLTIMO PASO
            
            const modalFelicitacion = document.getElementById('modalFelicitacion');
            const audioTriunfo = document.getElementById('audioTriunfo');
            
            // 1. Reproducir el sonido de triunfo
            if (audioTriunfo) {
                audioTriunfo.play().catch(error => {
                    console.error("Error al intentar reproducir audio:", error);
                });
            }
            
            // 2. Mostrar el modal de felicitación
            if (modalFelicitacion) {
                modalFelicitacion.style.display = 'block';
                // ** LÍNEAS AÑADIDAS PARA ASEGURAR LA VISIBILIDAD **
                modalFelicitacion.style.opacity = '1'; 
                modalFelicitacion.style.visibility = 'visible';
                // ************************************************
                
                // Para accesibilidad: mueve el foco al modal
                modalFelicitacion.focus(); 
            }
            
            // 3. Deshabilitar la navegación (para bloquear la actividad)
            btnSiguiente.disabled = true;
            btnSiguiente.classList.add('disabled');
            btnSiguienteMovil.disabled = true;
            btnSiguienteMovil.classList.add('disabled');
            
            btnAnterior.disabled = true;
            btnAnterior.classList.add('disabled');
            btnAnteriorMovil.disabled = true;
            btnAnteriorMovil.classList.add('disabled');
        }
    }

    function retrocederPaso() {
        if (currentStepIndex > 0) {
            currentStepIndex--;
            updateStepContent();
            updateNavigationButtons();
        } else {
            //alert("Estás en el primer paso.");
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
    } else {
        // Si no hay pasos, deshabilitar botones
        if (btnAnterior) btnAnterior.disabled = true;
        if (btnSiguiente) btnSiguiente.disabled = true;
        if (btnAnteriorMovil) btnAnteriorMovil.disabled = true;
        if (btnSiguienteMovil) btnSiguienteMovil.disabled = true;
        if (progressBar) progressBar.style.width = '0%'; // Asegura que la barra esté vacía
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

    // Elemento que captura los gestos (todo el contenido principal)
    const gestureZone = document.getElementById('principal'); // O puedes usar 'document.body' si prefieres

    if (gestureZone) {
        gestureZone.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches[0].screenX;
        }, false);

        gestureZone.addEventListener('touchend', function(event) {
            touchEndX = event.changedTouches[0].screenX;
            handleGesture();
        }, false);
    }


    function handleGesture() {
        const swipeDistance = touchStartX - touchEndX;

        if (Math.abs(swipeDistance) > 50) { // Umbral de 50px para considerar un swipe
            if (swipeDistance > 0) {
                avanzarPaso();
            } else {
                retrocederPaso();
            }
        }
    }
    </script>

    <div id="modalFelicitacion" style="display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);"
    role="dialog"
    aria-modal="true"
    tabindex="-1">
        <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; text-align: center; color: black; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
            <h2 style="font-size: 2em; color: #00796b;">¡Felicitaciones!</h2>
            <p style="font-size: 1.2em;">¡Has completado esta actividad de ejemplo!</p>
            
            <audio id="audioTriunfo">
                <source src="audios/success-1-6297.mp3" type="audio/mpeg">
                Tu navegador no soporta el elemento de audio.
            </audio>
            <img src="imagenes/felicitaciones.gif" alt="Felicitaciones" style="width: 100%; max-width: 300px; margin: 20px 0;">
            <button onclick="window.location.href='asistea.php'" style="background-color: #00796b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; margin-top: 15px;">Volver a la página principal</button>
        </div>
    </div>
</body>
</html>
