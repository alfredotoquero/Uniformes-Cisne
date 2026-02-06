<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$claseCotizaciones = new Cotizaciones();
$cotizaciones = $claseCotizaciones->obtenerCotizaciones($_POST);

if($cotizaciones["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th width="40">#</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Total</th>
                <th>Fecha</th>
                <th style="width: 260px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($cotizaciones["cotizaciones"] as $cotizacion){
            ?>
            <tr>
                <td><?= $cotizacion["idcotizacion"]; ?></td>
                <td><?= $cotizacion["cliente"]; ?></td>
                <td><?= $cotizacion["usuario"]; ?></td>
                <td>$<?= number_format($cotizacion["total"],2); ?></td>
                <td><?= fecha_formateada($cotizacion["fecha"]); ?></td>
                <td class="text-end">
                    <a href="/modulos/cotizaciones/cotizacion.php?idcotizacion=<?= $cotizacion["idcotizacion"]; ?>" class="btn btn-info btn-sm mb-1" title="Ver cotización" target="_blank"><i class="uil uil-eye"></i></a>
                    <?
                    // modificar la cotizacion o convertirla a pedido solo se puede hacer si no se ha convertido a pedido aún
                    $_POST["idcotizacion"] = $cotizacion["idcotizacion"];
                    if (!$claseCotizaciones->convertidaAPedido($_POST)) {
                        ?>
                        <a href="/cotizaciones/agregar/<?= $cotizacion["idcotizacion"] ?>" class="btn btn-primary btn-sm mb-1"><i class="uil uil-edit"></i></a>
                        <a href="/cotizaciones/convertir/<?= $cotizacion["idcotizacion"]; ?>" class="btn btn-warning btn-sm mb-1" title="Convertir a Pedido"><i class="uil-file-check"></i></a>
                        <?
                    }

                    if ($claseCotizaciones->tieneHistorial($cotizacion["idcotizacionpadre"],$cotizacion["idcotizacion"]) ) {
                        ?>
                        <a href="/cotizaciones/historial/<?= $cotizacion["idcotizacion"]; ?>" class="btn btn-secondary btn-sm mb-1" title="Historial de cotizaciones"><i class="uil uil-file-alt"></i></a>
                        <?
                    }
                    if ($cotizacion["status"]!="C") {
                        ?>
                        <!-- <a href="javascript:;" onclick="solicitudServidor('cotizaciones','cancelar','idcotizacion=<?= $cotizacion['idcotizacion'] ?>','¿Deseas eliminar el registro?','');" class="btn btn-danger btn-sm mb-1"><i class="uil uil-times"></i></a> -->
                        <?
                    }
                    ?>
                </td>
            </tr>
            <?
            }
            ?>
        </tbody>
    </table>
</div>
<div class="row mt-3">
    <div class="col-12 text-end">
        <nav>
            <ul class="pagination">
                <? if($cotizaciones["pagina"]>2){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                <? } ?>
                <? if($cotizaciones["pagina"]>1){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $cotizaciones['pagina']-1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                <? } ?>
                <?
                $i = 0;
                while($i<$cotizaciones["maxpaginas"]){
                    $page = $cotizaciones["pagina"]-($cotizaciones["maxpaginas"]-$i);
                    if($page>0){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                    }
                    $i++;
                }
                ?>
                <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $cotizaciones['pagina']; ?>);" href="javascript:;"><? echo $cotizaciones["pagina"]; ?></a></li>
                <?
                $i = $cotizaciones["maxpaginas"];
                while($i>0){
                    $page = $cotizaciones["pagina"]+($cotizaciones["maxpaginas"]-($i-1));
                    if($page<=$cotizaciones["numpaginas"]){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                    }
                    $i--;
                }
                ?>
                <? if($cotizaciones["pagina"]<$cotizaciones["numpaginas"]){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($cotizaciones['pagina'])+1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                <? } ?>
                <? if($cotizaciones["pagina"]<($cotizaciones["numpaginas"]-1)){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $cotizaciones['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
                <? } ?>
            </ul>
        </nav>
    </div>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $cotizaciones["mensaje"] ?>
    </div>
</div>
<?
}
?>