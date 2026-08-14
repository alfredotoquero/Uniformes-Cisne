-- Migración: complementos de pago con facturación parcial
-- Ejecutar una sola vez en la base de datos (antes de subir el código de
-- Tienda-Uniformes-Cisne rama fix/complementos-pago)

-- Tabla que relaciona un pago (complemento) con las facturas que amortizó y con
-- cuánto se aplicó a cada una. Antes esto se inferia de tpedidos.idfactura, que
-- solo guarda la ÚLTIMA factura del pedido y por lo tanto era incorrecto en
-- cuanto un pedido se facturaba en parcialidades.
CREATE TABLE IF NOT EXISTS tpagosfacturas (
    idpagofactura INT NOT NULL AUTO_INCREMENT,
    idpago        INT NOT NULL,
    idfactura     INT NOT NULL,
    monto         DECIMAL(12,2) NOT NULL,
    parcialidad   INT NOT NULL DEFAULT 1,
    registro      DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (idpagofactura),
    KEY idx_pgf_idpago    (idpago),
    KEY idx_pgf_idfactura (idfactura)
);

-- Backfill de los complementos que ya se timbraron: se asume la relación que el
-- código legado usó en su momento (la factura que estaba en tpedidos.idfactura).
-- Sirve para que el cálculo de NumParcialidad y la reversión de saldo al
-- cancelar sigan siendo correctos para los pagos históricos.
INSERT INTO tpagosfacturas (idpago, idfactura, monto, parcialidad)
SELECT
    fp.idpago,
    p.idfactura,
    SUM(fp.monto),
    1
FROM
    tformaspagopedido fp
JOIN
    tpagos g ON g.idpago = fp.idpago
JOIN
    tpedidos p ON p.idpedido = fp.idpedido
WHERE
    g.uuid IS NOT NULL AND g.uuid <> '' AND
    p.idfactura IS NOT NULL AND p.idfactura > 0
GROUP BY
    fp.idpago,
    p.idfactura;
