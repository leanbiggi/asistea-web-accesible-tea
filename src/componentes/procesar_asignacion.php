<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que la ruta sea correcta

// --- Establecer la zona horaria a Argentina (Buenos Aires) ---
date_default_timezone_set('America/Argentina/Buenos_Aires');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recopilar datos del formulario
    $id_nino = filter_input(INPUT_POST, 'nino_id', FILTER_VALIDATE_INT);
    $id_actividad = filter_input(INPUT_POST, 'actividad_id', FILTER_VALIDATE_INT);

    // Campos para la lógica de recurrencia/única
    $es_recurrente = isset($_POST['es_recurrente']) && $_POST['es_recurrente'] == '1' ? 1 : 0;
    $dias_semana = $_POST['dias_semana'] ?? []; // Será un array si se seleccionan múltiples, o vacío
    $fecha_asignada = $_POST['fecha_asignada'] ?? null; // date
    $hora_asignada = $_POST['hora_asignada'] ?? null; // time

    $id_padre_sesion = $_SESSION['id_padre'];

    // 2. Validaciones básicas y de seguridad
    if (!$id_nino || !$id_actividad) {
        header("Location: ../panel_padres.php?error_asignacion=datos_invalidos#caja_formulario_asignar_actividad");
        exit();
    }

    // Verificar que la actividad y el niño pertenecen a este padre por seguridad
    $verificar_actividad = mysqli_query($conexion, "SELECT id_actividad FROM actividades WHERE id_actividad = $id_actividad AND id_padre = $id_padre_sesion");
    $verificar_nino = mysqli_query($conexion, "SELECT id_nino FROM ninos WHERE id_nino = $id_nino AND id_padre = $id_padre_sesion");

    if (mysqli_num_rows($verificar_actividad) == 0 || mysqli_num_rows($verificar_nino) == 0) {
        header("Location: ../panel_padres.php?error_asignacion=permiso_denegado#caja_formulario_asignar_actividad");
        exit();
    }

    // 3. Lógica de validación condicional
    if ($es_recurrente == 1) { // Es una asignación recurrente
        if (empty($dias_semana)) { // Si es recurrente, debe tener al menos un día seleccionado
            header("Location: ../panel_padres.php?error_asignacion=dias_semana_requeridos");
            exit();
        }
        // Para recurrencia, fecha_asignada y hora_asignada son la fecha/hora de inicio
        // Podrían ser nulas si la recurrencia es "a partir de ahora, cada X día"
        // Si no se proporcionan, se insertarán como NULL en la DB, lo cual es válido si el campo es NULLABLE

        $old_dia_semana_field = null; // Este se usará para el campo 'dia_semana' si lo mantienes en la tabla principal y es NULLABLE
    } else { // Es una asignación única (no recurrente)
        // Para una asignación única, fecha_asignada y hora_asignada son obligatorias
        if (empty($fecha_asignada) || empty($hora_asignada)) {
            header("Location: ../panel_padres.php?error_asignacion=fecha_hora_obligatorias_unica");
            exit();
        }
        // Validar que la fecha y HORA no sean en el pasado *****
        $current_datetime = new DateTime(); // Obtiene la fecha y hora actuales del servidor con la zona horaria configurada
        $assigned_datetime = new DateTime($fecha_asignada . ' ' . $hora_asignada); // Crea un objeto DateTime con la fecha y hora asignada

        if ($assigned_datetime < $current_datetime) {
            header("Location: ../panel_padres.php?error_asignacion=fecha_hora_pasada"); // Nuevo tipo de error para mayor claridad
            exit();
        }
        

        $old_dia_semana_field = null;
        $dias_semana = []; // No se insertará nada en la tabla de relación de días
    }

    // 4. Preparar y ejecutar la inserción principal en asignaciones_actividades
    $columns = "id_nino, id_actividad, fecha_creacion, estado, es_recurrente";
    $placeholders = "?, ?, NOW(), 'pendiente', ?";
    $types = "iii"; // int, int, int (id_nino, id_actividad, es_recurrente)
    $params = [$id_nino, $id_actividad, $es_recurrente];


    // Solo añadir 'fecha_asignada' y 'hora_asignada' si no están vacías
    // NOTA: Para una tarea única, ya se validó que no estén vacías.
    if (!empty($fecha_asignada)) {
        $columns .= ", fecha_asignada";
        $placeholders .= ", ?";
        $types .= "s";
        $params[] = $fecha_asignada;
    }

    if (!empty($hora_asignada)) {
        $columns .= ", hora_asignada";
        $placeholders .= ", ?";
        $types .= "s";
        $params[] = $hora_asignada;
    }

    $query_insert_main = "INSERT INTO asignaciones_actividades ($columns) VALUES ($placeholders)";

    if ($stmt_main = mysqli_prepare($conexion, $query_insert_main)) {
        mysqli_stmt_bind_param($stmt_main, $types, ...$params);

        if (mysqli_stmt_execute($stmt_main)) {
            $id_asignacion_nueva = mysqli_insert_id($conexion); // Obtener el ID de la asignación principal

            // 5. Si es recurrente, insertar días en la tabla de relación
            if ($es_recurrente == 1 && !empty($dias_semana)) {
                $query_insert_dias = "INSERT INTO asignacion_recurrencia_dias (id_asignacion, dia_semana) VALUES (?, ?)";
                if ($stmt_dias = mysqli_prepare($conexion, $query_insert_dias)) {
                    foreach ($dias_semana as $dia) {
                        mysqli_stmt_bind_param($stmt_dias, "is", $id_asignacion_nueva, $dia);
                        mysqli_stmt_execute($stmt_dias);
                        // Considerar manejo de errores si una inserción de día falla
                    }
                    mysqli_stmt_close($stmt_dias);
                } else {
                    // Fallo al preparar la inserción de días
                    // Podrías registrar un error y/o redirigir
                    error_log("Error al preparar inserción de días: " . mysqli_error($conexion));
                    header("Location: ../panel_padres.php?error_asignacion=prepare_dias_error");
                    exit();
                }
            }

            // Éxito en la asignación
            header("Location: ../panel_padres.php?seccion=asignaciones&estado=exito_en_la_asignacion");
            // no le pongo el ancla #contenedor_asignaciones porque no me deja hacer foco con javascript donde yo quiero.
exit();
            exit();

        } else {
            // Error en la inserción principal
            error_log("Error en inserción principal: " . mysqli_error($conexion));
            header("Location: ../panel_padres.php?error_asignacion=db_error&sql_error=" . urlencode(mysqli_error($conexion)));
            exit();
        }
        mysqli_stmt_close($stmt_main);
    } else {
        // Error al preparar la consulta principal
        error_log("Error al preparar consulta principal: " . mysqli_error($conexion));
        header("Location: ../panel_padres.php?error_asignacion=prepare_error&sql_error=" . urlencode(mysqli_error($conexion)));
        exit();
    }
} else {
    header("Location: ../panel_padres.php?error_acceso=true");
    exit();
}
?>