<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");

$claseClientes = new Clientes();
$claseUsuarios = new Usuarios();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["status"] = "A";
$_POST["slcOrden"] = 2;
$_POST["idcliente"] = $_POST["slcCliente"];
$tareas = $claseClientes->obtenerTareas($_POST);

if($tareas["respuesta"]=="OK"){
    ?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Título</th>
                    <th>Cliente</th>
                    <th>Comentarios</th>
                    <th>F. Recordatorio</th>
                    <th>F. Registro</th>
                    <th style=" width: 230px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach($tareas["tareas"] as $tarea){
                    $_POST["idcliente"] = $tarea["idcliente"];
                    $cliente = $claseClientes->obtenerCliente($_POST)["cliente"];
                    ?>
                    <tr>
                        <td><?= $tarea["titulo"] ?></td>
                        <td><?= $cliente["nombre"] ?></td>
                        <td><?= $tarea["comentarios"] ?></td>
                        <td><?= fecha_formateada($tarea["fecha"]) ?></td>
                        <td><?= fecha_formateada($tarea["created_at"]) ?></td>
                        <td>
                            <a href="javascript:;" onClick="cargarTarea(<?= $tarea["idtarea"] ?>)" class="btn btn-primary btn-sm" title="Editar"><i class="uil uil-edit"></i></a>
                            <a href="javascript:;" onClick="solicitudServidor('clientes','finalizartarea','idtarea=<?= $tarea["idtarea"] ?>','','')" class="btn btn-sm btn-danger waves-effect waves-light">Finalizar</a>
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/clientes/detalle.php?idcliente=<?= $tarea["idcliente"] ?>&archivo=5" data-toggle="tooltip" title="detalle" class="btn btn-success btn-sm"><i class="uil uil-eye"></i></a>
                        </td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
    </div>
    <?
}else{
    ?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $tareas["mensaje"] ?>
        </div>
    </div>
    <?
}