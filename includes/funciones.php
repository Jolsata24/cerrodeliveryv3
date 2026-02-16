<?php
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    // Radio de la tierra en km
    $radioTierra = 6371; 

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
         
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distancia = $radioTierra * $c; // Distancia en KM

    return $distancia;
}

function calcularCostoEnvio($distanciaKm) {
    // CONFIGURACIÓN DE TARIFAS (Ajusta esto a tu negocio)
    $tarifa_base = 5.00; // Precio mínimo (ej. dentro del mismo distrito)
    $km_base = 1.5;      // Primeros 1.5 km cuestan la tarifa base
    $costo_por_km_extra = 2.00; // Cada km adicional cuesta 2 soles más

    if ($distanciaKm <= $km_base) {
        return $tarifa_base;
    } else {
        $km_extra = $distanciaKm - $km_base;
        return $tarifa_base + ($km_extra * $costo_por_km_extra);
    }
}
?>