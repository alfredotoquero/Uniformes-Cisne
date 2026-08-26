<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");

$claseCategorias = new Categorias();
$claseProductos = new Productos();
$claseSucursales = new Sucursales();
$claseCotizaciones = new Cotizaciones();
$claseTiposProducto = new TiposProducto();
$claseClientes = new Clientes();

$idusuario = $_SESSION["usuario"]["idusuario"];

$_POST["idcotizacion"] = $_GET["modulo3"];
$cotizacion = $claseCotizaciones->obtenerCotizacion($_POST)["cotizacion"];

$claseCotizaciones->eliminarPartidasTMP($idusuario);

$_POST["idusuario"] = $idusuario;
$claseCotizaciones->agregarPartidasTMP($_POST);

$claseCotizaciones->eliminarDesglosesTMP($idusuario);

$claseCotizaciones->eliminarEspecificacionesTMP($idusuario);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Convertir Cotización a Pedido</h4>
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
                        <input type="hidden" name="controlador" id="controlador" value="cotizaciones">
                        <input type="hidden" name="accion" id="accion" value="convertir">
                        <input type="hidden" name="archivo" id="archivo" value="/modulos/cotizaciones/listaproductosconvertir.php">
                        <input type="hidden" name="contenedor" id="contenedor" value="listaProductos">
                        <input type="hidden" name="href" id="href" value="/cotizaciones">
						<input type="hidden" name="subtotal" id="subtotal" value="0">
						<input type="hidden" name="iva" id="iva" value="0">
						<input type="hidden" name="total" id="total" value="0">
						<input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
						<input type="hidden" name="idcotizacion" value="<?= isset($_GET["modulo3"]) ? $_GET["modulo3"] : "0"; ?>">
						<input type="hidden" name="idcliente" id="idcliente" value="0">
						<input type="hidden" name="idcontacto" id="idcontacto" value="0">

                        <p>1. Ingresa la información del cliente. Si es público en general, omitir este paso.</p>
                        
                        <div class="mb-3 bg-light p-3 rounded">

                            <div class="row mb-2">
                                <div class="col-12 col-md-2"><label for="">Cliente</label></div>
                                <div class="col-12 col-md-10">
                                    <select name="slcCliente" id="slcCliente" class="form-control select2t" onchange="seleccionarCliente(this);" disabled>
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
                            </div>

                            <div class="row mb-2">
                                <div class="col-12 col-md-2" ><label for="">Correo</label></div>
                                <div class="col-12 col-md-4">
                                    <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" >
                                </div>
                                <div class="col-12 col-md-2" ><label for="">Teléfono</label></div>
                                <div class="col-12 col-md-4">
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
                                <div class="col-12 col-md-2" ><label for="">Contacto</label></div>
                                <div class="col-12 col-md-10" id="divContacto">
                                    <select name="slcContacto" id="slcContacto" class="form-control select2t" onchange="seleccionarContacto(this);">
                                        <option value="0">--Seleccionar--</option>
                                        
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-2" ><label for="">Correo</label></div>
                                <div class="col-12 col-md-4">
                                    <input type="text" name="txtCorreoC" id="txtCorreoC" class="form-control" >
                                </div>
                                <div class="col-12 col-md-2" ><label for="">Telefono</label></div>
                                <div class="col-12 col-md-4">
                                    <input type="text" name="txtTelefonoC" id="txtTelefonoC" class="form-control" >
                                </div>
                            </div>
                        </div>

                        <p style="margin-top: 30px;">2. Ingresa la información de los productos que deseas agregar a la cotización.</p>

                        <div class="row">
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
                                    <input type="checkbox" name="chkIncluyeIva" id="chkIncluyeIva" value="1" <? if($cotizacion["incluyeiva"]==1){?> checked <?} ?>> Los Productos incluyen IVA
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3 bg-light p-3 rounded">
                                    <input type="checkbox" name="chkSubtotalizar" id="chkSubtotalizar" value="1" <? if(($_GET["modulo3"]>0 && $cotizacion["subtotalizar"]==1) || !isset($_GET["modulo3"])){?> checked <?} ?>> Subtotalizar 
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
                                <div class="col-12 col-md-3" style="display:none;" id="textProducto">
                                    <label for="" class="mb-2">Producto</label>
                                    <input type="text" name="txtProducto" id="txtProducto" class="form-control">
                                </div>
                                <div class="col-12 col-md-2">
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

                        <div class="row mb-3">
                            <div class="col-xs-12 col-md-3 offset-md-9" style="text-align: right;">
                                <button type="button" data-fancybox data-type="ajax" data-src="/modulos/cotizaciones/agregarespecificacion.php" class="btn btn-primary btn-block waves-effect waves-light">Agregar Especificaci&oacute;n</button>
                            </div>
                        </div>

                        <div id="listaEspecificaciones" class="mb-3"></div>

                        <p>3. Ingresa la información final de la cotización.</p>

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
                                <a href="javascript:;" onclick="validarFormCotizacionConvertir();" class="btn btn-primary btn-sm">Guardar</a>
                                <!-- <button type="submit" class="btn btn-primary btn-sm">Guardar</button> -->
                                <a href="/cotizaciones" class="btn btn-warning btn-sm">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>