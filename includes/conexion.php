<?php
// =================================================================
// ARCHIVO DE CONEXIÓN A LA BASE DE DATOS
// =================================================================

// EN HOSTGATOR (Producción)
// Usamos 'localhost' porque el archivo PHP y la BD están en el mismo servidor.
$servername = "162.241.60.127";        
$username   = "herework_jolsata24";        
$password   = "polloasado500";             
$dbname     = "herework_cerrodelivery";     

// CREAR CONEXIÓN
$conn = new mysqli($servername, $username, $password, $dbname);

// VERIFICAR
if ($conn->connect_error) {
    // Si falla, matamos el proceso pero NO enviamos texto HTML
    // para no romper la respuesta JSON en la App.
    error_log("Connection failed: " . $conn->connect_error);
    die("Error de conexion BD");
}

// CARACTERES ESPECIALES (Ñ, Tildes)
$conn->set_charset("utf8mb4");