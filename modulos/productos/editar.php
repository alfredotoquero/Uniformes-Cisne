<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");

$_POST["idproducto"] = $_GET["modulo3"];

$claseProductos = new Productos();
$claseProveedores = new Proveedores();
$claseTiposProducto = new TiposProducto();
$claseAlmacenes = new Almacenes();
$claseColores = new Colores();
$claseTallas = new Tallas();

$producto = $claseProductos->obtenerProducto($_POST)["producto"];
$proveedores = $claseProveedores->obtenerProveedores($_POST);
$tiposproducto = $claseTiposProducto->obtenerTiposProducto($_POST);
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
$colores = $claseColores->obtenerColores($_POST);
$_POST["portipotalla"] = 1;
$tallas = $claseTallas->obtenerTallas($_POST);
$codigobarras = $claseProductos->obtenerSiguienteCodigoBarras()["codigobarras"];

$coloresp = $claseProductos->obtenerColoresProducto($_POST);
$tallasp = $claseProductos->obtenerTallasProducto($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Editar Producto</h4>
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
                    <form id="formEditar" name="formEditar">
                        <input type="hidden" name="controlador" id="controlador" value="productos">
                        <input type="hidden" name="accion" id="accion" value="editar">
                        <input type="hidden" name="href" id="href" value="/productos">
                        <input type="hidden" name="idproducto" id="idproducto" value="<?= $producto["idproducto"] ?>">
                        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
                        <input type="hidden" id="txtUnidadMedidaID" value="<? echo $producto["idunidadmedida"]; ?>">
                        <input type="hidden" id="txtUnidadMedidaDescripcion" value="<? echo $producto["descripcion_unidadmedida"]; ?>">
                        <input type="hidden" id="txtProductoServicioID" value="<? echo $producto["idproductoservicio"]; ?>">
                        <input type="hidden" id="txtProductoServicioDescripcion" value="<? echo $producto["descripcion_productoservicio"]; ?>">
                        <div class="mb-3">
                            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
                            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $producto["nombre"] ?>">
                        </div>
                        <div class="mb-3">
                            <label for="slcTipoProducto" class="form-label">Tipo de Producto<span>*</span></label>
                            <select name="slcTipoProducto" id="slcTipoProducto" class="form-control requerido" data-mensajeerror="Debes indicar el tipo de producto" onchange="precargarDatosSAT()">
                                <option value="0">--Seleccionar--</option>
                                <?
                                foreach ($tiposproducto["tiposproducto"] as $tipoproducto) {
                                    ?>
                                    <option value="<?= $tipoproducto["idtipoproducto"] ?>" <? if($tipoproducto["idtipoproducto"]==$producto["idtipoproducto"]){?> selected <?} ?>><?= $tipoproducto["nombre"] ?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="slcProveedor" class="form-label">Proveedor<span>*</span></label>
                                <select name="slcProveedor" id="slcProveedor" class="form-control requerido" data-mensajeerror="Debes indicar el proveedor">
                                    <option value="0">--Seleccionar--</option>
                                    <?
                                    foreach ($proveedores["proveedores"] as $proveedor) {
                                        ?>
                                        <option value="<?= $proveedor["idproveedor"] ?>" <? if($proveedor["idproveedor"]==$producto["idproveedor"]){?> selected <?} ?> ><?= $proveedor["nombre"] ?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="txtClaveProveedor" class="form-label">Clave Proveedor</label>
                                <input type="text" class="form-control" name="txtClaveProveedor" id="txtClaveProveedor" placeholder="Ingresa la clave" autocomplete="off" value="<?= $producto["claveproveedor"] ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="slcProductoServicio" class="form-label">Producto/Servicio SAT<span>*</span></label>
                                <select name="slcProductoServicio" id="slcProductoServicio" class="form-control requerido" data-mensajeerror="Debes indicar el producto/servicio SAT">
                                    <option value="0">--Selecciona el producto/servicio--</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="slcUnidadMedida" class="form-label">Unidad de Medida SAT<span>*</span></label>
                                <select name="slcUnidadMedida" id="slcUnidadMedida" class="form-control requerido" data-mensajeerror="Debes indicar la unidad de medida SAT">
                                    <option value="0">--Selecciona la unidad de medida--</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="txtNombre" class="form-label">Tipo<span>*</span></label>
                            <input type="radio" name="slcTipo" id="slcTipo1" value="P" class="requerido" data-mensajeerror="Debes indicar un tipo" <? if($producto["tipo"]=="P"){?> checked <?} ?> >&nbsp;&nbsp;Producto 
                            <input type="radio" name="slcTipo" id="slcTipo2" value="S" class="requerido" data-mensajeerror="Debes indicar un tipo" <? if($producto["tipo"]=="S"){?> checked <?} ?> >&nbsp;&nbsp;Servicio
                        </div>

                        <div class="mb-3">
                            <label for="chkAlmacenes" class="form-label">Almacenes<span>*</span></label>
                            <?
                            foreach ($almacenes["almacenes"] as $almacen) {
                                $_POST["idalmacen"] = $almacen["idalmacen"];
                                ?>
                                <div class="row">
                                    <div class="col-6">
                                        <?= $almacen["nombre"] ?>
                                    </div>
                                    <div class="col-6">
                                        <input type="checkbox" name="chkAlmacenes[]" id="chkAlmacen<?= $almacen["idalmacen"] ?>" data-switch="none" value="<?= $almacen["idalmacen"]; ?>" <? if($claseProductos->tieneAlmacenAsignado($_POST)){?> checked <?} ?>>
                                        <label for="chkAlmacen<?= $almacen["idalmacen"] ?>" data-on-label="" data-off-label=""></label>
                                    </div>
                                </div>
                                <?
                            }
                            unset($_POST["idalmacen"]);
                            ?>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="slcColorP" class="form-label">Color</label>
                                    <select name="slcColorP" id="slcColorP" class="form-control select2" onChange="agregarColor(this.value);">
                                        <option value="0">--Seleccionar--</option>
                                        <?
                                        foreach ($colores["colores"] as $color) {
                                            $_POST["idcolor"] = $color["idcolor"];
                                            ?>
                                            <option value="<?= $color["idcolor"] . "-" . $color["nombre"] ?>" <? if($claseProductos->tieneColorAsignado($_POST)){?> disabled <?} ?>><?= $color["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="listaColores">
                                        <table class="table m-0 table-striped" id="tablaColores">
                                            <!-- <p style="margin-top: 15px; display: none;">Lista de colores seleccionados para este producto</p> -->
                                            <thead <? if($coloresp["respuesta"]!="OK"){?>style="display:none;"<?} ?>>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
                                                if ($coloresp["respuesta"]=="OK") {
                                                    $i = 1;
                                                    foreach ($coloresp["colores"] as $color) {
                                                        ?>
                                                        <tr id="color<?= $i ?>">
                                                            <input type="hidden" name="idcolores[]" value="<?= $color["idcolor"] ?>">
                                                            <td><?= $color["color"] ?></td>
                                                            <td align="right">
                                                                <?
                                                                $_POST["idcolor"] = $color["idcolor"];
                                                                $existencias = $claseProductos->obtenerExistenciasYReservadoProducto($_POST)["existencias"];
                                                                if ($existencias==0) {
                                                                    ?>
                                                                    <a href="javascript:;" onClick="eliminarColor(<?= $i; ?>,'<?= $color["idcolor"] . "-" . $color["color"] ?>');"><i class="uil uil-times"></i></a>
                                                                    <?
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <?
                                                        $i++;
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="slcTallaP" class="form-label">Talla</label>
                                    <select name="slcTallaP" id="slcTallaP" class="form-control select2" onChange="agregarTalla(this.value);">
                                        <option value="0">--Seleccionar--</option>
                                        <?
                                        foreach ($tallas["tallas"] as $talla) {
                                            $_POST["idtalla"] = $talla["idtalla"];
                                            ?>
                                            <option value="<?= $talla["idtalla"] . "-" . $talla["nombre"] ?>" <? if($claseProductos->tieneTallaAsignada($_POST)){?> disabled <?} ?>><?= $talla["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                

                                    <div class="listaTallas">
                                    <!-- <p style="margin-top: 15px;">Lista de tallas seleccionadas para este producto</p> -->
                                        <table class="table m-0 table-striped" id="tablaTallas">
                                            <thead <? if($tallasp["respuesta"]!="OK"){?>style="display:none;"<?} ?>>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
                                                if ($tallasp["respuesta"]=="OK") {
                                                    unset($_POST["idcolor"]);
                                                    $i = 1;
                                                    foreach ($tallasp["tallas"] as $talla) {
                                                        ?>
                                                        <tr id="talla<?= $i ?>">
                                                            <input type="hidden" name="idtallas[]" id="idtalla<?= $talla["idtalla"] ?>" value="<?= $talla["idtalla"] ?>">
                                                            <td><?= $talla["talla"] ?></td>
                                                            <td align="right">
                                                                <?
                                                                $_POST["idtalla"] = $talla["idtalla"];
                                                                $existencias = $claseProductos->obtenerExistenciasYReservadoProducto($_POST)["existencias"];
                                                                if ($existencias==0) {
                                                                    ?>
                                                                    <a href="javascript:;" onClick="eliminarTalla(<?= $i ?>,'<?= $talla["idtalla"] . "-" . $talla["talla"]; ?>');"><i class="uil uil-times"></i></a>
                                                                    <?
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <?
                                                        $i++;
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="txtNombre" class="form-label">Tienda</label>
                            <input type="radio" name="rdTienda" id="rdTienda1" value="1" onClick="cambiarDiv2(this.value,'divPrecio');"<? if($producto["tienda"]==1){?> checked <?} ?>>&nbsp;&nbsp;Sí
                            <input type="radio" name="rdTienda" id="rdTienda1" value="2" onClick="cambiarDiv2(this.value,'divPrecio');"<? if($producto["tienda"]==2){?> checked <?} ?>>&nbsp;&nbsp;No
                        </div>
                        <div id="divPrecio" <? if($producto["tienda"]!=1){?>style="display:none;"<?} ?>>
                        <!-- son requeridos pero solo si son visibles -->
                            <div class="mb-3">
                                <label for="txtPrecio" class="form-label">Precio<span>*</span></label>
                                <input type="text" class="form-control" name="txtPrecio" id="txtPrecio" placeholder="Ingresa el precio" autocomplete="off" data-mensajeerror="Debes indicar el precio" value="<?= (($producto["precio"]==0) ? "" : $producto["precio"]) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="txtCodigo" class="form-label">Código de Barras<span>*</span></label>
                                <input type="text" class="form-control" name="txtCodigo" id="txtCodigo" placeholder="<?= $codigobarras ?>" autocomplete="off" data-mensajeerror="Debes indicar el código de barras" value="<?= $producto["codigobarras"] ?>">
                            </div>
                            <div class="mb-3">
                                <label for="txtNombre" class="form-label">¿Tarjeta de Regalo?<span>*</span></label>
                                <input type="radio" name="rdTarjetaRegalo" id="rdTarjetaRegalo1" value="0" <? if($producto["tarjetaregalo"]==0){?> checked <?} ?>>&nbsp;&nbsp;No
                                <input type="radio" name="rdTarjetaRegalo" id="rdTarjetaRegalo2" value="1" <? if($producto["tarjetaregalo"]==1){?> checked <?} ?>>&nbsp;&nbsp;Sí
                            </div>
                        </div>
                        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary waves-effect waves-light">Guardar</button>
                        <a href="/productos" class="btn btn-danger waves-effect waves-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>