<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$claseClientes = new Clientes();
$claseUsuarios = new Usuarios();

$tareas = $claseClientes->obtenerTareas($_POST);



if ($tareas["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Título</th>
                    <th>Comentarios</th>
                    <th>F. Recordatorio</th>
                    <th>F. Registro</th>
                    <th>Usuario</th>
                    <th style=" width: 170px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach ($tareas["tareas"] as $tarea) {
                    // $usuario = $claseUsuarios->obtenerUsuario($tarea["idusuario"])["usuario"];
                    $_POST["idusuario"] = $tarea["idusuarioasignado"];
                    $usuario = $claseUsuarios->obtenerUsuario($_POST)["usuario"];

                ?>
                    <tr>
                        <td><?= $tarea["titulo"] ?></td>
                        <td><?= $tarea["comentarios"] ?></td>
                        <td><?= fecha_formateada($tarea["fecha"]) ?></td>
                        <td><?= fecha_formateada($tarea["created_at"]) ?></td>
                        <td><?= $usuario["nombre"] ?></td>
                        <td>
                            <?
                            if ($tarea["status"] == "A") {
                            ?>
                                <a href="javascript:;" onClick="solicitudServidor('clientes','finalizartarea','idtarea=<?= $tarea["idtarea"] ?>&idcliente=<?= $_POST["idcliente"] ?>','','')" class="btn btn-sm btn-danger waves-effect waves-light">Finalizar</a>
                            <?
                            }
                            ?>
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
            <?= $tareas["mensaje"] ?>
        </div>
    </div>
<?
}
