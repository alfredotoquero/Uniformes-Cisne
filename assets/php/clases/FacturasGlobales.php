<?php
/**
 * Factura global de operaciones con el público en general.
 *
 * El dinero que entra por tarjeta y por transferencia y que nunca se amparó con un CFDI
 * tiene que declararse al SAT en una factura global al público en general. Son dos
 * orígenes distintos y ninguno se cruza con el otro:
 *
 *   - Tickets de mostrador (ttickets sin pedido): el cliente pagó y no pidió factura. El
 *     dinero está en tformaspagoticket.
 *   - Abonos a pedidos sin facturar: lo que se cobró de un pedido que no tiene ninguna
 *     factura vigente. Un pedido con factura queda fuera completo, aunque esté facturado
 *     solo en parte: ese ingreso ya lo ampara la factura, y lo que falta en ese caso es
 *     el complemento de pago, no una global.
 *
 * Los dos orígenes se acotan además a las sucursales marcadas con tsucursales.global = 1.
 * No todas las sucursales las declara este emisor: las que no traen la bandera facturan
 * por otra vía y su dinero no debe entrar aquí. El ticket dice su sucursal directo
 * (ttickets.idsucursal) y el abono la hereda del pedido (tpedidos.idsucursal).
 *
 * Se emite una factura por forma de pago, porque el CFDI declara una sola FormaPago y el
 * SAT tiene que ver el dinero de tarjeta separado del de transferencia. Cada una es un
 * consolidado: un único concepto con el total del periodo, no un renglón por ticket.
 *
 * La tasa de IVA default es TASA_IVA (8%, región fronteriza), y se puede cambiar por
 * factura: la tienda maneja las dos tasas (el POS timbra al 8% y los pedidos se capturan al
 * 8% o al 16%), y las globales que se emitieron a mano en julio fueron al 16%. El dinero
 * cobrado ya trae el IVA dentro (es lo que pagó el cliente), así que el subtotal es el
 * cobrado entre 1 + la tasa.
 */
class FacturasGlobales{

    private $con;

    /**
     * Formas de pago internas (tcatformaspago) que se facturan globalmente. Efectivo queda
     * fuera a propósito: lo declara el contador por otra vía. La tarjeta de regalo y el
     * efectivo en dólares tampoco entran (descuentan saldos y divisa, no son un cobro
     * limpio en pesos).
     */
    const FORMAPAGO_TARJETA = 3;
    const FORMAPAGO_TRANSFERENCIA = 5;

    // Tasa default. Se puede cambiar por factura con "tasaiva" en generar()/generarManual()
    // o con --tasa desde la línea de comandos.
    const TASA_IVA = 8;

    // Mensual. El SAT admite diaria (01), semanal (02), quincenal (03) y mensual (04).
    const PERIODICIDAD_MENSUAL = "04";

    // Receptor genérico del público en general: RFC genérico, sin efectos fiscales y sin
    // obligaciones fiscales. El código postal se toma del emisor porque el receptor
    // genérico no tiene domicilio propio.
    const RFC_PUBLICO_GENERAL = "XAXX010101000";
    const NOMBRE_PUBLICO_GENERAL = "PUBLICO EN GENERAL";
    const USOCFDI_PUBLICO_GENERAL = "S01";
    const REGIMEN_PUBLICO_GENERAL = "616";

    // Concepto único del consolidado: uniformes, unidad de servicio, cantidad 1.
    const CLAVE_PRODUCTO_SERVICIO = "53103000";
    const CLAVE_UNIDAD = "E48";
    const DESCRIPCION_CONCEPTO = "UNIFORMES";
    const OBJETO_IMPUESTO = "02";

    // tfacturas.idusuario tiene llave foránea a tusuarios, así que la global tiene que
    // quedar a nombre de un usuario que exista: no hay un "usuario sistema" al que
    // colgarla. Se resuelve con resolverUsuario() y se valida ANTES de timbrar.

