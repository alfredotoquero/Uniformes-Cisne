<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$claseCotizaciones = new Cotizaciones();

$idusuario = $_SESSION["usuario"]["idusuario"];
$_POST["idusuario"] = $idusuario;
?>
<div style="width: 600px; height: 400px; padding-left: 20px; padding-right: 20px;">

    <div class="box-header">
        <center><b>Escoge los desgloses que tendr&aacute;n esta especificaci&oacute;n</b> </center>
        <!-- <small>Los campos marcados con * son obligatorios</small> -->
    </div>

    <form name="formDesglose" id="formDesglose" action="" method="post">
        <?
            ?>
            <input type="hidden" name="imagen1" id="imagen1" value="<?= ($_GET["imagen1"]!="" ? "1" . $_GET["imagen1"] : ""); ?>">
            <input type="hidden" name="imagen2" id="imagen2" value="<?= ($_GET["imagen2"]!="" ? "2" . $_GET["imagen2"] : ""); ?>">
            <input type="hidden" name="imagen3" id="imagen3" value="<?= ($_GET["imagen3"]!="" ? "3" . $_GET["imagen3"] : ""); ?>">
            <input type="hidden" name="imagen4" id="imagen4" value="<?= ($_GET["imagen4"]!="" ? "4" . $_GET["imagen4"] : ""); ?>">
            <input type="hidden" name="imagen5" id="imagen5" value="<?= ($_GET["imagen5"]!="" ? "5" . $_GET["imagen5"] : ""); ?>">

            <input type="hidden" name="txtNombre" id="txtNombre" value="<?= $_GET["txtNombre"]; ?>">
            <!-- <input type="hidden" name="chkCalcas" id="chkCalcas" value="<?= $_GET["chkCalcas"]; ?>">
            <input type="hidden" name="chkPoster" id="chkPoster" value="<?= $_GET["chkPoster"]; ?>">
            <input type="hidden" name="chkLetrero" id="chkLetrero" value="<?= $_GET["chkLetrero"]; ?>">
            <input type="hidden" name="chkOtros" id="chkOtros" value="<?= $_GET["chkOtros"]; ?>"> -->
            <input type="hidden" name="txtFecha" id="txtFecha" value="<?= $_GET["txtFecha"]; ?>">
            <input type="hidden" name="chkSerigrafia" id="chkSerigrafia" value="<?= $_GET["chkSerigrafia"]; ?>">
            <input type="hidden" name="chkDigital" id="chkDigital" value="<?= $_GET["chkDigital"]; ?>">
            <input type="hidden" name="rdRequiereProduccion" id="rdRequiereProduccion" value="<?= $_GET["rdRequiereProduccion"]; ?>">
            <input type="hidden" name="rdRequiereDiseno" id="rdRequiereDiseno" value="<?= $_GET["rdRequiereDiseno"]; ?>">
            <input type="hidden" name="rdRequiereAprobacionDiseno" id="rdRequiereAprobacionDiseno" value="<?= $_GET["rdRequiereAprobacionDiseno"]; ?>">
            <input type="hidden" name="chkAutorizacion" id="chkAutorizacion" value="<?= $_GET["chkAutorizacion"]; ?>">
            <!-- <input type="hidden" name="chkCorteVinil" id="chkCorteVinil" value="<?= $_GET["chkCorteVinil"]; ?>">
            <input type="hidden" name="chkLonaBanner" id="chkLonaBanner" value="<?= $_GET["chkLonaBanner"]; ?>"> -->
            <input type="hidden" name="chkBordado" id="chkBordado" value="<?= $_GET["chkBordado"]; ?>">
            <input type="hidden" name="txtEspecificaciones" id="txtEspecificaciones" value="<?= $_GET["txtEspecificaciones"]; ?>">
            <?
        ?>
        <input type="hidden" name="accion" value="asignardesglose">
        <input type="hidden" name="idespecificacion" id="idespecificacion" value="<?= $_GET["idespecificacion"]; ?>">
        <div class="row">
            <?
            // recuperar todas las partidas
            $_POST["idusuario"] = $idusuario;
            $partidas = $claseCotizaciones->obtenerDesglosesPartidasTMP2($_POST);
            foreach ($partidas["partidas"] as $partida) {
                $nombre = $partida["producto"];
                
                $detalle = "";
                $desgloses = explode(";",$partida["desgloses"]);
                foreach($desgloses as $desglose){
                    // si no hay nada en la cadena del desglose, se salta esta parte y pasa a la siguiente partida (continue)
                    if (!strlen($desglose)) {
                        continue;
                    }
                    $desglose = explode(" : ",$desglose);
                    $color = $desglose[0];
                    $tallas = $desglose[1];

                    $detalle .= "<small><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $color . "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $tallas . "</small>";
                }
                ?>
                <div class="col-6">
                    <input type="checkbox" name="cotizacionproductos[]" id="producto<?= $partida["idtmp"]; ?>" value="<?= $partida["idtmp"]; ?>"><?= " " . $partida["cantidad"] . " - " . $nombre . $detalle; ?>
                </div>
                <?
            }
            ?>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-5"></div>
            <div class="col-4">
                <button type="button" onClick="asignarDesglose();" class="btn btn-primary btn-sm">ASIGNAR</button>
            </div>
        </div>
    </form>
</div>