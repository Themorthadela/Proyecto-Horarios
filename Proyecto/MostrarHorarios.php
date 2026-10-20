<?php
session_start();
if (isset($_SESSION['usuario'])) {
    require_once __DIR__ . '/Requerir_Sesion.php';
}
include_once __DIR__ . '/Logica/Curso.php';
include_once __DIR__ . '/Persistencia/Horario_FinalBD.php';

$rol = $_SESSION['usuario']['rol'] ?? '';
$mostrarDocentes = in_array($rol, ['Admin', 'Secretaria', 'Director'], true);
$esDocente = $rol === 'Docente';
$idDocente = $mostrarDocentes ? max(0, (int) ($_POST['docente'] ?? $_GET['docente'] ?? 0)) : 0;
$docentes = [];
if ($mostrarDocentes) {
    include_once __DIR__ . '/Logica/Docente.php';
    $docentes = (new Docente())->ListarDocentes();
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


if (!$esDocente && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'mostrar' && $idCurso <= 0 && $idDocente <= 0) {
    $error = $mostrarDocentes ? 'Seleccioná un curso o un docente para mostrar sus horarios.' : 'Seleccione un curso para mostrar su horario.';
}

if ($idCurso > 0 || $esDocente || $idDocente > 0) {
    try {
        $horarios = $horarioBD->ListarVisiblesPorCurso($idCurso, $anio, $_SESSION['usuario'] ?? [], $idDocente);
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
    $grilla[$franja][(int) $fila['Dia']][] = $fila;
}
asort($franjas);
$ultimoDia = max(array_merge([5], array_map(static fn ($fila) => (int) $fila['Dia'], $horarios)));
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
    <?php if ($mostrarDocentes): ?>
        <nav><a href="Panel_Admin.php">Volver al panel</a></nav>
    <?php endif; ?>
    <header class="horarios-header">
        <div>
            <span class="horarios-eyebrow">Consulta de horarios</span>
            <h1><?= $esDocente ? 'Mis horarios' : 'Mostrar horario' ?></h1>
            <p><?= $esDocente ? 'Estos son tus horarios. Podés filtrar por curso y año lectivo.' : ($mostrarDocentes ? 'Seleccioná un docente para ver sus horarios de todos los cursos, o filtrá por curso y año lectivo.' : 'Seleccioná un curso y el año lectivo.') ?></p>
        </div>
        <?php if (isset($_SESSION['usuario'])): ?>
        <a class="boton-cerrar-sesion" href="Cerrar_Sesion.php" aria-label="Cerrar la sesión actual">
            <span aria-hidden="true">→</span>
            Cerrar sesión
        </a>
        <?php else: ?>
        <a href="index.php">Iniciar sesión</a>
        <?php endif; ?>
    </header>

    <form method="post">
        <?php if ($mostrarDocentes): ?>
        <label>
            Docente
            <select name="docente">
                <option value="">Todos los docentes</option>
                <?php foreach ($docentes as $docente): ?>
                    <option value="<?= (int) $docente['ID_D'] ?>" <?= $idDocente === (int) $docente['ID_D'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($docente['Nombre'] . ' ' . $docente['Apellido'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php endif; ?>
        <label>
            Curso
            <select name="curso" <?= ($esDocente || $mostrarDocentes) ? '' : 'required' ?>>
                <option value=""><?= ($esDocente || $mostrarDocentes) ? 'Todos los cursos' : 'Seleccione un curso' ?></option>
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

    <?php if (($idCurso > 0 || $esDocente || $idDocente > 0) && !$horarios && !$error): ?>
        <p><?= $esDocente ? 'No tenés horarios asignados para este curso y año.' : 'No hay horarios para los filtros seleccionados.' ?></p>
    <?php elseif ($horarios): ?>
        <h2>Horario <?= $anio ?></h2>
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <?php for ($dia = 1; $dia <= $ultimoDia; $dia++): ?>
                            <th><?= $dias[$dia] ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($franjas as $franja => $orden): ?>
                        <tr>
                            <th><?= htmlspecialchars($franja, ENT_QUOTES, 'UTF-8') ?></th>
                            <?php for ($dia = 1; $dia <= $ultimoDia; $dia++): ?>
                                <td>
                                    <?php foreach ($grilla[$franja][$dia] ?? [] as $celda): ?>
                                        <?php if ($esDocente || $idDocente > 0): ?>
                                            <strong><?= htmlspecialchars($celda['Curso'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($celda['Materia'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($mostrarDocentes): ?>
                                            <small><?= htmlspecialchars($celda['Docente'], ENT_QUOTES, 'UTF-8') ?></small>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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
