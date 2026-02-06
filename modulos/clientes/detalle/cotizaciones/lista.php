<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$claseCotizaciones = new Cotizaciones();

$cotizaciones = $claseCotizaciones->obtenerCotizaciones($_POST);

if($cotizaciones["respuesta"]=="OK"){
    ?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width:50px;"></th>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th style="width: 170px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach($cotizaciones["cotizaciones"] as $cotizacion){
                    ?>
                    <tr>
                        <td>
                            <?
                            $_POST["idcotizacion"] = $cotizacion["idcotizacion"];
                            if ($claseCotizaciones->convertidaAPedido($_POST)) {
                                ?>
                                <a href="javascript:;" class="btn btn-sm btn-success">
                                    <i class="uil uil-check"></i>
                                </a>
                                <?
                            }
                            ?>
                        </td>
                        <td><?= $cotizacion["idcotizacion"] ?></td>
                        <td><?= fecha_formateada_largo($cotizacion["fecha"]) ?></td>
                        <td><?= $cotizacion["usuario"] ?></td>
                        <td>$<?= number_format($cotizacion["total"],2) ?></td>
                        <td>
                            <a href="javascript:;" class="btn btn-info btn-sm waves-effect waves-light" onClick="toggleDiv('divPartidas<?= $cotizacion["idcotizacion"] ?>')"><i class="fas fa-plus"></i></a>
                            <a href="/modulos/cotizaciones/cotizacion.php?idcotizacion=<?= $cotizacion["idcotizacion"] ?>" class="btn btn-secondary btn-sm waves-effect waves-light" target="_blank">PDF</a>
                        </td>
                    </tr>

                    <tr style="display: none;" id="divPartidas<?= $cotizacion["idcotizacion"] ?>">
                        <td colspan="5">
                            <?
                            $partidas = $claseCotizaciones->obtenerPartidas($_POST);
                            ?>
                            <table class="table mb-0">
                                <thead class="table">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <!-- <th style="width: 120px;"></th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    foreach($partidas["partidas"] as $partida){
                                        ?>
                                        <tr>
                                            <td><?= $partida["producto"] ?></td>
                                            <td><?= $partida["cantidad"] ?></td>
                                        </tr>
                                        <?
                                    }
                                    ?>
                                </tbody>
                            </table>

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