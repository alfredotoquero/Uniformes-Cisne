<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");

$clasePedidos = new Pedidos();

$pedidos = $clasePedidos->obtenerPedidos($_POST);

if($pedidos["respuesta"]=="OK"){
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
                <?php
                foreach($pedidos["pedidos"] as $pedido){
                    ?>
                    <tr>
                        <td>
                            <a href="javascript:;" class="btn btn-sm btn-<? if($pedido["status"]=="E"){?>success<?}else if($pedido["status"]=="A"){?>warning<?} ?>">
                                <i class="uil uil-check"></i>
                            </a>
                        </td>
                        <td><?= $pedido["idpedido"] ?></td>
                        <td><?= fecha_formateada_largo($pedido["fecha"]) ?></td>
                        <td><?= $pedido["usuario"] ?></td>
                        <td>$<?= number_format($pedido["total"],2) ?></td>
                        <td>
                            <a href="javascript:;" class="btn btn-info btn-sm waves-effect waves-light" onClick="toggleDiv('divPartidas<?= $pedido["idpedido"] ?>')"><i class="fas fa-plus"></i></a>
                            <a href="/modulos/pedidos/pedido.php?idpedido=<?= $pedido["idpedido"] ?>" class="btn btn-secondary btn-sm waves-effect waves-light" target="_blank">PDF</a>
                        </td>
                    </tr>

                    <tr style="display: none;" id="divPartidas<?= $pedido["idpedido"] ?>">
                        <td colspan="6">
                            <?
                            $_POST["idpedido"] = $pedido["idpedido"];
                            $partidas = $clasePedidos->obtenerPartidasCotizacion($_POST);
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
                                            <td>
                                                <?
                                                $detalle = "";

                                                $detalle .= $partida["producto"] . "<br>";

                                                $desgloses = explode(";",$partida["desgloses"]);
                                                foreach ($desgloses as $desglose) {
                                                    $desglose = explode(" : ",$desglose);
                                                    $color = $desglose[0];
                                                    $tallas = $desglose[1];
                                                    // $detalle .= "<br><br>".$partida["color"];
                                                    // $desglose = explode(",",$desglose);
                                                    $detalle .= "<br>".$color  . " / ". $tallas;
                                                }

                                                echo $detalle;
                                                ?>
                                            </td>
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
                    <? if($pedidos["pagina"]>2){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                    <? } ?>
                    <? if($pedidos["pagina"]>1){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['pagina']-1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                    <? } ?>
                    <?
                    $i = 0;
                    while($i<$pedidos["maxpaginas"]){
                        $page = $pedidos["pagina"]-($pedidos["maxpaginas"]-$i);
                        if($page>0){
                        ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                        <?
                        }
                        $i++;
                    }
                    ?>
                    <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['pagina']; ?>);" href="javascript:;"><? echo $pedidos["pagina"]; ?></a></li>
                    <?
                    $i = $pedidos["maxpaginas"];
                    while($i>0){
                        $page = $pedidos["pagina"]+($pedidos["maxpaginas"]-($i-1));
                        if($page<=$pedidos["numpaginas"]){
                        ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                        <?
                        }
                        $i--;
                    }
                    ?>
                    <? if($pedidos["pagina"]<$pedidos["numpaginas"]){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($pedidos['pagina'])+1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                    <? } ?>
                    <? if($pedidos["pagina"]<($pedidos["numpaginas"]-1)){ ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
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
            <?= $pedidos["mensaje"] ?>
        </div>
    </div>
    <?
    }
?>