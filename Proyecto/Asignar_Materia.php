<?php
require_once __DIR__ . '/Requerir_Sesion.php';
require_once __DIR__ . '/Logica/Curso.php';
require_once __DIR__ . '/Logica/Docente.php';
require_once __DIR__ . '/Logica/Asignacion.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = null;
$tipoMensaje = 'error';
$cursoSeleccionado = filter_input(INPUT_POST, 'curso', FILTER_VALIDATE_INT) ?: 0;
$docenteSeleccionado = filter_input(INPUT_POST, 'docente', FILTER_VALIDATE_INT) ?: 0;
$materiaSeleccionada = filter_input(INPUT_POST, 'materia', FILTER_VALIDATE_INT) ?: 0;
$curso = new Curso();
$cursos = $curso->ListarCursos();
$docentes = (new Docente())->GradoDocente();
$materiasPorCurso = [];

foreach ($cursos as $itemCurso) {
    $idCurso = (int) $itemCurso['ID_C'];
    $materiasPorCurso[$idCurso] = array_map(static function ($materia) {
        return [
            'id' => (int) $materia['ID_M'],
            'nombre' => $materia['Nombre'],
        ];
    }, $curso->ListarMateriasPorCurso($idCurso));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensaje = 'La solicitud venció. Volvé a intentarlo.';
    } elseif (!$cursoSeleccionado || !$docenteSeleccionado || !$materiaSeleccionada) {
        $mensaje = 'Seleccioná un curso, un docente y una materia válidos.';
    } elseif (!in_array($materiaSeleccionada, array_column($materiasPorCurso[$cursoSeleccionado] ?? [], 'id'), true)) {
        $mensaje = 'La materia seleccionada no pertenece al curso.';
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
        <select id="materia" name="materia" required disabled>
            <option value="">Primero seleccione un curso</option>
        </select>
        <small id="estadoMaterias" class="ayuda" aria-live="polite"></small>
        <input type="submit" value="Asignar materia">
    </form>
    <script>
        const materiasPorCurso = <?php echo json_encode($materiasPorCurso, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
        const curso = document.getElementById('curso');
        const materia = document.getElementById('materia');
        const estadoMaterias = document.getElementById('estadoMaterias');
        const materiaSeleccionada = <?php echo json_encode($materiaSeleccionada); ?>;

        function actualizarMaterias(conservarSeleccion = false) {
            const idCurso = curso.value;
            const materias = materiasPorCurso[idCurso] || [];
            materia.innerHTML = '';

            if (!idCurso) {
                materia.add(new Option('Primero seleccione un curso', ''));
                materia.disabled = true;
                estadoMaterias.textContent = '';
                return;
            }

            if (materias.length === 0) {
                materia.add(new Option('Este curso no tiene materias cargadas', ''));
                materia.disabled = true;
                estadoMaterias.textContent = 'Agregá materias al plan del curso antes de realizar una asignación.';
                return;
            }

            materia.add(new Option('Seleccione una materia', ''));
            materias.forEach(item => materia.add(new Option(item.nombre, item.id)));
            materia.disabled = false;
            materia.value = conservarSeleccion ? String(materiaSeleccionada) : '';
            estadoMaterias.textContent = `${materias.length} materia${materias.length === 1 ? '' : 's'} disponible${materias.length === 1 ? '' : 's'} para este curso.`;
        }

        curso.addEventListener('change', () => actualizarMaterias(false));
        actualizarMaterias(true);
    </script>
</body>
</html>
