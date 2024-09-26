<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = strtoupper(substr(md5(time() . rand()), 0, 6)); // Generar un ID de usuario único
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $puesto = $_POST['puesto'];
    $salario = $_POST['salario'];
    $status = $_POST['status'];

    // Manejar la foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $carpetaDestino = "uploads/";

        // Verifica si la carpeta existe, si no, créala
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true); // Crea la carpeta con permisos de escritura
        }

        $nombreFoto = $id_usuario . "_" . basename($_FILES['foto']['name']);
        $rutaFoto = $carpetaDestino . $nombreFoto;

        // Mover la foto a la carpeta uploads
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
            // Insertar el usuario y la ruta de la foto en la base de datos
            $sql = "INSERT INTO usuarios (id_usuario, nombre, edad, puesto, salario, status, foto) 
                    VALUES ('$id_usuario', '$nombre', $edad, '$puesto', '$salario', '$status', '$rutaFoto')";

            if (mysqli_query($conn, $sql)) {
                echo "Usuario registrado con éxito. ID de usuario: $id_usuario";
            } else {
                echo "Error al registrar el usuario: " . mysqli_error($conn);
            }
        } else {
            echo "Error al subir la foto.";
        }
    } else {
        echo "Por favor, selecciona una foto válida.";
    }

    // Refrescar la página para evitar reenvío de formulario
    header("Refresh:2");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Registro de Usuario</h2>
    <form method="post" action="registro.php" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required><br>
        <label for="edad">Edad:</label>
        <input type="number" name="edad" required><br>
        <label for="puesto">Puesto:</label>
        <input type="text" name="puesto" required><br>
        <label for="salario">Salario:</label>
        <input type="text" name="salario" required><br>
        <label for="status">Status:</label>
        <input type="text" name="status" required><br>
        <label for="foto">Foto:</label>
        <input type="file" name="foto" accept="image/*" required><br>
        <button type="submit">Registrar</button>
    </form>
</body>
</html>
