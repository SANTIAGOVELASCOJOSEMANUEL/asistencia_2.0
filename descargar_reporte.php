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

if (isset($_POST['id_usuario']) && isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
    $id_usuario = $_POST['id_usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Obtener datos del usuario desde la base de datos
    $sql_usuario = "SELECT nombre, salario FROM usuarios WHERE id_usuario='$id_usuario'";
    $result_usuario = mysqli_query($conn, $sql_usuario);
    $usuario = mysqli_fetch_assoc($result_usuario);
    $salario_base = $usuario['salario']; // Salario desde DB

    // Crear nuevo archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Títulos de las columnas
    $sheet->setCellValue('A1', 'Fecha');
    $sheet->setCellValue('B1', 'Entrada');
    $sheet->setCellValue('C1', 'Salida Comida');
    $sheet->setCellValue('D1', 'Regreso Comida');
    $sheet->setCellValue('E1', 'Salida');
    $sheet->setCellValue('F1', 'Horas Trabajadas');
    $sheet->setCellValue('G1', 'Minutos Tarde');
    $sheet->setCellValue('H1', 'Tiempo Comida');

    // Consulta para obtener asistencia del usuario entre las fechas indicadas
    $sql_asistencia = "SELECT * FROM asistencia WHERE id_usuario='$id_usuario' AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $result_asistencia = mysqli_query($conn, $sql_asistencia);

    // Variables para cálculos
    $veces_entre_8_y_8_05 = 0;
    $veces_despues_de_8_05 = 0;
    $veces_antes_de_8 = 0; // Se inicializa esta variable
    $minutos_tarde_acumulados = 0;
    $horas_trabajadas_totales = 0;
    $descuento_tarde = 2; // Descuento por minuto de retraso
    $bonus_puntualidad = 200;

    $fila = 2; // Para empezar a rellenar los datos en la fila 2
    while ($asistencia = mysqli_fetch_assoc($result_asistencia)) {
        $fecha = $asistencia['fecha'];
        $entrada = $asistencia['entrada'];
        $salida_comida = $asistencia['salida_comida'];
        $regreso_comida = $asistencia['regreso_comida'];
        $salida = $asistencia['salida'];

        // Calcular horas trabajadas
        $horas_trabajadas = calcularMinutos($entrada, $salida) - calcularMinutos($salida_comida, $regreso_comida);

        // Calcular minutos tarde y clasificar tiempos de entrada
        if ($entrada > '08:00:00' && $entrada <= '08:05:00') {
            $veces_entre_8_y_8_05++;
            $minutos_tarde = calcularMinutos('08:00:00', $entrada);
        } elseif ($entrada > '08:05:00') {
            $veces_despues_de_8_05++;
            $minutos_tarde = calcularMinutos('08:00:00', $entrada);
        } elseif ($entrada < '08:00:00') {
            $veces_antes_de_8++; // Contar entradas antes de las 8
            $minutos_tarde = 0;
        } else {
            $minutos_tarde = 0;
        }

        // Acumular datos
        $minutos_tarde_acumulados += $minutos_tarde;
        $horas_trabajadas_totales += $horas_trabajadas / 60; // convertir minutos a horas

        // Rellenar datos en la hoja de Excel
        $sheet->setCellValue('A' . $fila, $fecha);
        $sheet->setCellValue('B' . $fila, $entrada);
        $sheet->setCellValue('C' . $fila, $salida_comida);
        $sheet->setCellValue('D' . $fila, $regreso_comida);
        $sheet->setCellValue('E' . $fila, $salida);
        $sheet->setCellValue('F' . $fila, round($horas_trabajadas / 60, 2) . ' horas');
        $sheet->setCellValue('G' . $fila, $minutos_tarde . ' minutos');
        $sheet->setCellValue('H' . $fila, calcularMinutos($salida_comida, $regreso_comida) . ' minutos');

        $fila++;
    }

    // Calcular descuento y salario total
    $descuento_total = $minutos_tarde_acumulados * $descuento_tarde;
    $salario_total = $salario_base - $descuento_total;

    // Aplicar bono si corresponde
    if ($veces_antes_de_8 >= 6) {
        $salario_total += $bonus_puntualidad;
    } elseif ($veces_entre_8_y_8_05 <= 2 && $veces_antes_de_8 >= 4) {
        $salario_total += $bonus_puntualidad;
    }

    // Escribir resumen en las celdas
    $sheet->setCellValue('A' . ($fila + 1), 'Resumen');
    $sheet->setCellValue('A' . ($fila + 2), 'Salario Base:');
    $sheet->setCellValue('B' . ($fila + 2), '$' . $salario_base);
    $sheet->setCellValue('A' . ($fila + 3), 'Descuento Total:');
    $sheet->setCellValue('B' . ($fila + 3), '-$' . $descuento_total);
    $sheet->setCellValue('A' . ($fila + 4), 'Salario Total con Bonos:');
    $sheet->setCellValue('B' . ($fila + 4), '$' . $salario_total);

    // Descargar archivo Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="reporte_asistencia_' . $id_usuario . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
