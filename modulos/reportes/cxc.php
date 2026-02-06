<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");

$claseClientes = new Clientes();
$clientes = $claseClientes->obtenerClientes($_POST);

$claseSucursales = new Sucursales();
$sucursales = $claseSucursales->obtenerSucursales($_POST);

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <!-- <div class="page-title-right">
                </div> -->
                <h4 class="page-title">Cuentas por Cobrar</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/reportes/cxc/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <select name="slcCliente" id="slcCliente" class="form-control">
                                        <option value="0">--Selecciona un cliente--</option>
                                        <?
                                        foreach ($clientes["clientes"] as $cliente) {
                                            ?>
                                            <option value="<?= $cliente["idcliente"] ?>"><?= $cliente["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <!-- sucursales con selec2 para seleccionar reporte de multiples sucursales -->
                                    <select name="slcSucursales[]" id="slcSucursales" class="form-control select2SucursalM" multiple="multiple">
                                        <!-- <option value="0">--Selecciona una sucursal--</option> -->
                                        <?
                                        foreach ($sucursales["sucursales"] as $sucursal) {
                                            ?>
                                            <option value="<?= $sucursal["idsucursal"] ?>"><?= $sucursal["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <!-- <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div> -->
                                <div class="col-12 col-md-auto">
                                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                                    <button type="button" class="btn btn-info btn-sm btn-block waves-effect waves-light" name="btnGenerar" id="btnGenerar" onClick="cargarPDF('formBusqueda','/modulos/reportes/cxc/','cxcpdf');">Generar PDF</button>
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