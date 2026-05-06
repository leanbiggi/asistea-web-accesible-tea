<?php
// Este archivo contiene la lógica para listar y paginar las acciones.

require_once 'conexion.php'; 

// --- Lógica de Paginación y Filtro ---
$acciones_por_pagina = 10; 

// Obtener el número de página actual de la URL
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// Obtener el valor del filtro de la URL
$filtro = isset($_GET['filtro']) ? trim($_GET['filtro']) : ''; // Usamos trim para eliminar espacios en blanco

// Construir la cláusula WHERE para el filtro (si existe)
$where_clause = '';
if (!empty($filtro)) {
    // Usamos LIKE %...% para buscar coincidencias parciales en el título
    // Y mysqli_real_escape_string para escapar el filtro y prevenir inyección SQL
    $filtro_escaped = mysqli_real_escape_string($conexion, $filtro);
    $where_clause = " WHERE titulo LIKE '%" . $filtro_escaped . "%'";
}

// Consulta para obtener el total de acciones (considerando el filtro)
$consulta_total_acciones = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM acciones" . $where_clause);
$fila_total = mysqli_fetch_assoc($consulta_total_acciones);
$total_acciones = $fila_total['total'];
$total_paginas = ceil($total_acciones / $acciones_por_pagina);

// --- AQUI SE DEBE AÑADIR LA LOGICA DE CALCULO DE LAS VARIABLES DE Paginación ---
// Aseguramos que se calculen SIEMPRE que se incluya este archivo
$num_links_to_show = 5; 
$start_page = max(1, $pagina_actual - floor($num_links_to_show / 2));
$end_page = min($total_paginas, $pagina_actual + floor($num_links_to_show / 2));

// Ajustar start_page y end_page si están cerca de los límites
// Esto asegura que siempre se muestren $num_links_to_show si es posible
if ($end_page - $start_page + 1 < $num_links_to_show) {
    $start_page = max(1, $end_page - $num_links_to_show + 1);
}
if ($end_page - $start_page + 1 < $num_links_to_show) {
    $end_page = min($total_paginas, $start_page + $num_links_to_show - 1);
}


// Calcular el offset (desde dónde empezar a traer registros)
$offset = ($pagina_actual - 1) * $acciones_por_pagina;

// Consulta para obtener las acciones para la página actual (considerando el filtro)
$sql_acciones = "SELECT id_accion, titulo, texto, imagen_ruta FROM acciones" . $where_clause . " LIMIT $acciones_por_pagina OFFSET $offset";
$resultado_acciones = mysqli_query($conexion, $sql_acciones);

$acciones_listado = [];
if ($resultado_acciones) {
    while ($fila_accion = mysqli_fetch_assoc($resultado_acciones)) {
        $acciones_listado[] = $fila_accion;
    }
} else {
    error_log("Error al cargar las acciones: " . mysqli_error($conexion));
}

mysqli_close($conexion);
?>