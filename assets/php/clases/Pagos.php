<?php
class Pagos{

    private $con;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
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
                exists (
                    select 1
                    from tformaspagopedido fp
                    join tpedidos p on p.idpedido = fp.idpedido
                    join tfacturas f on f.idfactura = p.idfactura
                    where fp.idpago = a.idpago
                      and p.idfactura is not null
                      and p.idfactura > 0
                      and f.idmetodopago = 1
                ) as tiene_factura
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
                c.razon_social,
                c.rfc as cliente_rfc,
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

            if(empty($pago["uuid"])){
                // El pago nunca se timbró: no existe CFDI que cancelar ante el SAT,
                // solo se revierte su efecto sobre el pedido/factura y se marca cancelado
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
                        "mensaje" => "El pago se ha cancelado correctamente.",
                        "formulario" => "formBusqueda"
                    );
                }else{
                    mysqli_rollback($this->con);
                    throw new Exception("No se pudo cancelar el pago.");
                }
            }else{
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
                        "total" => $pago["total"],
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
                        // 3 días para aceptar/rechazar ante el SAT, así que todavía NO se revierte el
                        // efecto del pago. Eso se hará hasta que un proceso posterior confirme la
                        // cancelación definitiva (pendiente: cronjob que reconsulte el estatus).
                        $esDefinitiva = ($response["statusCFDI"] != "201");

                        mysqli_begin_transaction($this->con);

                        $query = "
                        update
                            tpagos
                        set
                            status = '".($esDefinitiva ? "3" : "2")."'
                        where
                            idpago = '".$idpago."'";
                        $ok = mysqli_query($this->con,$query);

                        if($esDefinitiva){
                            $ok = $ok && $this->revertirEfectoPago($idpago);
                        }

                        if($ok){
                            mysqli_commit($this->con);
                            $respuesta = array(
                                "respuesta" => "OK",
                                "tipo" => "mensajecargar",
                                "titulo" => $esDefinitiva ? "Pago cancelado" : "Cancelación en proceso",
                                "mensaje" => $esDefinitiva
                                    ? "El pago se ha cancelado correctamente."
                                    : "La cancelación se envió al SAT y quedó pendiente de aceptación por el receptor. El efecto del pago se revertirá hasta que se confirme la cancelación definitiva.",
                                "formulario" => "formBusqueda"
                            );
                        }else{
                            mysqli_rollback($this->con);
                            throw new Exception("El CFDI se canceló ante el SAT, pero no se pudo actualizar el registro del pago. Contacta a soporte para corregir el registro manualmente.");
                        }
                    }else{
                        throw new Exception($response["text"]);
                    }
                }else{
                    throw new Exception("Ocurrió un error en la generación de los archivos para cancelación");
                }
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

    // Revierte el efecto de un pago sobre los pedidos que cubrió (abonado/statuspago) y,
    // si alguno estaba ligado a una factura, le regresa el saldo cobrado. Se usa tanto para
    // pagos timbrados (tras cancelar el CFDI) como para pagos que nunca llegaron a timbrarse.
    private function revertirEfectoPago($idpago){
        $query = "
        select
            a.idpedido,
            a.monto,
            b.total,
            b.abonado,
            b.idfactura
        from
            tformaspagopedido a
        join
            tpedidos b
        on
            b.idpedido = a.idpedido
        where
            a.idpago = '".$idpago."'";
        $aplicaciones = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $ok = true;
        foreach($aplicaciones as $aplicacion){
            $idpedido = (int)$aplicacion["idpedido"];
            $monto = floatval($aplicacion["monto"]);
            $nuevoabonado = floatval($aplicacion["abonado"]) - $monto;
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

            if($aplicacion["idfactura"] > 0){
                $idfacturapagada = (int)$aplicacion["idfactura"];
                $query = "
                update
                    tfacturas
                set
                    saldo = saldo + ".$monto."
                where
                    idfactura = '".$idfacturapagada."'";
                $ok = $ok && mysqli_query($this->con,$query);
            }
        }

        $query = "
        delete
        from
            tformaspagopedido
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        return $ok;
    }

    // Placeholder: en este proyecto no existe (ni en el histórico de Tienda-Uniformes-Cisne se pudo
    // confirmar) la lógica real para timbrar un complemento de pago ante el SAT. Falta definir el flujo
    // correcto (generación del CFDI de pago, llamada al PAC) antes de habilitar esta acción.
    public function timbrarPago($post){
        return array(
            "respuesta" => "ERROR",
            "mensaje" => "La función de timbrado de pagos aún no está implementada."
        );
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