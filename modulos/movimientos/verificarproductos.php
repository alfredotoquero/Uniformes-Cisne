<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$claseMovimientos = new Movimientos();

$idusuario = $_SESSION["usuario"]["idusuario"];

// $partidas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["4dm1npl4y3r4sc1sn3usr"]."' group by idpartida order by idpartida"); 

$partidas = $claseMovimientos->obtenerPartidasTMP2($idusuario);
?>
    <script>
    // function guardarMovimiento(){
    //     if(confirm("ATENCIÓN: ¿Deseas continuar?")){
    //         $("#accion").val("agregar");
    //         // $("#formMovimiento").submit();
    //         console.log("Guardar movimiento");
    //         guardarMovimiento();
    //     }
    // }
    </script>

<div style="width: 1100px; height: 600px; padding-left: 20px; padding-right: 20px;">
    <div class="box">
        <div class="box-header">
            <p style="margin-top: 30px;">Verifica los productos agregados y guarda el movimiento.</p>
        </div>
        <div class="box-body">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach($partidas["partidas"] as $partida){
                        
                        $productos = $partida["lista"];
                        $datos1 = explode(" : ",$productos);
						$nombreproducto = $datos1[0];
						
						$detalle = "";
						$detalle .= $nombreproducto . "<br>";

                        $datos2 = explode("-_-",$datos1[1]);
							foreach ($datos2 as $dato) {
								$detalle .= $dato . "<br>";
							}
                        ?>
                    <tr>
                        <td>
                            <? 
                            echo $detalle;
                            ?>
                        </td>

                    </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>
            <div class="row">
                <div class="offset-md-10 col-md-2">
                    <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="guardarMovimiento();">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
