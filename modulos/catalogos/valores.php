<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");

$_POST["idcatalogo"] = $_GET["modulo3"];

$claseCatalogos = new Catalogos();
$catalogo = $claseCatalogos->obtenerCatalogo($_POST)["catalogo"];

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/catalogos/valores/agregar.php?idcatalogo=<?= $_GET["modulo3"]; ?>" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                    <a href="/catalogos" class="btn btn-danger btn-sm"><i class="uil uil-history-alt me-1"></i>Regresar</a>
                </div>
                <h4 class="page-title">Valores del catalogo "<?= $catalogo["nombre"]; ?>"</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/catalogos/valores/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="idcatalogo" id="idcatalogo" value="<?= $_GET["modulo3"] ?>">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
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