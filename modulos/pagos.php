<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseEmisores = new Emisores();
$claseClientes = new Clientes();

$emisores = $claseEmisores->obtenerEmisores();
$clientes = $claseClientes->obtenerClientes(array(
    "evitar_paginacion" => 1
));
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <!-- <div class="page-title-right">
                    <a href="/pedidos/agregar" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                </div> -->
                <h4 class="page-title">Pagos</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/pagos/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
							<input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control fecha" name="txtFechaInicial" id="txtFechaInicial" placeholder="Fecha Inicial" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control fecha" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha Final" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcEmisor" id="slcEmisor" class="form-control">
                                        <option value="">Todos los emisores</option>
                                        <?
                                        foreach ($emisores["emisores"] as $emisor) {
                                            ?>
                                            <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcCliente" id="slcCliente" class="form-control">
                                        <option value="">Todos los clientes</option>
                                        <?
                                        foreach ($clientes["clientes"] as $cliente) {
                                            ?>
                                            <option value="<?= $cliente["idcliente"] ?>"><?= $cliente["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-3">
                                    <input type="text" class="form-control" name="txtUUID" id="txtUUID" placeholder="UUID" autocomplete="off">
                                </div>
                                <div class="col-12 mt-2 col-md-3">
                                    <select name="slcStatus" id="slcStatus" class="form-control">
                                        <option value="0">Todos los status</option>
                                        <option value="1">Activas</option>
                                        <option value="2">Pendientes de cancelación</option>
                                        <option value="4">Complemento cancelado</option>
                                        <option value="3">Canceladas</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-auto">
                                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <hr>
                    <div id="divLista"></div>
                </div>
            </div>
        </div>
    </div>
</div>