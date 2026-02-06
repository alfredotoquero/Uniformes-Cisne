<div id="modalAgregar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-primary">
                <h4 class="modal-title" id="fullWidthModalLabel">Agregar Movimiento</h4>
                <small class="ms-2">Los campos marcados con * son obligatorios</small>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <form id="formMovimiento" name="formMovimiento" class="form-horizontal" role="form" method="post" autocomplete="off" enctype="multipart/form-data" action="?modulo1=movimientos">
                    <input type="text" name="hidden" autocomplete="nope" value="" style="display:none;">
                    <!-- <input type="hidden" name="accion" value="guardar"> -->
                    <input type="hidden" name="accion" id="accion" value="">
                    <input type="hidden" name="idmovimientopartida" id="idmovimientopartida" value="1">
                    <input type="hidden" name="hk0967ih509" value="<? echo $_SESSION["authToken"]; ?>">

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
                                <select name="slcAlmacenS" id="slcAlmacenS" class="form-control">
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
                                <select name="slcProducto" id="slcProducto" class="form-control" onchange="cargarColorTallaProductoMovimiento(this.value);">
                                    <option value="0">--Selecciona un Producto--</option>
                                    <?
                                    $productos = $claseProductos->obtenerProductos(array());
                                    foreach ($productos["productos"] as $producto) {
                                    ?>
                                    <option value="<?= $producto["idproducto"] ?>"><?= $producto["nombre"] ?></option>
                                    <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-2 text-end" id="divAgregar">
                                <label for="">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProductoMovimiento();">Agregar</button>
                            </div>
                            <!-- <div class="col-2" id="divEditar" style="display:none;">
                                <label for="">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="editarProducto();">Editar</button>
                            </div> -->
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="validarForm();">Guardar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->