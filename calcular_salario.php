<?php
require 'vendor/autoload.php'; // PhpSpreadsheet
include 'db.php'; // Conexión a la base de datos

// Función para calcular minutos entre dos horas
function calcularMinutos($hora_inicio, $hora_fin) {
    $inicio = new DateTime($hora_inicio);
    $fin = new DateTime($hora_fin);
    $intervalo = $inicio->diff($fin);
    return ($intervalo->h * 60) + $intervalo->i;
}

// Parámetros de bono y descuento
$bonus_puntualidad = 200;
$descuento_tarde = 2; // descuento por minuto de retraso

if (isset($_POST['id_usuario']) && isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
    $id_usuario = $_POST['id_usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Obtener salario desde la tabla `usuarios`
    $sql_usuario = "SELECT nombre, salario FROM usuarios WHERE id_usuario='$id_usuario'";
    $result_usuario = mysqli_query($conn, $sql_usuario);
    $usuario = mysqli_fetch_assoc($result_usuario);
    $salario_base = $usuario['salario']; // Se obtiene el salario de la tabla

    // Consulta para obtener asistencias entre las fechas indicadas
    $sql_asistencia = "SELECT * FROM asistencia WHERE id_usuario='$id_usuario' AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $result_asistencia = mysqli_query($conn, $sql_asistencia);

    // Inicializar variables de control
    $veces_antes_de_8 = 0;
    $veces_entre_8_y_8_05 = 0;
    $veces_despues_de_8_05 = 0;
    $minutos_tarde_acumulados = 0;
    $salario_total = $salario_base;
    $horas_trabajadas_totales = 0;

    echo '<h3>Datos de Asistencia para el ID: ' . $id_usuario . ' - ' . $usuario['nombre'] . '</h3>';
    echo '<table border="1">';
    echo '<tr>
            <th>Fecha</th>
            <th>Entrada</th>
            <th>Salida Comida</th>
            <th>Regreso Comida</th>
            <th>Salida</th>
            <th>Horas Trabajadas</th>
            <th>Minutos Tarde</th>
            <th>Tiempo Comida</th>
          </tr>';

    while ($asistencia = mysqli_fetch_assoc($result_asistencia)) {
        $fecha = $asistencia['fecha'];
        $hora_entrada = $asistencia['entrada'];
        $hora_salida = $asistencia['salida'];
        $hora_salida_comida = $asistencia['salida_comida'];
        $hora_regreso_comida = $asistencia['regreso_comida'];

        // Calcular horas trabajadas y minutos de retraso
        $horas_trabajadas = calcularMinutos($hora_entrada, $hora_salida) - calcularMinutos($hora_salida_comida, $hora_regreso_comida);

        // Calcular minutos tarde correctamente
        if ($hora_entrada > '08:00:00') {
            $minutos_tarde = calcularMinutos('08:00:00', $hora_entrada); // Calcular minutos tarde después de las 8:00
        } else {
            $minutos_tarde = 0; // No llegó tarde
        }

        // Contabilizar llegadas
        if ($hora_entrada <= '08:00:00') {
            $veces_antes_de_8++;
        } elseif ($hora_entrada >= '08:00:01' && $hora_entrada <= '08:05:00') {
            $veces_entre_8_y_8_05++;
        } elseif ($hora_entrada > '08:05:01') {
            $veces_despues_de_8_05++;
        }

        // Acumular minutos tarde y horas trabajadas
        $minutos_tarde_acumulados += $minutos_tarde;
        $horas_trabajadas_totales += $horas_trabajadas / 60; // convertir a horas

        // Mostrar resultados por día
        echo '<tr>';
        echo '<td>' . $fecha . '</td>';
        echo '<td>' . $asistencia['entrada'] . '</td>';
        echo '<td>' . $asistencia['salida_comida'] . '</td>';
        echo '<td>' . $asistencia['regreso_comida'] . '</td>';
        echo '<td>' . $asistencia['salida'] . '</td>';
        echo '<td>' . round($horas_trabajadas / 60, 2) . ' horas</td>'; // convertir a horas y mostrar
        echo '<td>' . $minutos_tarde . ' minutos</td>'; // Mostrar correctamente los minutos tarde
        echo '<td>' . calcularMinutos($hora_salida_comida, $hora_regreso_comida) . ' minutos</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Calcular descuentos por minutos tarde
    $descuento_total = $minutos_tarde_acumulados * $descuento_tarde;
    $salario_total -= $descuento_total;
/*
    // Lógica para el bono de puntualidad
    if ($veces_despues_de_8_05 >= 3) {
        // Si llega 3 veces o más después de las 8:05, no gana bono
        echo "<p>No alcanzó el bono por llegar tarde 3 o más veces después de las 8:05.</p>";
    } elseif ($veces_entre_8_y_8_05 <= 2 && $veces_despues_de_8_05 == 0) {
        // Si llegó máximo 2 veces entre 8:00 y 8:05 y no llegó después de las 8:05, gana el bono
        $salario_total += $bonus_puntualidad;
        echo "<p>Felicidades, has ganado el bono de puntualidad de $bonus_puntualidad.</p>";
    } else {
        // No califica para el bono si llegó tarde (después de las 8:05)
        echo "<p>No alcanzó el bono de puntualidad por llegar tarde.</p>";
    }
*/
/*---------------------------------------------------------------------------------------------*/


if ($veces_antes_de_8 >= 6) {
    echo "<p>Felicidades, has ganado el bono de puntualidad de $bonus_puntualidad.</p>";
    $salario_total += $bonus_puntualidad;
    } elseif ($veces_entre_8_y_8_05 <= 2 && $veces_antes_de_8 >= 4) {
    echo "<p>Felicidades, has ganado el bono de puntualidad de $bonus_puntualidad.</p>";
    $salario_total += $bonus_puntualidad;
    }else if($veces_despues_de_8_05 >= 3) {
    // Si llega 3 veces o más después de las 8:05, no gana bono
    echo "<p>No alcanzó el bono por llegar tarde 3 o más veces después de las 8:05.$bonus_puntualidad.</p>";
   }else{
    echo "<p>No alcanzó el bono por llegar tarde 3 o más veces después de las 8:05.-$bonus_puntualidad.</p>";
   }
/*-------------------------------------------------------------------------------------------------*/

    // Mostrar resumen final
    echo '<h3>Resumen</h3>';
    echo '<p>ID: ' . $id_usuario . '</p>';
    echo '<p>Nombre: ' . $usuario['nombre'] . '</p>';
    echo '<p>Horas Trabajadas Totales: ' . round($horas_trabajadas_totales, 2) . ' horas</p>';
    echo '<p>Minutos Tarde Acumulados: ' . $minutos_tarde_acumulados . ' minutos</p>';
    echo '<p>Descuento Total por Minutos Tarde: -$' . $descuento_total . '</p>';
    echo '<p>Salario Base: $' . $salario_base . '</p>';
    echo '<p>Salario Total con Bonos y Descuentos: $' . $salario_total . '</p>';

    // Botón para descargar el reporte individual
    echo '<form action="descargar_reporte.php" method="POST">';
    echo '<input type="hidden" name="id_usuario" value="' . $id_usuario . '">';
    echo '<input type="hidden" name="fecha_inicio" value="' . $fecha_inicio . '">';
    echo '<input type="hidden" name="fecha_fin" value="' . $fecha_fin . '">';
    echo '<button type="submit">Descargar Reporte en Excel</button>';
    echo '</form>';

    // Botón para descargar el reporte de todos los usuarios
    echo '<form action="descargar_todos.php" method="POST">';
    echo '<input type="hidden" name="fecha_inicio" value="' . $fecha_inicio . '">';
    echo '<input type="hidden" name="fecha_fin" value="' . $fecha_fin . '">';
    echo '<button type="submit">Descargar Reporte de Todos los Usuarios en Excel</button>';
    echo '</form>';
}
