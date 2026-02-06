<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Compras.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));

?>

<div style="width:1000px;">
    <div class="row">
        <div class="col-6">
            <h4 class="header-title">Recepciones - Compra # <?= $_GET["idcompra"] ?></h4>
        </div>
    </div>
    <hr>
    <?
    $claseCompras = new Compras();
    $_POST["idcompra"] = $_GET["idcompra"];
    $recepciones = $claseCompras->obtenerRecepciones($_POST);
    if ($recepciones["respuesta"] == "OK") {
    ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    foreach ($recepciones["recepciones"] as $recepcion) {
                    ?>
                        <tr>
                            <td><?= fecha_formateada($recepcion["registro"]) ?></td>
                            <td><?= $recepcion["usuario"] ?></td>
                            <td class="text-end">
                                <a href="/modulos/compras/historialrecepciones/historialpdf.php?idcompra=<?= $_POST["idcompra"] ?>&idrecepcion=<?= $recepcion["idrecepcion"] ?>" class="btn btn-secondary btn-sm" target="_blank"><i class="mdi mdi-file-pdf-box"></i></a>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?
    } else {
    ?>
        <div class="card  ">
            <div class="card-body p-3">
                <?= $recepciones["mensaje"] ?>
            </div>
        </div>
    <?
    }
    ?>
</div>