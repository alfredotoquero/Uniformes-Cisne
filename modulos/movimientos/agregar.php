<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$claseAlmacenes = new Almacenes();
$claseProductos = new Productos();
$claseMovimientos = new Movimientos();

$claseMovimientos->eliminarPartidasTMP($_SESSION["usuario"]["idusuario"]);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Agregar Movimiento</h4>
                <small class="ms-2">Los campos marcados con * son obligatorios</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formMovimiento" name="formMovimiento">
                            <input type="text" name="hidden" autocomplete="nope" value="" style="display:none;">
                            <input type="hidden" name="accion" id="accion" value="">
                            <input type="hidden" name="href" id="href" value="/movimientos">
                            <input type="hidden" name="idmovimientopartida" id="idmovimientopartida" value="1">
                            <input type="hidden" name="authToken" id="authToken" value="<?= $_SESSION["authToken"]; ?>">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-2" ><label for="">Tipo</label><span>*</span></div>
                                    <div class="col-10">
                                        <input type="hidden" name="tipomovimiento" id="tipomovimiento" value="">
                                        <select name="slcTipoMovimiento" id="slcTipoMovimiento" class="form-control" onchange="tipoMovimiento(this.value);">
                                            <option value="0">--Selecciona un tipo de movimiento--</option>
                                            <option value="1">Entrada</option>
                                            <option value="2">Salida</option>
                                            <option value="3">Traspaso</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-2" ><label for="">Almacen</label><span>*</span></div>
                                    <div class="col-10">
                                        <input type="hidden" name="almacen" id="almacen" value="">
                                        <input type="hidden" name="almacens" id="almacens" value="">
                                        <select name="slcAlmacen" id="slcAlmacen" class="form-control" onchange="cargarAlmacenes(this.value);">
                                            <option value="0">--Selecciona un almacen--</option>
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
                                </div>
                            </div>

                            <div class="form-group mt-2" style="display:none;" id="divAlmacenSec">
                                <div class="row">
                                    <div class="col-2" ><label for="">Almacen Secundario</label><span>*</span></div>
                                    <div class="col-10">
                                        <select name="slcAlmacenS" id="slcAlmacenS" class="form-control" onChange="$('#almacens').val(this.value);">
                                            <option value="0">--Selecciona un almacen--</option>
                                            <!-- poner opciones una vez que se haya seleccionado el primer almacen -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-2">
                                        <label for="">Producto</label>
                                    </div>
                                    <div class="col-8" id="selectProducto">
                                        <input type="hidden" name="idpartida" id="idpartida">
                                        <input type="hidden" name="idproducto" id="idproducto">
                                        <select name="slcProducto" id="slcProducto" class="form-control select2" onchange="asignarIdProductoMovimiento(this.value);">
                                            <option value="0">--Selecciona un Producto--</option>
                                            <?
                                            $productos = $claseProductos->obtenerProductos(array());
                                            foreach ($productos["productos"] as $producto) {
                                                ?>
                                                <option value="<?= $producto["idproducto"] ?>"><?= $producto["nombre"] . (($producto["codigobarras"]!="") ? " (" . $producto["codigobarras"] . ")" : "") ?></option>
                                                <?
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-2 text-end" id="divAgregar">
                                        <label for="">&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProductoMovimiento();">Agregar</button>
                                    </div>
                                </div>
                            </div>

                            <div id="divColorTalla"></div>

                            <div id="listaProductos"></div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-2" ><label for="">Notas</label></div>
                                    <div class="col-10">
                                        <textarea name="txtNotas" id="txtNotas" rows="5" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="validarFormMovimiento();">Guardar</button>
                                        <a href="/movimientos" class="btn btn-primary btn-warning waves-effect waves-light">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>