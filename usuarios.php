<?php
include 'db.php';

// Consulta para obtener los datos de la tabla `usuarios`
$sql = "SELECT id, id_usuario, nombre, edad, puesto, salario, status FROM usuarios ORDER BY salario ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="usuarios.css">
</head>
<body>
    <div class="container">
        <h1>Usuarios Registrados</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Usuario</th>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Puesto</th>
                    <th>Salario</th>
                    <th>Status</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Verificar si hay resultados y mostrarlos en la tabla
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['id_usuario'] . "</td>";
                        echo "<td>" . $row['nombre'] . "</td>";
                        echo "<td>" . $row['edad'] . "</td>";
                        echo "<td>" . $row['puesto'] . "</td>";
                        echo "<td>" . $row['salario'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>
                                <a href='editar.php?id=" . $row['id'] . "' class='editar'>Editar</a> | 
                                <a href='borrar_asistencia.php?id=" . $row['id'] . "' class='editar'>borrar asistencia</a> |
                                <a href='ver.php?id=" . $row['id'] . "' class='borrar'>ver</a>

                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay usuarios registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Cerrar la conexión a la base de datos
    $conn->close();
    ?>
</body>
</html>
