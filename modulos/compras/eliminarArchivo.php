<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");


if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/archivos/compras/" . $_POST["nombre"])) {
    unlink($_SERVER["DOCUMENT_ROOT"] . "/archivos/compras/" . $_POST["nombre"]);
}

