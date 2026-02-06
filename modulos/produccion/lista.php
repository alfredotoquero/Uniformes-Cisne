<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");

$claseProduccion = new Produccion();
$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$clientes = $claseProduccion->obtenerClientesProduccion($_POST);

if($clientes["respuesta"]=="OK"){
    foreach($clientes["clientes"] as $cliente){
        ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <a href="/produccionde<?= $cliente["url"] ?>"><b><?= $cliente["cliente"] ?></b></a>
                            </div>
                            <div class="col-4">
                                Pedidos: <b><?= implode(",",$cliente["pedidos"]) ?></b><br>
                                <button type="button" name="btnPDFPedido" id="btnPDFPedido" class="btn btn-primary waves-effect waves-light" onclick="<?= (count($cliente["pedidos"])==1) ? "window.open('/modulos/produccion/produccionclientepdf.php?idpedido=".$cliente["pedidos"][0]."', '_blank')" : "fancy('/modulos/produccion/indicarpedidocliente.php?idcliente=".$cliente["idcliente"]."&cliente=".urlencode($cliente["cliente"])."',900,300)" ?>">Generar PDF</button>
                            </div>
                            <div class="col-4">
                                <div class="row">
                                    <div class="col-4 offset-4 text-center">
                                        <i class="fas fa-<?= $cliente["icono_almacen"]; ?>-circle text-<?= $cliente["status_almacen"]; ?>"></i><br>
                                        <small>Almacén</small>
                                        <br>
                                        <b><?= (($cliente["porcentaje_almacen"]<100) ? number_format($cliente["porcentaje_almacen"],2) . "%" : "") ?></b>
                                    </div>
                                    <!-- <div class="col-4 text-center">
                                        <i class="fas fa-<?= $cliente["icono_diseno"]; ?>-circle text-<?= $cliente["status_diseno"]; ?>"></i><br>
                                        <small>Diseño</small>
                                    </div> -->
                                    <div class="col-4 text-center">
                                        <i class="fas fa-<?= $cliente["icono_produccion"]; ?>-circle text-<?= $cliente["status_produccion"]; ?>"></i><br>
                                        <small>Producción</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?
    }
    ?>
    <div class="row mt-3">
        <div class="col-12 text-end">
            <nav>
                <ul class="pagination">
                    <? if($clientes["pagina"]>2){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                    <? } ?>
                    <? if($clientes["pagina"]>1){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $clientes['pagina']-1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                    <? } ?>
                    <?
                    $i = 0;
                    while($i<$clientes["maxpaginas"]){
                        $page = $clientes["pagina"]-($clientes["maxpaginas"]-$i);
                        if($page>0){
                        ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                        <?
                        }
                        $i++;
                    }
                    ?>
                    <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $clientes['pagina']; ?>);" href="javascript:;"><? echo $clientes["pagina"]; ?></a></li>
                    <?
                    $i = $clientes["maxpaginas"];
                    while($i>0){
                        $page = $clientes["pagina"]+($clientes["maxpaginas"]-($i-1));
                        if($page<=$clientes["numpaginas"]){
                        ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                        <?
                        }
                        $i--;
                    }
                    ?>
                    <? if($clientes["pagina"]<$clientes["numpaginas"]){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($clientes['pagina'])+1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                    <? } ?>
                    <? if($clientes["pagina"]<($clientes["numpaginas"]-1)){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $clientes['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
                    <? } ?>
                </ul>
            </nav>
        </div>
    </div>
    <?
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $clientes["mensaje"] ?>
    </div>
</div>
<?
}
?>