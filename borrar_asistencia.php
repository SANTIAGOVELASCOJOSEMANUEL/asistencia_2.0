<?php
include 'db.php';

// Obtener el ID del usuario de la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Primero eliminar las asistencias relacionadas con el usuario
    $sql = "DELETE FROM asistencia WHERE id_usuario = (SELECT id_usuario FROM usuarios WHERE id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Luego eliminar el usuario de la tabla `usuarios`
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Usuario y sus asistencias eliminados correctamente.";
            header("Location: usuarios.php"); // Redireccionar a la página principal
            exit;
        } else {
            echo "Error al eliminar el usuario: " . $conn->error;
        }
    } else {
        echo "Error al eliminar asistencias: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "ID no proporcionado.";
}

$conn->close();
?>
