<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");

$claseSolicitudes = new Solicitudes();

$idusuario = $_SESSION["usuario"]["idusuario"];

$partidas = $claseSolicitudes->obtenerPartidasTMP2($idusuario);
if ($partidas["respuesta"]=="OK"){
	?>
    <div class="box">
        <div class="box-header">
            <p style="margin-top: 30px;">Lista de productos agregados.</p>
        </div>
        <div class="box-body">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th width="10"></th>
                        <th width="10"></th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach($partidas["partidas"] as $partida){

                        $productos = $partida["lista"];
						$datos = explode(" : ",$productos);
						$nombreproducto = $datos[0];
						$detalle = "";
						$detalle .= $nombreproducto . "<br>";

                        $datos = explode("-_-",$datos[1]);
                        foreach ($datos as $dato) {
                            $detalle .= $dato . "<br>";
                        }
                        ?>
                    <tr>
                        <td><?= $detalle ?></td>
                        <td align="right"><a href="javascript:;" onClick="cargarDatosSolicitud(<?= $partida["idsolicitudproducto"]; ?>,<?= $partida["idproducto"] ?>);"><i class="fas fa-pencil-alt"></i></a></td>
                        <td align="right"><a href="javascript:;" onClick="eliminarProductoSolicitud(<?= $partida["idsolicitudproducto"]; ?>);"><i class="fa fa-times"></i></a></td>
                    </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?
}else {
	?>
	<center><p><b>--No hay productos agregados--</b></p></center>
	<?
}
?>