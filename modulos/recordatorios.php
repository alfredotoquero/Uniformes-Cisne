<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["slcConRecordatorio"] = 3;
$clientes = $claseClientes->obtenerClientes($_POST);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                </div>
                <h4 class="page-title">Recordatorios</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <form name="formTareas2" id="formTareas2" style="display:none;">
                <input type="hidden" name="controlador" id="controlador" value="clientes">
                <input type="hidden" name="accion" id="accion" value="editartarea">
                <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/tareas/lista.php">
                <input type="hidden" name="contenedor" id="contenedor" value="divTareas">
                <input type="hidden" name="idseguimiento" id="idseguimiento" value="0">
                <input type="hidden" name="idtarea" id="idtarea" value="0">
                <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
                <div class="mt-3 mb-3">
                    <label for="txtTitulo" class="form-label">Título<span>*</span></label>
                    <input type="text" class="form-control requerido" name="txtTitulo" id="txtTitulo" placeholder="Ingresa el Título" autocomplete="off" data-mensajeerror="Debes indicar un título">
                </div>
                <div class="mb-3">
                    <label for="txtComentarios" class="form-label">Comentarios<span>*</span></label>
                    <textarea name="txtComentarios" id="txtComentarios" rows="5" class="form-control" autocomplete="off"></textarea>
                </div>
                <div class="mb-3">
                    <label for="txtFecha" class="form-label">Fecha<span>*</span></label>
                    <input type="text" name="txtFecha" id="txtFecha" rows="5" class="form-control requerido date" autocomplete="off" data-mensajeerror="Debes indicar una fecha">
                </div>
                <button type="button" onclick="validarFormulario('formTareas2');" class="btn btn-primary">Guardar</button>
                <button type="button" onclick="cancelarFormulario('formTareas2','btnAgregarT')" class="btn btn-danger">Cancelar</button>
                <hr>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/recordatorios/lista.php">
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