    const API_KEY_TIMBRADOR = "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu";
    const URL_TIMBRADOR = "https://api.xptk.app/timbrador/index.php";
    const AUTORIZACION_TIMBRADOR = "CH60NP5HQZYUPZEQ";

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Formas de pago que se facturan globalmente, con su nombre interno.
     *
     * @access public
     * @return array
     */
    public function formasPago(){
        $query = "
        select
            idformapago,
            nombre,
            idformapago_sat
        from
            tcatformaspago
        where
            idformapago in ('".self::FORMAPAGO_TARJETA."','".self::FORMAPAGO_TRANSFERENCIA."')
        order by
            idformapago";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    /**
     * Datos fiscales del emisor que va a timbrar la global, incluido su consecutivo propio
     * de serie y folio.
     *
     * @access public
     * @param string $rfc
     * @return array|null
     */
    public function getEmisor($rfc){
        $rfc = mysqli_real_escape_string($this->con,$rfc);

        $query = "
        select
            a.idemisor,
            a.rfc,
            a.razon_social,
            a.codigo_postal,
            a.serie_global,
            a.folio_global,
            b.regimenfiscal
        from
            temisores a
        left join
            sat_tcatregimenfiscal b
        on
            b.idregimenfiscal = a.idregimenfiscal
        where
            a.rfc = '".$rfc."'";

        return mysqli_fetch_assoc(mysqli_query($this->con,$query));
    }

    /**
     * Límites del periodo que se va a declarar. Se devuelven como rango medio abierto
     * (>= inicio, < fin) para no depender de si las fechas guardan hora: el último cobro
     * del mes a las 23:59 entra igual.
     *
     * @access public
     * @param int $mes
     * @param int $anio
     * @return array
     */
    public function periodo($mes, $anio){
        $mes = (int)$mes;
        $anio = (int)$anio;

        return array(
            "mes" => sprintf("%02d",$mes),
            "anio" => (string)$anio,
            "inicio" => date("Y-m-d 00:00:00", mktime(0,0,0,$mes,1,$anio)),
            "fin" => date("Y-m-d 00:00:00", mktime(0,0,0,$mes+1,1,$anio))
        );
    }

    /**
     * Clave del SAT con la que se declara cada forma de pago interna en la global.
     *
     * No se toma de tcatformaspago.idformapago_sat a propósito: ahí la tarjeta apunta a la
     * 28 (tarjeta de débito) y estas facturas se declaran con la 04 (tarjeta de crédito).
     * Ese mapeo del catálogo lo usan también los complementos de pago, así que se deja como
     * está y la equivalencia de la global se fija aquí.
     *
     * @access private
     * @return array idformapago interno => clave del SAT
     */
    private function clavesSAT(){
        return array(
            self::FORMAPAGO_TARJETA => "04",
            self::FORMAPAGO_TRANSFERENCIA => "03"
        );
    }

    /**
     * Resuelve la forma de pago del CFDI: la clave que va en el comprobante y el id con el
     * que se guarda en tfacturas, los dos a partir del mismo mapa para que el listado diga
     * exactamente lo que dice el CFDI.
     *
     * @access public
     * @param int $idformapago   Forma de pago interna (tcatformaspago)
     * @return array clave (por ejemplo "04") e idformapago_sat
     */
    public function formaPagoSAT($idformapago){
        $claves = $this->clavesSAT();

        if(!isset($claves[(int)$idformapago])){
            throw new Exception("La forma de pago interna ".$idformapago." no tiene una clave del SAT definida para la factura global");
        }

        $clave = $claves[(int)$idformapago];

        $query = "
        select
            idformapago
        from
            sat_tcatformaspago
        where
            formapago = '".mysqli_real_escape_string($this->con,$clave)."'";
        $idformapago_sat = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idformapago"];

        if(empty($idformapago_sat)){
            throw new Exception("La clave ".$clave." no existe en el catálogo de formas de pago del SAT (sat_tcatformaspago)");
        }

        return array(
            "formapago" => $clave,
            "idformapago_sat" => $idformapago_sat
        );
    }

    /**
     * Valida el usuario al que se le va a acreditar la factura.
     *
     * tfacturas.idusuario tiene llave foránea a tusuarios: si no existe, el INSERT truena
     * DESPUÉS de haber timbrado y el CFDI queda vivo ante el SAT pero fuera del sistema.
     * Por eso esto se llama antes de mandar nada al timbrador.
     *
     * @access public
     * @param int $idusuario
     * @return array Datos del usuario
     */
    public function resolverUsuario($idusuario){
        $idusuario = mysqli_real_escape_string($this->con,$idusuario);

        $query = "
        select
            idusuario,
            usuario,
            nombre
        from
            tusuarios
        where
            idusuario = '".$idusuario."' and
            status = 'A'";
        $usuario = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        if(empty($usuario)){
            throw new Exception("El usuario ".$idusuario." no existe o está inactivo; la factura tiene que quedar a nombre de un usuario válido (tfacturas.idusuario es llave foránea a tusuarios)");
        }

        return $usuario;
    }

    /**
     * Usuarios activos, para poder elegir a cuál acreditar la factura desde la línea de
     * comandos.
     *
     * @access public
     * @return array
     */
    public function usuariosActivos(){
        $query = "
        select
            idusuario,
            usuario,
            nombre
        from
            tusuarios
        where
            status = 'A'
        order by
            idusuario";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    /**
     * Desglosa un total cobrado (IVA incluido) como lo hace el CFDI.
     *
     * El subtotal se lleva a SEIS decimales, no a dos, y así viaja en el concepto. Es la
     * única forma de que el total del comprobante caiga exacto en lo que se cobró: el SAT
     * calcula Total = SubTotal + Traslados, cada uno redondeado a dos decimales por
     * separado, y con un subtotal de dos decimales hay importes que no se pueden alcanzar.
     * $62,444.00 al 16% es uno de ellos: 53,831.03 da 62,443.99 y 53,831.04 da 62,444.01,
     * mientras que 53,831.034483 sí da 62,444.00 exacto.
     *
     * Reproduce los importes de GA-1 y GA-2, las globales que se timbraron a mano.
     *
     * @access public
     * @param float $total     Total cobrado, con IVA incluido
     * @param float $tasaiva   Tasa a aplicar; por default TASA_IVA
     * @return array subtotal (6 decimales, el que se manda), y el subtotal, iva y total
     *               que quedarán en el comprobante
     */
    public function desglose($total, $tasaiva = null){
        $total = (float)$total;
        $tasaiva = ($tasaiva === null) ? self::TASA_IVA : (float)$tasaiva;
        $subtotal = round($total / (1 + ($tasaiva / 100)),6);
        $subtotal_cfdi = round($subtotal,2);
        $iva = round($subtotal * ($tasaiva / 100),2);

        return array(
            "tasaiva" => $tasaiva,
            "subtotal" => $subtotal,
            "subtotal_cfdi" => $subtotal_cfdi,
            "iva" => $iva,
            "total" => round($subtotal_cfdi + $iva,2)
        );
    }

    /**
     * Tablas de las que sale el dinero cobrado en mostrador. Se arma aparte de las
     * condiciones porque el mismo bloque se usa para contar, para sumar y para marcar los
     * renglones cuando el timbrado sale bien: si el criterio viviera duplicado, cualquier
     * ajuste dejaría de marcar exactamente lo que se facturó.
     *
     * La sucursal entra con join y no con left join a propósito: un ticket cuya sucursal
     * no exista no puede comprobarse que sea de las que este emisor declara, así que se
     * queda fuera en lugar de colarse por un NULL.
     *
     * @access private
     * @return string
     */
    private function origenTickets(){
        return "
            tformaspagoticket b
        join
            ttickets a
        on
            a.idticket = b.idticket
        join
            tsucursales e
        on
            e.idsucursal = a.idsucursal
        left join
            tcatformaspago c
        on
            c.idformapago = b.idformapago
        left join
            tfacturas d
        on
            d.idfactura = a.idfactura";
    }

    /**
     * Qué cobro de mostrador entra a la global.
     *
     * Solo tickets de venta directa de una sucursal marcada con tsucursales.global = 1.
     * Las demás sucursales declaran su dinero por otra vía; incluirlas aquí facturaría
     * ingresos que no le tocan a este emisor.
     *
     * Registrar un pago a pedido crea un ttickets con su renglón en tformaspagoticket, así
     * que ese dinero existe en las dos tablas: se toma únicamente del otro origen
     * (tformaspagopedido), que además es el único que dice a qué documento se aplicó cada
     * peso. Contarlo aquí también lo duplicaría.
     *
     * Se excluye por las dos marcas que deja el pago —el pedido y el idpago— y no solo por
     * una: idpago lo escribe Pagos::agregarPago de la tienda, e idpedido lo escriben tanto
     * esa ruta como la legada de modulos/pedidossistema.php, que nunca creó un tpagos. Con
     * las dos, ningún ticket de abono se cuela aunque alguna de las dos columnas falte.
     *
     * Un ticket con factura cancelada y sin refacturar sí entra: su ingreso se quedó sin
     * amparar. Si se hubiera refacturado, ttickets.idfactura ya apuntaría a la nueva.
     *
     * @access private
     * @param int $idformapago
     * @param array $periodo
     * @return string
     */
    private function condicionesTickets($idformapago, $periodo){
        $idformapago = mysqli_real_escape_string($this->con,$idformapago);
        $inicio = mysqli_real_escape_string($this->con,$periodo["inicio"]);
        $fin = mysqli_real_escape_string($this->con,$periodo["fin"]);

        return "
            b.idformapago = '".$idformapago."' and
            b.idfacturaglobal is null and
            b.monto > 0 and
            a.status = 'A' and
            e.global = 1 and
            coalesce(a.idpedido,0) = 0 and
            a.idpago is null and
            a.fecha >= '".$inicio."' and
            a.fecha < '".$fin."' and
            (a.idfactura is null or a.idfactura = 0 or d.status = 3)";
    }

    /**
     * Tablas de las que salen los abonos a pedidos.
     *
     * El abono no dice de qué sucursal es: la hereda del pedido, así que se pasa por
     * tpedidos para poder mirar la bandera de tsucursales. Los dos van con join y no con
     * left join por lo mismo que los tickets: sin sucursal comprobable, el dinero se queda
     * fuera.
     *
     * Si el pedido está facturado se resuelve por los dos caminos que existen: g son los
     * pedidos con alguna factura vigente en tpedidosfacturas (facturación parcial), y h es
     * el respaldo de tpedidos.idfactura, donde viven las facturas anteriores a esa
     * migración y a las que tpedidosfacturas no llega. Basta una vigente por cualquiera de
     * los dos lados para que el pedido quede fuera de la global.
     *
     * @access private
     * @return string
     */
    private function origenAbonos(){
        return "
            tformaspagopedido a
        join
            tpedidos c
        on
            c.idpedido = a.idpedido
        join
            tsucursales d
        on
            d.idsucursal = c.idsucursal
        left join
            (
            select distinct
                pf.idpedido
            from
                tpedidosfacturas pf
            join
                tfacturas ff
            on
                ff.idfactura = pf.idfactura
            where
                coalesce(ff.status,1) <> 3
            ) g
        on
            g.idpedido = a.idpedido
        left join
            tfacturas h
        on
            h.idfactura = c.idfactura
        left join
            tcatformaspago b
        on
            b.idformapago = a.idformapago
        left join
            (
            select distinct
                idpago
            from
                tformaspagopedido
            where
                idpago is not null and
                idfactura is not null
            ) e
        on
            e.idpago = a.idpago
        left join
            (
            select distinct
                idpago
            from
                tpagosfacturas
            ) f
        on
            f.idpago = a.idpago";
    }

    /**
     * Qué abono a pedido entra a la global. Se piden las tres cosas a la vez: que el
     * pedido no tenga ninguna factura vigente, que sea de una sucursal marcada con
     * tsucursales.global = 1, y que el abono mismo no haya caído sobre una factura.
     *
     * El criterio manda a nivel pedido, no a nivel abono. Un pedido con factura vigente
     * queda fuera completo, aunque esté facturado solo en parte y tenga abonos sin ligar:
     * ahí no hay forma de saber sin revisarlo a mano si ese dinero corresponde a la parte
     * ya facturada, y declararlo en la global lo pondría dos veces ante el SAT. Se prefiere
     * quedarse corto y que se revise, a declarar de más. Ojo con eso: la parte no facturada
     * de un pedido parcial no se declara en ningún lado, hay que meterla a mano.
     *
     * Los abonos aplicados a una factura quedan fuera aunque su complemento no se haya
     * timbrado: ese ingreso ya lo ampara la factura y meterlo en la global lo declararía
     * dos veces. Lo que ahí falta es el complemento, y el cronjob los reporta aparte para
     * que se revisen.
     *
     * Los abonos que apuntan a una factura cancelada también quedan fuera, aunque el pedido
     * ya no tenga ninguna vigente: tformaspagopedido.idfactura no se reapunta al refacturar,
     * así que ese renglón no es confiable. El cronjob los reporta.
     *
     * La última condición cubre los pagos legados: si el pago se capturó con desglose por
     * documento (e), un renglón sin factura es dinero sin amparar por definición. Si no
     * tiene desglose (captura vieja, o abono de modulos/pedidossistema.php que ni siquiera
     * creó un registro en tpagos), solo está amparado si el reparto automático lo amortizó
     * contra alguna factura, y eso consta en tpagosfacturas (f).
     *
     * Las dos se resuelven con las tablas derivadas de origenAbonos() y no con subconsultas
     * correlacionadas porque el mismo criterio se usa en el UPDATE que marca los renglones,
     * y MySQL no deja consultar en una subconsulta la tabla que se está actualizando.
     *
     * @access private
     * @param int $idformapago
     * @param array $periodo
     * @return string
     */
    private function condicionesAbonos($idformapago, $periodo){
        $idformapago = mysqli_real_escape_string($this->con,$idformapago);
        $inicio = mysqli_real_escape_string($this->con,$periodo["inicio"]);
        $fin = mysqli_real_escape_string($this->con,$periodo["fin"]);

        return "
            a.idformapago = '".$idformapago."' and
            a.idfacturaglobal is null and
            a.monto > 0 and
            d.global = 1 and
            g.idpedido is null and
            (c.idfactura is null or c.idfactura = 0 or h.status = 3) and
            a.idfactura is null and
            a.fecha >= '".$inicio."' and
            a.fecha < '".$fin."' and
            (e.idpago is not null or f.idpago is null)";
    }

    /**
     * Lo cobrado en el periodo por una forma de pago que todavía no se declara en ninguna
     * global, separado por origen.
     *
     * Los montos se multiplican por tcatformaspago.pesos porque las formas en divisa
     * guardan el monto en su moneda. Para tarjeta y transferencia el factor es 1, así que
     * en la práctica es una red de seguridad por si algún día se agrega una forma en otra
     * moneda a la lista.
     *
     * @access public
     * @param int $idformapago
     * @param array $periodo
     * @return array
     */
    public function cobros($idformapago, $periodo){
        $query = "
        select
            count(distinct a.idticket) as documentos,
            coalesce(sum(b.monto * coalesce(c.pesos,1)),0) as total
        from
            ".$this->origenTickets()."
        where
            ".$this->condicionesTickets($idformapago,$periodo);
        $tickets = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        $query = "
        select
            count(*) as documentos,
            coalesce(sum(a.monto * coalesce(b.pesos,1)),0) as total
        from
            ".$this->origenAbonos()."
        where
            ".$this->condicionesAbonos($idformapago,$periodo);
        $abonos = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        return array(
            "tickets" => (int)$tickets["documentos"],
            "total_tickets" => round((float)$tickets["total"],2),
            "abonos" => (int)$abonos["documentos"],
            "total_abonos" => round((float)$abonos["total"],2),
            "total" => round((float)$tickets["total"] + (float)$abonos["total"],2)
        );
    }

    /**
     * Detalle de lo que entraría a la global, para el log de la simulación.
     *
     * @access public
     * @param int $idformapago
     * @param array $periodo
     * @return array
     */
    public function detalleCobros($idformapago, $periodo){
        $query = "
        select
            'ticket' as origen,
            a.idticket as documento,
            a.folio,
            a.fecha,
            sum(b.monto * coalesce(c.pesos,1)) as monto
        from
            ".$this->origenTickets()."
        where
            ".$this->condicionesTickets($idformapago,$periodo)."
        group by
            a.idticket,
            a.folio,
            a.fecha
        order by
            a.fecha";
        $tickets = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $query = "
        select
            'abono' as origen,
            a.idpedido as documento,
            a.idpago,
            a.fecha,
            a.monto * coalesce(b.pesos,1) as monto
        from
            ".$this->origenAbonos()."
        where
            ".$this->condicionesAbonos($idformapago,$periodo)."
        order by
            a.fecha";
        $abonos = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        return array_merge($tickets,$abonos);
    }

    /**
     * Abonos del periodo que se dejaron fuera de la global y conviene revisar: los que
     * amparan una factura PPD viva cuyo complemento nunca se timbró, y los que apuntan a
     * una factura cancelada.
     *
     * No son un error del cronjob, son trabajo pendiente de otra parte del sistema. Se
     * reportan para que no pasen inadvertidos: en el primer caso falta emitir el
     * complemento, y en el segundo hay que confirmar si el pedido se refacturó o si ese
     * ingreso se quedó sin amparar y debe entrar a mano a una global.
     *
     * Se filtra por la misma bandera de sucursal que la global: lo de las sucursales que
     * no declara este emisor no es trabajo pendiente aquí y solo ensuciaría el log.
     *
     * @access public
     * @param int $idformapago
     * @param array $periodo
     * @return array
     */
    public function abonosPendientesRevision($idformapago, $periodo){
        $idformapago = mysqli_real_escape_string($this->con,$idformapago);
        $inicio = mysqli_real_escape_string($this->con,$periodo["inicio"]);
        $fin = mysqli_real_escape_string($this->con,$periodo["fin"]);

        $query = "
        select
            a.idpedido,
            a.idpago,
            a.idfactura,
            a.fecha,
            a.monto,
            c.serie,
            c.folio,
            c.status,
            case
                when c.status = 3 then 'factura cancelada'
                else 'factura PPD sin complemento vigente'
            end as motivo
        from
            tformaspagopedido a
        join
            tpedidos d
        on
            d.idpedido = a.idpedido
        join
            tsucursales s
        on
            s.idsucursal = d.idsucursal
        join
            tfacturas c
        on
            c.idfactura = a.idfactura
        left join
            tpagos p
        on
            p.idpago = a.idpago
        where
            a.idformapago = '".$idformapago."' and
            a.idfacturaglobal is null and
            a.monto > 0 and
            s.global = 1 and
            a.fecha >= '".$inicio."' and
            a.fecha < '".$fin."' and
            (
                c.status = 3 or
                (
                    c.idmetodopago = 1 and
                    coalesce(c.status,1) = 1 and
                    (p.uuid is null or p.uuid = '' or p.status = 4)
                )
            )
        order by
            a.fecha";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    /**
     * Emite la factura global de una forma de pago en un periodo.
     *
     * El orden importa. Primero se marca el dinero con el idfacturaglobal y después se
     * suma lo marcado: así lo que se timbra es exactamente lo que quedó cerrado, sin
     * hueco entre la consulta y la marca por el que se pudiera colar un cobro registrado
     * tarde. Si el timbrado falla, el rollback devuelve los renglones a NULL y la
     * siguiente corrida los vuelve a tomar.
     *
     * @access public
     * @param array $post   idemisor, idformapago, mes, anio y "simular" => true para
     *                      calcular el importe sin timbrar ni escribir nada
     * @return array
     */
    public function generar($post){
        $entransaccion = false;

        try{
            $idemisor = mysqli_real_escape_string($this->con,$post["idemisor"]);
            $idformapago = mysqli_real_escape_string($this->con,$post["idformapago"]);
            $simular = !empty($post["simular"]);
            $tasaiva = isset($post["tasaiva"]) ? (float)$post["tasaiva"] : self::TASA_IVA;
            $periodo = $this->periodo($post["mes"],$post["anio"]);

            $query = "
            select
                a.idemisor,
                a.rfc,
                a.razon_social,
                a.codigo_postal,
                a.serie_global,
                a.folio_global,
                b.regimenfiscal
            from
                temisores a
            left join
                sat_tcatregimenfiscal b
            on
                b.idregimenfiscal = a.idregimenfiscal
            where
                a.idemisor = '".$idemisor."'";
            $emisor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(empty($emisor)){
                throw new Exception("No se encontró el emisor ".$idemisor);
            }

            if(empty($emisor["serie_global"]) || (int)$emisor["folio_global"] <= 0){
                throw new Exception("El emisor ".$emisor["rfc"]." no tiene configurada la serie y el folio de facturas globales (temisores.serie_global / folio_global)");
            }

            // Una global por emisor, periodo y forma de pago. Si ya existe timbrada, el mes
            // ya se declaró: volver a emitirla duplicaría el ingreso ante el SAT.
            $query = "
            select
                a.idfacturaglobal,
                b.serie,
                b.folio,
                b.uuid,
                b.status
            from
                tfacturasglobales a
            join
                tfacturas b
            on
                b.idfactura = a.idfactura
            where
                a.idemisor = '".$idemisor."' and
                a.idformapago = '".$idformapago."' and
                a.mes = '".$periodo["mes"]."' and
                a.anio = '".$periodo["anio"]."' and
                coalesce(b.status,1) <> 3";
            $existente = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(!empty($existente)){
                throw new Exception("El periodo ".$periodo["mes"]."/".$periodo["anio"]." ya está declarado en la factura ".$existente["serie"]."-".$existente["folio"]." (".$existente["uuid"].")");
            }

            // Se resuelve antes de cualquier cosa: tfacturas.idusuario tiene llave foránea
            // a tusuarios, y descubrirlo después de timbrar dejaría un CFDI vivo ante el SAT
            // sin registrar en el sistema.
            $usuario = $this->resolverUsuario($post["idusuario"]);

            $cobros = $this->cobros($idformapago,$periodo);

            // Los dos casos que no timbran salen por aquí asignando la respuesta y no con un
            // return: el finally de este método devuelve $respuesta, así que un return dentro
            // del try lo pisaría y el método terminaría regresando NULL.
            if($cobros["total"] <= 0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "timbrada" => false,
                    "mensaje" => "No hay cobros pendientes de declarar en el periodo",
                    "cobros" => $cobros
                );
            }else if($simular){
                // El dinero cobrado ya trae el IVA dentro: es lo que pagó el cliente, así que
                // el subtotal es el cobrado entre 1 + la tasa.
                $respuesta = array(
                    "respuesta" => "OK",
                    "timbrada" => false,
                    "mensaje" => "Simulación: no se timbró nada",
                    "cobros" => $cobros,
                    "tasaiva" => $tasaiva,
                    "subtotal" => $this->desglose($cobros["total"],$tasaiva)["subtotal_cfdi"],
                    "serie" => $emisor["serie_global"],
                    "folio" => $emisor["folio_global"]
                );
            }else{

                mysqli_begin_transaction($this->con);
                $entransaccion = true;

                $query = "
                insert
                into
                    tfacturasglobales
                (
                    idemisor,
                    idformapago,
                    periodicidad,
                    mes,
                    anio,
                    tasaiva
                ) values (
                    '".$idemisor."',
                    '".$idformapago."',
                    '".self::PERIODICIDAD_MENSUAL."',
                    '".$periodo["mes"]."',
                    '".$periodo["anio"]."',
                    '".$tasaiva."'
                )";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("No se pudo registrar la factura global");
                }

                $idfacturaglobal = mysqli_insert_id($this->con);

                $query = "
                update
                    ".$this->origenTickets()."
                set
                    b.idfacturaglobal = '".$idfacturaglobal."'
                where
                    ".$this->condicionesTickets($idformapago,$periodo);

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("No se pudieron marcar los tickets del periodo");
                }

                $query = "
                update
                    ".$this->origenAbonos()."
                set
                    a.idfacturaglobal = '".$idfacturaglobal."'
                where
                    ".$this->condicionesAbonos($idformapago,$periodo);

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("No se pudieron marcar los abonos del periodo");
                }

                // A partir de aquí se trabaja sobre lo marcado, no sobre la consulta anterior
                $marcado = $this->marcado($idfacturaglobal);

                if($marcado["total"] <= 0){
                    throw new Exception("No quedó ningún cobro marcado para la factura global");
                }

                $subtotal = $this->desglose($marcado["total"],$tasaiva)["subtotal"];

                $query = "
                select
                    idmetodopago
                from
                    sat_tcatmetodospago
                where
                    metodopago = 'PUE'";
                $idmetodopago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idmetodopago"];

                if(empty($idmetodopago)){
                    throw new Exception("No se encontró el método de pago PUE en el catálogo del SAT");
                }

                $idformapago_sat = $this->formaPagoSAT($idformapago)["idformapago_sat"];

                // Timbrar es el punto de no retorno: de aquí en adelante el CFDI ya existe
                // ante el SAT y cualquier fallo obliga a cancelarlo, así que todo lo que
                // podía fallar por datos ya se validó.
                $respuestaTimbrado = $this->timbrar($emisor,$idformapago,$periodo,$subtotal,false,$tasaiva);

                $ruta = $_SERVER["DOCUMENT_ROOT"]."/emisores/".str_replace("&","_",$emisor["rfc"]);

                // Los archivos se guardan apenas llega la respuesta: si el registro fallara,
                // el CFDI ya es real y hay que conservarlo para poder cancelarlo o darlo de
                // alta a mano.
                file_put_contents($ruta."/facturas/".$respuestaTimbrado["uuid"].".xml",base64_decode($respuestaTimbrado["xml"]));
                file_put_contents($ruta."/facturas/".$respuestaTimbrado["uuid"].".pdf",base64_decode($respuestaTimbrado["pdf"]));

                $iva = round($respuestaTimbrado["total"] - $respuestaTimbrado["subtotal"],2);

                $query = "
                insert
                into
                    tfacturas
                (
                    idusuario,
                    idemisor,
                    razonsocial,
                    rfc,
                    codigo_postal,
                    regimenfiscal,
                    usocfdi,
                    idmetodopago,
                    idformapago,
                    serie,
                    folio,
                    subtotal,
                    iva,
                    total,
                    saldo,
                    uuid,
                    timbrado
                ) values (
                    '".$usuario["idusuario"]."',
                    '".$idemisor."',
                    '".self::NOMBRE_PUBLICO_GENERAL."',
                    '".self::RFC_PUBLICO_GENERAL."',
                    '".$emisor["codigo_postal"]."',
                    '".self::REGIMEN_PUBLICO_GENERAL."',
                    '".self::USOCFDI_PUBLICO_GENERAL."',
                    '".$idmetodopago."',
                    '".$idformapago_sat."',
                    '".$emisor["serie_global"]."',
                    '".$emisor["folio_global"]."',
                    '".$respuestaTimbrado["subtotal"]."',
                    '".$iva."',
                    '".$respuestaTimbrado["total"]."',
                    '".$respuestaTimbrado["total"]."',
                    '".$respuestaTimbrado["uuid"]."',
                    '".$respuestaTimbrado["fechaTimbrado"]."'
                )";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo registrar en tfacturas");
                }

