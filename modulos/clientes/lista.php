<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();
$clientes = $claseClientes->obtenerClientes($_POST);

if($clientes["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th style="width: 240px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($clientes["clientes"] as $cliente){
            ?>
            <tr>
                <td><?= $cliente["nombre"] ?></td>
                <td><?= $cliente["telefono"] ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/clientes/detalle.php?idcliente=<?= $cliente["idcliente"] ?>&archivo=0" data-toggle="tooltip" title="detalle" class="btn btn-success btn-sm"><i class="uil uil-eye"></i></a>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/clientes/editar.php?idcliente=<?= $cliente["idcliente"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('clientes','eliminar','idcliente=<?= $cliente['idcliente'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
<?php
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