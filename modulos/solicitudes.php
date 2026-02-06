<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");//los ultimos dos se agregaron porque otro archivo los requeria
// include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseProveedores = new Proveedores();
$claseSolicitudes = new Solicitudes();
$claseCategorias = new Categorias();
// $claseProductos = new Productos();

// include($_SERVER["DOCUMENT_ROOT"]."/modulos/solicitudes/agregar.php");
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <!-- <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/solicitudes/agregar.php" class="btn btn-primary btn-sm"><i class="mdi mdi-plus me-1"></i> Producto</a> -->
                    <a href="/solicitudes/agregar" class="btn btn-primary btn-sm"><i class="mdi mdi-plus me-1"></i> Producto</a>
                </div>
                <h4 class="page-title">Solicitudes</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="formBusqueda" name="formBusqueda">
                        <div class="mb-3 bg-light p-3 rounded">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/solicitudes/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="solicitudes[]" id="solicitudes">
                            <!-- <input type="hidden" name="idproveedor" id="idproveedor" value="<? echo $_GET["modulo2"]; ?>"> -->
                            <div class="row">
                                <div class="col-12 mb-2 col-md-4">
                                    <select name="slcProveedor" id="slcProveedor" class="form-control" onchange="mostrarSolicitudes(this.value,'formBusqueda');">
                                        <option value="-1">--Selecciona un Proveedor--</option>
                                        <?
                                        $proveedores = $claseProveedores->obtenerProveedores($_POST);
                                        foreach ($proveedores["proveedores"] as $proveedor) {
                                            $_POST["idproveedor"] = $proveedor["idproveedor"];
                                            $total = $claseSolicitudes->obtenerTotalSolicitudes($_POST)["total"];
                                            if ($total) {
                                                ?>
                                                <option value="<?= $proveedor["idproveedor"] ?>" <? if($proveedor["idproveedor"]==$_GET["modulo2"]){?> selected <?} ?>><?= $proveedor["nombre"] . " (" . $total . ")" ?></option>
                                                <?
                                            }
                                        }
                                        // se obtiene el total de solicitudes que no tienen proveedor asignado
                                        $_POST["idproveedor"] = 0;
                                        $total = $claseSolicitudes->obtenerTotalSolicitudes($_POST)["total"];
                                        if ($total>0) {
                                            ?>
                                            <option value="0"><? echo "Otros " . " (" . $total . ")"; ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-2 col-md-auto">
                                    <button type="button" id="btnGenerar" onclick="generarOrdenCompra('formBusqueda');" class="btn btn-secondary btn-sm">Generar Orden de Compra</button>
                                    <button type="button" id="btnAsignar" onclick="asignarProveedor('formBusqueda');" class="btn btn-secondary btn-sm" disabled>Asignar Proveedor</button>
                                    <a href="javascript:;" onclick="cargarPDF('formBusqueda','/modulos/solicitudes/','impresion',1,'chkSolicitud');" class="btn btn-info btn-sm"><i class="mdi mdi-printer"></i></a>
                                    <a href="javascript:;" onclick="cargarPDF('formBusqueda','/modulos/solicitudes/','descargarsolicitudescomprapdf',1,'chkSolicitud');" class="btn btn-info btn-sm"><i class="mdi mdi-file-pdf-box"></i></a>
                                    <a href="javascript:;" onclick="eliminarSolicitudes('formBusqueda');" class="btn btn-danger btn-sm"><i class="mdi mdi-trash-can"></i></a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div id="divLista"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>