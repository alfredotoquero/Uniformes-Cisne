<div id="modalAgregar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-primary">
                <h4 class="modal-title" id="fullWidthModalLabel">Nuevo Pedido</h4>
                <small class="ms-2">Los campos marcados con * son obligatorios</small>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <form id="formPedido" name="formPedido" class="form-horizontal" role="form" method="post" autocomplete="off" enctype="multipart/form-data" action="?modulo1=pedidos">
                    <input type="text" name="hidden" autocomplete="nope" value="" style="display:none;">
                    <!-- <input type="hidden" name="accion" value="guardar"> -->
                    <input type="hidden" name="controladores" id="controladores" value="pedidos">
                    <input type="hidden" name="accion" id="accion" value="guardar">
                    <input type="hidden" name="subtotal" id="subtotal" value="0">
                    <input type="hidden" name="iva" id="iva" value="0">
                    <input type="hidden" name="total" id="total" value="0">
                    <input type="hidden" name="idcliente" id="idcliente" value="0">
                    <input type="hidden" name="idcontacto" id="idcontacto" value="0">
                    <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">

                    <!-- <input type="hidden" name="hk0967ih509" value="<?= $_SESSION["authToken"]; ?>"> -->

                    <div class="form-group" id="divGuardarCliente">
                        <div class="row">
                            <div class="col-12" >
                                <input type="checkbox" name="chkNoGuardarCliente" id="chkNoGuardarCliente" value="1"> Guardar datos del cliente solo en este pedido
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <div class="row">
                            <div class="col-2" ><label for="">Cliente</label></div>
                            <div class="col-8">
                                <input type="text" name="txtCliente" id="txtCliente" class="form-control" >
                            </div>
                            <div class="col-2">
                                <button type="button" onclick="fancy('/modulos/cotizaciones/buscarcliente.php');" id="btnBuscar1"><i class="fas fa-search fa-2x" id="iconBuscar1"></i></button>
                                <button type="button" onclick="borrar(1);" style="margin-left:15px;" id="btnBorrar1" disabled><i class="fas fa-eraser fa-2x fa-disabled" id="iconBorrar1"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <div class="row">
                            <div class="col-2" ><label for="">Correo</label></div>
                            <div class="col-3">
                                <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" >
                            </div>
                            <div class="col-2" ><label for="">Telefono</label></div>
                            <div class="col-3">
                                <input type="text" name="txtTelefono" id="txtTelefono" class="form-control" >
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <div class="row">
                            <div class="col-2" ><label for="">Contacto</label></div>
                            <div class="col-8">
                                <input type="text" name="txtContacto" id="txtContacto" class="form-control" >
                            </div>
                            <div class="col-2">
                                <button type="button" onclick="buscarContactos();" id="btnBuscar2"><i class="fas fa-search fa-2x" id="iconBuscar2"></i></button>
                                <button type="button"  onclick="borrar(2);" style="margin-left:15px;" id="btnBorrar2" disabled><i class="fas fa-eraser fa-2x fa-disabled" id="iconBorrar2"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Correo</label></div>
                            <div class="col-3">
                                <input type="text" name="txtCorreoC" id="txtCorreoC" class="form-control" >
                            </div>
                            <div class="col-2" ><label for="">Telefono</label></div>
                            <div class="col-3">
                                <input type="text" name="txtTelefonoC" id="txtTelefonoC" class="form-control" >
                            </div>
                        </div>
                    </div>

                    <p style="margin-top: 30px;">Ingresa la información de los productos que deseas agregar al pedido.</p>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-12" >
                                <input type="checkbox" name="chkPedidoInterno" id="chkPedidoInterno" value="1" onclick="validarPedidoInterno();"> Pedido interno
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-12" >
                                Tasa de IVA: 
                                <select name="slcIVA" id="slcIVA">
                                    <option value="8">8%</option>
                                    <option value="16">16%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12" >
                                <input type="checkbox" name="chkIncluyeIva" id="chkIncluyeIva" value="1"> Los Productos incluyen IVA
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-1">
                                <label for="">Cantidad</label>
                                <input type="text" name="txtCantidad" id="txtCantidad" value="" placeholder="1" class="form-control">
                            </div>
                            <div class="col-2">
                                <label for="">Origen</label>
                                <select name="slcOrigen" id="slcOrigen" class="form-control" onchange="mostrar(this.value);">
                                    <option value="0">--Selecciona Origen--</option>
                                    <option value="1">Inventario</option>
                                    <option value="2">Libre</option>
                                </select>
                            </div>
                            <div class="col-2" id="selectCategoria">
                                <label for="">Categoría</label>
                                <select name="slcCategoria" id="slcCategoria" class="form-control" disabled>
                                    <option value="0">--Selecciona una Categoría--</option>
                                    <?
                                    $categorias = $claseCategorias->obtenerCategorias($_POST);
                                    // $categorias = mysqli_query($con,"select * from tcategoriasproductos");
                                    // while ($categoria = mysqli_fetch_assoc($categorias)) {
                                    foreach ($categorias["categorias"] as $categoria) {
                                        ?>
                                        <option value="<?= $categoria["idcategoriaproducto"]; ?>"><?= $categoria["nombre"]; ?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-3" id="selectProducto">
                                <label for="">Producto</label>
                                <input type="hidden" name="idpartida" id="idpartida">
                                <input type="hidden" name="idproducto" id="idproducto">
                                <select name="slcProducto" id="slcProducto" class="form-control">
                                    <option value="0">--Selecciona un Producto--</option>
                                    <?
                                    // $productos = mysqli_query($con,"select * from tproductos where status = 1");
                                    $_POST["ordenar"] = 1;
                                    $productos = $claseProductos->obtenerProductos($_POST);
                                    // while ($producto = mysqli_fetch_assoc($productos)) {
                                    foreach ($productos["productos"] as $producto) {
                                        ?>
                                        <option value="<?= $producto["idproducto"]; ?>"><?= $producto["nombre"]; ?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-3" style="display:none;" id="textProducto">
                                <label for="">Producto</label>
                                <input type="text" name="txtProducto" id="txtProducto" class="form-control">
                            </div>
                            <div class="col-2">
                                <label for="">Precio Unitario</label>
                                <input type="text" name="txtPrecio" id="txtPrecio" value="" placeholder="0.00" class="form-control">
                            </div>
                            <div class="col-2" id="divAgregar">
                                <label for="">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProducto();">Agregar</button>
                            </div>
                            <div class="col-2" id="divEditar" style="display:none;">
                                <label for="">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="editarProducto();">Editar</button>
                            </div>
                        </div>
                    </div>


                    <div id="listaProductos"></div><br><br>

                    <div class="row mt-2">
                        <!-- <div class="col-9"></div> -->
                        <div class="col-3 offset-9">
                            <label for="">&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="crearEspecificacion();">Agregar Especificaci&oacute;n</button>
                        </div>
                    </div>

                    <div id="listaEspecificaciones"></div>
                    
                    <p>Ingresa la información final del pedido.</p>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Sucursal</label></div>
                            <div class="col-8">
                                <select name="slcSucursal" id="slcSucursal" class="form-control">
                                    <option value="">--Selecciona una Sucursal--</option>
                                    <!-- <option value="0">Almacen Principal</option> -->
                                    <?
                                    // $sucursales = mysqli_query($con,"select * from tsucursales where status = 'A'");
                                    $sucursales = $claseSucursales->obtenerSucursales($_POST);
                                    // while ($sucursal = mysqli_fetch_assoc($sucursales)) {
                                    foreach ($sucursales["sucursales"] as $sucursal) {
                                        ?>
                                        <option value="<?= $sucursal["idsucursal"]; ?>"><?= $sucursal["nombre"]; ?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Vigencia</label></div>
                            <div class="col-8">
                                <select name="slcVigencia" id="slcVigencia" class="form-control">
                                    <option value="0">--Selecciona una Vigencia--</option>
                                    <option value="1">1 Día</option>
                                    <option value="3">3 Días</option>
                                    <option value="7">7 Días</option>
                                    <option value="14">14 Días</option>
                                    <option value="21" selected>21 Días</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Linea 1</label></div>
                            <div class="col-8">
                                <input type="text" name="txtLinea1" id="txtLinea1" value="Precios en Moneda Nacional más I.V.A." placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Linea 2</label></div>
                            <div class="col-8">
                                <input type="text" name="txtLinea2" id="txtLinea2" value="Todo trabajo requiere 50% de anticipo y resto contra entrega." placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Linea 3</label></div>
                            <div class="col-8">
                                <input type="text" name="txtLinea3" id="txtLinea3" value="Tiempo de entrega 7-10 días hábiles." placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Linea 4</label></div>
                            <div class="col-8">
                                <input type="text" name="txtLinea4" id="txtLinea4" value="" placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-2" ><label for="">Linea 5</label></div>
                            <div class="col-8">
                                <input type="text" name="txtLinea5" id="txtLinea5" value="" placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="validarForm();">Guardar</button>
                <button type="button" class="btn btn-danger btn-block waves-effect waves-light" onClick="history.back();">Cancelar</button>
                <!-- al cancelar, cerrar el modal -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->