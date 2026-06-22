<?php

include("../conexion.php");

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    // Obtener imagen
    $consulta = $conn->query("SELECT imagen FROM envios WHERE id='$id'");

    if($consulta->num_rows > 0){

        $fila = $consulta->fetch_assoc();

        if(!empty($fila['imagen']) && file_exists("uploads/".$fila['imagen'])){
            unlink("uploads/".$fila['imagen']);
        }

        $conn->query("DELETE FROM envios WHERE id='$id'");
    }
}

header("Location: Editar_Servicios.php");
exit;

?>