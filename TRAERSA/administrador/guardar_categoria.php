<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $paquetes = $_POST['paquetes'] ?? '';
    $kilogramos = $_POST['kilogramos'] ?? '';
    $tipo = $_POST['tipo_entrega'] ?? '';
    $precio_sin_iva = $_POST['precio'] ?? 0;

    // Validar campos vacíos
    if (
        empty($titulo) ||
        empty($descripcion) ||
        empty($paquetes) ||
        empty($kilogramos) ||
        empty($tipo) ||
        empty($precio_sin_iva)
    ) {
        die("Todos los campos son obligatorios.");
    }

    // Calcular IVA
    $iva = $precio_sin_iva * 0.12;
    $precio = $precio_sin_iva + $iva;

   if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {

    $permitidos = ['png', 'jpg', 'jpeg'];
    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $permitidos)) {
        die("Solo se permiten imágenes PNG, JPG o JPEG.");
    }

    $imagen = uniqid() . "." . $extension;
    $ruta = "../uploads/" . $imagen;

    if ($extension == "jpg" || $extension == "jpeg") {
        $img = imagecreatefromjpeg($_FILES['imagen']['tmp_name']);
        imagejpeg($img, $ruta, 60);
    }

    if ($extension == "png") {
        $img = imagecreatefrompng($_FILES['imagen']['tmp_name']);
        imagepng($img, $ruta, 7);
    }

    imagedestroy($img);

} else {
    $imagen = "default-package.png";
}

    // Insertar en BD
    $sql = "INSERT INTO categoria (
        titulo,
        descripcion,
        paquetes,
        kilogramos,
        tipo_entrega,
        precio,
        imagen
    ) VALUES (
        '$titulo',
        '$descripcion',
        '$paquetes',
        '$kilogramos',
        '$tipo',
        '$precio',
        '$imagen'
    )";

    if ($conn->query($sql)) {
        header("Location: Editar_categorias.php");
        exit();
    } else {
        echo "Error en la base de datos: " . $conn->error;
    }

} else {
    echo "No se enviaron datos.";
}

?>