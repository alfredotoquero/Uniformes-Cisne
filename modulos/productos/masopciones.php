<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$_POST["idproducto"] = $_GET["idproducto"];

$claseProductos = new Productos();
$producto = $claseProductos->obtenerProducto($_POST)["producto"];

?>
<div style="width:90%; height: 90%;" class="box">
    <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="b-b b-primary nav-active-primary">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(7,1)" id="contactos">Información del Producto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(8,1)" id="contactos">Asignar Almacenes</a>
                    </li>
                    <?
                    if ($producto["tipo"]=="P") {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(9,1)" id="cotizaciones">Asignar Colores/Tallas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="javascript:;" onClick="cargarDatosPestana(10,1)" id="pedidos">Asignar Catálogos</a>
                        </li>
                        <?
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:;" onClick="cargarDatosPestana(11,1)" id="general">Variantes de Precio</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="divArchivo"></div>
</div>

<script>
    $(document).ready(function (e) {
        cargarDatosPestana(7,1);
    });
</script>