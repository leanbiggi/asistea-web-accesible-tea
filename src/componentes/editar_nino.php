<?php


if (!isset($_SESSION['id_padre'])) {
    // Redirigir al login si no hay sesión iniciada
    header("Location: panel_padres.php");
    exit();
}

include("conexion.php");

// --- LÓGICA PARA PROCESAR EL FORMULARIO ENVIADO (MÉTODO POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validar y Sanitizar los datos recibidos
    $id_nino = filter_input(INPUT_POST, 'id_nino', FILTER_VALIDATE_INT);
    $nombre_nino = trim($_POST['nombre_nino'] ?? '');
    $usuario_nino = trim($_POST['usuario_nino'] ?? '');
    $contrasena_nino = $_POST['contrasena_nino'] ?? '';
    $id_padre_form = filter_input(INPUT_POST, 'id_padre', FILTER_VALIDATE_INT); // Obtener id_padre del formulario (oculto)

    // Validaciones básicas
    if (!$id_nino || empty($nombre_nino) || empty($usuario_nino) || empty($contrasena_nino) || !$id_padre_form) {
        header("Location: panel_padres.php?estado=datos_invalidos_#contenedor_ninos_flex");
        exit();
    }

    // 2. Preparar la consulta SQL para actualizar el niño
    $sql_update = "UPDATE ninos SET nombre = ?, usuario = ?, contrasena = ? WHERE id_nino = ? AND id_padre = ?";

    if ($stmt = mysqli_prepare($conexion, $sql_update)) {
        // 'sssii' -> s para nombre, usuario, contrasena (string), i para id_nino, i para id_padre (entero)
        mysqli_stmt_bind_param($stmt, "sssii", $nombre_nino, $usuario_nino, $contrasena_nino, $id_nino, $id_padre_form);

        if (mysqli_stmt_execute($stmt)) {
            // Éxito en la actualización
    header("Location: panel_padres.php?seccion=ninos&estado=exito_editar_perfil#contenedor_ninos_flex");
            exit();
        } else {
            // Error en la ejecución de la consulta
    header("Location: panel_padres.php?seccion=ninos&estado=error_consulta_db#contenedor_ninos_flex");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        // ... Error en la preparación de la consulta ...
        header("Location: panel_padres.php?seccion=ninos&estado=error_db#contenedor_ninos_flex"); 
        exit();
    }
}


// --- LÓGICA PARA CARGAR LOS DATOS DEL NIÑO EXISTENTE (MÉTODO GET) ---
$nino_datos = null;
// Se espera 'id_nino' del parámetro de la URL, consistente con el enlace del botón Modificar.
$id_nino_url = $_GET['id_nino'] ?? '';
$id_nino_a_editar = intval($id_nino_url);

$id_padre_sesion = $_SESSION['id_padre'] ?? 0;

// Solo intentar cargar datos si id_nino_a_editar y id_padre_sesion son válidos
if ($id_nino_a_editar > 0 && $id_padre_sesion > 0) {
    // Prepara la consulta para seleccionar los datos del niño
    $sql_select_nino = "SELECT id_nino, nombre, usuario, contrasena, fecha_creacion, id_padre FROM ninos WHERE id_nino = ? AND id_padre = ?";
    if ($stmt_select = mysqli_prepare($conexion, $sql_select_nino)) {
        // Vincula los parámetros (id_nino y id_padre)
        mysqli_stmt_bind_param($stmt_select, "ii", $id_nino_a_editar, $id_padre_sesion);
        mysqli_stmt_execute($stmt_select);
        $resultado_select = mysqli_stmt_get_result($stmt_select);

        // Si se encuentra una fila, guarda los datos
        if (mysqli_num_rows($resultado_select) == 1) {
            $nino_datos = mysqli_fetch_assoc($resultado_select);
        }
        mysqli_stmt_close($stmt_select);
    }
}

// Si $nino_datos sigue siendo nulo (no se encontró el niño o no hay permisos)
if (!$nino_datos) {
    header("Location: ../panel_padres.php?seccion=ninos&estado=nino_no_encontrado_o_sin_permisos"); // Redirección mejorada
    exit();
}

// Prepara las variables que se usarán para rellenar el formulario HTML
// Asegúrate de usar htmlspecialchars para seguridad al imprimir en HTML
$id_nino_form = htmlspecialchars($nino_datos['id_nino']);
$id_padre_form = htmlspecialchars($nino_datos['id_padre']); // Necesario para el campo oculto en el formulario POST
$nombre_nino_form = htmlspecialchars($nino_datos['nombre']);
$usuario_nino_form = htmlspecialchars($nino_datos['usuario']);
$contrasena_nino_form = htmlspecialchars($nino_datos['contrasena']);
$fecha_creacion_form = htmlspecialchars($nino_datos['fecha_creacion']);

// --- EL CÓDIGO HTML DEL FORMULARIO EMPIEZA AQUÍ ---
?>

<section class="seccion-panel" id="contenedor-form-editar-nino" aria-labelledby="editar-datos-del-niño">
    <h2 id="editar-datos-del-niño">Editar Datos del Niño/a: <?php echo $nombre_nino_form; ?></h2>

    <form method="POST" action="panel_padres.php?seccion=ninos&accion=editar&id_nino=<?= $id_nino_form ?>">
        <input type="hidden" name="id_nino" value="<?php echo $id_nino_form; ?>">
        <input type="hidden" name="id_padre" value="<?php echo $id_padre_form; ?>">

            <label for="nombre_nino" class="form_label">Nombre del Niño/a:</label>
            <input type="text" class="form-control" id="nombre_nino" name="nombre_nino" value="<?php echo $nombre_nino_form; ?>" placeholder="Ingrese Nombre del niño/a" required>

            <label for="usuario_nino" class="form_label">Usuario del Niño/a:</label>
            <input type="text" class="form-control" id="usuario_nino" name="usuario_nino" value="<?php echo $usuario_nino_form; ?>" placeholder="Ingrese usuario del niño/a" required>
        
            <label for="contrasena_nino" class="form_label">Contraseña:</label>
            <input type="text" class="form-control" id="contrasena_nino" name="contrasena_nino" placeholder="Ingrese contraseña del niño/a" value="<?php echo $contrasena_nino_form; ?>" required>
            <!--
            <small class="form-text text-muted">La contraseña se guardará sin cifrado, por favor, tenlo en cuenta.</small>
-->

            <label for="fecha_creacion" class="form_label">Fecha de Creación (este campo no es editable): </label>
            <input type="text" id="fecha_creacion" name="fecha_creacion" value="<?= $fecha_creacion_form ?>" class="form-control" readonly>
<!-- el readonly hace que no se pueda editar este input. Tambien se puede poner solo un <p>...</p>, sin el label. Pero con esta forma
 se mantiene semantica del formulario. -->
        
        <button type="submit" role="button">Guardar Cambios</button>
        <a href="panel_padres.php?seccion=ninos">Cancelar</a>
    </form>
</section>
