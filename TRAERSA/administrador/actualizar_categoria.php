<?php
include("../conexion.php");

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$paquetes = $_POST['paquetes'];
$kilogramos = $_POST['kilogramos'];
$tipo_entrega = $_POST['tipo_entrega'];
$precio_sin_iva = $_POST['precio'];
$iva = $precio_sin_iva * 0.12;
$precio = $precio_sin_iva + $iva;

$sql = "UPDATE envios SET
titulo='$titulo',
descripcion='$descripcion',
paquetes='$paquetes',
kilogramos='$kilogramos',
tipo_entrega='$tipo_entrega',
precio='$precio'
WHERE id='$id'";

if($conn->query($sql)){
    header("Location: Editar_Servicios.php");
}else{
    echo $conn->error;
}
?>