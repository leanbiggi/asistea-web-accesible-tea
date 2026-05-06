<?php
session_start();
// La ruta a conexion.php es directa porque está en la misma carpeta 'componentes/'
include("conexion.php");

// 1. Verificar si el usuario es un administrador
if (!isset($_SESSION['admin'])) {
    // Si no es admin, redirigir a la página de admin (subiendo un nivel)
    header("Location: ../admin_panel.php");
    exit(); // Es crucial usar exit() después de un header Location
}

// 2. Verificar si se recibió un ID de acción válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no hay ID o no es numérico, redirigir al panel de administración (subiendo un nivel)
    header("Location: ../admin_panel.php");
    exit(); // Es crucial usar exit() después de un header Location
}

// Sanitizamos el ID y lo convertimos a entero
// NOTA: Esta forma de concatenar el ID directamente en la consulta es VULNERABLE a inyección SQL.
// Para un entorno de desarrollo puede ser aceptable, pero NUNCA en producción.
$id_accion = intval($_GET['id']);

// 3. Cargar los datos de la acción desde la base de datos
// Inicializamos la variable a null. Será un array asociativo si la acción se encuentra.
$array_accion_a_modificar = null; 

// Realizamos la consulta a la base de datos
$consulta_sql = "SELECT titulo, texto, imagen_ruta FROM acciones WHERE id_accion = $id_accion";
$resultado = mysqli_query($conexion, $consulta_sql);

// 4. Verificar si la consulta se ejecutó correctamente y si se encontró la acción
if ($resultado && mysqli_num_rows($resultado) === 1) {
    $array_accion_a_modificar = mysqli_fetch_assoc($resultado);
} else {
    // Si la acción no fue encontrada o hubo un error en la consulta
    // Redirigimos al panel principal, ya que no podemos mostrar el formulario sin datos válidos
    header("Location: ../admin_panel.php");
    exit(); // Es crucial usar exit() después de un header Location
}

// 5. Inicializar variables para el formulario con los datos cargados
// En este punto, sabemos que $array_accion_a_modificar contiene los datos de la DB
$titulo = htmlspecialchars($array_accion_a_modificar['titulo']);
$texto = htmlspecialchars($array_accion_a_modificar['texto']);
$imagen_actual_ruta = htmlspecialchars($array_accion_a_modificar['imagen_ruta']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Acción - LINA Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="../estilos_admin_panel.css"> 
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Modificar Acción</h2>

        <?php if (isset($_GET['ok_modificar'])): ?>
            <div class="alert alert-success" role="alert">Acción modificada con éxito.</div>
        <?php endif; ?>

        <form action="procesar_modificacion.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_accion" value="<?php echo htmlspecialchars($id_accion); ?>">
            
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $titulo; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="texto" class="form-label">Texto Breve</label>
                <textarea class="form-control" id="texto" name="texto" rows="5" required><?php echo $texto; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Imagen Actual:</label><br>
                <?php if (!empty($imagen_actual_ruta)): ?>
                    <img src="../<?php echo $imagen_actual_ruta; ?>" alt="Imagen Actual" class="img-thumbnail" style="max-width: 200px; height: auto;">
                    <p class="form-text mt-2">Ruta: <?php echo $imagen_actual_ruta; ?></p>
                    <input type="hidden" name="imagen_actual_ruta" value="<?php echo $imagen_actual_ruta; ?>">
                <?php else: ?>
                    <p class="text-muted">No hay imagen actual.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="nueva_imagen" class="form-label">Cambiar Imagen (opcional)</label>
                <input type="file" class="form-control" id="nueva_imagen" name="nueva_imagen" accept="image/*">
                <p class="form-text">Dejar en blanco para mantener la imagen actual.</p>
            </div>
            
            <button type="submit" class="btn btn-success me-2">Guardar Cambios</button>
            <a href="../admin_panel.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php
mysqli_close($conexion);
?>