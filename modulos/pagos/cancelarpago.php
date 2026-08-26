<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pagos.php");

$p = new Pagos();

$pago = $p->getPago(array(
    "idpago" => $_GET["idpago"]
));

// Cancelar el pago borra los tickets de caja que generó, así que la confirmación muestra
// exactamente qué se va a eliminar y de qué corte sale
$tickets = $p->getTicketsPago($_GET["idpago"]);

$cortescerrados = false;
foreach($tickets as $ticket){
    if($ticket["statuscorte"] != "A"){
        $cortescerrados = true;
    }
}
?>
<div style="width:520px;">
    <?php
    if($pago["respuesta"]=="OK"){
        $pago = $pago["pago"];
    ?>
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Cancelar pago <?= trim($pago["serie"]."-".$pago["folio"],"-") ?></h4>
        </div>
    </div>
    <hr>
    <div class="mb-3">
        <label class="form-label">Cliente</label><br>
        <?= $pago["cliente"] ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha</label><br>
        <?= str_replace("<br>"," ",fecha_formateada($pago["registro"])); ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Total</label><br>
        $<?= number_format($pago["total"],2) ?>
    </div>
    <hr>
    <div class="mb-3">
        <label class="form-label">Se revertirá</label>
        <ul class="mb-0">
            <li>El abono de cada pedido que cubrió este pago.</li>
            <li>El saldo que su complemento amortizó a cada factura.</li>
            <?php if(!empty($tickets)): ?>
            <li>Los tickets de caja que generó, con sus formas de pago.</li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if(!empty($tickets)): ?>
    <div class="mb-3">
        <label class="form-label">Tickets de caja que se eliminarán</label>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ticket</th>
                        <th>Sucursal</th>
                        <th class="text-end">Monto</th>
                        <th>Corte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= $ticket["folio"] ?></td>
                        <td><?= $ticket["sucursal"] ?></td>
                        <td class="text-end">$<?= number_format($ticket["total"],2) ?></td>
                        <td>
                            <?php if($ticket["statuscorte"] == "A"): ?>
                                <span class="badge bg-success">ABIERTO</span>
                            <?php else: ?>
                                <span class="badge bg-danger">CERRADO</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php if($cortescerrados): ?>
    <div class="alert alert-warning">
        <strong>Atención:</strong> uno o más de estos tickets pertenecen a un corte que ya fue cerrado y arqueado.
        Al eliminarlos, el reporte de ese corte dejará de cuadrar contra el arqueo del día.
    </div>
    <?php endif; ?>
    <?php if(empty($tickets)): ?>
    <div class="alert alert-warning">
        <strong>Atención:</strong> no se encontró el ticket de caja asociado a este pago.
        Se revertirán los pedidos y las facturas, pero el ticket tendrás que revisarlo manualmente.
    </div>
    <?php endif; ?>
    <div class="text-end">
        <button type="button" onclick="solicitudServidor('pagos','cancelar','idpago=<?= (int)$_GET['idpago'] ?>','');" class="btn btn-danger">Cancelar pago</button>
    </div>
    <?php
    }else{
    ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger"><?= $pago["mensaje"] ?></div>
        </div>
    </div>
    <?php
    }
    ?>
</div>
