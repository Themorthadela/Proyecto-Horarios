<?php
require_once __DIR__ . '/Requerir_Sesion.php';
include_once __DIR__ . '/Logica/Curso.php';
include_once __DIR__ . '/Persistencia/Horario_FinalBD.php';

if (!in_array($_SESSION['usuario']['rol'] ?? '', ['Admin', 'Secretaria', 'Director'], true)) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?error=rol');
    exit;
}

$anio = isset($_POST['anio']) ? (int) $_POST['anio'] : (int) date('Y');
$idCurso = isset($_POST['curso']) ? (int) $_POST['curso'] : (isset($_GET['curso']) ? (int) $_GET['curso'] : 0);
$mensaje = '';
$error = '';
$pendientes = [];
$horarios = [];

$curso = new Curso();
$cursos = $curso->ListarCursos();
$horarioBD = new Horario_FinalBD();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'mostrar' && $idCurso <= 0) {
    $error = 'Seleccione un curso para mostrar su horario.';
}

if ($idCurso > 0) {
    try {
        $horarios = $horarioBD->ListarPorCurso($idCurso, $anio);
    } catch (Throwable $e) {
        $error = $error ?: $e->getMessage();
    }
}

$dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
$grilla = [];
$franjas = [];
foreach ($horarios as $fila) {
    $franja = substr($fila['HoraEntrada'], 0, 5) . ' - ' . substr($fila['HoraSalida'], 0, 5);
    $franjas[$franja] = $fila['HoraEntrada'];
    $grilla[$franja][(int) $fila['Dia']] = $fila;
}
asort($franjas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar horarios</title>
</head>
<body>
    <h1>Mostrar horario</h1>

    <form method="post">
        <label>
            Curso
            <select name="curso" required>
                <option value="">Seleccione un curso</option>
                <?php foreach ($cursos as $item): ?>
                    <option value="<?= (int) $item['ID_C'] ?>" <?= $idCurso === (int) $item['ID_C'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Año lectivo
            <input type="number" name="anio" min="2000" max="2100" value="<?= $anio ?>" required>
        </label>
        <button type="submit" name="accion" value="mostrar" class="secundario">Mostrar horarios</button>
    </form>

    <a href="Cerrar_Sesion.php">Cerrar sesión</a></header>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $pendientes ? 'advertencia' : '' ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($pendientes): ?>
        <ul>
            <?php foreach ($pendientes as $pendiente): ?>
                <li><?= htmlspecialchars($pendiente, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($idCurso > 0 && !$horarios && !$error): ?>
        <p>Todavía no hay un horario generado para este curso y año.</p>
    <?php elseif ($horarios): ?>
        <h2>Horario <?= $anio ?></h2>
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <?php for ($dia = 1; $dia <= 5; $dia++): ?>
                            <th><?= $dias[$dia] ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($franjas as $franja => $orden): ?>
                        <tr>
                            <th><?= htmlspecialchars($franja, ENT_QUOTES, 'UTF-8') ?></th>
                            <?php for ($dia = 1; $dia <= 5; $dia++): ?>
                                <td>
                                    <?php if (isset($grilla[$franja][$dia])): $celda = $grilla[$franja][$dia]; ?>
                                        <?= htmlspecialchars($celda['Materia'], ENT_QUOTES, 'UTF-8') ?>
                                        <small><?= htmlspecialchars($celda['Docente'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>
