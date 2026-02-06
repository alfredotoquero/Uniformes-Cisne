<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["status"] = "A";
$tareas = $claseClientes->obtenerTareas($_POST);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="/recordatorios" class="btn btn-secondary btn-sm">Recordatorios <? if($tareas["total"]){?><div class="badge"><span><?= $tareas["total"]; ?></span></div><?} ?> </a>
                    <a href="javascript:;" onclick="marcarAtendida(this,1,'notificaciones','marcar_como_atendidas','','','cargar','',800);" data-toggle="tooltip" title="eliminar" class="btn btn-primary btn-sm">Marcar todas como atendidas</a>
                </div>
                <h4 class="page-title">Notificaciones</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="formBusqueda" name="formBusqueda">
                        <input type="hidden" name="archivo" id="archivo" value="/modulos/notificaciones/lista.php">
                        <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                    </form>
                    <div id="divLista"></div>
                </div>
            </div>
        </div>
    </div>
</div>