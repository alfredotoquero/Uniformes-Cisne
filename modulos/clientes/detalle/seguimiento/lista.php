<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Contactos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$claseClientes = new Clientes();
$claseUsuarios = new Usuarios();
$claseContactos = new Contactos();

$seguimiento = $claseClientes->obtenerSeguimiento($_POST["idcliente"]);

if ($seguimiento["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Título</th>
                    <th>Comentarios</th>
                    <th>Fechas C.</th>
                    <th>Usuarios C.</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th style=" width: 170px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach ($seguimiento["seguimiento"] as $seguimiento) {
                    $_POST["idusuario"] = $seguimiento["idusuario"];
                    $usuario = $claseUsuarios->obtenerUsuario($_POST)["usuario"];
                    $_POST["idcontacto"] = $seguimiento["idcontacto"];
                    $contacto = $claseContactos->obtenerContacto($_POST)["contacto"];

                    $array_colores = array('#808080', '#e6194b', '#3cb44b', '#e6bc09', '#4363d8', '#f58231', '#911eb4', '#0dd6c4', '#f032e6', '#539e07', '#a81c30', '#008080', '#7c30af', '#9a6324', '#800000', '#0099ff', '#808000', '#f78200', '#000075');
                    $lista_comentarios = explode("|", $seguimiento["comentarios"]);
                    $lista_fechas = explode("|", $seguimiento["fechas_c"]);
                    $lista_usuarios = explode("|", $seguimiento["usuarios_c"]);
                    foreach ($lista_comentarios as $i => $comentario) {
                        if ($i == 0) {
                            $comentario = "<p style='color:#000000;'><b>" . $comentario . "</b></p>";
                            $fecha_c = "<p style='color:#000000;'><b>" . fecha_formateada($lista_fechas[$i]) . "</b></p>";
                            $usuario_c = "<p style='color:#000000;'><b>" . $lista_usuarios[$i] . "</b></p>";
                            $lista_comentarios[$i] = $comentario;
                            $lista_fechas[$i] = $fecha_c;
                            $lista_usuarios[$i] = $usuario_c;
                            continue;
                        }
                        $comentario = "<p style='color:" . $array_colores[$i % count($array_colores)] . ";'><b>" . $comentario . "</b></p>";
                        $fecha_c = "<p style='color:" . $array_colores[$i % count($array_colores)] . ";'><b>" . fecha_formateada($lista_fechas[$i]) . "</b></p>";
                        $usuario_c = "<p style='color:" . $array_colores[$i % count($array_colores)] . ";'><b>" . $lista_usuarios[$i] . "</b></p>";
                        $lista_comentarios[$i] = $comentario;
                        $lista_fechas[$i] = $fecha_c;
                        $lista_usuarios[$i] = $usuario_c;
                    }
                    $seguimiento["comentarios"] = implode("--------------", $lista_comentarios);
                    $seguimiento["fechas_c"] = implode("--------------", $lista_fechas);
                    $seguimiento["usuarios_c"] = implode("--------------", $lista_usuarios);
                ?>
                    <tr>
                        <td><?= $seguimiento["titulo"] . "<br><br>Contacto: " . $contacto["nombre"] ?></td>
                        <td><?= str_replace("\n\n", "<br>", $seguimiento["comentarios"]) ?></td>
                        <td><?= str_replace("\n\n", "<br>", $seguimiento["fechas_c"]) ?></td>
                        <td><?= str_replace("\n\n", "<br>", $seguimiento["usuarios_c"]) ?></td>
                        <td><?= (($seguimiento["created_at"] != "0000-00-00 00:00:00" && $seguimiento["created_at"] != "") ? fecha_formateada($seguimiento["created_at"]) : "") ?></td>
                        <td><?= $usuario["nombre"] ?></td>
                        <td>
                            <a href="javascript:;" onClick="mostrarFormularioRespuestaSeguimiento('formComentarios','btnAgregarS',<?= $seguimiento["idseguimiento"] ?>)" class="btn btn-sm btn-secondary waves-effect waves-light">Responder</a>
                        </td>
                    </tr>
                <?
                }
                ?>
            </tbody>
        </table>
    </div>
<?
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $seguimiento["mensaje"] ?>
        </div>
    </div>
<?
}
?>