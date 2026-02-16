<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_SESSION['restaurante_id'];
    
    // Obtenemos el array de categorías seleccionadas (puede venir vacío si desmarca todo)
    $categorias = isset($_POST['categorias']) ? $_POST['categorias'] : [];

    // 1. Iniciar transacción para seguridad
    $conn->begin_transaction();

    try {
        // 2. Primero BORRAMOS todas las categorías actuales de este restaurante
        // Esto es más fácil que verificar una por una cuál agregar o quitar
        $sql_delete = "DELETE FROM restaurante_categorias WHERE id_restaurante = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_restaurante);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 3. Insertamos las nuevas categorías seleccionadas
        if (!empty($categorias)) {
            $sql_insert = "INSERT INTO restaurante_categorias (id_restaurante, id_categoria) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);

            foreach ($categorias as $id_categoria) {
                // Aseguramos que sea un número para evitar inyecciones raras
                $id_cat_clean = (int)$id_categoria;
                $stmt_insert->bind_param("ii", $id_restaurante, $id_cat_clean);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        }

        // 4. Guardamos cambios
        $conn->commit();
        header("Location: ../restaurante/dashboard.php?status=categorias_actualizadas");

    } catch (Exception $e) {
        $conn->rollback(); // Si falla algo, deshacemos todo
        die("Error al actualizar categorías: " . $e->getMessage());
    }
    
    $conn->close();
} else {
    header("Location: ../restaurante/dashboard.php");
    exit();
}
?>