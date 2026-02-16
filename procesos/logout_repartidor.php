<?php
// Inicia la sesión para poder acceder a ella.
session_start();

// Elimina todas las variables de la sesión.
$_SESSION = array();

// Destruye la sesión por completo.
session_destroy();

// Redirige al repartidor a su página de inicio de sesión.
header('Location: ../login_repartidor.php');
exit();
?>