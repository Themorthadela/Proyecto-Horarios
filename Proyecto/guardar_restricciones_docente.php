<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');
header('Content-Type: application/json; charset=utf-8');

include_once "Persistencia/Conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $msg = 'Método no permitido';
    error_log($msg);
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$con = new Conexion();
$mysqli = $con->Conectar();

$idDocente = isset($_POST['docente']) ? intval($_POST['docente']) : 0;
$idCurso = isset($_POST['curso']) ? intval($_POST['curso']) : 0;
$datos = isset($_POST['datos']) ? json_decode($_POST['datos'], true) : null;

if (isset($_POST['datos']) && json_last_error() !== JSON_ERROR_NONE) {
    $msg = 'JSON inválido: ' . json_last_error_msg();
    error_log($msg . ' - datos:' . $_POST['datos']);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if (!$idDocente || !$idCurso || !is_array($datos)) {
    $msg = 'Faltan datos o JSON inválido';
    error_log($msg . ' - docente:' . ($_POST['docente'] ?? '') . ' curso:' . ($_POST['curso'] ?? '') . ' datos:' . ($_POST['datos'] ?? ''));
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$selectStmt = $mysqli->prepare("SELECT ID_R FROM Restriccion WHERE ID_D = ? AND ID_C = ? AND Dia = ? AND Hora = ?");
$updateStmt = $mysqli->prepare("UPDATE Restriccion SET Estado = ? WHERE ID_D = ? AND ID_C = ? AND Dia = ? AND Hora = ?");
$insertStmt = $mysqli->prepare("INSERT INTO Restriccion (ID_D, ID_C, Dia, Hora, Estado) VALUES (?, ?, ?, ?, ?)");

if (!$selectStmt || !$updateStmt || !$insertStmt) {
    $msg = 'Error al preparar la consulta: ' . $mysqli->error;
    error_log($msg);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$mysqli->begin_transaction();

foreach ($datos as $d) {
    if (!isset($d['dia'], $d['hora'], $d['estado'])) {
        continue;
    }

    $dia = intval($d['dia']);
    $hora = intval($d['hora']);
    $estado = intval($d['estado']);

    $selectStmt->bind_param('iiii', $idDocente, $idCurso, $dia, $hora);
    if (!$selectStmt->execute()) {
        $msg = 'Error al ejecutar SELECT: ' . $selectStmt->error;
        error_log($msg);
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    $selectStmt->store_result();

    if ($selectStmt->num_rows > 0) {
        $updateStmt->bind_param('iiiii', $estado, $idDocente, $idCurso, $dia, $hora);
        if (!$updateStmt->execute()) {
            $msg = 'Error al ejecutar UPDATE: ' . $updateStmt->error;
            error_log($msg);
            $mysqli->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $msg]);
            exit;
        }
    } elseif ($estado === 1) {
        $insertStmt->bind_param('iiiii', $idDocente, $idCurso, $dia, $hora, $estado);
        if (!$insertStmt->execute()) {
            $msg = 'Error al ejecutar INSERT: ' . $insertStmt->error;
            error_log($msg);
            $mysqli->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $msg]);
            exit;
        }
    }
}

$mysqli->commit();

echo json_encode(['success' => true]);
?>