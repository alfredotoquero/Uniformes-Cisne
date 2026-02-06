<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$claseSucursales = new Sucursales();
$claseTiendas = new Tiendas();

$sucursales = $claseSucursales->obtenerSucursales($_POST);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="/pedidos/agregar" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                </div>
                <h4 class="page-title">Pedidos</h4>
            </div>
        </div>
    </div>

	<div class="b-b b-primary nav-active-primary">
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a class="nav-link <? if ($_GET["modulo2"]=="activos" || $_GET["modulo2"]=="") { ?>active<? } ?>" href="/pedidos/activos">Activos</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <? if ($_GET["modulo2"]=="finalizados") { ?>active<? } ?>" href="/pedidos/finalizados">Finalizados</a>
			</li>
		</ul>
	</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/pedidos/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="tipopedido" id="tipopedido" value="<?= (($_GET["modulo2"]=="") ? "activos" : $_GET["modulo2"]) ?>">
							<input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control fecha" name="txtFechaInicial" id="txtFechaInicial" placeholder="Fecha Inicial" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control fecha" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha Final" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="slcSucursal" id="slcSucursal" class="form-control">
                                        <option value="">Todas las sucursales</option>
                                        <option value="0">Almacen Principal</option>
                                        <?
                                        foreach ($sucursales["sucursales"] as $sucursal) {
                                            ?>
                                            <option value="<?= $sucursal["idsucursal"] ?>"><?= $sucursal["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-3">
                                    <select name="slcTienda" id="slcTienda" class="form-control">
                                        <option value="">Todas las tiendas</option>
                                        <?
                                        $tiendas = $claseTiendas->obtenerTiendas(array());
                                        foreach ($tiendas["tiendas"] as $tienda) {
                                            ?>
                                            <option value="<?= $tienda["idtienda"] ?>"><?= $tienda["nombre"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-3">
                                    <select name="slcTipo" id="slcTipo" class="form-control">
                                        <option value="1">Todos</option>
                                        <?
                                        if ($_GET["modulo2"]=="activos" or $_GET["modulo2"]=="") {
                                            ?>
                                            <option value="ST">Sin Terminar</option>
                                            <option value="SE">Sin Entregar</option>
                                            <?
                                        } else {
                                            ?>
                                            <option value="T">Terminados</option>
                                            <option value="C">Cancelados</option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-2 col-md-auto">
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