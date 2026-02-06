<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");

$claseAlmacenes = new Almacenes();
$claseProveedores = new Proveedores();
$claseColores = new Colores();
$claseTallas = new Tallas();

$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
$proveedores = $claseProveedores->obtenerProveedores($_POST);
$colores = $claseColores->obtenerColores($_POST);
$_POST["portipotalla"] = 1;
$tallas = $claseTallas->obtenerTallas($_POST);

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="/productos/agregar" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                </div>
                <h4 class="page-title">Productos</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/productos/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 mb-2 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div>
                                <div class="col-12 mb-2 col-md-4">
                                    <select name="slcAlmacenes[]" id="slcAlmacenes" class="form-control select2AlmacenM" multiple>
                                        <?
                                        foreach ($almacenes["almacenes"] as $almacen) {
                                            ?>
                                            <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-2 col-md-4">
                                    <select name="slcTalla" id="slcTalla" class="form-control">
                                        <option value="0">Todas las tallas</option>
                                        <?
                                        foreach ($tallas["tallas"] as $talla) {
                                            ?>
                                            <option value="<?= $talla["idtalla"] ?>"><?= $talla["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-2 col-md-4">
                                    <select name="slcColor" id="slcColor" class="form-control">
                                        <option value="0">Todos los colores</option>
                                        <?
                                        foreach ($colores["colores"] as $color) {
                                            ?>
                                            <option value="<?= $color["idcolor"] ?>"><?= $color["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-2 col-md-4">
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
                                <div class="col-12 mb-2 col-md-4">
                                    <select name="slcExistencias" id="slcExistencias" class="form-control">
                                        <option value="0">Con o sin existencias</option>
                                        <option value="1">Con existencias</option>
                                        <option value="2">Sin existencias</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-2 col-md-4">
                                    <input type="text" name="txtPrecioMin" id="txtPrecioMin" class="form-control" placeholder="Precio Min">
                                </div>
                                <div class="col-12 mb-2 col-md-4">
                                    <input type="text" name="txtPrecioMax" id="txtPrecioMax" class="form-control" placeholder="Precio Max">
                                </div>
                                <div class="col-12 mb-2 col-md-auto">
                                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                                </div>
                                <div class="col-sm-2 mb-2">
                                    <div class="dropdown">
                                        <button class="btn btn-info btn-sm btn-block" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            Reportes <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="javascript:;" onclick="descargarReporte('formBusqueda','/modulos/productos/reportes/','existencias','chkProducto');">Existencias (general)</a></li>
                                            <li><a class="dropdown-item" href="javascript:;" onclick="descargarReporte('formBusqueda','/modulos/productos/reportes/','existenciassucursal','chkProducto');">Existencias (dividido por almacenes)</a></li>
                                            <li><a class="dropdown-item" href="javascript:;" onclick="descargarReporte('formBusqueda','/modulos/productos/reportes/','precios','chkProducto');">Precios</a></li>
                                        </ul>
                                    </div>

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