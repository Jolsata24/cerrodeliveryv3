<?php
// Inicia la sesión para poder acceder a ella.
session_start();

// Elimina todas las variables de la sesión.
$_SESSION = array();

// Destruye la sesión por completo.
session_destroy();

// Redirige al administrador a su página de login.
header('Location: ../admin/login.php');
exit();
?>