<?php

include("../conexion.php");

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$paquetes = $_POST['paquetes'];
$kilogramos = $_POST['kilogramos'];
$tipo = $_POST['tipo_entrega'];
$precio_sin_iva = $_POST['precio'];
$iva = $precio_sin_iva * 0.12;
$precio = $precio_sin_iva + $iva;

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){

    $imagen = $_FILES['imagen']['name'];

    move_uploaded_file(
        $_FILES['imagen']['tmp_name'],
        "uploads/".$imagen
    );

}else{

    $imagen = "default-package.png";

}

$sql = "INSERT INTO envios
(
titulo,
descripcion,
paquetes,
kilogramos,
tipo_entrega,
precio,
imagen
)
VALUES
(
'$titulo',
'$descripcion',
'$paquetes',
'$kilogramos',
'$tipo',
'$precio',
'$imagen'
)";

if($conn->query($sql)){
    header("Location: Editar_Servicios.php");
}else{
    echo "Error: " . $conn->error;
}
?>