<?php
include 'db.php';

// Obtener el ID del usuario de la URL
if (isset($_GET['id'])) {
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

    // Eliminar el usuario si se pasa el ID
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Usuario eliminado correctamente";
        header("Location: usuario.php"); // Redireccionar a la página principal después de eliminar
        exit;
    } else {
        echo "Error al eliminar: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "ID no proporcionado.";
}

$conn->close();
?>

