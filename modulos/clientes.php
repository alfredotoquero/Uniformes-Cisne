<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Tiendas.php");

$claseClientes = new Clientes();
$claseTiendas = new Tiendas();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/clientes/agregar.php" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                </div>
                <h4 class="page-title">Clientes</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="pagina" id="pagina" value="1">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-4">
                                    <select name="slcCiudad" id="slcCiudad" class="form-control">
                                        <option value="0">Todas las ciudades</option>
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
                                <div class="col-12 col-md-4">
                                    <select name="slcTienda" id="slcTienda" class="form-control">
                                        <option value="0">Todas las tiendas</option>
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
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 col-md-4">
                                    <select name="slcConRecordatorio" id="slcConRecordatorio" class="form-control" onchange="opcionesFiltroClientes(this.value)">
                                        <option value="0">Todos</option>
                                        <option value="1">Con recordatorios pendientes</option>
                                        <option value="2">Cotizaciones activas</option>
                                        <option value="3">Cotizaciones realizadas</option>
                                        <option value="4">Sin cotizaciones realizadas</option>
                                        <option value="5">Pedidos realizados</option>
                                        <option value="6">Sin pedidos realizados</option>
                                        <option value="7">Con seguimiento realizado</option>
                                        <option value="8">Sin seguimiento realizado</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control fecha" name="txtFecha" id="txtFecha" placeholder="Fecha" autocomplete="off">
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