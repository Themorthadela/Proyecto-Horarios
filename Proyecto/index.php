<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <title>Panel de administración</title>
</head>
<body>
<main class="admin-panel">
    <header class="admin-hero"><h1>Panel de administración</h1><p>Gestioná los datos académicos y generá los horarios desde un solo lugar.</p></header>
    <section class="admin-section"><h2>Ingreso de datos</h2><div class="admin-grid">
        <a class="admin-card" href="Crear_Curso.php"><strong>Cursos</strong><span>Crear cursos, definir turnos, días y carga horaria.</span></a>
        <a class="admin-card" href="Crear_Docente.php"><strong>Docentes</strong><span>Registrar docentes y asociarlos a sus áreas.</span></a>
        <a class="admin-card" href="Crear_Area.php"><strong>Áreas</strong><span>Crear y consultar las áreas académicas.</span></a>
        <a class="admin-card" href="Crear_Materia.php"><strong>Materias</strong><span>Ingresar materias y asignar docentes.</span></a>
        <a class="admin-card" href="Crear_Usuario.php"><strong>Usuarios</strong><span>Crear accesos y definir roles del sistema.</span></a>
        <a class="admin-card" href="Asignar_Materia.php"><strong>Asignaciones</strong><span>Vincular curso, docente y materia.</span></a>
    </div></section>
    <section class="admin-section"><h2>Horarios y restricciones</h2><div class="admin-grid">
        <a class="admin-card" href="Crear_Restriccion_Curso.php"><strong>Restricciones de cursos</strong><span>Definir los bloques disponibles para cada curso.</span></a>
        <a class="admin-card" href="Crear_Restriccion_Docente.php"><strong>Restricciones de docentes</strong><span>Registrar la disponibilidad de cada docente.</span></a>
        <a class="admin-card destacada" href="GenerarHorario.php"><strong>Generar horarios</strong><span>Crear, regenerar y consultar el horario por curso.</span></a>
    </div></section>
</main>
</body>
</html>
