<?php
require_once __DIR__ . '/Requerir_Sesion.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', 'php://stderr');

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/Persistencia/Conexion.php';

function responderJson($success, $error = null, $codigoHttp = 200)
{
    http_response_code($codigoHttp);

    $respuesta = ['success' => $success];

    if ($error !== null) {
        $respuesta['error'] = $error;
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

$idCurso = isset($_POST['curso']) ? (int) $_POST['curso'] : 0;
$datosJson = $_POST['datos'] ?? '';
$datos = json_decode($datosJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responderJson(false, 'JSON inválido: ' . json_last_error_msg(), 400);
}

if ($idCurso <= 0 || !is_array($datos)) {
    responderJson(false, 'Datos incompletos.', 400);
}

$con = new Conexion();
$mysqli = $con->Conectar();

$selectStmt = null;
$updateStmt = null;
$insertStmt = null;

try {
    $selectStmt = $mysqli->prepare("\n        SELECT ID_Rc\n        FROM Restriccion_Curso\n        WHERE ID_C = ?\n          AND Dia = ?\n          AND HoraEntrada = ?\n          AND HoraSalida = ?\n        LIMIT 1\n    ");

    $updateStmt = $mysqli->prepare("\n        UPDATE Restriccion_Curso\n        SET Disponible = ?\n        WHERE ID_C = ?\n          AND Dia = ?\n          AND HoraEntrada = ?\n          AND HoraSalida = ?\n    ");

    $insertStmt = $mysqli->prepare("\n        INSERT INTO Restriccion_Curso\n            (ID_C, Dia, HoraEntrada, HoraSalida, Disponible)\n        VALUES\n            (?, ?, ?, ?, ?)\n    ");

    if (!$selectStmt || !$updateStmt || !$insertStmt) {
        throw new Exception('Error al preparar las consultas: ' . $mysqli->error);
    }

    $mysqli->begin_transaction();

    foreach ($datos as $indice => $fila) {
        if (
            !isset($fila['dia'], $fila['hora_I'], $fila['hora_F'], $fila['activo'])
        ) {
            throw new Exception('El bloque número ' . ($indice + 1) . ' está incompleto.');
        }

        $dia = (int) $fila['dia'];
        $horaEntrada = trim((string) $fila['hora_I']);
        $horaSalida = trim((string) $fila['hora_F']);
        $disponible = (int) $fila['activo'];

        if ($dia < 1 || $dia > 7) {
            throw new Exception('Día inválido en el bloque número ' . ($indice + 1) . '.');
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $horaEntrada) || !preg_match('/^\d{2}:\d{2}$/', $horaSalida)) {
            throw new Exception('Formato de hora inválido en el bloque número ' . ($indice + 1) . '.');
        }

        $disponible = ($disponible === 1) ? 1 : 0;

        $selectStmt->bind_param(
            'iiss',
            $idCurso,
            $dia,
            $horaEntrada,
            $horaSalida
        );

        if (!$selectStmt->execute()) {
            throw new Exception($selectStmt->error);
        }

        $selectStmt->store_result();
        $existe = $selectStmt->num_rows > 0;
        $selectStmt->free_result();

        if ($existe) {
            $updateStmt->bind_param(
                'iiiss',
                $disponible,
                $idCurso,
                $dia,
                $horaEntrada,
                $horaSalida
            );

            if (!$updateStmt->execute()) {
                throw new Exception($updateStmt->error);
            }
        } else {
            $insertStmt->bind_param(
                'iissi',
                $idCurso,
                $dia,
                $horaEntrada,
                $horaSalida,
                $disponible
            );

            if (!$insertStmt->execute()) {
                throw new Exception($insertStmt->error);
            }
        }
    }

    $mysqli->commit();
    responderJson(true);
} catch (Throwable $e) {
    if ($mysqli->errno === 0 || $mysqli->ping()) {
        $mysqli->rollback();
    }

    responderJson(false, $e->getMessage(), 500);
} finally {
    if ($selectStmt instanceof mysqli_stmt) {
        $selectStmt->close();
    }

    if ($updateStmt instanceof mysqli_stmt) {
        $updateStmt->close();
    }

    if ($insertStmt instanceof mysqli_stmt) {
        $insertStmt->close();
    }

    $con->Desconectar();
}
