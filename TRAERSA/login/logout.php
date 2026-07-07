<?php
session_start();

// Vaciar todas las variables de sesión
$_SESSION = array();

// Borrar también la cookie de sesión del navegador (esto es lo que
// faltaba: sin esto, a veces el navegador seguía "recordando" la sesión
// vieja al volver atrás o recargar).
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión en el servidor
session_destroy();

header("Location: Inicio_sesion.php");
exit();
?>