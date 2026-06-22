<?php

include("../conexion.php");

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    // Obtener imagen
    $consulta = $conn->query("SELECT imagen FROM categoria WHERE id='$id'");

    if($consulta->num_rows > 0){

        $fila = $consulta->fetch_assoc();

        // No borrar imagen por defecto
        if(
            !empty($fila['imagen']) &&
            $fila['imagen'] != "default-package.png" &&
            file_exists("../uploads/".$fila['imagen'])
        ){
            unlink("../uploads/".$fila['imagen']);
        }

        // Eliminar registro
        $conn->query("DELETE FROM categoria WHERE id='$id'");
    }
}

header("Location: Editar_categorias.php");
exit;

?>