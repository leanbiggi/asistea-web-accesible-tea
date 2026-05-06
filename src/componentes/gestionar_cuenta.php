<?php

include("componentes/conexion.php"); //como este archivo está incluido en panel_padres.php, todas las rutas relativas deben ser relativas al padre (panel_padres.php)

// estos datos se generan en componentes/validar_padre.php
//$_SESSION[] son variable globales
$padre_id = $_SESSION['id_padre'];
$nombre_padre = $_SESSION['nombre_padre'] ?? ''; // Si la variable de sesión no existe, asigna una cadena vacía.
$apellido_padre = $_SESSION['apellido_padre'] ?? '';
$correo_padre = $_SESSION['padre'];  // $_SESSION['padre']; es el correo, el usuario
//$pass_cuenta_padre = $_SESSION['contrasena_cuenta_padre'];

?>

<section class="seccion-panel" id="section-form-editar-cuenta" aria-labelledby="editar-datos-de-cuenta">
    <h2 id="editar-datos-de-cuenta">Editar datos de cuenta</h2>

    <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1"><!-- -- tabindex="-1" permite que javascript mueva el foco aqui -->
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

    <form action="componentes/actualizar_datos_padre.php" method="POST">
        <div class="campo-form">
            <label for="nombre" class="form_label">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre_padre); ?>" required>
        </div>
        <div class="campo-form">
            <label for="apellido" class="form_label">Apellido:</label>
            <input type="text" id="apellido" name="apellido" class="form-control" value="<?php echo htmlspecialchars($apellido_padre); ?>" required>
        </div>
        <div class="campo-form">
            <label for="email" class="form_label">Correo electrónico:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($correo_padre); ?>" required>
        </div>
        <div class="campo-form" class="form_label">
            <label for="password" class="form_label">Nueva Contraseña (opcional):</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Deja vacío para no cambiar">
        </div>
        <button type="submit">Guardar Cambios</button>
    </form>
</section>

<section class="seccion-panel" aria-labelledby="eliminar-cuenta">
    <h2 id="eliminar-cuenta">Eliminar mi cuenta</h2>
    <p>¡Atención! Esta acción es irreversible y eliminará todos los datos asociados, incluyendo los perfiles de los niños y sus asignaciones.</p>
    <button id="btn-eliminar-cuenta" class="boton-accion boton-eliminar">Eliminar cuenta</button> <!-- Ver el script. Alli está el redireccionamiento -->
</section>

<script>
    document.getElementById('btn-eliminar-cuenta').addEventListener('click', function() {
        if (confirm('¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.')) {
            // La ruta es correcta porque se ejecuta en el navegador desde el contexto de panel_padres.php
            window.location.href = 'componentes/eliminar_cuenta_padre.php'; 
        }
    });
</script>