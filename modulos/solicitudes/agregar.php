<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");

$claseCategorias = new Categorias();
$claseProductos = new Productos();
$claseSolicitudes = new Solicitudes();

// al entrar, se deben borrar las partidas que se habian ingresado antes
$claseSolicitudes->eliminarPartidasTMP($_SESSION["usuario"]["idusuario"]);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Agregar Productos</h4>
                <small class="ms-2">Los campos marcados con * son obligatorios</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formSolicitud" name="formSolicitud">
                            <input type="text" name="hidden" autocomplete="nope" value="" style="display:none;">
                            <input type="hidden" name="accion" id="accion" value="">
                            <input type="hidden" name="href" id="href" value="/solicitudes">
                            <input type="hidden" name="idpartida" id="idpartida" value="">
                            <input type="hidden" name="idproducto" id="idproducto" value="">
                            <input type="hidden" name="idsolicitudproducto" id="idsolicitudproducto" value="1">
                            <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-2" ><label for="">Origen</label><span>*</span></div>
                                    <div class="col-10">
                                        <select name="slcOrigen" id="slcOrigen" class="form-control" onchange="mostrar(this.value,1);">
                                            <option value="0">--Seleccionar--</option>
                                            <option value="1">Inventario</option>
                                            <option value="2">Libre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2" id="divCategoria">
                                <div class="row">
                                    <div class="col-2" ><label for="">Categoria</label><span>*</span></div>
                                    <div class="col-10">
                                        <input type="hidden" name="idcategoriaselect" id="idcategoriaselect">
                                        <select name="slcCategoria" id="slcCategoria" class="form-control" onchange="cargarColorTallaLibreSolicitud(this.value,true);">
                                            <option value="0">--Seleccionar--</option>
                                            <?
                                            $categorias = $claseCategorias->obtenerCategorias($_POST);
                                            foreach ($categorias["categorias"] as $categoria) {
                                                ?>
                                                <option value="<?= $categoria["idcategoriaproducto"] ?>"><?= $categoria["nombre"] ?></option>
                                                <?
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-2">
                                        <label for="slcProducto">Producto</label>
                                    </div>
                                    <div class="col-8" id="selectProducto">
                                        <select name="slcProducto" id="slcProducto" class="form-control select2" onchange="asignarIdProductoSolicitud(this.value);">
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
                                    <div class="col-8" style="display:none;" id="textProducto">
                                        <input type="text" name="txtProducto" id="txtProducto" value="" class="form-control" placeholder="Ingresa un Producto" >
                                    </div>
                                    <div class="col-2 text-end" id="divAgregar">
                                        <label for="">&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProductoSolicitud();">Agregar</button>
                                    </div>
                                </div>
                            </div>

                            <div id="listaProductos"></div>

                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-2" ><label for=""># Pedido:</label></div>
                                    <div class="col-10">
                                        <input type="text" name="txtPedido" id="txtPedido" class="form-control">
                                    </div>
                                </div>
                            </div>

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
                                        <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="validarSolicitudes();">Guardar</button>
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