<?php
require 'vendor/autoload.php'; // PhpSpreadsheet
include 'db.php'; // Conexión a la base de datos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Función para calcular minutos entre dos horas
function calcularMinutos($hora_inicio, $hora_fin) {
    $inicio = new DateTime($hora_inicio);
    $fin = new DateTime($hora_fin);
    $intervalo = $inicio->diff($fin);
    return ($intervalo->h * 60) + $intervalo->i;
}

if (isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Crear nuevo archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Títulos de las columnas
    $sheet->setCellValue('A1', 'ID Usuario');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Fecha');
    $sheet->setCellValue('D1', 'Entrada');
    $sheet->setCellValue('E1', 'Salida Comida');
    $sheet->setCellValue('F1', 'Regreso Comida');
    $sheet->setCellValue('G1', 'Salida');
    $sheet->setCellValue('H1', 'Horas Trabajadas');
    $sheet->setCellValue('I1', 'Minutos Tarde');
    $sheet->setCellValue('J1', 'Tiempo Comida');
    $sheet->setCellValue('K1', 'Salario Base');
    $sheet->setCellValue('L1', 'Descuento Total');
    $sheet->setCellValue('M1', 'Salario Total con Bonos');

    // Obtener todos los usuarios
    $sql_usuarios = "SELECT id_usuario, nombre, salario FROM usuarios";
    $result_usuarios = mysqli_query($conn, $sql_usuarios);

    $fila = 2; // Para empezar a rellenar los datos en la fila 2
    while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
        $id_usuario = $usuario['id_usuario'];
        $nombre = $usuario['nombre'];
        $salario_base = $usuario['salario'];

        // Consulta para obtener asistencia del usuario entre las fechas indicadas
        $sql_asistencia = "SELECT * FROM asistencia WHERE id_usuario='$id_usuario' AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $result_asistencia = mysqli_query($conn, $sql_asistencia);

        $minutos_tarde_acumulados = 0;
        $horas_trabajadas_totales = 0;
        $descuento_tarde = 2; // Descuento por minuto de retraso
        $bonus_puntualidad = 200;
        $veces_antes_de_8 = 0;
        $veces_entre_8_y_8_05 = 0;
        $veces_despues_de_8_05 = 0;

        while ($asistencia = mysqli_fetch_assoc($result_asistencia)) {
            $fecha = $asistencia['fecha'];
            $entrada = $asistencia['entrada'];
            $salida_comida = $asistencia['salida_comida'];
            $regreso_comida = $asistencia['regreso_comida'];
            $salida = $asistencia['salida'];

            // Calcular horas trabajadas
            $horas_trabajadas = calcularMinutos($entrada, $salida) - calcularMinutos($salida_comida, $regreso_comida);

            // Calcular minutos tarde
            if ($entrada > '08:00:00') {
                $minutos_tarde = calcularMinutos('08:00:00', $entrada);
            } else {
                $minutos_tarde = 0;
            }

            // Acumular datos
            $minutos_tarde_acumulados += $minutos_tarde;
            $horas_trabajadas_totales += $horas_trabajadas / 60; // Convertir minutos a horas

            // Rellenar datos en la hoja de Excel para este usuario
            $sheet->setCellValue('A' . $fila, $id_usuario);
            $sheet->setCellValue('B' . $fila, $nombre);
            $sheet->setCellValue('C' . $fila, $fecha);
            $sheet->setCellValue('D' . $fila, $entrada);
            $sheet->setCellValue('E' . $fila, $salida_comida);
            $sheet->setCellValue('F' . $fila, $regreso_comida);
            $sheet->setCellValue('G' . $fila, $salida);
            $sheet->setCellValue('H' . $fila, round($horas_trabajadas / 60, 2) . ' horas');
            $sheet->setCellValue('I' . $fila, $minutos_tarde . ' minutos');
            $sheet->setCellValue('J' . $fila, calcularMinutos($salida_comida, $regreso_comida) . ' minutos');

            $fila++;
        }

        // Calcular descuento y salario total
        $descuento_total = $minutos_tarde_acumulados * $descuento_tarde;
        $salario_total = $salario_base - $descuento_total;

        // Aplicar bono si corresponde
      /*  if ($veces_entre_8_y_8_05 <= 2 && $veces_despues_de_8_05 == 0) {
            $salario_total += $bonus_puntualidad;
        }*/
       /* -----------------------------------------------------------------------------*/
       if ($veces_antes_de_8 >= 6) {
        $salario_total += $bonus_puntualidad;
    } elseif ($veces_entre_8_y_8_05 <= 2 && $veces_antes_de_8 >= 4) {
        $salario_total += $bonus_puntualidad;
    } elseif ($veces_despues_de_8_05 >= 3) {
       $salario_total;
    }

      /*---------------------------------------------------------------------------------------*/






        // Rellenar resumen para el usuario
        $sheet->setCellValue('K' . ($fila - 1), '$' . $salario_base);
        $sheet->setCellValue('L' . ($fila - 1), '-$' . $descuento_total);
        $sheet->setCellValue('M' . ($fila - 1), '$' . $salario_total);
    }

    // Descargar archivo Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="reporte_asistencia_todos.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
