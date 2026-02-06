<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");

$claseUsuarios = new Usuarios();
$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$usuario = $claseUsuarios->obtenerUsuario($_POST)["usuario"];

$claseMovimientos = new Movimientos();
$movimientos = $claseMovimientos->obtenerMovimientos($_POST);

if($movimientos["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th width="20">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" role="switch" checked onchange="cambiarChecks(this,'chkMovimiento')">
                        <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                    </div>
                </th>
                <th width="40">#</th>
                <th width="40"></th>
                <th>Tipo</th>
                <th>Almacen(es)</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($movimientos["movimientos"] as $movimiento){
            ?>
            <tr>
                <td>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input chkMovimiento" role="switch" name="chkMovimientos[]" checked value="<?= $movimiento["idmovimientoinventario"] ?>">
                        <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                    </div>
                </td>
                <td class="text-center">
                    <?= $movimiento["idmovimientoinventario"]."<br>(".$movimiento["folio"].")".(($movimiento["folio2"]>0 && $movimiento["idtipomovimiento"]==3) ? "<br>(".$movimiento["folio2"].")" : ""); ?>
                </td>
                <td class="text-center">
                    <?
                    if ($movimiento["idtipomovimiento"]==3) {
                        if ($movimiento["autorizacion"]==0 and (strpos($usuario["almacenes"], $movimiento["idalmacen"]) !== false)){
                            ?>
                            <i class="fas fa-exclamation-triangle"></i>
                            <?
                        }else if ($movimiento["autorizacion"]==1){
                            ?>
                            <i class="fas fa-ban"></i>
                            <?
                        }else if ($movimiento["autorizacion"]==2 and $movimiento["recepcionparcial"]==1){
                            ?>
                            <i class="fas fa-asterisk"></i>
                            <?
                        }else if ($movimiento["recepcionparcial"]==0 and (strpos($usuario["almacenes"], $movimiento["idalmacensecundario"]) !== false)){
                            ?>
                            <i class="fas fa-exclamation-triangle"></i>
                            <?
                        }
                    }
                    ?>
                </td>
                <td><?= $movimiento["movimiento"] ?></td>
                <td><?= $movimiento["almacen"].((!is_null($movimiento["almacensecundario"])) ? "<br>".$movimiento["almacensecundario"] : "") ?></td>
                <td><?= fecha_formateada($movimiento["fecha"]); ?></td>
                <td><?= $movimiento["usuario"]; ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/movimientos/ver.php?idmovimientoinventario=<?= $movimiento["idmovimientoinventario"] ?>" class="btn btn-success btn-sm"><i class="uil uil-eye"></i></a>
                    <!-- <a href="javascript:;" class="btn btn-secondary btn-sm"><i class="uil uil-print"></i></a> -->
                    <a href="/modulos/movimientos/movimiento.php?idmovimientoinventario=<?= $movimiento["idmovimientoinventario"]; ?>" class="btn btn-secondary btn-sm" target="_blank"><i class="uil uil-print"></i></a>
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
                <? if($movimientos["pagina"]>2){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                <? } ?>
                <? if($movimientos["pagina"]>1){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<?= $movimientos['pagina']-1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                <? } ?>
                <?
                $i = 0;
                while($i<$movimientos["maxpaginas"]){
                    $page = $movimientos["pagina"]-($movimientos["maxpaginas"]-$i);
                    if($page>0){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<?= $page; ?>);" href="javascript:;"><?= $page; ?></a></li>
                    <?
                    }
                    $i++;
                }
                ?>
                <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<?= $movimientos['pagina']; ?>);" href="javascript:;"><?= $movimientos["pagina"]; ?></a></li>
                <?
                $i = $movimientos["maxpaginas"];
                while($i>0){
                    $page = $movimientos["pagina"]+($movimientos["maxpaginas"]-($i-1));
                    if($page<=$movimientos["numpaginas"]){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<?= $page; ?>);" href="javascript:;"><?= $page; ?></a></li>
                    <?
                    }
                    $i--;
                }
                ?>
                <? if($movimientos["pagina"]<$movimientos["numpaginas"]){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<?= ($movimientos['pagina'])+1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                <? } ?>
                <? if($movimientos["pagina"]<($movimientos["numpaginas"]-1)){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<?= $movimientos['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
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
        <?= $movimientos["mensaje"] ?>
    </div>
</div>
<?
}
?>