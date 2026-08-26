<?php
class Pagos{

    private $con;
    private $claseSAT;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
        $this->con = $con;
        $this->claseSAT = new SAT();
    }

    /**
     * Devuelve el SQL de la relación pedido -> factura, resolviéndola por las tres vías que
     * existen en la base: tpedidosfacturas (facturación parcial, la relación vigente),
     * tpedidos.idfactura (legado, solo conserva la última factura del pedido) y
     * ttickets.idfactura (facturación de tickets desde cortes). Se usa como tabla derivada.
     *
     * @return string SQL de la tabla derivada con las columnas idpedido e idfactura
     */
    private function relacionPedidoFactura(){
        return "
                (
                select
                    idpedido,
                    idfactura
                from
                    tpedidosfacturas
                union
                select
                    idpedido,
                    idfactura
                from
                    tpedidos
                where
                    idfactura > 0
                union
                select
                    idpedido,
                    idfactura
                from
                    ttickets
                where
                    idfactura > 0
                )";
    }

    public function getPagos($post){
        try{
            $fecha_inicial = mysqli_real_escape_string($this->con,$post["txtFechaInicial"]);
            $fecha_final = mysqli_real_escape_string($this->con,$post["txtFechaFinal"]);
            $idemisor = mysqli_real_escape_string($this->con,$post["slcEmisor"]);
            $idcliente = mysqli_real_escape_string($this->con,$post["slcCliente"]);
            $uuid = mysqli_real_escape_string($this->con,$post["txtUUID"]);
            $status = mysqli_real_escape_string($this->con,$post["slcStatus"]);
            
            $pagina = (isset($post["pagina"])) ? $post["pagina"] : 1;
            $regpagina = 100;

            $query = "
            select
                a.idpago,
                a.idcliente,
                coalesce(b.nombre, a.cliente) as cliente,
                a.idrazonsocial,
                c.razon_social,
                a.idemisor,
                d.razon_social as razon_social_emisor,
                a.idformapago,
                e.formapago,
                e.descripcion as descripcion_formapago,
                a.serie,
                a.folio,
                a.total,
                a.uuid,
                a.status,
                a.timbrado,
                a.registro,
                -- Los pagos capturados con el desglose por documento dicen a qué factura fue
                -- cada abono; los anteriores al desglose solo guardaron el pedido, así que para
                -- esos se sigue resolviendo por las facturas del pedido
                case when exists (
                    select 1
                    from tformaspagopedido fp
                    where fp.idpago = a.idpago and fp.idfactura is not null
                ) then exists (
                    select 1
                    from tformaspagopedido fp
                    join tfacturas f on f.idfactura = fp.idfactura
                    where fp.idpago = a.idpago
                      and f.idmetodopago = 1
                      and (f.status is null or f.status = 1)
                      and f.uuid is not null
                      and f.uuid <> ''
                      and f.saldo > 0
                ) else exists (
                    select 1
                    from tformaspagopedido fp
                    join ".$this->relacionPedidoFactura()." pf on pf.idpedido = fp.idpedido
                    join tfacturas f on f.idfactura = pf.idfactura
                    where fp.idpago = a.idpago
                      and f.idmetodopago = 1
                      and (f.status is null or f.status = 1)
                      and f.uuid is not null
                      and f.uuid <> ''
                      and f.saldo > 0
                ) end as tiene_factura
            from
                tpagos a
            left join
                tclientes b
            on
                b.idcliente = a.idcliente
            left join
                tclienterazonessociales c
            on
                c.idrazonsocial = a.idrazonsocial
            left join
                temisores d
            on
                d.idemisor = a.idemisor
            left join
                sat_tcatformaspago e
            on
                e.idformapago = a.idformapago";

            $condiciones = array();

            if(!empty($uuid)){
                $condiciones[] = "uuid like '%".$uuid."%'";
            }else{
                if(!empty($fecha_inicial)){
                    $condiciones[] = "date(a.registro) >= '".$fecha_inicial."'";
                }
                if(!empty($fecha_final)){
                    $condiciones[] = "date(a.registro) <= '".$fecha_final."'";
                }
                if(!empty($idemisor)){
                    $condiciones[] = "a.idemisor = '".$idemisor."'";
                }
                if(!empty($idcliente)){
                    $condiciones[] = "a.idcliente = '".$idcliente."'";
                }
                if(!empty($status)){
                    $condiciones[] = "a.status = '".$status."'";
                }
            }

            $query .= (count($condiciones)>0) ? " where ".implode(" and ",$condiciones) : "";

            //obtenemos el número de páginas para el front (paginación)
            $numpaginas = ceil(mysqli_num_rows(mysqli_query($this->con, $query)) / $regpagina);
            $maxpaginas = 3;

            $query .= "
            order by
                a.registro desc
            limit 
                " . (($pagina - 1) * $regpagina) . "," . $regpagina;

            //ejecutamos el query que traerá los resultados incluidos solo para la página seleccionada
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)>0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "pagos" => mysqli_fetch_all($result,MYSQLI_ASSOC),
                    "pagina" => $pagina,
                    "numpaginas" => $numpaginas,
                    "maxpaginas" => $maxpaginas
                );
            }else{
                throw new Exception("No se encontraron resultados");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Pagos que amortizaron una factura, con el monto que se le aplicó a cada uno. Solo
     * existen registros para los pagos cuyo complemento se timbró, que es cuando se
     * descuenta el saldo de la factura.
     *
     * @access public
     * @param array $post Contiene "idfactura"
     * @return array
     */
    public function getPagosFactura($post){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$post["idfactura"]);

            $query = "
            select
                a.idpagofactura,
                a.idpago,
                a.monto,
                a.parcialidad,
                b.serie,
                b.folio,
                b.uuid,
                b.total,
                b.fecha,
                b.timbrado,
                b.status,
                c.formapago,
                c.descripcion as descripcion_formapago
            from
                tpagosfacturas a
            inner join
                tpagos b
            on
                b.idpago = a.idpago
            left join
                sat_tcatformaspago c
            on
                c.idformapago = b.idformapago
            where
                a.idfactura = '".$idfactura."'
            order by
                b.timbrado, a.idpagofactura";

            $result = mysqli_query($this->con,$query);

            $respuesta = array(
                "respuesta" => "OK",
                "pagos" => ($result) ? mysqli_fetch_all($result,MYSQLI_ASSOC) : array()
            );
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getPDF($idpago){
        try{
            $idpago = mysqli_real_escape_string($this->con,$idpago);

            $query = "
            select
                a.uuid,
                b.rfc as rfc_emisor
            from
                tpagos a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $factura = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $uuid = $factura["uuid"];
            $rfc_emisor = $factura["rfc_emisor"];
            $ruta_fancy = "/emisores/".$rfc_emisor."/pagos/".$uuid.".pdf";
            $ruta = $_SERVER["DOCUMENT_ROOT"].$ruta_fancy;

            if(file_exists($ruta)){
                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo" => "fancyArchivo",
                    "pdf" => $ruta,
                    "pdf_fancy" => $ruta_fancy
                );
            }else{
                throw new Exception("No se encontró el PDF del pago");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getXML($idpago){
        try{
            $idpago = mysqli_real_escape_string($this->con,$idpago);

            $query = "
            select
                a.uuid,
                b.rfc as rfc_emisor
            from
                tpagos a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $factura = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $uuid = $factura["uuid"];
            $rfc_emisor = $factura["rfc_emisor"];
            $ruta_fancy = "/emisores/".$rfc_emisor."/pagos/".$uuid.".xml";
            $ruta = $_SERVER["DOCUMENT_ROOT"].$ruta_fancy;

            if(file_exists($ruta)){
                $respuesta = array(
                    "respuesta" => "OK",
                    "xml" => $ruta,
                    "xml_fancy" => $ruta_fancy
                );
            }else{
                throw new Exception("No se encontró el XML del pago");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $query = "
            select
                a.idpago,
                a.idcliente,
                coalesce(b.nombre, a.cliente) as cliente,
                a.idrazonsocial,
                -- Los complementos de facturas sin cliente no tienen razón social relacionada:
                -- el receptor se recupera de los datos en texto de las facturas que amortizó
                case when a.idrazonsocial > 0 then c.razon_social else (
                    select
                        f.razonsocial
                    from
                        tpagosfacturas pf
                    join
                        tfacturas f
                    on
                        f.idfactura = pf.idfactura
                    where
                        pf.idpago = a.idpago and
                        f.razonsocial is not null and
                        f.razonsocial <> ''
                    limit 1
                ) end as razon_social,
                case when a.idrazonsocial > 0 then c.rfc else (
                    select
                        f.rfc
                    from
                        tpagosfacturas pf
                    join
                        tfacturas f
                    on
                        f.idfactura = pf.idfactura
                    where
                        pf.idpago = a.idpago and
                        f.rfc is not null and
                        f.rfc <> ''
                    limit 1
                ) end as cliente_rfc,
                a.idemisor,
                d.razon_social as razon_social_emisor,
                d.rfc as emisor_rfc,
                a.idformapago,
                e.formapago,
                e.descripcion as descripcion_formapago,
                a.serie,
                a.folio,
                a.total,
                a.uuid,
                a.status,
                a.timbrado,
                a.registro
            from
                tpagos a
            left join
                tclientes b
            on
                b.idcliente = a.idcliente
            left join
                tclienterazonessociales c
            on
                c.idrazonsocial = a.idrazonsocial
            left join
                temisores d
            on
                d.idemisor = a.idemisor
            left join
                sat_tcatformaspago e
            on
                e.idformapago = a.idformapago
            where
                a.idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)>0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "pago" => mysqli_fetch_assoc($result)
                );
            }else{
                throw new Exception("No se pudo recuperar la información del pago");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getZIPData($idpago){
        try{
            $idpago = mysqli_real_escape_string($this->con,$idpago);

            $query = "
            select
                a.uuid,
                a.serie,
                a.folio,
                coalesce(b.nombre, a.cliente) as cliente,
                c.rfc as rfc_emisor
            from
                tpagos a
            left join
                tclientes b
            on
                b.idcliente = a.idcliente
            left join
                temisores c
            on
                c.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";

            $pago = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(!$pago){
                throw new Exception("No se encontró el pago");
            }

            $base = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$pago["rfc_emisor"]."/pagos/".$pago["uuid"];

            if(!file_exists($base.".pdf")){
                throw new Exception("No se encontró el PDF del pago");
            }
            if(!file_exists($base.".xml")){
                throw new Exception("No se encontró el XML del pago");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "uuid"      => $pago["uuid"],
                "serie"     => $pago["serie"],
                "folio"     => $pago["folio"],
                "cliente"   => $pago["cliente"],
                "pdf_path"  => $base.".pdf",
                "xml_path"  => $base.".xml"
            );
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje"   => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    // Cancela el CFDI del complemento de pago ante el SAT. Solo aplica a pagos timbrados
    // (status = 1 con uuid). No cierra el pago como tal: deja status = 4 (complemento
    // cancelado, pago pendiente de cancelar) para que después se llame a cancelarPago().
    public function cancelarComplemento($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $pago = $this->getPago(array(
                "idpago" => $idpago
            ));

            if($pago["respuesta"]!="OK"){
                throw new Exception($pago["mensaje"]);
            }

            $pago = $pago["pago"];

            if(empty($pago["uuid"])){
                throw new Exception("Este pago no tiene un complemento timbrado que cancelar.");
            }

            if($pago["status"] != 1){
                throw new Exception("El complemento de este pago no se encuentra activo.");
            }

            // El SAT exige el RFC del receptor para cancelar; sin él la petición se rechaza
            if(empty($pago["cliente_rfc"])){
                throw new Exception("No se pudo determinar el RFC del receptor del complemento; contacta a soporte.");
            }

            $idmotivocancelacion = mysqli_real_escape_string($this->con,$post["slcMotivoCancelacion"]);
            $uuid = mysqli_real_escape_string($this->con,$post["txtUUID"]);

            $query = "
            select
                *
            from
                sat_tcatmotivoscancelacion
            where
                idmotivo = '".$idmotivocancelacion."'";
            $motivo_cancelacion = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($motivo_cancelacion["requiere_uuid"]==1 && !$this->esUUIDValido($uuid)){
                throw new Exception("El formato del UUID de sustitución no es válido");
            }

            $cerpath = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$pago["emisor_rfc"]."/sat/certificado.cer";
            $cerpem = $cerpath.".pem";
            exec("openssl x509 -in $cerpath -inform DER -out $cerpem");

            $keypath = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$pago["emisor_rfc"]."/sat/llave.key";
            $keypem = $keypath.".pem";

            $pfx = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$pago["emisor_rfc"]."/sat/pfx.pfx";
            $pwdPfx = uniqid();

            if($this->generarPfx($keypem,$cerpem,$pfx,$pwdPfx)){
                // Se manda a cancelar el complemento de pago al SAT
                $datos = array(
                    "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                    "pruebas" => 0,
                    "tipoComprobante" => "C2",
                    "pfx" => base64_encode(file_get_contents($pfx)),
                    "pfx_pwd" => $pwdPfx,
                    "uuid" => $pago["uuid"],
                    "rfc_emisor" => $pago["emisor_rfc"],
                    "rfc_receptor" => $pago["cliente_rfc"],
                    "total" => 0,
                    "cve_motivo_cancelacion" => $motivo_cancelacion["clave"],
                    "uuid_sustitucion" => $uuid
                );

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.xptk.app/timbrador/index.php',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($datos),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded'
                    )
                ));

                $response = curl_exec($curl);
                $response = json_decode($response,true);
                curl_close($curl);

                if($response["status"]=="success"){
                    // statusCFDI 201 = "cancelado con aceptación pendiente": el receptor tiene
                    // 3 días para aceptar/rechazar ante el SAT, así que el pago se queda en
                    // status = 2 y no se habilita todavía "Cancelar pago" (pendiente: cronjob
                    // que reconsulte el estatus y lo mueva a definitivo).
                    $esDefinitiva = ($response["statusCFDI"] != "201");

                    $query = "
                    update
                        tpagos
                    set
                        status = '".($esDefinitiva ? "4" : "2")."'
                    where
                        idpago = '".$idpago."'";
                    $ok = mysqli_query($this->con,$query);

                    if($ok){
                        $respuesta = array(
                            "respuesta" => "OK",
                            "tipo" => "mensajecargar",
                            "titulo" => $esDefinitiva ? "Complemento cancelado" : "Cancelación en proceso",
                            "mensaje" => $esDefinitiva
                                ? "El complemento de pago se ha cancelado correctamente. Ahora puedes cancelar el pago."
                                : "La cancelación se envió al SAT y quedó pendiente de aceptación por el receptor. Podrás cancelar el pago hasta que se confirme la cancelación definitiva.",
                            "formulario" => "formBusqueda"
                        );
                    }else{
                        throw new Exception("El CFDI se canceló ante el SAT, pero no se pudo actualizar el registro del pago. Contacta a soporte para corregir el registro manualmente.");
                    }
                }else{
                    throw new Exception($response["text"]);
                }
            }else{
                throw new Exception("Ocurrió un error en la generación de los archivos para cancelación");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Reconsulta ante el SAT un complemento de pago que quedó en proceso de cancelación y
     * aplica el desenlace: lo marca como cancelado (status 4, dejando el pago vivo para que
     * el usuario decida cancelarlo), lo regresa a activo si el receptor rechazó la solicitud,
     * o lo deja igual si la solicitud sigue viva.
     *
     * Sirve tanto para el cronjob que barre todos los complementos en proceso como para el
     * botón "Verificar estatus en SAT" del listado.
     *
     * @access public
     * @param array $post   Debe contener idpago. Con "simular" => true consulta al SAT y
     *                      reporta el desenlace, pero no modifica ningún registro.
     * @return array
     */
    public function verificarEstatusSAT($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);
            $simular = !empty($post["simular"]);

            $pago = $this->getPago(array(
                "idpago" => $idpago
            ));

            if($pago["respuesta"] != "OK"){
                throw new Exception($pago["mensaje"]);
            }

            $pago = $pago["pago"];

            if($pago["status"] != 2){
                throw new Exception("El complemento de este pago no se encuentra en proceso de cancelación.");
            }

            if(empty($pago["uuid"])){
                throw new Exception("Este pago no tiene un complemento timbrado; no hay nada que consultar ante el SAT.");
            }

            $consulta = $this->claseSAT->consultarEstatusCFDI(
                $this->claseSAT->rutaXMLComprobante($_SERVER["DOCUMENT_ROOT"],$pago["emisor_rfc"],"pagos",$pago["uuid"]),
                $pago["emisor_rfc"],
                $pago["cliente_rfc"]
            );

            if($consulta["respuesta"] != "OK"){
                throw new Exception($consulta["mensaje"]);
            }

            if($consulta["resultado"] == "cancelado" || $consulta["resultado"] == "activo"){
                // Cancelado deja el pago en 4: el complemento ya no existe ante el SAT, pero
                // revertir el efecto del pago sobre pedidos y saldos sigue siendo una decisión
                // del usuario a través de "Cancelar pago".
                $nuevostatus = ($consulta["resultado"] == "cancelado") ? "4" : "1";

                if($consulta["resultado"] == "cancelado"){
                    $titulo = "Complemento cancelado";
                    $consulta["mensaje"] .= " Ahora puedes cancelar el pago.";
                }else{
                    $titulo = "Complemento activo";
                }

                if(!$simular){
                    $query = "
                    update
                        tpagos
                    set
                        status = '".$nuevostatus."'
                    where
                        idpago = '".$idpago."'";

                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("El SAT resolvió la solicitud de cancelación, pero no se pudo actualizar el registro del pago. Contacta a soporte.");
                    }
                }
            }else{
                $titulo = "Cancelación en proceso";
            }

            if($simular){
                $consulta["mensaje"] = "[SIMULACIÓN] ".$consulta["mensaje"];
            }

            $respuesta = array_merge($consulta,array(
                "tipo" => "mensajecargar",
                "titulo" => $titulo,
                "formulario" => "formBusqueda"
            ));
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Devuelve los ids de los pagos cuyo complemento quedó en proceso de cancelación, para
     * que el cronjob los reconsulte.
     *
     * @access public
     * @return array
     */
    public function getPagosEnProcesoCancelacion(){
        $query = "
        select
            idpago
        from
            tpagos
        where
            status = 2 and
            uuid is not null and
            uuid <> ''
        order by
            idpago";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    // Cierra el pago como tal: revierte su efecto sobre el pedido/factura y lo marca
    // cancelado. Aplica a pagos que nunca se timbraron (status = 1 sin uuid) y a pagos
    // cuyo complemento ya se canceló ante el SAT (status = 4).
    public function cancelarPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $pago = $this->getPago(array(
                "idpago" => $idpago
            ));

            if($pago["respuesta"]!="OK"){
                throw new Exception($pago["mensaje"]);
            }

            $pago = $pago["pago"];

            if($pago["status"] == 2){
                throw new Exception("La cancelación del complemento sigue pendiente de aceptación ante el SAT. Espera a que se resuelva antes de cancelar el pago.");
            }

            if(!(($pago["status"] == 1 && empty($pago["uuid"])) || $pago["status"] == 4)){
                throw new Exception("Este pago no se puede cancelar en su estado actual.");
            }

            // Se recuperan antes de la transacción porque la reversión los borra: de aquí
            // sale el aviso de qué se eliminó de caja, o de que no había nada que eliminar
            $tickets = $this->getTicketsPago($idpago);

            mysqli_begin_transaction($this->con);

            $query = "
            update
                tpagos
            set
                status = 3
            where
                idpago = '".$idpago."'";
            $ok = mysqli_query($this->con,$query);
            $ok = $ok && $this->revertirEfectoPago($idpago);

            if($ok){
                mysqli_commit($this->con);
                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo" => "mensajecargar",
                    "titulo" => "Pago cancelado",
                    "mensaje" => $this->mensajeTicketsCancelados($tickets),
                    "formulario" => "formBusqueda"
                );
            }else{
                mysqli_rollback($this->con);
                throw new Exception("No se pudo cancelar el pago.");
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    // Revierte el efecto de un pago sobre los pedidos que cubrió (abonado/statuspago) y le
    // regresa a cada factura el saldo que su complemento amortizó, según lo registrado en
    // tpagosfacturas. Se usa tanto para pagos timbrados (tras cancelar el CFDI) como para
    // pagos que nunca llegaron a timbrarse; en ese segundo caso no hay saldo que devolver,
    // porque el saldo de la factura solo se descuenta al timbrar el complemento.
    private function revertirEfectoPago($idpago){
        $query = "
        select
            a.idpedido,
            sum(a.monto) as monto,
            b.total,
            b.abonado
        from
            tformaspagopedido a
        join
            tpedidos b
        on
            b.idpedido = a.idpedido
        where
            a.idpago = '".$idpago."'
        group by
            a.idpedido,
            b.total,
            b.abonado";
        $aplicaciones = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $ok = true;
        foreach($aplicaciones as $aplicacion){
            $idpedido = (int)$aplicacion["idpedido"];
            $monto = sprintf("%.2f", floatval($aplicacion["monto"]));
            $nuevoabonado = floatval($aplicacion["abonado"]) - floatval($aplicacion["monto"]);
            $statuspago = ($nuevoabonado >= floatval($aplicacion["total"])) ? 1 : 0;

            $query = "
            update
                tpedidos
            set
                abonado = abonado - ".$monto.",
                statuspago = ".$statuspago."
            where
                idpedido = '".$idpedido."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        select
            idfactura,
            monto
        from
            tpagosfacturas
        where
            idpago = '".$idpago."'";
        $amortizaciones = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        foreach($amortizaciones as $amortizacion){
            $query = "
            update
                tfacturas
            set
                saldo = saldo + ".sprintf("%.2f", floatval($amortizacion["monto"]))."
            where
                idfactura = '".(int)$amortizacion["idfactura"]."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        delete
        from
            tpagosfacturas
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        $query = "
        delete
        from
            tformaspagopedido
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        $ok = $ok && $this->borrarTicketsPago($idpago);

        return $ok;
    }

    /**
     * Tickets de caja que generó un pago: uno por cada pedido cobrado. Se usan para avisar en
     * pantalla qué se va a eliminar antes de cancelar, y para armar el mensaje de resultado.
     *
     * @param int $idpago
     * @return array Tickets con su corte y sucursal (vacío si el pago no tiene tickets ligados)
     */
    public function getTicketsPago($idpago){
        $idpago = (int)$idpago;

        $query = "
        select
            a.idticket,
            a.idpedido,
            a.folio,
            a.total,
            a.fecha,
            a.idcorte,
            b.nombre as sucursal,
            c.status as statuscorte
        from
            ttickets a
        left join
            tsucursales b
        on
            b.idsucursal = a.idsucursal
        left join
            tcortessucursales c
        on
            c.idcorte = a.idcorte
        where
            a.idpago = '".$idpago."'
        order by
            a.idticket";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    /**
     * Borra el rastro del pago en caja: el ticket de cada pedido cobrado, sus formas de pago y
     * el renglón de tticketspedidos. Al desaparecer el ticket deja de sumar en el corte y en
     * los reportes de ventas; los cortes ya cerrados conservan los totales que se congelaron al
     * cerrarlos (tcortessucursales.ventas y tcortesucursal_formaspago), así que solo cambian
     * los reportes que se recalculan al vuelo desde ttickets.
     *
     * Los pagos anteriores a la migración que el backfill no pudo ligar no tienen tickets
     * asociados: en ese caso no hay nada que borrar y la cancelación continúa avisándolo.
     *
     * @param int $idpago
     * @return bool
     */
    private function borrarTicketsPago($idpago){
        $idpago = (int)$idpago;

        $query = "
        select
            idticket
        from
            ttickets
        where
            idpago = '".$idpago."'";
        $tickets = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $ok = true;

        if(!empty($tickets)){
            $idtickets = array();
            foreach($tickets as $ticket){
                $idtickets[] = (int)$ticket["idticket"];
            }

            $query = "
            delete
            from
                tformaspagoticket
            where
                idticket in (".implode(",",$idtickets).")";
            $ok = $ok && mysqli_query($this->con,$query);

            $query = "
            delete
            from
                ttickets
            where
                idpago = '".$idpago."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        delete
        from
            tticketspedidos
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        return $ok;
    }

    /**
     * Mensaje de resultado de la cancelación. Cuando no había tickets ligados se avisa
     * explícitamente para que nadie asuma que la caja quedó ajustada.
     *
     * @param array $tickets Tickets recuperados con getTicketsPago() antes de la reversión
     * @return string
     */
    private function mensajeTicketsCancelados($tickets){
        if(empty($tickets)){
            return "El pago se ha cancelado correctamente. No se encontró el ticket de caja asociado; verifícalo manualmente.";
        }

        $folios = array();
        foreach($tickets as $ticket){
            $folios[] = "#".$ticket["folio"];
        }

        return "El pago se ha cancelado correctamente. Se eliminó de caja ".((count($folios) == 1) ? "el ticket " : "los tickets ").implode(", ",$folios).".";
    }

    /**
     * Obtiene las facturas PPD vigentes y con saldo pendiente de un pedido, ordenadas de la
     * más antigua a la más reciente (así se amortizan las parcialidades en ese orden).
     *
     * @param int $idpedido
     * @return array Facturas del pedido que requieren complemento de pago
     */
    private function getFacturasPPDPedido($idpedido){
        $idpedido = mysqli_real_escape_string($this->con,$idpedido);

        $query = "
        select distinct
            f.idfactura,
            f.serie,
            f.folio,
            f.uuid,
            f.saldo,
            f.idemisor,
            f.idrazonsocial,
            f.razonsocial,
            f.rfc,
            f.codigo_postal,
            f.regimenfiscal,
            round((f.iva/f.subtotal)*100,0) as impuesto
        from
            ".$this->relacionPedidoFactura()." pf
        join
            tfacturas f
        on
            f.idfactura = pf.idfactura
        where
            pf.idpedido = '".$idpedido."' and
            f.idmetodopago = 1 and
            (f.status is null or f.status = 1) and
            f.uuid is not null and
            f.uuid <> '' and
            f.saldo > 0
        order by
            f.idfactura";
        $result = mysqli_query($this->con,$query);

        return ($result) ? mysqli_fetch_all($result,MYSQLI_ASSOC) : array();
    }

    /**
     * Genera y timbra el complemento de pago de un pago que quedó sin timbrar. Es el mismo
     * flujo que Tienda-Uniformes-Cisne ejecuta al registrar el pago (Pagos::generarComplementoPago),
     * duplicado aquí a propósito para poder rescatar desde el admin los pagos de cualquier
     * sucursal: la lista de la tienda solo muestra los de la sucursal del vendedor conectado.
     * Si algún día se toca la lógica de timbrado, hay que tocar los dos lados.
     *
     * @param array $post Contiene "idpago"
     * @return array Respuesta en el formato del controlador (respuesta/tipo/mensaje)
     */
    /**
     * Obtiene una factura PPD vigente y con saldo pendiente por su ID, con los datos fiscales
     * que necesita el complemento de pago. Devuelve null si la factura no aplica para
     * complemento (no es PPD, está cancelada, no está timbrada o ya no tiene saldo).
     *
     * @param int $idfactura
     * @return array|null
     */
    private function getFacturaPPD($idfactura){
        $idfactura = mysqli_real_escape_string($this->con,$idfactura);

        $query = "
        select
            f.idfactura,
            f.serie,
            f.folio,
            f.uuid,
            f.saldo,
            f.idemisor,
            f.idrazonsocial,
            f.razonsocial,
            f.rfc,
            f.codigo_postal,
            f.regimenfiscal,
            round((f.iva/f.subtotal)*100,0) as impuesto
        from
            tfacturas f
        where
            f.idfactura = '".$idfactura."' and
            f.idmetodopago = 1 and
            (f.status is null or f.status = 1) and
            f.uuid is not null and
            f.uuid <> '' and
            f.saldo > 0";
        $result = mysqli_query($this->con,$query);

        return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    }

    /**
     * Número de parcialidad que le toca a un complemento sobre una factura: cuántos
     * complementos vigentes la han amortizado antes, más uno. Se cuenta por factura, no por
     * pedido, porque así lo pide el CFDI de pagos.
     *
     * @param int $idfactura
     * @param int $idpago       Pago que se está timbrando, se excluye del conteo
     * @return int
     */
    private function parcialidadFactura($idfactura,$idpago){
        $idfactura = mysqli_real_escape_string($this->con,$idfactura);
        $idpago = mysqli_real_escape_string($this->con,$idpago);

        $query = "
        select
            count(*) + 1 as parcialidad
        from
            tpagosfacturas a
        join
            tpagos b
        on
            b.idpago = a.idpago
        where
            a.idfactura = '".$idfactura."' and
            a.idpago <> '".$idpago."' and
            b.status <> 3";

        return mysqli_fetch_assoc(mysqli_query($this->con, $query))["parcialidad"];
    }

    /**
     * Resuelve qué facturas amortiza un pago y con cuánto cada una, junto con la tienda del
     * pedido (para el logo del correo).
     *
     * La asignación la captura el vendedor documento por documento al registrar el pago y vive
     * en tformaspagopedido.idfactura, así que aquí solo se lee: el sistema ya no decide a qué
     * factura va el dinero. Los pagos registrados antes del desglose por documento no traen
     * esa asignación; para esos, y solo para esos, se conserva el reparto automático de la
     * factura más antigua a la más reciente.
     *
     * @param int $idpago
     * @return array array("facturas" => array, "idtienda" => int)
     */
    private function facturasComplemento($idpago){
        $idpago = mysqli_real_escape_string($this->con,$idpago);

        $query = "
        select
            a.idpedido,
            a.idfactura,
            sum(a.monto) as monto
        from
            tformaspagopedido a
        where
            a.idpago = '".$idpago."'
        group by
            a.idpedido,
            a.idfactura
        order by
            a.idpedido,
            a.idfactura";
        $result = mysqli_query($this->con,$query);

        if(!$result || mysqli_num_rows($result)==0){
            throw new Exception("No se pudo recuperar la información de los pagos");
        }

        $aplicaciones = mysqli_fetch_all($result,MYSQLI_ASSOC);

        $query = "
        select
            idtienda
        from
            vpedidos
        where
            idpedido = '".$aplicaciones[0]["idpedido"]."'";
        $idtienda = mysqli_fetch_assoc(mysqli_query($this->con, $query))["idtienda"];

        $asignadas = array_filter($aplicaciones,function($aplicacion){
            return $aplicacion["idfactura"] > 0;
        });

        $facturas = (!empty($asignadas))
            ? $this->facturasAsignadas($idpago,$asignadas)
            : $this->facturasReparto($idpago,$aplicaciones);

        return array(
            "facturas" => $facturas,
            "idtienda" => $idtienda
        );
    }

    /**
     * Arma los documentos relacionados del complemento a partir de la asignación que capturó
     * el vendedor. Los renglones que no correspondan a una factura PPD vigente con saldo (los
     * abonos a la parte no facturada del pedido o a facturas PUE) simplemente no se relacionan.
     *
     * @param int   $idpago
     * @param array $asignadas   Renglones de tformaspagopedido con idfactura
     * @return array
     */
    private function facturasAsignadas($idpago,$asignadas){
        $facturas = array();

        foreach($asignadas as $aplicacion){
            $idfactura = $aplicacion["idfactura"];
            $monto = round(floatval($aplicacion["monto"]), 2);

            if($monto < 0.01){
                continue;
            }

            // Si dos pedidos del mismo pago abonaron a la misma factura, se acumulan en un
            // solo documento relacionado
            if(isset($facturas[$idfactura])){
                $facturas[$idfactura]["monto"] = round($facturas[$idfactura]["monto"] + $monto, 2);
                continue;
            }

            $factura = $this->getFacturaPPD($idfactura);

            if($factura === null){
                continue;
            }

            $facturas[$idfactura] = array(
                "idfactura" => $idfactura,
                "monto" => $monto,
                "saldo" => round(floatval($factura["saldo"]), 2),
                "uuid" => $factura["uuid"],
                "serie" => $factura["serie"],
                "folio" => $factura["folio"],
                "idemisor" => $factura["idemisor"],
                "idrazonsocial" => $factura["idrazonsocial"],
                "razonsocial" => $factura["razonsocial"],
                "rfc" => $factura["rfc"],
                "codigo_postal" => $factura["codigo_postal"],
                "regimenfiscal" => $factura["regimenfiscal"],
                "parcialidad" => $this->parcialidadFactura($idfactura,$idpago),
                "impuesto" => $factura["impuesto"]
            );
        }

        // Nunca se puede amortizar más de lo que la factura debe, aunque la captura lo diga
        foreach($facturas as $idfactura => $factura){
            if($factura["monto"] > $factura["saldo"]){
                $facturas[$idfactura]["monto"] = $factura["saldo"];
            }
        }

        return array_values($facturas);
    }

    /**
     * Reparto automático del abono entre las facturas PPD del pedido, de la más antigua a la
     * más reciente. Solo se usa con los pagos anteriores al desglose por documento, que no
     * guardaron a qué factura iba cada peso.
     *
     * @param int   $idpago
     * @param array $aplicaciones   Renglones de tformaspagopedido agrupados por pedido
     * @return array
     */
    private function facturasReparto($idpago,$aplicaciones){
        $facturas = array();
        $saldosdisponibles = array();
        $montospedido = array();

        foreach($aplicaciones as $aplicacion){
            $idpedido = $aplicacion["idpedido"];
            $montospedido[$idpedido] = round((isset($montospedido[$idpedido]) ? $montospedido[$idpedido] : 0) + floatval($aplicacion["monto"]), 2);
        }

        foreach($montospedido as $idpedido => $porAplicar){
            foreach($this->getFacturasPPDPedido($idpedido) as $factura){
                if($porAplicar < 0.01){
                    break;
                }

                $idfactura = $factura["idfactura"];
                $saldo = round(floatval($factura["saldo"]), 2);

                // El saldo disponible se lleva en memoria para el caso (raro) de que dos
                // pedidos del mismo pago compartan factura: así no se relaciona más de lo
                // que la factura debe
                $disponible = isset($saldosdisponibles[$idfactura]) ? $saldosdisponibles[$idfactura] : $saldo;
                $aplicado = round(min($porAplicar, $disponible), 2);

                if($aplicado < 0.01){
                    continue;
                }

                $porAplicar = round($porAplicar - $aplicado, 2);
                $saldosdisponibles[$idfactura] = round($disponible - $aplicado, 2);

                if(isset($facturas[$idfactura])){
                    $facturas[$idfactura]["monto"] = round($facturas[$idfactura]["monto"] + $aplicado, 2);
                    continue;
                }

                $facturas[$idfactura] = array(
                    "idfactura" => $idfactura,
                    "monto" => $aplicado,
                    "saldo" => $saldo,
                    "uuid" => $factura["uuid"],
                    "serie" => $factura["serie"],
                    "folio" => $factura["folio"],
                    "idemisor" => $factura["idemisor"],
                    "idrazonsocial" => $factura["idrazonsocial"],
                    "razonsocial" => $factura["razonsocial"],
                    "rfc" => $factura["rfc"],
                    "codigo_postal" => $factura["codigo_postal"],
                    "regimenfiscal" => $factura["regimenfiscal"],
                    "parcialidad" => $this->parcialidadFactura($idfactura,$idpago),
                    "impuesto" => $factura["impuesto"]
                );
            }
        }

        return array_values($facturas);
    }

    public function timbrarPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con, $post["idpago"]);

            // Recuperamos la información principal del pago
            $query = "
            select
                idcliente,
                idformapago,
                fecha,
                total,
                uuid,
                status
            from
                tpagos
            where
                idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se pudo recuperar la información del pago");
            }

            $pago = mysqli_fetch_assoc($result);

            // Un pago timbrado no se puede volver a timbrar: se duplicaría el CFDI y se
            // descontaría dos veces el saldo de las facturas
            if(!empty($pago["uuid"])){
                throw new Exception("Este pago ya tiene un complemento timbrado");
            }

            if($pago["status"] != 1){
                throw new Exception("Solo se puede timbrar el complemento de un pago activo");
            }

            $idcliente = $pago["idcliente"];
            $idformapago = $pago["idformapago"];
            $fecha = substr($pago["fecha"], 0, 10);
            $totalpago = floatval($pago["total"]);

            // La asignación factura por factura la capturó el vendedor al registrar el pago en
            // la tienda; aquí solo se lee para armar los documentos relacionados
            $resolucion = $this->facturasComplemento($idpago);
            $facturas = $resolucion["facturas"];
            $idtienda = $resolucion["idtienda"];

            if(empty($facturas)){
                throw new Exception("El pago no corresponde a facturas PPD con saldo pendiente, por lo que no requiere complemento");
            }

            // Obtener idemisor e idrazonsocial de la primera factura
            $idemisor = $facturas[0]["idemisor"];
            $idrazonsocial = $facturas[0]["idrazonsocial"];

            // Las facturas de pedidos sin cliente no tienen razón social relacionada: sus datos
            // fiscales viven en texto dentro de tfacturas
            $sinrazonsocial = !($idrazonsocial > 0);

            // Un CFDI de pago lleva un solo emisor y un solo receptor, así que no se puede
            // timbrar un pago que amortice facturas de emisores o razones sociales distintas.
            // Sin idrazonsocial se compara el RFC en texto, porque de otro modo dos facturas
            // con receptores distintos pasarían la validación (ambas con idrazonsocial vacío)
            foreach($facturas as $factura){
                $mismoreceptor = ($sinrazonsocial)
                    ? strcasecmp(trim($factura["rfc"]), trim($facturas[0]["rfc"])) == 0 && !($factura["idrazonsocial"] > 0)
                    : $factura["idrazonsocial"] == $idrazonsocial;

                if($factura["idemisor"] != $idemisor || !$mismoreceptor){
                    throw new Exception("El pago amortiza facturas de distinto emisor o razón social; hay que registrarlo por separado");
                }
            }

            // Obtener datos del emisor
            $query = "
            select
                *
            from
                temisores
            where
                idemisor = '".$idemisor."'";
            $infoEmisor = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // Obtener datos de la razón social del cliente. Si la factura no tiene razón social
            // relacionada (pedido sin cliente) se usan los datos fiscales en texto que quedaron
            // guardados en tfacturas al timbrarla
            if($sinrazonsocial){
                $razonsocial = array(
                    "rfc" => $facturas[0]["rfc"],
                    "razon_social" => $facturas[0]["razonsocial"],
                    "codigo_postal" => $facturas[0]["codigo_postal"],
                    "regimenfiscal" => $facturas[0]["regimenfiscal"]
                );
            }else{
                $query = "
                select
                    *
                from
                    tclienterazonessociales
                where
                    idrazonsocial = '".$idrazonsocial."'";
                $razonsocial = mysqli_fetch_assoc(mysqli_query($this->con, $query));
            }

            if(empty($razonsocial["rfc"]) || empty($razonsocial["razon_social"])){
                throw new Exception("La factura no tiene datos fiscales del receptor; no se puede timbrar el complemento");
            }

            // Calcular total del pago
            $total = array_sum(array_column($facturas, "monto"));

            // Actualizar registro en tpagos con emisor y razon social. Sin razón social
            // relacionada la columna se deja nula en vez de guardar un 0 que no existe
            $query = "
            update
                tpagos
            set
                idemisor = '".$idemisor."',
                idrazonsocial = ".(($sinrazonsocial) ? "NULL" : "'".$idrazonsocial."'")."
            where
                idpago = '".$idpago."'";

            if(!mysqli_query($this->con, $query)){
                throw new Exception("Error al actualizar el registro de pago");
            }

            // Obtener régimen fiscal del emisor
            $query = "
            select
                regimenfiscal
            from
                sat_tcatregimenfiscal
            where
                idregimenfiscal = '".$infoEmisor["idregimenfiscal"]."'";
            $regimen_fiscal = mysqli_fetch_assoc(mysqli_query($this->con, $query))["regimenfiscal"];

            $emisor = array(
                "Rfc" => $infoEmisor["rfc"],
                "Nombre" => utf8_decode(trim($infoEmisor["razon_social"])),
                "RegimenFiscal" => $regimen_fiscal,
                "LugarExpedicion" => $infoEmisor["codigo_postal"]
            );

            $receptor = array(
                "Rfc" => $razonsocial["rfc"],
                "Nombre" => utf8_decode(trim($razonsocial["razon_social"])),
                "UsoCFDI" => "CP01",
                "DomicilioFiscalReceptor" => $razonsocial["codigo_postal"],
                "RegimenFiscalReceptor" => $razonsocial["regimenfiscal"]
            );

            // Obtenemos los datos de la forma de pago interna
            $query = "
            select
                idformapago_sat
            from
                tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $idformapago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idformapago_sat"];

            // Obtener clave de forma de pago SAT
            $query = "
            select
                formapago
            from
                sat_tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $formapago = mysqli_fetch_assoc(mysqli_query($this->con, $query))["formapago"];

            $datospago = array(
                "fecha" => $fecha . " 12:00:00",
                "FormaPago" => $formapago,
                "moneda" => "MXN",
                "monto" => sprintf("%.2f", $total),
                "tipocambio" => 1
            );

            // Construir documentos relacionados
            $pagos = array();
            foreach($facturas as $fac){
                $pagos[] = array(
                    "Folio" => $fac["folio"],
                    "IdDocumento" => $fac["uuid"],
                    "ImpPagado" => sprintf("%.2f", $fac["monto"]),
                    "ImpSaldoAnt" => sprintf("%.2f", $fac["saldo"]),
                    "ImpSaldoInsoluto" => sprintf("%.2f", $fac["saldo"] - $fac["monto"]),
                    "MonedaDR" => "MXN",
                    "equivalencia" => 1,
                    "NumParcialidad" => $fac["parcialidad"],
                    "Serie" => $fac["serie"],
                    "ObjetoImpDR" => "02",
                    "base_iva_trasladado_" . $fac["impuesto"] => sprintf("%.6f", $fac["monto"] / (1 + ($fac["impuesto"] / 100)))
                );
            }

            // Obtener numero de certificado, certificado y archivo keypem
            $ruta = $_SERVER["DOCUMENT_ROOT"]."/emisores/" . str_replace("&", "_", $infoEmisor['rfc']);
            $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/"."certificado.cer");
            $certificado = $this->obtenerContenidoCertificado($ruta."/sat/"."certificado.cer");
            $archivo_keypem = file_get_contents($ruta."/sat/"."llave.key.pem");

            // Preparar datos para el timbrador
            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "Version" => "4.0",
                "new" => 1,
                "pruebas" => 0,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "colortxt" => "000000",
                "tipoComprobante" => "P",
                "serie" => $infoEmisor["serie_pagos"],
                "folio" => $infoEmisor["folio_pagos"],
                "emisor" => $emisor,
                "receptor" => $receptor,
                "pago" => $datospago,
                "pagos" => $pagos
            );

            // Enviar al timbrador
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.xptk.app/timbrador/index.php",
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
                    'Authorization: CH60NP5HQZYUPZEQ'
                ),
            ));

            $response = curl_exec($curl);

            if($response === false){
                throw new Exception("Error de conexión con el timbrador: " . curl_error($curl));
            }

            $response = json_decode($response, true);

            if($response === null){
                throw new Exception("Respuesta inválida del timbrador");
            }

            if($response["response"] != true){
                throw new Exception("El complemento no pudo ser timbrado (" . $response["mensaje"] . ")");
            }

            // Timbrado exitoso: actualizar tpagos, incrementar folio, descontar saldo de las
            // facturas y registrar la amortización, todo en una transacción
            mysqli_begin_transaction($this->con);

            $query = "
            update
                tpagos
            set
                serie = '".$infoEmisor["serie_pagos"]."',
                folio = '".$infoEmisor["folio_pagos"]."',
                uuid = '".$response["uuid"]."',
                timbrado = NOW()
            where
                idpago = '".$idpago."'";
            $ok = mysqli_query($this->con, $query);

            $query = "
            update
                temisores
            set
                folio_pagos = folio_pagos + 1
            where
                idemisor = '".$idemisor."'";
            $ok = $ok && mysqli_query($this->con, $query);

            foreach($facturas as $fac){
                $monto = sprintf("%.2f", $fac["monto"]);

                $query = "
                update
                    tfacturas
                set
                    saldo = saldo - ".$monto."
                where
                    idfactura = '".$fac["idfactura"]."'";
                $ok = $ok && mysqli_query($this->con, $query);

                $query = "
                insert
                into
                    tpagosfacturas
                (
                    idpago,
                    idfactura,
                    monto,
                    parcialidad
                ) values (
                    '".$idpago."',
                    '".$fac["idfactura"]."',
                    '".$monto."',
                    '".$fac["parcialidad"]."'
                )";
                $ok = $ok && mysqli_query($this->con, $query);
            }

            if(!$ok){
                mysqli_rollback($this->con);
                throw new Exception("El timbrado fue exitoso pero ocurrió un error al actualizar los datos. Contacta a soporte antes de volver a intentar, porque el CFDI ya existe ante el SAT (UUID ".$response["uuid"].")");
            }

            mysqli_commit($this->con);

            // Se guardan los documentos en la carpeta del emisor
            if(!is_dir($ruta."/pagos")){
                mkdir($ruta."/pagos", 0775, true);
            }

            file_put_contents($ruta."/pagos/".$response["uuid"].".xml",base64_decode($response["xml"]));
            file_put_contents($ruta."/pagos/".$response["uuid"].".pdf",base64_decode($response["pdf"]));

            $mensaje = "Se ha timbrado el complemento de pago correctamente";

            // El complemento solo puede relacionar la parte facturada del abono; si sobró
            // monto (pedido facturado parcialmente) hay que decirlo
            if(round($totalpago - $total, 2) > 0){
                $mensaje .= ". Se relacionaron $".number_format($total,2)." de los $".number_format($totalpago,2)." del pago, el resto se aplicó a documentos que no llevan complemento (facturas PUE o la parte del pedido que aún no está facturada)";
            }

            // Enviar complemento por correo
            $query = "
            select
                correo
            from
                tclientes
            where
                idcliente = '".$idcliente."'";
            $correoCliente = mysqli_fetch_assoc(mysqli_query($this->con, $query))["correo"];

            if(!empty($correoCliente)){
                include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Correos.php");
                $claseCorreos = new Correos();

                $folio = $infoEmisor["serie_pagos"]."-".$infoEmisor["folio_pagos"];
                $fecha = date("Y-m-d");

                $logo = $_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/".$idtienda."_logo.png";
                $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png")));

                include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/envioComplemento.php");
                include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/base.php");

                $envio = $claseCorreos->enviarCorreo(array(
                    "idtienda" => $idtienda,
                    "asunto" => "Envío de complemento de pago",
                    "mensaje" => $cuerpo,
                    "correos" => array(
                        $correoCliente
                    ),
                    "adjuntos" => array(
                        array(
                            "nombre" => $response["uuid"].".xml",
                            "archivo" => $response["xml"]
                        ),
                        array(
                            "nombre" => $response["uuid"].".pdf",
                            "archivo" => $response["pdf"]
                        )
                    )
                ));

                $mensaje .= ($envio["result"] == "success")
                    ? ". El complemento fue enviado por correo electrónico a ".$correoCliente
                    : ". Sin embargo, no se pudo enviar por correo: ".$envio["mensaje"];
            }else{
                $mensaje .= ". No se envió por correo porque el cliente no tiene correo electrónico registrado";
            }

            $respuesta = array(
                "respuesta" => "OK",
                "tipo" => "mensajecargar",
                "titulo" => "Complemento timbrado",
                "mensaje" => $mensaje,
                "formulario" => "formBusqueda"
            );

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }catch(Throwable $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * getNumCer
     * Obtener el numero de certificado de un archivo .cer
     * @param  string Path del archivo .cer
     * @return string Numero de certificado
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

    private function esUUIDValido($uuid){
        return preg_match(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',
            $uuid
        ) === 1;
    }

    private function generarPfx($keypem, $cerpem, $pfx, $pwd){
        // -legacy fuerza algoritmos RC2/3DES compatibles con librerías antiguas como Chilkat 9.5.x
        exec("openssl pkcs12 -export -legacy -inkey $keypem -in $cerpem -passout pass:'$pwd' -out $pfx");
        return file_exists($pfx) && filesize($pfx) > 0;
    }

}
?>