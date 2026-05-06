<?php
session_start();
require_once 'conexion.php';

$mensaje = ''; // Variable para almacenar mensajes para la URL de redirección
$id_actividad_redireccion = 0; // Para asegurar una redirección segura

// Verificar si se recibió el id_paso y el id_actividad por GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_paso']) && isset($_GET['id_actividad'])) {
    $id_paso_a_eliminar = intval($_GET['id_paso']);
    $id_actividad_redireccion = intval($_GET['id_actividad']);

    if ($id_paso_a_eliminar > 0 && $id_actividad_redireccion > 0) {
        // Iniciar una transacción
        mysqli_begin_transaction($conexion);

        try {
            // 1. Obtener el numero_paso del paso que se va a eliminar
            $stmt_get_numero = mysqli_prepare($conexion, "SELECT numero_paso, id_actividad FROM pasos WHERE id_paso = ?");
            if (!$stmt_get_numero) {
                throw new Exception('Error al preparar la consulta para obtener numero_paso: ' . mysqli_error($conexion));
            }
            mysqli_stmt_bind_param($stmt_get_numero, "i", $id_paso_a_eliminar);
            mysqli_stmt_execute($stmt_get_numero);
            $result_get_numero = mysqli_stmt_get_result($stmt_get_numero);
            $paso_info = mysqli_fetch_assoc($result_get_numero);
            mysqli_stmt_close($stmt_get_numero);

            if (!$paso_info) {
                throw new Exception('El paso no fue encontrado o ya ha sido eliminado.');
            }

            $numero_paso_eliminado = $paso_info['numero_paso'];
            $id_actividad_del_paso = $paso_info['id_actividad'];

            // 2. Eliminar el paso
            $stmt_delete = mysqli_prepare($conexion, "DELETE FROM pasos WHERE id_paso = ? AND id_actividad = ?");
            if (!$stmt_delete) {
                throw new Exception('Error al preparar la consulta de eliminación: ' . mysqli_error($conexion));
            }
            mysqli_stmt_bind_param($stmt_delete, "ii", $id_paso_a_eliminar, $id_actividad_del_paso);
            if (!mysqli_stmt_execute($stmt_delete)) {
                throw new Exception('Error al ejecutar la consulta de eliminación: ' . mysqli_error($conexion));
            }

            if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                mysqli_stmt_close($stmt_delete);

                // 3. Actualizar los numero_paso de los pasos subsiguientes
                $stmt_update_order = mysqli_prepare($conexion, "UPDATE pasos SET numero_paso = numero_paso - 1 WHERE id_actividad = ? AND numero_paso > ?");
                if (!$stmt_update_order) {
                    throw new Exception('Error al preparar la consulta de reordenamiento: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($stmt_update_order, "ii", $id_actividad_del_paso, $numero_paso_eliminado);
                if (!mysqli_stmt_execute($stmt_update_order)) {
                    throw new Exception('Error al ejecutar la consulta de reordenamiento: ' . mysqli_error($conexion));
                }
                mysqli_stmt_close($stmt_update_order);

                mysqli_commit($conexion);
                // Redirección de éxito
                header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=exito_eliminar_paso");
                exit();

            } else {
                mysqli_rollback($conexion);
                // Redirección si no se encontró el paso
                header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
                exit();
            }
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            error_log("Error al eliminar paso: " . $e->getMessage());
            // Redirección de error
            header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
            exit();
        }
    } else {
        // Redirección si los IDs son inválidos
        header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
        exit();
    }
} else {
    // Redirección si faltan parámetros
    header("Location: ../panel_padres.php?seccion=actividades&accion=pasos&id_actividad=" . $id_actividad_redireccion . "&estado=error_paso");
    exit();
}

mysqli_close($conexion);
?>