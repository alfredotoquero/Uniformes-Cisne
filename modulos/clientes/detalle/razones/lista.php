<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$razones = $claseClientes->obtenerRazonesSociales($_POST["idcliente"]);

if($razones["respuesta"]=="OK"){
    ?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Razón Social</th>
                    <th>RFC</th>
                    <th>Código Postal</th>
                    <th style="width: 180px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($razones["razones"] as $razon){
                    ?>
                    <tr>
                        <td><?= $razon["razon_social"] ?></td>
                        <td><?= $razon["rfc"] ?></td>
                        <td><?= $razon["codigo_postal"] ?></td>
                        <td>
                            <a href="javascript:;" class="btn btn-secondary btn-sm waves-effect waves-light" onClick="cargarRazonSocial(<?= $razon["idrazonsocial"] ?>)">Editar</a>
                            <a href="javascript:;" class="btn btn-danger btn-sm waves-effect waves-light" onClick="solicitudServidor('clientes','eliminarrazon','idrazonsocial=<?= $razon["idrazonsocial"] ?>','¿Estás seguro de que quieres eliminar esta razón social?','')">Eliminar</a>
                        </td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}else{
    ?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $razones["mensaje"] ?>
        </div>
    </div>
    <?
}
?>