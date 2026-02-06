<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <?
                    // antes de mostrar el boton de agregar, se debe revisar si el usuario tiene asignada esta sección y sin modo de "solo lectura"
                    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
                    $_POST["idseccion"] = 15;
                    if ($claseUsuarios->tienePermiso($_POST)) {
                        ?>
                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/categorias/agregar.php" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Agregar</a>
                        <?
                    }
                    ?>
                </div>
                <h4 class="page-title">Categorias</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/categorias/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
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