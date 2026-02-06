<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");

$claseSucursales = new Sucursales();
$sucursales = $claseSucursales->obtenerSucursales(null);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Producción</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/produccion/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Nombre de cliente, # de pedido" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-4">
                                    <select name="slcSucursal" id="slcSucursal" class="form-control">
                                        <option value="0">Todas las sucursales</option>
                                        <?
                                        foreach ($sucursales["sucursales"] as $sucursal) {
                                            ?>
                                            <option value="<?= $sucursal["idsucursal"] ?>"><?= $sucursal["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <select name="slcSurtido" id="slcSurtido" class="form-control">
                                        <!-- <option value="0">Status:</option> -->
                                        <option value="-1">Todos</option>
                                        <option value="1">Surtidos</option>
                                        <option value="0">Sin surtir</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-4">
                                    <select name="slcPendiente" id="slcPendiente" class="form-control">
                                        <!-- <option value="0">Status:</option> -->
                                        <option value="-1">Filtrar por:</option>
                                        <option value="0">Pendiente</option>
                                        <option value="1">En proceso</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-4">
                                    <select name="slcOrden" id="slcOrden" class="form-control">
                                        <!-- <option value="0">Status:</option> -->
                                        <option value="-1">Ordenar por:</option>
                                        <option value="asc">Pedido (ascendente)</option>
                                        <option value="desc">Pedido (descendente)</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-auto">
                                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                                    <button type="button" class="btn btn-sm btn-info btn-block waves-effect waves-light" name="btnGenerar" id="btnGenerar" onclick="cargarPDF('formBusqueda','/modulos/produccion/','produccionpdf');">Generar PDF</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="divLista"></div>
</div>