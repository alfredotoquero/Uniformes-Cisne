<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");

$claseCategorias = new Categorias();
$claseProductos = new Productos();
$claseSucursales = new Sucursales();
$claseCotizaciones = new Cotizaciones();
$claseClientes = new Clientes();
$claseTiendas = new Tiendas();
$claseTiposProducto = new TiposProducto();

$idusuario = $_SESSION["usuario"]["idusuario"];

$claseCotizaciones->eliminarPartidasTMP($idusuario);
$claseCotizaciones->eliminarDesglosesTMP($idusuario);
$claseCotizaciones->eliminarEspecificacionesTMP($idusuario);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Nuevo Pedido</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <b>Ingresa los datos solicitados</b><br><small>Los campos marcados con * son obligatorios</small>
                </div>
                <div class="card-body">
                    <form id="formCotizacion" name="formCotizacion">
                        <input type="hidden" name="controlador" id="controlador" value="pedidos">
                        <input type="hidden" name="accion" id="accion" value="agregar">
                        <input type="hidden" name="archivo" id="archivo" value="/modulos/cotizaciones/listaproductosconvertir.php">
                        <input type="hidden" name="contenedor" id="contenedor" value="listaProductos">
                        <input type="hidden" name="href" id="href" value="/pedidos">
                        <input type="hidden" name="subtotal" id="subtotal" value="0">
                        <input type="hidden" name="iva" id="iva" value="0">
                        <input type="hidden" name="total" id="total" value="0">
                        <input type="hidden" name="idcliente" id="idcliente" value="0">
                        <input type="hidden" name="idcontacto" id="idcontacto" value="0">
                        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">

                        <p>1. Ingresa la información del cliente. Si es público en general, omitir este paso.</p>
                        
                        <div class="mb-3 bg-light p-3 rounded">
                            <div class="row mb-2" id="divGuardarCliente">
                                <div class="col-12">
                                    <input type="checkbox" name="chkNoGuardarCliente" id="chkNoGuardarCliente" value="1"> <label for="">Guardar Datos del Cliente Sólo en Esta Cotización</label>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2"><label for="">Cliente</label></div>
                                <div class="col-12 col-md-8">
                                    <select name="slcCliente" id="slcCliente" class="form-control select2t" onchange="seleccionarCliente(this);">
                                        <option value="0">--Seleccionar--</option>
                                        <?
                                            $clientes = $claseClientes->obtenerClientes($_POST);
                                            foreach($clientes["clientes"] as $cliente){
                                                ?>
                                                <option value="<?= $cliente["idcliente"] ?>" data-nombre="<?= $cliente["nombre"] ?>" data-correo="<?= $cliente["correo"] ?>" data-telefono="<?= $cliente["telefono"] ?>" data-idciudad="<?= $cliente["idciudad"] ?>"><?= $cliente["nombre"] ?></option>
                                                <?
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="button" onclick="borrarDatosCliente();" style="margin-left:15px;" id="btnBorrar1" disabled class="btn btn-sm btn-danger"><i class="fas fa-eraser fa-disabled" id="iconBorrar1"></i></button>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Correo</label></div>
                                <div class="col-12 col-md-3">
                                    <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" >
                                </div>
                                <div class="col-12 col-md-2" ><label for="">Teléfono</label></div>
                                <div class="col-12 col-md-3">
                                    <input type="text" name="txtTelefono" id="txtTelefono" class="form-control" >
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="slcCiudad" class="form-label">Ciudad</label></div>
                                <div class="col-12 col-md-8">
                                    <select name="slcCiudad" id="slcCiudad" class="form-control">
                                        <option value="0">--Seleccionar--</option>
                                        <option value="1">Todas las ciudades</option>
                                        <?
                                        $ciudades = $claseClientes->obtenerCiudades();
                                        foreach ($ciudades["ciudades"] as $ciudad) {
                                            ?>
                                            <option value="<?= $ciudad["idciudad"] ?>"><?= $ciudad["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="slcTienda" class="form-label">Tienda</label></div>
                                <div class="col-12 col-md-8">
                                    <select name="slcTienda" id="slcTienda" class="form-control">
                                        <option value="0">--Seleccionar--</option>
                                        <?
                                        $tiendas = $claseTiendas->obtenerTiendas();
                                        foreach ($tiendas["tiendas"] as $tienda) {
                                            ?>
                                            <option value="<?= $tienda["idtienda"] ?>"><?= $tienda["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Contacto</label></div>
                                <div class="col-12 col-md-8" id="divContacto">
                                    <select name="slcContacto" id="slcContacto" class="form-control select2t" onchange="seleccionarContacto(this);">
                                        <option value="0">--Seleccionar--</option>
                                        
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="button"  onclick="borrarDatosContacto();" style="margin-left:15px;" id="btnBorrar2" disabled class="btn btn-sm btn-danger"><i class="fas fa-eraser fa-disabled" id="iconBorrar2"></i></button>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Correo</label></div>
                                <div class="col-12 col-md-3">
                                    <input type="text" name="txtCorreoC" id="txtCorreoC" class="form-control" >
                                </div>
                                <div class="col-12 col-md-2" ><label for="">Telefono</label></div>
                                <div class="col-12 col-md-3">
                                    <input type="text" name="txtTelefonoC" id="txtTelefonoC" class="form-control" >
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-2" ><label for="">Puesto</label></div>
                                <div class="col-12 col-md-8">
                                    <input type="text" name="txtPuesto" id="txtPuesto" class="form-control" >
                                </div>
                            </div>
                        </div>


                        <p style="margin-top: 30px;">2. Ingresa la información de los productos que deseas agregar a la cotización.</p>

                        <div class="row">
                            <input type="hidden" name="chkSubtotalizar" id="chkSubtotalizar" value="1">
                            <div class="col-12 col-md-4">
                                <div class="mb-3 bg-light p-3 rounded">
                                    <input type="checkbox" name="chkPedidoInterno" id="chkPedidoInterno" value="1" onclick="validarPedidoInterno();"> Pedido interno
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3 bg-light p-3 rounded">
                                    Tasa de IVA: 
                                    <select name="slcIVA" id="slcIVA">
                                        <option value="8">8%</option>
                                        <option value="16">16%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3 bg-light p-3 rounded">
                                    <input type="checkbox" name="chkIncluyeIva" id="chkIncluyeIva" value="1"> Los Productos incluyen IVA
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 bg-light p-3 rounded">
                            <div class="row">
                                <div class="col-12 col-md-1">
                                    <label for="" class="mb-2">Cantidad</label>
                                    <input type="text" name="txtCantidad" id="txtCantidad" value="" placeholder="1" class="form-control">
                                </div>
                                <div class="col-12 col-md-2">
                                    <label for="" class="mb-2">Origen</label>
                                    <select name="slcOrigen" id="slcOrigen" class="form-control" onchange="mostrar2(this.value);">
                                        <option value="0">--Selecciona Origen--</option>
                                        <option value="1">Inventario</option>
                                        <option value="2">Libre</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" id="selectCategoria">
                                    <label for="" class="mb-2">Categoría</label>
                                    <select name="slcCategoria" id="slcCategoria" class="form-control" disabled>
                                        <option value="0">--Selecciona una Categoría--</option>
                                        <?
                                        $categorias = $claseCategorias->obtenerCategorias($_POST);
                                        foreach ($categorias["categorias"] as $categoria) {
                                            ?>
                                            <option value="<?= $categoria["idcategoriaproducto"]; ?>"><?= $categoria["nombre"]; ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" style="display:none;" id="selectTipoProducto">
                                    <label for="" class="mb-2">Tipo de Producto</label>
                                    <select name="slcTipoProducto" id="slcTipoProducto" class="form-control select2">
                                        <option value="0">--Selecciona un Tipo--</option>
                                        <?
                                        $tiposproducto = $claseTiposProducto->obtenerTiposProducto($_POST);
                                        if($tiposproducto["respuesta"] == "OK"){
                                            while($tipoproducto = mysqli_fetch_assoc($tiposproducto["tiposproducto"])){
                                                ?>
                                                <option value="<?= $tipoproducto["idtipoproducto"]; ?>"><?= $tipoproducto["nombre"]; ?></option>
                                                <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3" id="selectProducto">
                                    <label for="" class="mb-2">Producto</label>
                                    <input type="hidden" name="idpartida" id="idpartida">
                                    <input type="hidden" name="idproducto" id="idproducto">
                                    <select name="slcProducto" id="slcProducto" class="form-control select2">
                                        <option value="0">--Selecciona un Producto--</option>
                                        <?
                                        $productos = $claseProductos->obtenerProductos($_POST);
                                        foreach ($productos["productos"] as $producto) {
                                            ?>
                                            <option value="<?= $producto["idproducto"]; ?>"><?= $producto["nombre"]; ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" style="display:none;" id="textProducto">
                                    <label for="" class="mb-2">Producto</label>
                                    <input type="text" name="txtProducto" id="txtProducto" class="form-control">
                                </div>
                                <div class="col-12 col-md-2" id="divPrecio">
                                    <label for="" class="mb-2">Precio Unitario</label>
                                    <input type="text" name="txtPrecio" id="txtPrecio" value="" placeholder="0.00" class="form-control maskNumber">
                                </div>
                                <div class="col-12 col-md-2" id="divAgregar">
                                    <label for="" class="mb-2">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProductoCotizacion();">Agregar</button>
                                </div>
                                <div class="col-12 col-md-2" id="divEditar" style="display:none;">
                                    <label for="" class="mb-2">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="editarProductoCotizacion();">Editar</button>
                                </div>
                            </div>
                        </div>

                        <div id="listaProductos" class="mb-3"></div>

                        <p>3. Ingresa la información de las especificaciones.</p>

                        <div class="row mb-3">
                            <div class="col-12 col-md-auto ms-auto">
                                <button type="button" data-fancybox="" data-type="ajax" data-src="/modulos/cotizaciones/agregarespecificacion.php" class="btn btn-primary btn-block waves-effect waves-light">Agregar Especificación</button>
                            </div>
                        </div>

                        <div id="listaEspecificaciones"></div>

                        <p>4. Ingresa la información final del pedido.</p>

                        <div class="mb-3 bg-light p-3 rounded">
                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Sucursal</label></div>
                                <div class="col-12 col-md-4">
                                    <select name="slcSucursal" id="slcSucursal" class="form-control">
                                        <option value="">--Selecciona una Sucursal--</option>
                                        <?
                                        $sucursales = $claseSucursales->obtenerSucursales($_POST);
                                        foreach ($sucursales["sucursales"] as $sucursal) {
                                            ?>
                                            <option value="<?= $sucursal["idsucursal"]; ?>"><?= $sucursal["nombre"]; ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" ><label for="">Vigencia</label></div>
                                <div class="col-12 col-md-4">
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
                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Linea 1</label></div>
                                <div class="col-12 col-md-10">
                                    <input type="text" name="txtLinea1" id="txtLinea1" value="Precios en Moneda Nacional más I.V.A." placeholder="" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Linea 2</label></div>
                                <div class="col-12 col-md-10">
                                    <input type="text" name="txtLinea2" id="txtLinea2" value="Todo trabajo requiere 50% de anticipo y resto contra entrega." placeholder="" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Linea 3</label></div>
                                <div class="col-12 col-md-10">
                                    <input type="text" name="txtLinea3" id="txtLinea3" value="Tiempo de entrega 7-10 días hábiles." placeholder="" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Linea 4</label></div>
                                <div class="col-12 col-md-10">
                                    <input type="text" name="txtLinea4" id="txtLinea4" value="" placeholder="" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-2" ><label for="">Linea 5</label></div>
                                <div class="col-12 col-md-10">
                                    <input type="text" name="txtLinea5" id="txtLinea5" value="" placeholder="" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-auto ms-auto">
                                <a href="javascript:;" onclick="validarFormPedido();" class="btn btn-primary btn-sm">Guardar</a>
                                <a href="/pedidos" class="btn btn-warning btn-sm">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>