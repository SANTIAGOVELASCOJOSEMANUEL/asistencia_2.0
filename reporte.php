<?php
include 'db.php';

// Obtener el rango de fechas para la última semana (de miércoles a martes)
$hoy = date('Y-m-d');
$inicio_semana = date('Y-m-d', strtotime('last Wednesday', strtotime($hoy)));
$fin_semana = date('Y-m-d', strtotime('next Tuesday', strtotime($hoy)));

$sql = "SELECT u.id_usuario, u.nombre, a.fecha, a.entrada, a.salida_comida, a.regreso_comida, a.salida
        FROM usuarios u
        LEFT JOIN asistencia a ON u.id_usuario = a.id_usuario
        AND a.fecha BETWEEN '$inicio_semana' AND '$fin_semana'
        ORDER BY u.id_usuario, a.fecha";
$result = mysqli_query($conn, $sql);

$usuarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $usuarios[$row['id_usuario']]['nombre'] = $row['nombre'];
    $usuarios[$row['id_usuario']]['asistencias'][$row['fecha']] = $row;
}

function obtenerDiaSemana($fecha) {
    $dias_semana = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
    return $dias_semana[date('l', strtotime($fecha))];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <link rel="stylesheet" href="reporte.css">
</head>
<body>
    <h2>Reporte de Asistencia</h2>
    <table>
        <thead>
            <tr>
                <th>ID Usuario</th>
                <th>Nombre</th>
                <?php for ($i = 0; $i < 7; $i++) : 
                    $fecha = date('Y-m-d', strtotime("-$i day", strtotime($fin_semana))); ?>
                    <th><?php echo obtenerDiaSemana($fecha) . "<br>" . $fecha; ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $id_usuario => $usuario) : ?>
                <tr>
                    <td><?php echo $id_usuario; ?></td>
                    <td><?php echo $usuario['nombre']; ?></td>
                    <?php for ($i = 0; $i < 7; $i++) : 
                        $fecha = date('Y-m-d', strtotime("-$i day", strtotime($fin_semana)));
                        $asistencia = isset($usuario['asistencias'][$fecha]) ? $usuario['asistencias'][$fecha] : null;
                        ?>
                        <td>
                            <?php if ($asistencia) : ?>
                                <div>Entrada: <?php echo $asistencia['entrada']; ?></div>
                                <div>Salida Comida: <?php echo $asistencia['salida_comida']; ?></div>
                                <div>Regreso Comida: <?php echo $asistencia['regreso_comida']; ?></div>
                                <div>Salida: <?php echo $asistencia['salida']; ?></div>
                            <?php else : ?>
                                <div>No asistencia</div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
