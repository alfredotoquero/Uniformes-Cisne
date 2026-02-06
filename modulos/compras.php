<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");

$claseProveedores = new Proveedores();
$proveedores = $claseProveedores->obtenerProveedores($_POST);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Compras</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/compras/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-4">
                                    <select name="slcProveedor" id="slcProveedor" class="form-control">
                                        <option value="0">Todos los proveedores</option>
                                        <?
                                        foreach ($proveedores["proveedores"] as $proveedor) {
                                            ?>
                                            <option value="<?= $proveedor["idproveedor"] ?>"><?= $proveedor["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <select name="slcStatus" id="slcStatus" class="form-control">
                                        <!-- <option value="0">Status:</option> -->
                                        <option value="4">Todas las compras</option>
                                        <option value="1">No Recibidas</option>
                                        <option value="2">Parcialmente Recibidas</option>
                                        <option value="3">Totalmente Recibidas</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-4">
                                    <select name="slcFiltroFechas" id="slcFiltroFechas" class="form-control">
                                        <option value="0">Todas las fechas</option>
                                        <option value="1">Fecha de Registro</option>
                                        <option value="2">Fecha de Recepción</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-4">
                                    <input type="text" class="form-control fecha" name="txtFechaInicial" id="txtFechaInicial" placeholder="Fecha Inicial" autocomplete="off">
                                </div>
                                <div class="col-12 mt-2 col-md-4">
                                    <input type="text" class="form-control fecha" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha Final" autocomplete="off">
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