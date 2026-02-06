<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");

$claseAlmacenes = new Almacenes();
$claseProductos = new Productos();

$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
$productos = $claseProductos->obtenerProductos($_POST);

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary waves-effect waves-light" onClick="validarFormulario('formReorden')">Solicitar productos</button>
                </div>
                <h4 class="page-title">Reporte de Reorden</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/reportes/reorden/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <b>Almacenes:</b>
                                        <?
                                        foreach ($almacenes["almacenes"] as $almacen) {
                                        ?>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="slcAlmacenes[]" value="<?= $almacen['idalmacen'] ?>" checked>
                                                    <label class="form-check-label" for="<?= $almacen['idalmacen'] ?>">
                                                        <?= $almacen['nombre'] ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcProducto" id="slcProducto" class="form-control select2">
                                        <option value="0">--Todos los productos--</option>
                                        <?
                                        foreach ($productos["productos"] as $producto) {
                                        ?>
                                            <option value="<?= $producto["idproducto"] ?>"><?= $producto["nombre"] ?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-auto ms-auto">
                                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <hr>
                    <form name="formReorden" id="formReorden">
                        <input type="hidden" name="controlador" id="controlador" value="solicitudes">
                        <input type="hidden" name="accion" id="accion" value="solicitarProductos">

                        <div id="divLista"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>