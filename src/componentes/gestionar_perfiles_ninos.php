<section id="caja_ver_lista_ninos" class="seccion-panel" aria-labelledby="perfiles-de-niños/as">
    <h2 id="perfiles-de-niños/as">Perfiles de niños/as</h2>

    <div id="notificacion_aria" role="status" aria-live="polite" tabindex="-1"><!-- -- tabindex="-1" permite que javascript mueva el foco aqui -->
        <?php if (!empty($mensaje_general)) { ?>
            <p class="mensaje_form <?php echo htmlspecialchars($clase_general); ?>">
                <?php echo htmlspecialchars($mensaje_general); ?>
            </p>
        <?php } ?>
    </div>

    <?php
    $consulta = mysqli_query($conexion, "SELECT * FROM ninos WHERE id_padre='$id_padre_sesion'");

    if (mysqli_num_rows($consulta) > 0) {
    ?>
        <ul class="contenedor_ninos_flex" class="lista-ninos">
            <?php
            while ($listar_nin = mysqli_fetch_assoc($consulta)) {
            ?>
                <li class="caja_nino_para_repetir">
                    <p class="nombre_nino">Nombre: <?php echo htmlspecialchars($listar_nin['nombre']); ?></p>
                    <p class="nombre_nino">Usuario: <?php echo htmlspecialchars($listar_nin['usuario']); ?></p>
                    <p class="nombre_nino">Contraseña: <?php echo htmlspecialchars($listar_nin['contrasena']); ?></p>
                    <ul class="lista-acciones-actividad">
                    <li><p>
                        <a href="panel_padres.php?seccion=ninos&accion=editar&id_nino=<?php echo htmlspecialchars($listar_nin['id_nino']); ?>"
                           aria-label="Modificar el perfil de <?php echo htmlspecialchars($listar_nin['nombre']); ?>">Modificar</a>
                    </p></li>
                    <li><p>
                        <a href="componentes/eliminar_nino.php?id=<?php echo htmlspecialchars($listar_nin['id_nino']); ?>"
                           aria-label="Eliminar el perfil de <?php echo htmlspecialchars($listar_nin['nombre']); ?>" role="button">Eliminar</a>
                    </p></li>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    <?php
    } else {
    ?>
        <p class="mensaje_resaltado">Aún no tiene Perfil de niño/a creado. Use el formulario de 'Crear Perfil niño/a' para crear un usuario niño/a.</p>
    <?php
    }
    ?>
</section>

<section id="caja_formulario_crear_nino" class="seccion-panel" aria-labelledby="crear-perfil-niño/a">
    <h2 id="crear-perfil-niño/a">Crear Perfil niño/a:</h2>
    <p class="instrucciones-formulario">Este formulario es para crear un usuario del niño/a, al cual se le asignaran actividades.</p>

    <form action="componentes/procesar_insertar_nino.php" method="POST">
        <input type="hidden" name="accion" value="agregar_nino">
        <label for="nombre_nino" class="form_label">Nombre del niño: </label>
        <input id="nombre_nino" type="text" class="form-control" name="nombre_nino" placeholder="Ingrese Nombre del niño/a" required>
        <label for="usuario_nino" class="form_label">Usuario para el niño/a: </label>
        <input id="usuario_nino" type="text" class="form-control" name="usuario_nino" placeholder="Ingrese Usuario para el niño/a" required>
        <label for="contrasena_nino" class="form_label">Contraseña: </label>
        <input id="contrasena_nino" type="password" class="form-control" name="contrasena_nino" placeholder="Ingrese contraseña para el niño/a" required>
        <input type="submit" value="Agregar Niño/a">
    </form>

</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificacionDiv = document.getElementById('notificacion_aria');

        if (notificacionDiv && notificacionDiv.innerText.trim() !== '') {
            // Añade un pequeño retraso para asegurar que el foco se mueva correctamente
            setTimeout(() => {
                notificacionDiv.focus();
            }, 100); // 100 milisegundos de retraso
        }
    });
</script>