                $idfactura = mysqli_insert_id($this->con);

                $query = "
                update
                    tfacturasglobales
                set
                    idfactura = '".$idfactura."',
                    cobrado = '".$marcado["total"]."',
                    subtotal = '".$respuestaTimbrado["subtotal"]."',
                    iva = '".$iva."',
                    total = '".$respuestaTimbrado["total"]."',
                    tickets = '".$marcado["tickets"]."',
                    abonos = '".$marcado["abonos"]."'
                where
                    idfacturaglobal = '".$idfacturaglobal."'";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo ligar con la factura global");
                }

                $query = "
                update
                    temisores
                set
                    folio_global = folio_global + 1
                where
                    idemisor = '".$idemisor."'";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo incrementar el folio del emisor");
                }

                mysqli_commit($this->con);
                $entransaccion = false;

                $respuesta = array(
                    "respuesta" => "OK",
                    "timbrada" => true,
                    "idfacturaglobal" => $idfacturaglobal,
                    "idfactura" => $idfactura,
                    "serie" => $emisor["serie_global"],
                    "folio" => $emisor["folio_global"],
                    "uuid" => $respuestaTimbrado["uuid"],
                    "cobrado" => $marcado["total"],
                    "subtotal" => $respuestaTimbrado["subtotal"],
                    "iva" => $iva,
                    "total" => $respuestaTimbrado["total"],
                    "tickets" => $marcado["tickets"],
                    "abonos" => $marcado["abonos"]
                );

            }

        }catch(Exception $e){
            if($entransaccion){
                mysqli_rollback($this->con);
            }

            $respuesta = array(
                "respuesta" => "ERROR",
                "timbrada" => false,
                "mensaje" => $e->getMessage()
            );
        }catch(Throwable $e){
            if($entransaccion){
                mysqli_rollback($this->con);
            }

            $respuesta = array(
                "respuesta" => "ERROR",
                "timbrada" => false,
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Emite una factura global con un total capturado a mano, sin consultar lo cobrado.
     *
     * Es la contraparte manual de generar(): mismo comprobante, mismo receptor y mismo
     * concepto, pero el importe lo pone quien la ejecuta. Sirve cuando el total que hay que
     * declarar no coincide con lo que el sistema sabe (cifras que trae el contador, meses
     * anteriores a la migración, ajustes acordados con él).
     *
     * En pruebas no toca la base ni gasta folio: solo devuelve el CFDI de prueba para
     * revisarlo. En real registra la factura igual que el cronjob, para que aparezca en el
     * listado y se pueda cancelar.
     *
     * Marcar los renglones del periodo es opcional y aparte, porque con un total capturado
     * a mano no hay garantía de que el dinero marcado sume ese total: la respuesta trae los
     * dos números para poder compararlos antes de decidir.
     *
     * @access public
     * @param array $post   idemisor, idformapago, mes, anio, total (con IVA incluido),
     *                      "pruebas" => true para el timbrado de prueba,
     *                      "marcar" => true para marcar los cobros del periodo,
     *                      "serie"/"folio" para forzar el consecutivo
     * @return array
     */
    public function generarManual($post){
        $entransaccion = false;

        try{
            $idemisor = mysqli_real_escape_string($this->con,$post["idemisor"]);
            $idformapago = mysqli_real_escape_string($this->con,$post["idformapago"]);
            $pruebas = !empty($post["pruebas"]);
            $marcar = !empty($post["marcar"]);
            $total = round((float)$post["total"],2);
            $tasaiva = isset($post["tasaiva"]) ? (float)$post["tasaiva"] : self::TASA_IVA;
            $periodo = $this->periodo($post["mes"],$post["anio"]);

            if($total <= 0){
                throw new Exception("El total a facturar tiene que ser mayor a cero");
            }

            $query = "
            select
                a.idemisor,
                a.rfc,
                a.razon_social,
                a.codigo_postal,
                a.serie_global,
                a.folio_global,
                b.regimenfiscal
            from
                temisores a
            left join
                sat_tcatregimenfiscal b
            on
                b.idregimenfiscal = a.idregimenfiscal
            where
                a.idemisor = '".$idemisor."'";
            $emisor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(empty($emisor)){
                throw new Exception("No se encontró el emisor ".$idemisor);
            }

            // La serie y el folio se pueden forzar para poder timbrar sin depender de que el
            // consecutivo del emisor esté configurado, y para retomar una numeración que se
            // venía llevando a mano.
            if(!empty($post["serie"])){
                $emisor["serie_global"] = mysqli_real_escape_string($this->con,$post["serie"]);
            }
            if(!empty($post["folio"])){
                $emisor["folio_global"] = mysqli_real_escape_string($this->con,$post["folio"]);
            }

            if(empty($emisor["serie_global"]) || (int)$emisor["folio_global"] <= 0){
                throw new Exception("El emisor ".$emisor["rfc"]." no tiene configurada la serie y el folio de facturas globales; indícalos con --serie y --folio");
            }

            // Todo lo que se necesita de la base se resuelve AQUÍ, antes de timbrar. Un
            // dato que falte después del timbrado deja un CFDI vivo ante el SAT que el
            // sistema no registró, y eso solo se arregla cancelándolo a mano.
            $usuario = $this->resolverUsuario($post["idusuario"]);

            $query = "
            select
                idmetodopago
            from
                sat_tcatmetodospago
            where
                metodopago = 'PUE'";
            $idmetodopago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idmetodopago"];

            if(empty($idmetodopago)){
                throw new Exception("No se encontró el método de pago PUE en el catálogo del SAT");
            }

            $idformapago_sat = $this->formaPagoSAT($idformapago)["idformapago_sat"];

            // El total capturado ya trae el IVA dentro, igual que el dinero que cobra la
            // tienda. desglose() reparte subtotal e IVA de modo que el total del CFDI caiga
            // exacto en la cifra capturada.
            $subtotal = $this->desglose($total,$tasaiva)["subtotal"];

            $respuestaTimbrado = $this->timbrar($emisor,$idformapago,$periodo,$subtotal,$pruebas,$tasaiva);

            $iva = round($respuestaTimbrado["total"] - $respuestaTimbrado["subtotal"],2);

            // Los archivos se escriben apenas llega la respuesta, antes de tocar la base: si
            // el registro fallara, el CFDI ya existe ante el SAT y hay que conservarlo.
            $rutaarchivos = $pruebas
                ? $_SERVER["DOCUMENT_ROOT"]."/txts/pruebas"
                : $_SERVER["DOCUMENT_ROOT"]."/emisores/".str_replace("&","_",$emisor["rfc"])."/facturas";

            if(!is_dir($rutaarchivos)){
                mkdir($rutaarchivos,0775,true);
            }

            file_put_contents($rutaarchivos."/".$respuestaTimbrado["uuid"].".xml",base64_decode($respuestaTimbrado["xml"]));
            file_put_contents($rutaarchivos."/".$respuestaTimbrado["uuid"].".pdf",base64_decode($respuestaTimbrado["pdf"]));

            // El CFDI de pruebas no vale fiscalmente: no se registra, no gasta folio y sus
            // archivos van aparte para que nadie los confunda con los buenos.
            if($pruebas){
                $respuesta = array(
                    "respuesta" => "OK",
                    "timbrada" => true,
                    "pruebas" => true,
                    "tasaiva" => $tasaiva,
                    "serie" => $emisor["serie_global"],
                    "folio" => $emisor["folio_global"],
                    "uuid" => $respuestaTimbrado["uuid"],
                    "capturado" => $total,
                    "subtotal" => $respuestaTimbrado["subtotal"],
                    "iva" => $iva,
                    "total" => $respuestaTimbrado["total"],
                    "archivos" => $rutaarchivos."/".$respuestaTimbrado["uuid"]
                );
            }else{

                mysqli_begin_transaction($this->con);
                $entransaccion = true;

                $query = "
                insert
                into
                    tfacturas
                (
                    idusuario,
                    idemisor,
                    razonsocial,
                    rfc,
                    codigo_postal,
                    regimenfiscal,
                    usocfdi,
                    idmetodopago,
                    idformapago,
                    serie,
                    folio,
                    subtotal,
                    iva,
                    total,
                    saldo,
                    uuid,
                    timbrado
                ) values (
                    '".$usuario["idusuario"]."',
                    '".$idemisor."',
                    '".self::NOMBRE_PUBLICO_GENERAL."',
                    '".self::RFC_PUBLICO_GENERAL."',
                    '".$emisor["codigo_postal"]."',
                    '".self::REGIMEN_PUBLICO_GENERAL."',
                    '".self::USOCFDI_PUBLICO_GENERAL."',
                    '".$idmetodopago."',
                    '".$idformapago_sat."',
                    '".$emisor["serie_global"]."',
                    '".$emisor["folio_global"]."',
                    '".$respuestaTimbrado["subtotal"]."',
                    '".$iva."',
                    '".$respuestaTimbrado["total"]."',
                    '".$respuestaTimbrado["total"]."',
                    '".$respuestaTimbrado["uuid"]."',
                    '".$respuestaTimbrado["fechaTimbrado"]."'
                )";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo registrar en tfacturas");
                }

                $idfactura = mysqli_insert_id($this->con);

                $query = "
                insert
                into
                    tfacturasglobales
                (
                    idfactura,
                    idemisor,
                    idformapago,
                    periodicidad,
                    mes,
                    anio,
                    tasaiva,
                    cobrado,
                    subtotal,
                    iva,
                    total
                ) values (
                    '".$idfactura."',
                    '".$idemisor."',
                    '".$idformapago."',
                    '".self::PERIODICIDAD_MENSUAL."',
                    '".$periodo["mes"]."',
                    '".$periodo["anio"]."',
                    '".$tasaiva."',
                    '".$total."',
                    '".$respuestaTimbrado["subtotal"]."',
                    '".$iva."',
                    '".$respuestaTimbrado["total"]."'
                )";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo registrar como factura global");
                }

                $idfacturaglobal = mysqli_insert_id($this->con);

                $marcado = array("tickets" => 0, "abonos" => 0, "total" => 0);

                if($marcar){
                    $query = "
                    update
                        ".$this->origenTickets()."
                    set
                        b.idfacturaglobal = '".$idfacturaglobal."'
                    where
                        ".$this->condicionesTickets($idformapago,$periodo);

                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudieron marcar los tickets del periodo");
                    }

                    $query = "
                    update
                        ".$this->origenAbonos()."
                    set
                        a.idfacturaglobal = '".$idfacturaglobal."'
                    where
                        ".$this->condicionesAbonos($idformapago,$periodo);

                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudieron marcar los abonos del periodo");
                    }

                    $marcado = $this->marcado($idfacturaglobal);

                    $query = "
                    update
                        tfacturasglobales
                    set
                        tickets = '".$marcado["tickets"]."',
                        abonos = '".$marcado["abonos"]."'
                    where
                        idfacturaglobal = '".$idfacturaglobal."'";
                    mysqli_query($this->con,$query);
                }

                // Solo se mueve el consecutivo del emisor cuando el folio salió de ahí: si se
                // forzó con --folio, quien lo forzó decide qué sigue.
                if(empty($post["folio"])){
                    $query = "
                    update
                        temisores
                    set
                        folio_global = folio_global + 1
                    where
                        idemisor = '".$idemisor."'";

                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("La factura se timbró (".$respuestaTimbrado["uuid"].") pero no se pudo incrementar el folio del emisor");
                    }
                }

                mysqli_commit($this->con);
                $entransaccion = false;

                $respuesta = array(
                    "respuesta" => "OK",
                    "timbrada" => true,
                    "pruebas" => false,
                    "tasaiva" => $tasaiva,
                    "idfacturaglobal" => $idfacturaglobal,
                    "idfactura" => $idfactura,
                    "serie" => $emisor["serie_global"],
                    "folio" => $emisor["folio_global"],
                    "uuid" => $respuestaTimbrado["uuid"],
                    "capturado" => $total,
                    "subtotal" => $respuestaTimbrado["subtotal"],
                    "iva" => $iva,
                    "total" => $respuestaTimbrado["total"],
                    "marcado" => $marcado["total"],
                    "tickets" => $marcado["tickets"],
                    "abonos" => $marcado["abonos"],
                    "archivos" => $rutaarchivos."/".$respuestaTimbrado["uuid"]
                );

            }

        }catch(Exception $e){
            if($entransaccion){
                mysqli_rollback($this->con);
            }

            $respuesta = array(
                "respuesta" => "ERROR",
                "timbrada" => false,
                "mensaje" => $e->getMessage()
            );
        }catch(Throwable $e){
            if($entransaccion){
                mysqli_rollback($this->con);
            }

            $respuesta = array(
                "respuesta" => "ERROR",
                "timbrada" => false,
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Lo que quedó marcado para una factura global. Es la fuente de la verdad del importe
     * que se timbra: se lee después de marcar, no antes.
     *
     * @access public
     * @param int $idfacturaglobal
     * @return array
     */
    public function marcado($idfacturaglobal){
        $idfacturaglobal = mysqli_real_escape_string($this->con,$idfacturaglobal);

        $query = "
        select
            count(distinct a.idticket) as documentos,
            coalesce(sum(b.monto * coalesce(c.pesos,1)),0) as total
        from
            tformaspagoticket b
        join
            ttickets a
        on
            a.idticket = b.idticket
        left join
            tcatformaspago c
        on
            c.idformapago = b.idformapago
        where
            b.idfacturaglobal = '".$idfacturaglobal."'";
        $tickets = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        $query = "
        select
            count(*) as documentos,
            coalesce(sum(a.monto * coalesce(b.pesos,1)),0) as total
        from
            tformaspagopedido a
        left join
            tcatformaspago b
        on
            b.idformapago = a.idformapago
        where
            a.idfacturaglobal = '".$idfacturaglobal."'";
        $abonos = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        return array(
            "tickets" => (int)$tickets["documentos"],
            "abonos" => (int)$abonos["documentos"],
            "total" => round((float)$tickets["total"] + (float)$abonos["total"],2)
        );
    }

    /**
     * Arma el CFDI global y lo manda al timbrador.
     *
     * InformacionGlobal es lo único que distingue a este comprobante de una factura
     * normal al público en general: le dice al SAT qué periodo consolida. Sin ese nodo el
     * CFDI se rechaza cuando el receptor es el RFC genérico.
     *
     * @access private
     * @param array $emisor
     * @param int $idformapago
     * @param array $periodo
     * @param float $subtotal
     * @param float $tasaiva  Tasa de IVA del comprobante; por default TASA_IVA
     * @param bool $pruebas   Timbra contra el ambiente de pruebas del PAC. El CFDI que
     *                        regresa NO tiene validez fiscal: sirve para revisar que el
     *                        comprobante salga bien antes de gastar un folio de verdad.
     * @return array Respuesta del timbrador
     */
    private function timbrar($emisor, $idformapago, $periodo, $subtotal, $pruebas = false, $tasaiva = null){
        $formapago = $this->formaPagoSAT($idformapago)["formapago"];

        $ruta = $_SERVER["DOCUMENT_ROOT"]."/emisores/".str_replace("&","_",$emisor["rfc"]);
        $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/certificado.cer");
        $certificado = $this->obtenerContenidoCertificado($ruta."/sat/certificado.cer");
        $archivo_keypem = file_get_contents($ruta."/sat/llave.key.pem");

        if(empty($numero_certificado) || empty($certificado) || empty($archivo_keypem)){
            throw new Exception("No se pudieron leer los archivos del sello del emisor ".$emisor["rfc"]);
        }

        $logo = $_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png";
        $logo = "data:image/png;base64,".base64_encode(file_get_contents($logo));

        $datos = array(
            "api_key" => self::API_KEY_TIMBRADOR,
            "Version" => "4.0",
            "pruebas" => ($pruebas ? 1 : 0),
            "numero_certificado" => $numero_certificado,
            "certificado" => $certificado,
            "keypem" => $archivo_keypem,
            "colortxt" => "000000",
            "logo" => $logo,
            "tipoComprobante" => "I",
            "serie" => $emisor["serie_global"],
            "folio" => $emisor["folio_global"],
            "InformacionGlobal" => array(
                "Periodicidad" => self::PERIODICIDAD_MENSUAL,
                "Meses" => $periodo["mes"],
                "Año" => $periodo["anio"]
            ),
            "emisor" => array(
                "Rfc" => $emisor["rfc"],
                "Nombre" => utf8_decode(trim($emisor["razon_social"])),
                "RegimenFiscal" => $emisor["regimenfiscal"],
                "LugarExpedicion" => $emisor["codigo_postal"]
            ),
            "receptor" => array(
                "Rfc" => self::RFC_PUBLICO_GENERAL,
                "Nombre" => self::NOMBRE_PUBLICO_GENERAL,
                "UsoCFDI" => self::USOCFDI_PUBLICO_GENERAL,
                "DomicilioFiscalReceptor" => $emisor["codigo_postal"],
                "RegimenFiscalReceptor" => self::REGIMEN_PUBLICO_GENERAL
            ),
            "conceptos" => array(
                array(
                    "Cantidad" => 1,
                    "Descripcion" => self::DESCRIPCION_CONCEPTO,
                    "ValorUnitario" => sprintf("%.6f",$subtotal),
                    "Importe" => sprintf("%.6f",$subtotal),
                    "ClaveUnidad" => self::CLAVE_UNIDAD,
                    "ClaveProdServ" => self::CLAVE_PRODUCTO_SERVICIO,
                    "ObjetoImp" => self::OBJETO_IMPUESTO
                )
            ),
            "subtotal" => $subtotal,
            "iva_trasladado" => (($tasaiva === null) ? self::TASA_IVA : $tasaiva),
            "metodopago" => "PUE",
            "formapago" => $formapago,
            "moneda" => "MXN",
            "tipo_cambio" => "1",
            "carta_porte" => false,
            "comentarios" => ""
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::URL_TIMBRADOR,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($datos),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.self::AUTORIZACION_TIMBRADOR
            ),
        ));

        $response = curl_exec($curl);
        $errorcurl = curl_error($curl);
        curl_close($curl);

        $response = json_decode($response, true);

        // Mismo rastro que deja el resto de la facturación, con nombre propio para no
        // pisar el de facturarPedido cuando haya que revisar qué se mandó
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/facturaGlobal".($pruebas ? "Pruebas" : "").".txt",print_r($datos,true)."\n\n".print_r($response,true));

        if(!empty($errorcurl)){
            throw new Exception("No se pudo contactar al timbrador: ".$errorcurl);
        }

        if(empty($response) || $response["response"] != true){
            throw new Exception("El timbrador rechazó la factura global: ".(isset($response["mensaje"]) ? $response["mensaje"] : "respuesta vacía"));
        }

        return $response;
    }

    /**
     * getSerial
     * Obtener el numero de serie del certificado
     * @param  string $certificado Path de certificado
     * @return string Retorna el numero de serie del certificado
     */
    public function obtenerNumeroCertificado($certificado)
    {
        $numero = FALSE;
        exec("openssl x509 -inform DER -in $certificado -serial", $datacer);
        //Reemplazamos el texto que no nos interesa(str_replace) y convertimos el string a array(str_split)
        $serialnumbers = str_split(str_replace("serial=", "", $datacer[0]));
        //Para despues obtener los numeros en posiciones impares
        for ($i = 0; $i < count($serialnumbers); $i++) {
            if ($i % 2 != 0) {
                $numero .= $serialnumbers[$i];
            }
        }
        return $numero;
    }

    /**
     * getCer
     * Obtener el contenido del certificado
     * @param  string $certificado Path de certificado
     * @return string Retorna el contenido del certificado
     */
    public function obtenerContenidoCertificado($certificado)
    {
        exec("openssl x509 -inform DER -in $certificado", $cer);
        array_pop($cer);                //elimino el ultimo elemento
        array_shift($cer);              //y el primero
        $contenido = implode($cer);     //despues convierto a string
        return $contenido;
    }

}
?>
