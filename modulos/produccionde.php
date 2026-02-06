<?
if (isset($_GET["modulo2"]) and $_GET["modulo2"] > 0) {
    include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");

    $claseClientes = new Clientes();
    $nombre = $claseClientes->obtenerCliente(array("idcliente" => $_GET["modulo2"]))["cliente"]["nombre"];
} else {
    $nombre = str_replace("-", " ", $_GET["modulo3"]);
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Producción - Cliente: <?= $nombre ?></h4>
            </div>
        </div>
    </div>

    <form id="formBusqueda" name="formBusqueda">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/produccionde/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divLista">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["modulo2"] ?>">
        <input type="hidden" name="cliente" id="cliente" value="<?= $nombre ?>">
    </form>

    <div class="row mb-3">
        <div class="col-12 col-sm-3 offset-sm-9">
            <a href="/produccion" class="btn btn-danger waves-effect pull-right" style="float:right;margin-right:0px;margin-left:10px;">Regresar</a>
            <button type="button" class="btn btn-info btn-block waves-effect waves-light" name="btnGenerar" id="btnGenerar" onclick="cargarPDF('formBusqueda','/modulos/produccionde/','producciondepdf');" style="float:right;margin-left:10px;">Generar PDF</button>
        </div>
    </div>

    <div id="divLista"></div>
</div>