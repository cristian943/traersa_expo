<?php
// Incluimos la conexion con la base de datos
include("../conexion.php");

// Recibir los datos enviados desde el formulario
$id = $_POST['id'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$paquetes = $_POST['paquetes'];
$kilogramos = $_POST['kilogramos'];
$tipo_entrega = $_POST['tipo_entrega'];
$precio_sin_iva = $_POST['precio'];

// Calcular el IVA y el precio final
$iva = $precio_sin_iva * 0.12;
$precio = $precio_sin_iva + $iva;

// Actualizar los datos de la categoria en la base de datos
$sql = "UPDATE categoria SET
titulo='$titulo',
descripcion='$descripcion',
paquetes='$paquetes',
kilogramos='$kilogramos',
tipo_entrega='$tipo_entrega',
precio='$precio'
WHERE id='$id'";

if($conn->query($sql)){
    // Si se actualiza bien, redireccionar a la pagina de edicion
    header("Location: Editar_categorias.php");
}else{
    // Mostrar error en caso de fallo
    echo $conn->error;
}
?>