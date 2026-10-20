<?php
require_once __DIR__ . '/../Requerir_Sesion.php';
require_once __DIR__ . '/../Logica/Curso.php';
require_once __DIR__ . '/../Logica/Materia.php';

if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$materiasDisponibles = (new Materia())->ListarMaterias();
$mensaje = null;
$tipoMensaje = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new DomainException('La solicitud venció. Volvé a intentarlo.');
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $horas = filter_var($_POST['horas'] ?? null, FILTER_VALIDATE_INT);
        $dias = filter_var($_POST['dias'] ?? null, FILTER_VALIDATE_INT);
        $semanal = filter_var($_POST['semanal'] ?? null, FILTER_VALIDATE_INT);
        $duracion = filter_var($_POST['duracion'] ?? null, FILTER_VALIDATE_INT);
        $entrada = $_POST['hora_entrada'] ?? '';
        $salida = $_POST['hora_salida'] ?? '';
        $turno = $_POST['turno'] ?? '';
        if ($nombre === '' || $horas < 1 || !in_array($dias, [5, 6], true) || $semanal < 1 || $duracion < 1 || $duracion > 10) {
            throw new DomainException('Revisá los datos generales del curso.');
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $entrada) || !preg_match('/^\d{2}:\d{2}$/', $salida) || $entrada >= $salida || !in_array($turno, ['Matutino', 'Vespertino', 'Nocturno'], true)) {
            throw new DomainException('Revisá el turno y las horas de entrada y salida.');
        }

        $plan = [];
        $ids = $_POST['materia_id'] ?? [];
        $anios = $_POST['materia_anio'] ?? [];
        $periodos = $_POST['materia_periodo'] ?? [];
        $permitidos = ['Anual', 'Semestre 1', 'Semestre 2'];
        $vistos = [];
        foreach ($ids as $indice => $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            $anio = filter_var($anios[$indice] ?? null, FILTER_VALIDATE_INT);
            $periodo = $periodos[$indice] ?? '';
            if (!$id || !$anio || $anio > $duracion || !in_array($periodo, $permitidos, true)) {
                throw new DomainException('Hay una materia con año o modalidad inválidos.');
            }
            $clave = $id . '-' . $anio . '-' . $periodo;
            if (isset($vistos[$clave])) { throw new DomainException('Una materia está repetida en el mismo año y período.'); }
            $vistos[$clave] = true;
            $plan[] = ['id' => $id, 'anio' => $anio, 'periodo' => $periodo];
        }

        $curso = new Curso();
        $curso->SetNombre($nombre); $curso->SetHoras($horas); $curso->SetDias($dias);
        $curso->SetSemanal($semanal); $curso->SetDuracion($duracion);
        $curso->AgregarCursoCompleto(['entrada' => $entrada, 'salida' => $salida, 'turno' => $turno], $plan);
        $mensaje = 'Curso y plan de materias creados correctamente.';
        $tipoMensaje = '';
        $_POST = [];
    } catch (DomainException $e) {
        $mensaje = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $mensaje = 'No se pudo crear el curso. Verificá que la base de datos esté disponible.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../Estilo/General.css">
    <link rel="stylesheet" href="../Estilo/Formularios.css">
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear curso</title>
</head>
<body>
<nav><a href="../Panel_Admin.php">Volver al panel</a></nav>
<h1>Crear curso</h1>
<?php if ($mensaje): ?><p class="mensaje <?= $tipoMensaje ?>" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" id="formCurso">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <label>Nombre<input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label>Duración del curso (años)<input type="number" id="duracion" name="duracion" min="1" max="10" value="<?= (int) ($_POST['duracion'] ?? 3) ?>" required></label>
    <label>Horas diarias<input type="number" name="horas" min="1" value="<?= (int) ($_POST['horas'] ?? '') ?>" required></label>
    <label>Días<select name="dias"><option value="5">Lunes a viernes</option><option value="6">Lunes a sábado</option></select></label>
    <label>Horas semanales<input type="number" name="semanal" min="1" value="<?= (int) ($_POST['semanal'] ?? '') ?>" required></label>
    <label>Turno<select name="turno"><option>Matutino</option><option>Vespertino</option><option>Nocturno</option></select></label>
    <label>Hora de entrada<input type="time" name="hora_entrada" required></label>
    <label>Hora de salida<input type="time" name="hora_salida" required></label>

    <section class="plan-estudios">
        <div class="plan-encabezado"><div><h2>Plan de materias</h2><p>Agregá las materias y elegí en qué año se dictan y si son anuales o semestrales.</p></div><button type="button" id="agregarMateria">Agregar materia</button></div>
        <div class="tabla"><table><thead><tr><th>Materia</th><th>Año</th><th>Modalidad</th><th>Acción</th></tr></thead><tbody id="filasMaterias"></tbody></table></div>
        <p id="sinMaterias" class="ayuda">Todavía no agregaste materias. Podés crear el curso igualmente y completar el plan después.</p>
    </section>
    <input type="submit" value="Crear curso">
</form>

<template id="plantillaMateria"><tr>
    <td class="selector-materia"><input type="search" class="buscar-materia" placeholder="Buscar por nombre parecido" autocomplete="off" aria-label="Buscar materia"><select name="materia_id[]" class="materia-id" required><option value="">Seleccioná una materia</option></select><small class="resultado-busqueda" aria-live="polite"></small></td>
    <td><select name="materia_anio[]" class="selector-anio" required></select></td>
    <td><select name="materia_periodo[]" required><option value="Anual">Anual</option><option value="Semestre 1">Semestre 1</option><option value="Semestre 2">Semestre 2</option></select></td>
    <td><button type="button" class="quitar-materia secundario" aria-label="Quitar materia">Quitar</button></td>
</tr></template>
<script>
const duracion = document.getElementById('duracion');
const filas = document.getElementById('filasMaterias');
const vacio = document.getElementById('sinMaterias');
const materias = <?= json_encode(array_map(static function ($m) { return ['id' => (int) $m['ID_M'], 'nombre' => $m['Nombre'], 'carga' => (int) $m['CargaHoraria']]; }, $materiasDisponibles), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
function normalizar(texto) { return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim(); }
function distancia(a, b) {
    const fila = Array.from({length: b.length + 1}, (_, i) => i);
    for (let i = 1; i <= a.length; i++) {
        let anterior = fila[0]; fila[0] = i;
        for (let j = 1; j <= b.length; j++) {
            const temporal = fila[j];
            fila[j] = Math.min(fila[j] + 1, fila[j - 1] + 1, anterior + (a[i - 1] === b[j - 1] ? 0 : 1));
            anterior = temporal;
        }
    }
    return fila[b.length];
}
function coincide(nombre, busqueda) {
    const texto = normalizar(nombre); const consulta = normalizar(busqueda);
    if (!consulta || texto.includes(consulta)) return true;
    return consulta.split(/\s+/).every(palabra => texto.split(/\s+/).some(parte =>
        parte.includes(palabra) || distancia(parte, palabra) <= Math.max(1, Math.floor(palabra.length / 3))
    ));
}
function filtrarMaterias(fila, consulta = '') {
    const select = fila.querySelector('.materia-id');
    const actual = select.value;
    const resultados = materias.filter(m => coincide(`${m.nombre} ${m.carga}`, consulta)).slice(0, 30);
    select.innerHTML = '<option value="">Seleccioná una materia</option>';
    resultados.forEach(m => select.add(new Option(`${m.nombre} - ${m.carga} ${m.carga === 1 ? 'hora' : 'horas'}`, m.id, false, String(m.id) === actual)));
    fila.querySelector('.resultado-busqueda').textContent = consulta ? `${resultados.length} coincidencia${resultados.length === 1 ? '' : 's'}` : '';
}
function cargarAnios(select) {
    const actual = Number(select.value) || 1;
    const cantidad = Math.max(1, Math.min(10, Number(duracion.value) || 1));
    select.innerHTML = '';
    for (let i = 1; i <= cantidad; i++) select.add(new Option(i + '.º año', i, false, i === Math.min(actual, cantidad)));
}
function actualizarEstado() { vacio.hidden = filas.children.length > 0; }
document.getElementById('agregarMateria').addEventListener('click', () => {
    const fila = document.getElementById('plantillaMateria').content.cloneNode(true);
    cargarAnios(fila.querySelector('.selector-anio'));
    const elementoFila = fila.querySelector('tr');
    filtrarMaterias(elementoFila);
    filas.appendChild(fila); actualizarEstado();
});
filas.addEventListener('click', e => { if (e.target.classList.contains('quitar-materia')) { e.target.closest('tr').remove(); actualizarEstado(); } });
filas.addEventListener('input', e => { if (e.target.classList.contains('buscar-materia')) filtrarMaterias(e.target.closest('tr'), e.target.value); });
duracion.addEventListener('input', () => document.querySelectorAll('.selector-anio').forEach(cargarAnios));
actualizarEstado();
</script>
</body></html>
