<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Semanal</title>
</head>
<body>
    <h2>Generar Reporte de Asistencia Semanal</h2>
    <form method="post" action="calcular_salario.php">
        <label for="id_usuario">ID de Usuario:</label>
        <input type="text" name="id_usuario" required><br>
        <label for="fecha_inicio">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" required><br>
        <label for="fecha_fin">Fecha Fin:</label>
        <input type="date" name="fecha_fin" required><br>
        <button type="submit">Generar Reporte</button>
    </form>
</body>
</html>
