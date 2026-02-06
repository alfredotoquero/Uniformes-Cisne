<div style="width:90%; height: 90%;" class="box">
    <form name="formDetalle" id="formDetalle">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["idcliente"] ?>">
    
    </form>
    <div class="row">
        <div class="col-md-12">
            <div class="b-b b-primary nav-active-primary">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:;" onClick="cargarDatosPestana(0)" id="general">General</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(2)" id="razones">Razones Sociales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(3)" id="cotizaciones">Cotizaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(4)" id="pedidos">Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(5)" id="seguimiento">Seguimiento</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(6)" id="tareas">Recordatorios</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="divArchivo"></div>
</div>

<script>
    $(document).ready(function(e) {
        cargarDatosPestana(<?= $_GET["archivo"] ?>);
    });
</script>