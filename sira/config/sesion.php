<?php
/**
 * SIRA-LSC | Configuración segura de sesiones.
 * Centraliza las opciones de la cookie de sesión para todo el proyecto.
 */
function iniciarSesionSegura()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $segura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params(array(
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $segura,
        'httponly' => true,
        'samesite' => 'Lax',
    ));

    session_start();
}
