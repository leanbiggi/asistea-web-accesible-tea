<?php
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires'); // Asegúrate que esta línea esté aquí
require_once 'componentes/conexion.php';

// --- COMIENZO DE LA FUNCIÓN getDayNameSpanish (MUEVE ESTO AQUÍ) ---
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
// --- FIN DE LA FUNCIÓN getDayNameSpanish ---

/*
// --- LÍNEAS A AGREGAR PARA DIAGNÓSTICO (AHORA NO DARÁN ERROR) ---
echo "<h2>Diagnóstico de Fecha/Hora del Servidor</h2>";
echo "<p>Hora actual del servidor (America/Argentina/Buenos_Aires): <strong>" . date('Y-m-d H:i:s') . "</strong></p>";
echo "<p>Día de la semana actual (en español): <strong>" . getDayNameSpanish(date('l')) . "</strong></p>";
echo "<hr>";
// --- FIN LÍNEAS DE DIAGNÓSTICO ---
*/

// Verificar si el niño ya ha iniciado sesión
if (!isset($_SESSION['nino_id']) || !isset($_SESSION['nino_nombre'])) {


//LOGICA PARA LOS MENSAJE DE EXITO Y ERROR QUE DEVUELVE LOS FORMULARIOS  
$mensaje_general = '';
$clase_general = '';

    // Se verifica si el parámetro 'estado' está presente en la URL.
    if (isset($_GET['estado'])) {
        // Si existe, se usa un solo switch para manejar todos los posibles estados.
        switch ($_GET['estado']) {
            // Casos para cuando se elimina cuenta de padre exitosamente
            case 'usu_o_contr_incorrecto':
                $mensaje_general = "Usuario o contraseña incorrectos.";
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
    <title>Acceso Niños</title>
    
    <link href="estilos/estilos_login_nino.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- libreria para icono de boton volver -->
</head>
<body>
<?php
include('componentes/enlace-saltar-a-contenido-principal.php');
?>
<header role="banner" class="header-login">
    <a href="asistea.php" class="boton-volver">
        <i class="fas fa-arrow-left"></i> Volver a Asistea
    </a>
    <h1>Bienvenido/a</h1>
    <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1">
            <?php if (!empty($mensaje_general)) { ?>
                <p class="mensaje_form <?php echo htmlspecialchars($clase_general); ?>">
                    <?php echo htmlspecialchars($mensaje_general); ?>
                </p>
            <?php } ?>
        </div>
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
</header>
<main class="envoltorio-login" role="main" id="contenido-principal">

    <section class="login-container" aria-labelledby="hola,-ingresa-para-ver-tus-actividades">
        
        <h2 id="hola,-ingresa-para-ver-tus-actividades">Hola, ¡ingresa para ver tus actividades!</h2>
        <form action="panel_nino.php" method="POST">
            <label for="usuario_nino" class="form_label">Tu usuario: </label>
            <input id="usuario_nino" type="text" name="usuario_nino" placeholder="Tu usuario" required>
            <label for="pass_nino" class="form_label">Tu contraseña: </label>
            <input id="pass_nino" type="password" name="pass_nino" placeholder="Tu contraseña" required>
            <input type="submit" value="Entrar">
        </form>
        <?php
        //AQUI valido el usuario y contraseña del niño y creo SESSION del niño
        if (isset($_POST['usuario_nino']) && isset($_POST['pass_nino'])) {
            $usuario_nino = trim($_POST['usuario_nino']);
            $pass_nino = $_POST['pass_nino'];

            if (empty($usuario_nino) || empty($pass_nino)) {
                echo "<p class='error-message'>Por favor, ingresa tu usuario y contraseña.</p>";
            } else {
                $consulta_existe = mysqli_query($conexion, "SELECT id_nino,nombre,usuario,contrasena FROM ninos WHERE 
                usuario='$usuario_nino' AND contrasena='$pass_nino'");

                if (mysqli_num_rows($consulta_existe) == 1) {
                    $nino_array = mysqli_fetch_assoc($consulta_existe);
                    $_SESSION['nino_id'] = $nino_array['id_nino'];
                    $_SESSION['nino_nombre'] = $nino_array['nombre'];
                    $_SESSION['nino_usuario'] = $nino_array['usuario'];
                    header("Location: panel_nino.php?ingreso_ok");
                    exit();
                } else {
                    header("Location: panel_nino.php?estado=usu_o_contr_incorrecto");
                    //echo "<p class='error-message'>Usuario o contraseña incorrectos.</p>";
                }
            }
        }
        $conexion->close();
        ?>
    </section>
</main>
</body>
</html>
<?php
    exit();
}

// --- Si el niño ha iniciado sesión, mostrar el panel ---

$id_nino_actual = $_SESSION['nino_id']; //tomo de la session el id del niño 
$nombre_nino_actual = $_SESSION['nino_nombre']; // tomo de la session el nombre del niño
$today_date = date('Y-m-d'); //devuekve fecha de hoy. ejemplo: 2025-07-13
$today_day = date('l'); //devuelve dia de la semana en ingles. ejemplo: Monday
$dias_traducidos = [
    'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miercoles',
    'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
];
$today_day_spanish = $dias_traducidos[$today_day] ?? '';

$actividades_asignadas = []; //creo un array vacio para guardar las actividades asignadas

$sql_asignadas = "
    SELECT aa.id_asignacion, a.id_actividad, a.nombre_actividad, a.foto_actividad, a.descripcion, a.texto_alternativo,
            aa.fecha_asignada, aa.hora_asignada, aa.estado, aa.es_recurrente, aa.fecha_completado
    FROM asignaciones_actividades aa
    JOIN actividades a ON aa.id_actividad = a.id_actividad
    LEFT JOIN asignacion_recurrencia_dias ard ON aa.id_asignacion = ard.id_asignacion
    WHERE aa.id_nino = ? AND (
        (aa.es_recurrente = 1 AND ard.dia_semana = ?) OR
        (aa.es_recurrente = 0 AND aa.fecha_asignada = ?)
    ) AND aa.estado != 'saltada'
    ORDER BY
        CASE WHEN aa.estado = 'completada' THEN 1 ELSE 0 END ASC,
        aa.hora_asignada ASC
";

$stmt_asignadas = $conexion->prepare($sql_asignadas);
$stmt_asignadas->bind_param("iss", $id_nino_actual, $today_day_spanish, $today_date);
$stmt_asignadas->execute();
$resultado_asignadas = $stmt_asignadas->get_result();

while ($fila = $resultado_asignadas->fetch_assoc()) {
    $actividades_asignadas[] = $fila;
}
$stmt_asignadas->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividades de Hoy</title>
    <link rel="stylesheet" href="estilos/estilos_panel_nino.css">
    
</head>
<body>
<?php
include('componentes/enlace-saltar-a-contenido-principal.php');
?>
<header class="nino-header">
        <h1>Hola, <?php echo htmlspecialchars($nombre_nino_actual); ?>!</h1>
        <p><a href="componentes/salir_nino.php">Cerrar sesión</a></p>
</header>
<main id="contenido-principal" role="main">
    <?php if (!empty($actividades_asignadas)): ?>
        <section class="actividades-grid">
        <?php foreach ($actividades_asignadas as $asignacion): ?>
            <?php
            // Inicializamos el estado a mostrar y si el botón está habilitado
            $estado_a_mostrar = $asignacion['estado']; // Estado original de la base de datos
            $boton_habilitado = ($asignacion['estado'] !== 'completada'); // Botón habilitado si no está 'completada'

            // Lógica para asignaciones recurrentes
            if ($asignacion['es_recurrente']) {
                // Si la asignación está 'completada' en la base de datos y tiene fecha de completado
                if ($asignacion['estado'] === 'completada' && !empty($asignacion['fecha_completado'])) {
                    $fecha_completado_dt = new DateTime(date('Y-m-d', strtotime($asignacion['fecha_completado'])));
                    $hoy = new DateTime(date('Y-m-d'));

                    // Si se completó HOY, se muestra como completada y el botón deshabilitado
                    if ($fecha_completado_dt->format('Y-m-d') === $hoy->format('Y-m-d')) {
                        $estado_a_mostrar = 'completada';
                        $boton_habilitado = false;
                    } else {
                        // Si se completó en un DÍA ANTERIOR, pero es una tarea recurrente
                        // y HOY es uno de sus días asignados, la mostramos como 'pendiente'
                        // y habilitamos el botón para que el niño pueda hacerla de nuevo.
                        $estado_a_mostrar = 'pendiente';
                        $boton_habilitado = true;
                    }
                } else {
                    // Si es recurrente y su estado no es 'completada' (ej. 'pendiente', 'en_progreso'),
                    // simplemente se mantiene su estado actual y el botón habilitado.
                    $estado_a_mostrar = $asignacion['estado'];
                    $boton_habilitado = true;
                }
            }
            // Para las tareas NO recurrentes, la lógica inicial (estado y habilitación del botón
            // según lo que venga de la BD) se mantiene tal cual.
            ?>
            <div class="actividad-card" role="region" aria-labelledby="titulo_actividad_<?php echo $asignacion['id_asignacion']; ?>">
                <img src="<?php echo htmlspecialchars($asignacion['foto_actividad']); ?>" alt="<?php echo
                 htmlspecialchars($asignacion['texto_alternativo'] ?? $asignacion['nombre_actividad']); ?>">
                <h2 id="titulo_actividad_<?php echo $asignacion['id_asignacion']; ?>">
                    <?php echo htmlspecialchars($asignacion['nombre_actividad']); ?>
                </h2>
                <p><?php echo htmlspecialchars($asignacion['descripcion']); ?></p>
                <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($asignacion['hora_asignada'])); ?></p>
                <p><strong>Estado:</strong> <?php echo ucfirst($estado_a_mostrar); ?></p>
                <p><strong>¿Es recurrente?:</strong> <?php echo $asignacion['es_recurrente'] ? 'Sí' : 'No'; ?></p>
                <?php if ($boton_habilitado): ?>
                    <a href="actividad.php?id=<?php echo $asignacion['id_actividad']; ?>&id_asignacion=<?php echo $asignacion['id_asignacion'];
                     ?>" class="boton-ir" role="button">Ir a Actividad</a>
                <?php else: ?>
                    <span class="estado-completada">Ya completada</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </section>
    <?php else: ?>
        <p style="text-align:center; padding: 20px; font-size: 1.2em;">No hay actividades asignadas para hoy.</p>
    <?php endif; ?>
</main>
</body>
</html>