<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/tarjetasregalo/agregar.php" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/tarjetasregalo/agregar_excel.php" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar (Excel)</a>
                    <!-- <a href="javascript:;" onclick="cambiarAccionFormulario('formTarjetas','reactivar');" class="btn btn-primary btn-sm">Reactivar</a>
                    <a href="javascript:;" onclick="cambiarAccionFormulario('formTarjetas','reactivarventa');" class="btn btn-primary btn-sm">Reactivar venta</a> -->
                    <a href="javascript:;" onclick="cambiarAccionFormulario3('formTarjetas','activar');" class="btn btn-primary btn-sm">Activar</a>
                </div>
                <h4 class="page-title">Tarjetas de Regalo</h4>
            </div>
        </div>
    </div>

	<div class="b-b b-primary nav-active-primary">
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a class="nav-link <? if ($_GET["modulo2"]=="activas" || $_GET["modulo2"]=="") { ?>active<? } ?>" href="/tarjetasregalo/activas">Activas</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <? if ($_GET["modulo2"]=="inactivas") { ?>active<? } ?>" href="/tarjetasregalo/inactivas">Inactivas</a>
			</li>
		</ul>
	</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/tarjetasregalo/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="activas" id="activas" value="<?= (($_GET["modulo2"]=="" || $_GET["modulo2"]=="activas") ? 1 : 0) ?>">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="txtBusqueda" id="txtBusqueda" placeholder="Busqueda" autocomplete="off">
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