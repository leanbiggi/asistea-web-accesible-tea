<?php
session_start();
require_once 'conexion.php'; // Incluye el archivo de conexión

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_asignacion = $_POST['id_asignacion'] ?? null;

    if (!empty($id_asignacion) && is_numeric($id_asignacion)) {
        $id_asignacion = intval($id_asignacion);

        // Opcional: Asegurarse de que el niño logueado sea el que tiene la asignación
        // $id_nino_sesion = $_SESSION['nino_id'] ?? null;
        // if ($id_nino_sesion === null) {
        //     $response['message'] = 'Niño no logueado.';
        //     echo json_encode($response);
        //     exit();
        // }
        // $stmt_check = $conexion->prepare("SELECT id_nino FROM asignaciones_actividades WHERE id_asignacion = ?");
        // $stmt_check->bind_param("i", $id_asignacion);
        // $stmt_check->execute();
        // $result_check = $stmt_check->get_result();
        // $asignacion_data = $result_check->fetch_assoc();
        // $stmt_check->close();

        // if ($asignacion_data['id_nino'] !== $id_nino_sesion) {
        //     $response['message'] = 'No autorizado para modificar esta asignación.';
        //     echo json_encode($response);
        //     exit();
        // }


        // Actualizar el estado de la asignación en la base de datos
        // Solo si el estado actual es 'pendiente' o 'en_progreso'
        $stmt = $conexion->prepare("UPDATE asignaciones_actividades SET estado = 'completada', fecha_completado = NOW() WHERE id_asignacion = ? AND (estado = 'pendiente' OR estado = 'en_progreso')");
        
        if ($stmt) {
            $stmt->bind_param("i", $id_asignacion);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Actividad marcada como completada.';
                } else {
                    $response['message'] = 'La asignación no pudo ser actualizada (quizás ya estaba completada o no existe o el estado no era pendiente/en_progreso).';
                }
            } else {
                $response['message'] = 'Error al ejecutar la actualización: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error en la preparación de la consulta: ' . $conexion->error;
        }
    } else {
        $response['message'] = 'ID de asignación no válido o faltante.';
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conexion->close();
echo json_encode($response);
?>