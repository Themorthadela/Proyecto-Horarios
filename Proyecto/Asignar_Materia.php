<?php
require_once __DIR__ . '/Requerir_Sesion.php';
require_once __DIR__ . '/Logica/Curso.php';
require_once __DIR__ . '/Logica/Docente.php';
require_once __DIR__ . '/Logica/Materia.php';
require_once __DIR__ . '/Logica/Asignacion.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = null;
$tipoMensaje = 'error';
$cursoSeleccionado = filter_input(INPUT_POST, 'curso', FILTER_VALIDATE_INT) ?: 0;
$docenteSeleccionado = filter_input(INPUT_POST, 'docente', FILTER_VALIDATE_INT) ?: 0;
$materiaSeleccionada = filter_input(INPUT_POST, 'materia', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensaje = 'La solicitud venció. Volvé a intentarlo.';
    } elseif (!$cursoSeleccionado || !$docenteSeleccionado || !$materiaSeleccionada) {
        $mensaje = 'Seleccioná un curso, un docente y una materia válidos.';
    } else {
        try {
            $asignacion = new Asignacion();
            $asignacion->setIdCurso($cursoSeleccionado);
            $asignacion->setIdDocente($docenteSeleccionado);
            $asignacion->setIdMateria($materiaSeleccionada);
            $asignacion->GuardarAsignacion();
            $mensaje = 'Materia asignada correctamente.';
            $tipoMensaje = '';
            $cursoSeleccionado = $docenteSeleccionado = $materiaSeleccionada = 0;
        } catch (DomainException $e) {
            $mensaje = $e->getMessage();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $mensaje = 'No se pudo guardar la asignación. Verificá los datos seleccionados.';
        }
    }
}

$cursos = (new Curso())->ListarCursos();
$docentes = (new Docente())->GradoDocente();
$materias = (new Materia())->ListarMaterias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar materia a docente</title>
</head>
<body>
    <nav><a href="Panel_Admin.php">Volver al panel</a></nav>
    <h1>Asignar materia</h1>
    <?php if ($mensaje): ?>
        <p class="mensaje <?php echo $tipoMensaje; ?>" role="alert"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="curso">Curso:</label>
        <select id="curso" name="curso" required>
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos as $c): ?>
                <option value="<?php echo (int) $c['ID_C']; ?>" <?php echo $cursoSeleccionado === (int) $c['ID_C'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['Nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="docente">Docente:</label>
        <select id="docente" name="docente" required>
            <option value="">Seleccione un docente</option>
            <?php foreach ($docentes as $d): ?>
                <option value="<?php echo (int) $d['ID_D']; ?>" <?php echo $docenteSeleccionado === (int) $d['ID_D'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['Nombre'] . ' ' . $d['Apellido'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="materia">Materia:</label>
        <select id="materia" name="materia" required>
            <option value="">Seleccione una materia</option>
            <?php foreach ($materias as $m): ?>
                <option value="<?php echo (int) $m['ID_M']; ?>" <?php echo $materiaSeleccionada === (int) $m['ID_M'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['Nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Asignar materia">
    </form>
</body>
</html>
