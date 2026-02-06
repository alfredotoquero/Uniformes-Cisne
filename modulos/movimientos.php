<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseAlmacenes = new Almacenes();
$claseProductos = new Productos();

// tcuentas
// tformaspagoticket
// vrcuentaproductos
// vrpedidoproductos
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="javascript:;" onClick="generarPDFMovimientos()" class="btn btn-secondary btn-sm"><i class="mdi mdi-file-pdf-box me-1"></i>Generar PDF</a>
                    <a href="/movimientos/agregar" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                </div>
                <h4 class="page-title">Movimientos</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/movimientos/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-2">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <input type="text" class="form-control fecha" name="txtFechaInicial" id="txtFechaInicial" placeholder="Fecha Inicial" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <input type="text" class="form-control fecha" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha Final" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <select id="slcTipoMovimiento" name="slcTipoMovimiento" class="form-control">
                                        <option value="0">Todos los movimientos</option>
                                        <option value="1" <? if($_POST["slcTipoMovimiento"]=="1"){?> selected <?} ?>>Entradas</option>
                                        <option value="2" <? if($_POST["slcTipoMovimiento"]=="2"){?> selected <?} ?>>Salidas</option>
                                        <option value="3" <? if($_POST["slcTipoMovimiento"]=="3"){?> selected <?} ?>>Traspaso</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcAlmacenO" id="slcAlmacenO" class="form-control">
                                        <option value="">Almacen origen</option>
                                        <?
                                        $almacenes = $claseAlmacenes->obtenerAlmacenes(array());
                                        foreach ($almacenes["almacenes"] as $almacen) {
                                        ?>
                                        <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["nombre"] ?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcAlmacenD" id="slcAlmacenD" class="form-control">
                                        <option value="">Almacen destino</option>
                                        <?
                                        $almacenes = $claseAlmacenes->obtenerAlmacenes(array());
                                        foreach ($almacenes["almacenes"] as $almacen) {
                                        ?>
                                        <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["nombre"] ?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <select id="slcStatusMovimiento" name="slcStatusMovimiento" class="form-control">
                                        <option value="0">Todos los status</option>
                                        <option value="1">Pendientes</option>
                                        <option value="2">Finalizados</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-auto">
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