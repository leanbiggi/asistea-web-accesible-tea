<?php
session_start();
require_once '../componentes/conexion.php'; // Ajusta la ruta si es necesario

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

if (!isset($_SESSION['id_padre'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada.']);
    exit();
}

$id_padre_sesion = $_SESSION['id_padre'];
$id_actividad = filter_input(INPUT_POST, 'id_actividad', FILTER_VALIDATE_INT);
$orden_pasos_str = filter_input(INPUT_POST, 'orden_pasos', FILTER_SANITIZE_STRING);

if (!$id_actividad || !$orden_pasos_str) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
    exit();
}

$pasos_ids_ordenados = explode(',', $orden_pasos_str);
$success = true;

// Iniciar una transacción para asegurar que todas las actualizaciones se hagan o ninguna
mysqli_begin_transaction($conexion);

foreach ($pasos_ids_ordenados as $index => $paso_id) {
    $numero_paso = $index + 1; // El nuevo número de paso (1-basado)

    // Prepara la consulta para actualizar el numero_paso del paso
    // Es CRÍTICO verificar también el id_actividad y el id_padre para seguridad
    $stmt = mysqli_prepare($conexion, "
        UPDATE pasos p
        JOIN actividades a ON p.id_actividad = a.id_actividad
        SET p.numero_paso = ?
        WHERE p.id_paso = ? AND p.id_actividad = ? AND a.id_padre = ?
    ");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiii", $numero_paso, $paso_id, $id_actividad, $id_padre_sesion);
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
            error_log("Error al actualizar paso " . $paso_id . ": " . mysqli_error($conexion));
            break; // Salir del bucle si hay un error
        }
        mysqli_stmt_close($stmt);
    } else {
        $success = false;
        error_log("Error en la preparación de la consulta: " . mysqli_error($conexion));
        break; // Salir del bucle si hay un error
    }
}

if ($success) {
    mysqli_commit($conexion);
    echo json_encode(['success' => true, 'mensaje' => 'Orden de pasos guardado exitosamente.']);
} else {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el orden de los pasos. Por favor, inténtalo de nuevo.']);
}

mysqli_close($conexion);
?>