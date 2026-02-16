<?php
// Es crucial iniciar la sesión para poder destruirla.
session_start();

// Elimina todas las variables de la sesión.
$_SESSION = array();

// Destruye la sesión por completo.
session_destroy();

// Redirige al usuario a la página principal o al login de clientes.
header('Location: ../index.php');
exit();
?>