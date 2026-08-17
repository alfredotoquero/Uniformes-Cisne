<?php
class NotasCredito{

    private $con;

    // El timbrador distingue entre CFDI reales (0) y de prueba (1). Se deja como propiedad
    // para poder probar el flujo completo contra el ambiente de pruebas sin tocar el resto
    // del código: se pone en 1, se timbra, y se regresa a 0.
    private $pruebas = 0;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Catálogo c_TipoRelacion del SAT, acotado a las claves que aplican para un CFDI de
     * egreso emitido contra una factura. Las demás claves del catálogo (02 nota de débito,
     * 04 sustitución, 05 y 06 traslados) corresponden a comprobantes de ingreso o de
     * traslado, así que no se ofrecen aquí.
     *
     * @access public
     * @return array
     */
    public function obtenerTiposRelacion(){
        return array(
            "01" => "Nota de crédito de los documentos relacionados",
            "03" => "Devolución de mercancía sobre facturas o traslados previos",
            "07" => "CFDI por aplicación de anticipo"
        );
    }

    /**
     * Notas de crédito timbradas contra una factura.
     *
     * @access public
     * @param array $post Contiene "idfactura"
     * @return array
     */
    public function getNotasCredito($post){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$post["idfactura"]);

            $query = "
            select
                a.idnotacredito,
                a.idfactura,
                a.tiporelacion,
                a.serie,
                a.folio,
                a.descripcion,
                a.cantidad,
                a.subtotal,
                a.iva,
                a.total,
                a.uuid,
                a.status,
                a.timbrado,
                a.registro,
                b.nombre as usuario
            from
                tnotascredito a
            left join
                tusuarios b
            on
                b.idusuario = a.idusuario
            where
                a.idfactura = '".$idfactura."'
            order by
                a.idnotacredito desc";

            $result = mysqli_query($this->con,$query);

