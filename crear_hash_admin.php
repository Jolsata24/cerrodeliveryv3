<?php
// ----- SCRIPT TEMPORAL PARA CREAR CONTRASEÑA DE ADMIN -----

// ¡¡¡ IMPORTANTE !!!
// CAMBIA 'TuClaveSecreta123' por la contraseña real que quieres usar.
$password_plana = '123456'; 

// Esta función encripta la contraseña de forma segura.
$hash_seguro = password_hash($password_plana, PASSWORD_DEFAULT);

// Muestra el resultado para que puedas copiarlo.
echo "Copia esta línea completa y pégala en tu consulta SQL:<br><br>";
echo "<strong>" . $hash_seguro . "</strong>";
?>