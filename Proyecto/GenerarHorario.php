<?php
include_once __DIR__ . '/Logica/Curso.php';
include_once __DIR__ . '/Persistencia/Horario_FinalBD.php';

$anio = isset($_POST['anio']) ? (int) $_POST['anio'] : (int) date('Y');
$idCurso = isset($_POST['curso']) ? (int) $_POST['curso'] : (isset($_GET['curso']) ? (int) $_GET['curso'] : 0);
$mensaje = '';
$error = '';
$pendientes = [];
$horarios = [];

$curso = new Curso();
$cursos = $curso->ListarCursos();
$horarioBD = new Horario_FinalBD();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'generar') {
    try {
        $pendientes = $horarioBD->GenerarPorCurso($idCurso, $anio);
        $mensaje = $pendientes
            ? 'Se generó un horario parcial. No hubo suficientes bloques para completar toda la carga horaria.'
            : 'Horario generado correctamente.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar horarios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #222; }
        form { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; margin-bottom: 1.5rem; }
        label { display: grid; gap: .35rem; font-weight: bold; }
        select, input, button { padding: .55rem; font: inherit; }
        button { cursor: pointer; background: #1769aa; color: white; border: 0; border-radius: 4px; }
        button.secundario { background: #555; }
        .mensaje { padding: .8rem; margin: 1rem 0; border-radius: 4px; background: #e8f5e9; }
        .error { background: #ffebee; color: #a40000; }
        .advertencia { background: #fff8e1; }
        .tabla { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; min-width: 760px; }
        th, td { border: 1px solid #bbb; padding: .65rem; text-align: center; }
        th { background: #eee; }
        td { height: 3.5rem; }
        small { display: block; margin-top: .25rem; color: #555; }
        nav { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav><a href="index.php">Volver al inicio</a></nav>
    <h1>Generar horario</h1>

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
        <button type="submit" name="accion" value="generar">Generar / regenerar</button>
        <button type="submit" name="accion" value="mostrar" class="secundario">Mostrar horarios</button>
    </form>

    <p>Al regenerar se reemplaza solamente el horario del curso y año elegidos.</p>

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
