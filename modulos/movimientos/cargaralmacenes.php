<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");

$claseAlmacenes = new Almacenes();

$_POST["tipoalmacen"] = 2;
// $_POST["idalmacen"] = ;
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
if ($almacenes["respuesta"]=="OK") {
    # code...
    ?>
    <div class="row">
        <div class="col-2" ><label for="">Almacen Secundario</label><span>*</span></div>
        <div class="col-10">
            <select name="slcAlmacenS" id="slcAlmacenS" class="form-control" onChange="$('#almacens').val(this.value);">
                <option value="0">--Selecciona un almacen--</option>
                <?
                foreach ($almacenes["almacenes"] as $almacen) {
                    ?>
                    <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
    </div>
    <?
    echo "|OK";
} else {
    echo "|".$almacenes["respuesta"];
}
?>