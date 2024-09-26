<?php
include 'db.php'; // Conexión a la base de datos

// Obtener el ID del usuario de la URL
$id = $_GET['id'];

// Consultar los datos del usuario a partir del ID
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc(); // Obtener los datos del usuario
} else {
    echo "Usuario no encontrado.";
    exit;
}

// Verificar si se ha enviado el formulario para actualizar los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los nuevos datos del formulario
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $puesto = $_POST['puesto'];
    $salario = $_POST['salario'];
    $status = $_POST['status'];

    // Manejar la imagen (foto) subida por el usuario
    $foto = $row['foto']; // Asignar el valor actual de la foto por defecto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreImagen = $_FILES['foto']['name'];
        $rutaTemporal = $_FILES['foto']['tmp_name'];
        $destino = "uploads/" . $nombreImagen;

        // Mover el archivo subido a la carpeta uploads
        if (move_uploaded_file($rutaTemporal, $destino)) {
            $foto = $nombreImagen; // Actualizar el nombre de la imagen si la subida fue exitosa
        }
    }

    // Actualizar los datos en la base de datos
    $update_sql = "UPDATE usuarios SET nombre = ?, edad = ?, puesto = ?, salario = ?, foto = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sisdssi", $nombre, $edad, $puesto, $salario, $foto, $status, $id);

    if ($update_stmt->execute()) {
        echo "<p>Usuario actualizado correctamente.</p>";
        header("Refresh:2; url=usuarios.php"); // Redirigir de nuevo a la lista de usuarios
        exit;
    } else {
        echo "<p>Error al actualizar los datos.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="ver.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <img src="uploads/<?php echo $row['foto']; ?>" alt="Foto de perfil" class="profile-picture">
            <h2><?php echo $row['nombre']; ?></h2>
            <p class="status"><?php echo $row['status']; ?></p>
        </div>

        <!-- Mostrar los datos actuales del usuario -->
        <form method="POST" enctype="multipart/form-data">
            <label>ID Usuario:</label>
            <input type="text" value="<?php echo $row['id_usuario']; ?>" disabled><br>

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo $row['nombre']; ?>" required><br>

            <label>Edad:</label>
            <input type="number" name="edad" value="<?php echo $row['edad']; ?>" required><br>

            <label>Puesto:</label>
            <input type="text" name="puesto" value="<?php echo $row['puesto']; ?>" required><br>

            <label>Salario:</label>
            <input type="number" name="salario" value="<?php echo $row['salario']; ?>" required><br>

            <label>Status:</label>
            <input type="text" name="status" value="<?php echo $row['status']; ?>" required><br>

            <label>Cambiar Foto (opcional):</label>
            <input type="file" name="foto"><br>

            <div class="button-group">
                <button type="submit">Guardar cambios</button>
                <a href="usuarios.php" class="cancelar">Cancelar</a>
            </div>
        </form>
    </div>

    <?php
    // Cerrar la conexión a la base de datos
    $conn->close();
    ?>
</body>
</html>