            $respuesta = array(
                "respuesta" => "OK",
                "notascredito" => ($result) ? mysqli_fetch_all($result,MYSQLI_ASSOC) : array()
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

    /**
     * Genera y timbra una nota de crédito (CFDI de egreso) relacionada a una factura, le
     * descuenta el saldo a la factura y la envía por correo al cliente.
     *
     * @access public
     * @param array $post Datos del formulario de nota de crédito
     * @return array Respuesta en el formato del controlador (respuesta/tipo/mensaje)
     */
    public function crearNotaCredito($post){
        try{
            $idfactura = (int)$post["idfactura"];
            $idusuario = (int)$post["idusuario"];
            $tiporelacion = mysqli_real_escape_string($this->con,$post["slcTipoRelacion"]);
            $idmetodopago = (int)$post["slcMetodoPago"];
            $idformapago = (int)$post["slcFormaPago"];
            $descripcion = trim(isset($post["txtDescripcion"]) ? $post["txtDescripcion"] : "");
            $cantidad = (float)str_replace(",","",isset($post["txtCantidad"]) ? $post["txtCantidad"] : "0");
            $monto = (float)str_replace(",","",isset($post["txtMonto"]) ? $post["txtMonto"] : "0");

            if(!array_key_exists($tiporelacion,$this->obtenerTiposRelacion())){
                throw new Exception("El tipo de relación seleccionado no es válido para una nota de crédito");
            }

            if($descripcion==""){
                throw new Exception("Debes indicar la descripción de la nota de crédito");
            }

            // El anticipo siempre es un solo concepto: no tiene sentido multiplicarlo
            if($tiporelacion=="07"){
                $cantidad = 1;
            }

            if($cantidad <= 0){
                throw new Exception("La cantidad debe ser mayor a cero");
            }

            if($monto <= 0){
                throw new Exception("El importe de la nota de crédito debe ser mayor a cero");
            }

            $factura = $this->getDatosFactura($idfactura);

            if(!$factura){
                throw new Exception("No se encontró la factura");
            }

            if($factura["status"]!=1){
                throw new Exception("Solo se pueden generar notas de crédito de facturas activas");
            }

            if(empty($factura["uuid"])){
                throw new Exception("La factura no está timbrada, no se puede relacionar una nota de crédito");
            }

            $saldo = round((float)$factura["saldo"],2);

            if($saldo <= 0){
                throw new Exception("La factura no tiene saldo pendiente");
            }

            // Las facturas viejas sin razón social pueden no tener los datos fiscales en
            // texto; sin receptor no hay CFDI que timbrar
            if(empty($factura["rfc"]) || empty($factura["razonsocial"]) || empty($factura["regimenfiscal"]) || empty($factura["codigo_postal"])){
                throw new Exception("La factura no tiene completos los datos fiscales del receptor. Ejecuta scripts/repararDatosFiscalesFacturas.php antes de generar la nota de crédito");
            }

            if(empty($factura["serie_notascredito"])){
                throw new Exception("El emisor de la factura no tiene configurada la serie de notas de crédito");
            }

            $serie = $factura["serie_notascredito"];
            $folio = (int)$factura["folio_notascredito"];

            // La tasa se toma de la propia factura para que la nota de crédito la refleje
            // igual (hay facturas al 16% y al 8%); si la factura no trae IVA, la nota tampoco
            $tasaiva = (float)$factura["tasaiva"];

            $importe = round($cantidad * $monto,2);
            $iva = round($importe * ($tasaiva/100),2);
            $total = round($importe + $iva,2);

            if($total > ($saldo + 0.01)){
                throw new Exception("El total de la nota de crédito ($".number_format($total,2).") no puede ser mayor al saldo de la factura ($".number_format($saldo,2).")");
            }

            // Claves SAT del método y la forma de pago seleccionados
            $query = "
            select
                metodopago
            from
                sat_tcatmetodospago
            where
                idmetodopago = '".$idmetodopago."'";
            $metodopago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["metodopago"];

            $query = "
            select
                formapago
            from
                sat_tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $formapago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["formapago"];

            if(empty($metodopago) || empty($formapago)){
                throw new Exception("Debes indicar el método y la forma de pago de la nota de crédito");
            }

            // En PPD el pago todavía no se conoce, así que la única forma válida es
            // "99 Por definir"; en PUE pasa lo contrario, la forma siempre está definida
            if($metodopago=="PPD" && $formapago!="99"){
                throw new Exception("Con método de pago PPD la forma de pago debe ser 99 - Por definir");
            }

            if($metodopago=="PUE" && $formapago=="99"){
                throw new Exception("Con método de pago PUE la forma de pago no puede ser 99 - Por definir");
            }

            $rfc_emisor = str_replace("&","_",$factura["rfc_emisor"]);
            $ruta = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc_emisor;

            if(!file_exists($ruta."/sat/certificado.cer") || !file_exists($ruta."/sat/llave.key.pem")){
                throw new Exception("No se encontraron los archivos del certificado del emisor");
            }

            $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/certificado.cer");
            $certificado = $this->obtenerContenidoCertificado($ruta."/sat/certificado.cer");
            $archivo_keypem = file_get_contents($ruta."/sat/llave.key.pem");

            $emisor = array(
                "Rfc" => $factura["rfc_emisor"],
                "Nombre" => utf8_decode(trim($factura["razonsocial_emisor"])),
                "RegimenFiscal" => $factura["regimenfiscal_emisor"],
                "LugarExpedicion" => $factura["codigo_postal_emisor"]
            );

            // G02 (devoluciones, descuentos o bonificaciones) es el uso que corresponde al
            // receptor en un CFDI de egreso, sin importar con qué uso se emitió la factura
            $receptor = array(
                "Rfc" => $factura["rfc"],
                "Nombre" => utf8_decode(trim($factura["razonsocial"])),
                "UsoCFDI" => "G02",
                "DomicilioFiscalReceptor" => $factura["codigo_postal"],
                "RegimenFiscalReceptor" => $factura["regimenfiscal"]
            );

            $cadena_utf8 = mb_convert_encoding($descripcion,'UTF-8','auto');
            $descripcion_cfdi = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$cadena_utf8);

            $conceptos = array(
                array(
                    "Cantidad" => $cantidad,
                    "Descripcion" => $descripcion_cfdi,
                    "ValorUnitario" => sprintf("%.6f",$monto),
                    "Importe" => sprintf("%.6f",$importe),
                    "ClaveUnidad" => "ACT",
                    "ClaveProdServ" => "84111506",
                    "ObjetoImp" => ($tasaiva > 0) ? "02" : "01"
                )
            );

            $logo = $_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/".$factura["idtienda"]."_logo.png";
            $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png")));

            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "Version" => "4.0",
                "pruebas" => $this->pruebas,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "colortxt" => "000000",
                "logo" => $logo,
                "tipoComprobante" => "E",
                "serie" => $serie,
                "folio" => $folio,
                "emisor" => $emisor,
                "receptor" => $receptor,
                "conceptos" => $conceptos,
                "subtotal" => $importe,
                "iva_trasladado" => $tasaiva,
                "metodopago" => $metodopago,
                "formapago" => $formapago,
                "moneda" => "MXN",
                "tipo_cambio" => "1",
                "carta_porte" => false,
                "comentarios" => "",
                "relaciones" => array(
                    "TipoRelacion" => $tiporelacion,
                    "relacionados" => array($factura["uuid"])
                )
            );

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
            $errorcurl = curl_error($curl);
            curl_close($curl);

            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/crearNotaCredito.txt",print_r($datos,true)."\n\n".print_r($response,true));

            if($response===false){
                throw new Exception("Error de conexión con el timbrador: ".$errorcurl);
            }

            $response = json_decode($response,true);

            if($response===null){
                throw new Exception("Respuesta inválida del timbrador");
            }

            if($response["response"]!=true){
                throw new Exception("La nota de crédito no pudo ser timbrada (".$response["mensaje"].")");
            }

            // El total con el que se afecta el saldo es el que devuelve el timbrador, que es
            // el que quedó en el CFDI; el calculado solo sirvió para validar contra el saldo
            $subtotal_cfdi = round((float)$response["subtotal"],2);
            $total_cfdi = round((float)$response["total"],2);
            $iva_cfdi = round($total_cfdi - $subtotal_cfdi,2);

            // Por redondeos el total del CFDI puede quedar unos centavos arriba del saldo;
            // en ese caso la factura queda saldada, nunca en negativo
            $saldo_nuevo = max(0,round($saldo - $total_cfdi,2));

            mysqli_begin_transaction($this->con);

            $sinrazonsocial = !($factura["idrazonsocial"] > 0);

            $query = "
            insert
            into
                tnotascredito
            (
                idfactura,
                idusuario,
                ".(($factura["idcliente"] > 0) ? "idcliente," : "")."
                ".(($factura["idrazonsocial"] > 0) ? "idrazonsocial," : "")."
                ".(($sinrazonsocial) ? "razonsocial, rfc, codigo_postal, regimenfiscal, usocfdi," : "")."
                idemisor,
                idmetodopago,
                idformapago,
                tiporelacion,
                serie,
                folio,
                descripcion,
                cantidad,
                valor_unitario,
                subtotal,
                iva,
                total,
                uuid,
                timbrado
            ) values (
                '".$idfactura."',
                '".$idusuario."',
                ".(($factura["idcliente"] > 0) ? "'".$factura["idcliente"]."'," : "")."
                ".(($factura["idrazonsocial"] > 0) ? "'".$factura["idrazonsocial"]."'," : "")."
                ".(($sinrazonsocial) ? "'".mysqli_real_escape_string($this->con,$factura["razonsocial"])."', '".mysqli_real_escape_string($this->con,$factura["rfc"])."', '".mysqli_real_escape_string($this->con,$factura["codigo_postal"])."', '".mysqli_real_escape_string($this->con,$factura["regimenfiscal"])."', 'G02'," : "")."
                '".$factura["idemisor"]."',
                '".$idmetodopago."',
                '".$idformapago."',
                '".$tiporelacion."',
                '".mysqli_real_escape_string($this->con,$serie)."',
                '".$folio."',
                '".mysqli_real_escape_string($this->con,$descripcion)."',
                '".$cantidad."',
                '".$monto."',
                '".$subtotal_cfdi."',
                '".$iva_cfdi."',
                '".$total_cfdi."',
                '".$response["uuid"]."',
                '".$response["fechaTimbrado"]."'
            )";
            $ok = mysqli_query($this->con,$query);

            $query = "
            update
                temisores
            set
                folio_notascredito = folio_notascredito + 1
            where
                idemisor = '".$factura["idemisor"]."'";
            $ok = $ok && mysqli_query($this->con,$query);

            $query = "
            update
                tfacturas
            set
                saldo = '".$saldo_nuevo."'
            where
                idfactura = '".$idfactura."'";
            $ok = $ok && mysqli_query($this->con,$query);

            if(!$ok){
                mysqli_rollback($this->con);
                throw new Exception("El timbrado fue exitoso pero ocurrió un error al guardar los datos. Contacta a soporte antes de volver a intentar, porque el CFDI ya existe ante el SAT (UUID ".$response["uuid"].")");
            }

            mysqli_commit($this->con);

            // La carpeta del emisor se crea al vuelo igual que la de facturas y pagos: la
            // primera nota de crédito de cada emisor es la que la genera
            if(!is_dir($ruta."/notascredito")){
                mkdir($ruta."/notascredito", 0775, true);
            }

            file_put_contents($ruta."/notascredito/".$response["uuid"].".xml",base64_decode($response["xml"]));
            file_put_contents($ruta."/notascredito/".$response["uuid"].".pdf",base64_decode($response["pdf"]));

            $mensaje = "Se ha generado la nota de crédito ".$serie."-".$folio." por $".number_format($total_cfdi,2).". El saldo de la factura quedó en $".number_format($saldo_nuevo,2).".";

            $envio = $this->enviarNotaCredito(array(
                "idcliente" => $factura["idcliente"],
                "idtienda"  => $factura["idtienda"],
                "uuid"      => $response["uuid"],
                "xml"       => $response["xml"],
                "pdf"       => $response["pdf"],
                "folio"     => $serie."-".$folio,
                "fecha"     => $response["fechaTimbrado"],
                "total"     => $total_cfdi
            ));

            $mensaje .= " ".$envio["mensaje"];

            $respuesta = array(
                "respuesta" => "OK",
                "tipo" => "mensajecargar",
                "titulo" => "Nota de crédito generada",
                "mensaje" => $mensaje,
                "formulario" => "formBusqueda"
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

    /**
     * Datos de la factura necesarios para timbrar la nota de crédito: los del receptor
     * resueltos (razón social o datos en texto, igual que al facturar), los del emisor y la
     * tienda desde la que se emitió, para el logo y el correo.
     *
     * @access private
     * @param int $idfactura
     * @return array|null
     */
    private function getDatosFactura($idfactura){
        $idfactura = mysqli_real_escape_string($this->con,$idfactura);

        $query = "
        select
            a.idfactura,
            a.idcliente,
            a.idrazonsocial,
            a.idemisor,
            a.serie,
            a.folio,
            a.uuid,
            a.subtotal,
            a.iva,
            a.total,
            a.saldo,
            a.status,
            case when a.idrazonsocial > 0 then b.razon_social else a.razonsocial end as razonsocial,
            case when a.idrazonsocial > 0 then b.rfc else a.rfc end as rfc,
            case when a.idrazonsocial > 0 then b.codigo_postal else a.codigo_postal end as codigo_postal,
            case when a.idrazonsocial > 0 then b.regimenfiscal else a.regimenfiscal end as regimenfiscal,
            case when a.idrazonsocial > 0 then b.usocfdi else a.usocfdi end as usocfdi,
            round((a.iva / nullif(a.subtotal,0)) * 100,0) as tasaiva,
            c.rfc as rfc_emisor,
            c.razon_social as razonsocial_emisor,
            c.codigo_postal as codigo_postal_emisor,
            c.serie_notascredito,
            c.folio_notascredito,
            d.regimenfiscal as regimenfiscal_emisor,
            f.idtienda
        from
            tfacturas a
        left join
            tclienterazonessociales b
        on
            b.idrazonsocial = a.idrazonsocial
        left join
            temisores c
        on
            c.idemisor = a.idemisor
        left join
            sat_tcatregimenfiscal d
        on
            d.idregimenfiscal = c.idregimenfiscal
        left join
            (
                select idpedido, idfactura from tpedidosfacturas
                union
                select idpedido, idfactura from ttickets where idfactura > 0
            ) pf
        on
            pf.idfactura = a.idfactura
        left join
            tpedidos e
        on
            e.idpedido = pf.idpedido
        left join
            tsucursales f
        on
            f.idsucursal = e.idsucursal
        where
            a.idfactura = '".$idfactura."'
        limit 1";

        $result = mysqli_query($this->con,$query);

        return ($result && mysqli_num_rows($result)>0) ? mysqli_fetch_assoc($result) : null;
    }

    /**
     * Envía la nota de crédito timbrada al correo del cliente, con el XML y el PDF
     * adjuntos. Las facturas sin cliente (mostrador) no tienen a quién enviarse.
     *
     * @access private
     * @param array $post
     * @return array
     */
    private function enviarNotaCredito($post){
        try{
            if(!($post["idcliente"] > 0)){
                throw new Exception("La factura no tiene cliente, así que la nota de crédito no se envió por correo.");
            }

            $query = "
            select
                correo,
                correos_adicionales
            from
                tclientes
            where
                idcliente = '".mysqli_real_escape_string($this->con,$post["idcliente"])."'";
            $cliente = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            $correos = array_values(array_filter(array_map('trim',explode(',',$cliente["correo"].",".$cliente["correos_adicionales"]))));

            if(count($correos)==0){
                throw new Exception("El cliente no tiene correo registrado, así que la nota de crédito no se envió.");
            }

            $logo = $_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/".$post["idtienda"]."_logo.png";
            $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png")));

            $folio = $post["folio"];
            $fecha = $post["fecha"];
            $total = $post["total"];

            include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/envioNotaCredito.php");
            include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/base.php");

            include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Correos.php");
            $claseCorreos = new Correos();

            $resultado = $claseCorreos->enviarCorreo(array(
                "idtienda" => $post["idtienda"],
                "asunto" => "Envío de nota de crédito",
                "mensaje" => $cuerpo,
                "correos" => $correos,
                "adjuntos" => array(
                    array("nombre" => $post["uuid"].".xml", "archivo" => $post["xml"]),
                    array("nombre" => $post["uuid"].".pdf", "archivo" => $post["pdf"])
                )
            ));

            if($resultado["result"]!="success"){
                throw new Exception("La nota de crédito no se pudo enviar por correo (".$resultado["mensaje"].").");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "mensaje" => "Se envió por correo al cliente."
            );
        }catch(Exception $e){
            // El correo no invalida la nota de crédito: ya está timbrada y aplicada, así que
            // el problema solo se informa dentro del mensaje de éxito
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Ruta del PDF de una nota de crédito, para abrirla en el fancybox.
     *
     * @access public
     * @param int $idnotacredito
     * @return array
     */
    public function getPDF($idnotacredito){
        try{
            $nota = $this->getArchivos($idnotacredito);

            if(!$nota){
                throw new Exception("No se encontró la nota de crédito");
            }

            if(!file_exists($nota["pdf"])){
                throw new Exception("No se encontró el PDF de la nota de crédito");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "tipo" => "fancyArchivo",
                "pdf" => $nota["pdf"],
                "pdf_fancy" => $nota["pdf_fancy"]
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

    /**
     * Datos para armar el ZIP con el PDF y el XML de una nota de crédito.
     *
     * @access public
     * @param int $idnotacredito
     * @return array
     */
    public function getZIPData($idnotacredito){
        try{
            $nota = $this->getArchivos($idnotacredito);

            if(!$nota){
                throw new Exception("No se encontró la nota de crédito");
            }

            if(!file_exists($nota["pdf"])){
                throw new Exception("No se encontró el PDF de la nota de crédito");
            }

            if(!file_exists($nota["xml"])){
                throw new Exception("No se encontró el XML de la nota de crédito");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "uuid" => $nota["uuid"],
                "serie" => $nota["serie"],
                "folio" => $nota["folio"],
                "cliente" => $nota["cliente"],
                "pdf_path" => $nota["pdf"],
                "xml_path" => $nota["xml"]
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

    /**
     * Rutas de los archivos timbrados de una nota de crédito.
     *
     * @access private
     * @param int $idnotacredito
     * @return array|null
     */
    private function getArchivos($idnotacredito){
        $idnotacredito = mysqli_real_escape_string($this->con,$idnotacredito);

        $query = "
        select
            a.uuid,
            a.serie,
            a.folio,
            case when a.idcliente > 0 then c.nombre else a.razonsocial end as cliente,
            b.rfc as rfc_emisor
        from
            tnotascredito a
        left join
            temisores b
        on
            b.idemisor = a.idemisor
        left join
            tclientes c
        on
            c.idcliente = a.idcliente
        where
            a.idnotacredito = '".$idnotacredito."'";

        $nota = mysqli_fetch_assoc(mysqli_query($this->con,$query));

        if(!$nota || empty($nota["uuid"])){
            return null;
        }

        $base_fancy = "/emisores/".str_replace("&","_",$nota["rfc_emisor"])."/notascredito/".$nota["uuid"];

        $nota["pdf_fancy"] = $base_fancy.".pdf";
        $nota["pdf"] = $_SERVER["DOCUMENT_ROOT"].$base_fancy.".pdf";
        $nota["xml"] = $_SERVER["DOCUMENT_ROOT"].$base_fancy.".xml";

        return $nota;
    }

    /**
     * Obtener el numero de certificado de un archivo .cer
     *
     * @access private
     * @param string $certificado Path del certificado
     * @return string
     */
    private function obtenerNumeroCertificado($certificado){
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
     * Obtener el contenido del certificado
     *
     * @access private
     * @param string $certificado Path del certificado
     * @return string
     */
    private function obtenerContenidoCertificado($certificado){
        exec("openssl x509 -inform DER -in $certificado", $cer);
        array_pop($cer);                //elimino el ultimo elemento
        array_shift($cer);              //y el primero
        $contenido = implode($cer);     //despues convierto a string
        return $contenido;
    }

}
?>
