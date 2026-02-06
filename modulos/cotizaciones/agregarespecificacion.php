<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

// al momento de abrir esta ventana (quieres agregar una nueva especificacion), se debe limpiar la carpeta "imagenes/usariosesptmp/$idusuario/" de cualquier imagen que se haya quedado rezagada

$files = glob($_SERVER["DOCUMENT_ROOT"].'/imagenes/usuarioesptmp/'.$_SESSION["usuario"]["idusuario"].'/*'); // get all file names
foreach($files as $file){ // iterate files 
  if(is_file($file))
    unlink($file); // delete file
}

?>
<div style="width:1200px; height: 600px; padding-left: 20px; padding-right: 20px;">

    <div class="box-header">
        <center><b>Ingresa los datos solicitadoss</b> </center>
    </div>

    <form name="formEspecificacion" id="formEspecificacion" method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion" value="insertarimagenestmp">
        <!-- <input type="hidden" name="idpartida" id="idpartida" value="<? echo $_GET["idpartida"]; ?>"> -->
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Nombre Diseño:</div>
            <div class="col-10"><input type="text" name="txtNombre" id="txtNombre" value="" class="form-control" placeholder="Ingresa un Nombre"></div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Fecha Entrega:</div>
            <div class="col-10">
                <input type="text" name="txtFecha" id="txtFecha" class="form-control date" autocomplete="off">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Serigrafia:</div>
            <div class="col-10">
                <input type="checkbox" name="chkSerigrafia" id="chkSerigrafia" value="1"> S&iacute;
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Bordado:</div>
            <div class="col-10">
                <input type="checkbox" name="chkBordado" id="chkBordado" value="1"> S&iacute;
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Digital:</div>
            <div class="col-10">
                <input type="checkbox" name="chkDigital" id="chkDigital" value="1"> S&iacute;
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Requiere Producción:</div>
            <div class="col-10">
                <input type="radio" name="rdRequiereProduccion" id="rdRequiereProduccion1" value="1"> S&iacute;
                <input type="radio" name="rdRequiereProduccion" id="rdRequiereProduccion2" value="0"> No
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Especificaciones:</div>
            <div class="col-10">
                <textarea name="txtEspecificaciones" id="txtEspecificaciones" rows="10" class="form-control"></textarea>
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Imagen 1:</div>
            <div class="col-10">
                <input type="file" name="img1" id="img1" class="form-control">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Imagen 2:</div>
            <div class="col-10">
                <input type="file" name="img2" id="img2" class="form-control">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Imagen 3:</div>
            <div class="col-10">
                <input type="file" name="img3" id="img3" class="form-control">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Imagen 4:</div>
            <div class="col-10">
                <input type="file" name="img4" id="img4" class="form-control">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-2">Imagen 5:</div>
            <div class="col-10">
                <input type="file" name="img5" id="img5" class="form-control">
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-5"></div>
            <div class="col-4">
                <button type="button" onClick="agregarEspecificacion();" class="btn btn-primary btn-sm">SELECCIONAR</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function (e) {
        if ($(".date").length) {
            var d = new Date();

            var month = d.getMonth()+1;
            var day = d.getDate();

            var output = d.getFullYear() + '-' +
                ((''+month).length<2 ? '0' : '') + month + '-' +
                ((''+day).length<2 ? '0' : '') + day;

            $(".date").datepicker({
                format: "yyyy-mm-dd",
                showOtherMonths: true,
                selectOtherMonths: true,
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                orientation: "bottom",
                startDate: output
            });
        }
    });
</script>