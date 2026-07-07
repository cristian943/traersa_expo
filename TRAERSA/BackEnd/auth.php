<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['usuario_id'])) {
    $base = dirname($_SERVER['DOCUMENT_ROOT']);
    header("Location: /login/Inicio_sesion.php");
    exit();
}