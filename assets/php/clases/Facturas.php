<?php
class Facturas{

    private $con;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }

    public function getFacturas($post){
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
                a.idfactura,
                a.idcliente,
                case when a.idcliente > 0 then b.nombre else a.razonsocial end as cliente,
                a.idrazonsocial,
                c.razon_social,
                a.idemisor,
                d.razon_social as razon_social_emisor,
                a.idmetodopago,
                e.metodopago,
                e.descripcion as descripcion_metodopago,
                a.idformapago,
                f.formapago,
                f.descripcion as descripcion_formapago,
                a.serie,
                a.folio,
                a.subtotal,
                a.iva,
                a.total,
                a.uuid,
                a.status,
                a.timbrado,
                a.registro,
                g.idpedido,
                h.folio as folio_ticket
            from
                tfacturas a
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
                sat_tcatmetodospago e
            on
                e.idmetodopago = a.idmetodopago
            left join
                sat_tcatformaspago f
            on
                f.idformapago = a.idformapago
            left join
                tpedidosfacturas g
            on
                g.idfactura = a.idfactura
            left join
                ttickets h
            on
                h.idfactura = a.idfactura";

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
                    "facturas" => mysqli_fetch_all($result,MYSQLI_ASSOC),
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

    public function getPDF($idfactura){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$idfactura);

            $query = "
            select
                a.uuid,
                b.rfc as rfc_emisor
            from
                tfacturas a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            where
                a.idfactura = '".$idfactura."'";
            $factura = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $uuid = $factura["uuid"];
            $rfc_emisor = $factura["rfc_emisor"];
            $ruta_fancy = "/emisores/".$rfc_emisor."/facturas/".$uuid.".pdf";
            $ruta = $_SERVER["DOCUMENT_ROOT"].$ruta_fancy;

            if(file_exists($ruta)){
                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo" => "fancyArchivo",
                    "pdf" => $ruta,
                    "pdf_fancy" => $ruta_fancy
                );
            }else{
                throw new Exception("No se encontró el PDF de la factura");
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

    public function getXML($idfactura){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$idfactura);

            $query = "
            select
                a.uuid,
                b.rfc as rfc_emisor
            from
                tfacturas a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            where
                a.idfactura = '".$idfactura."'";
            $factura = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $uuid = $factura["uuid"];
            $rfc_emisor = $factura["rfc_emisor"];
            $ruta_fancy = "/emisores/".$rfc_emisor."/facturas/".$uuid.".xml";
            $ruta = $_SERVER["DOCUMENT_ROOT"].$ruta_fancy;

            if(file_exists($ruta)){
                $respuesta = array(
                    "respuesta" => "OK",
                    "xml" => $ruta,
                    "xml_fancy" => $ruta_fancy
                );
            }else{
                throw new Exception("No se encontró el XML de la factura");
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

    public function getZIPData($idfactura){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$idfactura);

            $query = "
            select
                a.uuid,
                a.serie,
                a.folio,
                b.nombre as cliente,
                c.rfc as rfc_emisor
            from
                tfacturas a
            left join
                tclientes b
            on
                b.idcliente = a.idcliente
            left join
                temisores c
            on
                c.idemisor = a.idemisor
            where
                a.idfactura = '".$idfactura."'";

            $factura = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(!$factura){
                throw new Exception("No se encontró la factura");
            }

            $base = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$factura["rfc_emisor"]."/facturas/".$factura["uuid"];

            if(!file_exists($base.".pdf")){
                throw new Exception("No se encontró el PDF de la factura");
            }
            if(!file_exists($base.".xml")){
                throw new Exception("No se encontró el XML de la factura");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "uuid"      => $factura["uuid"],
                "serie"     => $factura["serie"],
                "folio"     => $factura["folio"],
                "cliente"   => $factura["cliente"],
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

    public function getFactura($post){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$post["idfactura"]);

            $query = "
            select
                a.idfactura,
                a.idusuario,
                b.nombre as usuario,
                a.idcliente,
                a.idrazonsocial,
                c.razon_social as cliente,
                c.rfc as cliente_rfc,
                c.idusocfdi,
                c.idregimenfiscal,
                c.codigo_postal,
                a.idemisor,
                d.razon_social as emisor,
                d.rfc as emisor_rfc,
                a.idmetodopago,
                e.descripcion as metodopago,
                a.idformapago,
                f.descripcion as formapago,
                a.serie,
                a.folio,
                a.subtotal,
                a.iva,
                a.total,
                a.uuid,
                a.status,
                a.timbrado,
                a.registro,
                g.idpedido
            from
                tfacturas a
            left join
                tusuarios b
            on
                b.idusuario = a.idusuario
            left join
                tclienterazonessociales c
            on
                c.idrazonsocial = a.idrazonsocial
            left join
                temisores d
            on
                d.idemisor = a.idemisor
            left join
                sat_tcatmetodospago e
            on
                e.idmetodopago = a.idmetodopago
            left join
                sat_tcatformaspago f
            on
                f.idformapago = a.idformapago
            left join
                tpedidosfacturas g
            on
                g.idfactura = a.idfactura
            where
                a.idfactura = '".$idfactura."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)>0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "factura" => mysqli_fetch_assoc($result)
                );
            }else{
                throw new Exception("No se pudo recuperar la información de la factura");
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

    public function cancelarFactura($post){
        try{
            $idfactura = mysqli_real_escape_string($this->con,$post["idfactura"]);
            $idmotivocancelacion = mysqli_real_escape_string($this->con,$post["slcMotivoCancelacion"]);
            $uuid = mysqli_real_escape_string($this->con,$post["txtUUID"]);

            // Obtenemos la información de la factura
            $factura = $this->getFactura(array(
                "idfactura" => $idfactura
            ));

            if($factura["respuesta"]=="OK"){
                $factura = $factura["factura"];

                // Se obtiene la información del método de cancelación para ver si requiere UUID
                $query = "
                select
                    *
                from
                    sat_tcatmotivoscancelacion
                where
                    idmotivo = '".$idmotivocancelacion."'";
                $motivo_cancelacion = mysqli_fetch_assoc(mysqli_query($this->con,$query));

                // En caso de que requiera UUID y el formato no sea válido entonces mostramos un error
                if($motivo_cancelacion["requiere_uuid"]==1 && !esUUIDValido($uuid)){
                    throw new Exception("El formato del UUID de sustitución no es válido");
                }

                // Se inicia el proceso de cancelación fiscal enviando los datos al timbrador
                // Se genera el archivo .cer.pem
                $cerpath = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$factura["emisor_rfc"]."/sat/certificado.cer";
                $cerpem = $cerpath.".pem";
                exec("openssl x509 -in $cerpath -inform DER -out $cerpem");

                // Indicamos la ruta del archivo .key.pem
                $keypath = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$factura["emisor_rfc"]."/sat/llave.key";
                $keypem = $keypath.".pem";

                $pfx = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$factura["emisor_rfc"]."/sat/pfx.pfx";
                $pwdPfx = uniqid();

                if($this->generarPfx($keypem,$cerpem,$pfx,$pwdPfx)){
                    $xml = simplexml_load_file($_SERVER["DOCUMENT_ROOT"]."/emisores/".$factura["emisor_rfc"]."/facturas/".$factura["uuid"].".xml");
                    $namespaces = $xml->getNamespaces(true);
                    $arrayXml = $this->XMLNode($xml, $namespaces);
                    $total = $arrayXml['Total'];

                    // Se manda a cancelar la factura al SAT
                    $datos = array(
                        "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                        "pruebas" => 0,
                        "tipoComprobante" => "C2",
                        "pfx" => base64_encode(file_get_contents($pfx)),
                        "pfx_pwd" => $pwdPfx,
                        "uuid" => $factura["uuid"],
                        "rfc_emisor" => $factura["emisor_rfc"],
                        "rfc_receptor" => $factura["cliente_rfc"],
                        "total" => $total,
                        "cve_motivo_cancelacion" => $motivo_cancelacion["clave"],
                        "uuid_sustitucion" => $uuid
                    );

                    file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/pruebaCancelacion.txt",print_r($datos,true));

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
                        $query = "
                        update
                            tfacturas
                        set
                            status = '".(($response["statusCFDI"]=="201") ? "2" : "3")."'
                        where
                            idfactura = '".$idfactura."'";
                        
                        if(mysqli_query($this->con,$query)){
                            $query = "
                            update
                                tpedidos
                            set
                                idfactura = NULL
                            where
                                idfactura = '".$idfactura."'";
                            mysqli_query($this->con,$query);

                            $query = "
                            delete
                            from
                                tpedidosfacturas
                            where
                                idfactura = '".$idfactura."'";
                            mysqli_query($this->con,$query);
                            
                            $respuesta = array(
                                "respuesta" => "OK",
                                "tipo" => "mensajecargar",
                                "titulo" => "Factura cancelada",
                                "mensaje" => "La factura se ha cancelado correctamente.",
                                "formulario" => "formBusqueda"
                            );
                        }else{
                            throw new Exception("No se pudo cancelar la factura.");
                        }
                    }else{
                        throw new Exception($response["text"]);
                    }
                }else{
                    throw new Exception("Ocurrió un error en la generación de los archivos para cancelación");
                }
            }else{
                throw new Exception($factura["mensaje"]);
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

    public function esUUIDValido($uuid) {
        return preg_match(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',
            $uuid
        ) === 1;
    }

    private function XMLNode($XMLNode, $ns){
        //
        $nodes = array();
        $response = array();
        $attributes = array();

        // first item ?
        $_isfirst = array();

        // each namespace
        //  - xmlns:cfdi="http://www.sat.gob.mx/cfd/3"
        //  - xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"
        foreach ($ns as $eachSpace) {
            //
            // each node
            foreach ($XMLNode->children($eachSpace) as $_tag => $_node) {
                if (!isset($_isfirst[$_tag]))
                    $_isfirst[$_tag] = true;
                //
                $_value = $this->XMLNode($_node, $ns);

                // exists $tag in $children?
                if (key_exists($_tag, $nodes) || !$_isfirst[$_tag]) {
                    if ($_isfirst[$_tag]) {
                        $tmp = $nodes[$_tag];
                        unset($nodes[$_tag]);
                        $nodes[] = $tmp;
                        $_isfirst[$_tag] = false;
                    }
                    $nodes[] = $_value;
                } else {
                    $nodes[$_tag] = $_value;
                }
            }
        }

        //
        $atts = get_mangled_object_vars($XMLNode->attributes());

        if (is_array($atts)) {
            if (isset($atts["@attributes"]) && is_array($atts["@attributes"]))
                $atts = $atts["@attributes"];

            $attributes = array_merge(
                $attributes,
                $atts
            );
        }

        // nodes ?
        if (is_countable($nodes)) {
            if (count($nodes)) {
                $response = array_merge(
                    $response,
                    $nodes
                );
            }
        }

        // attributes ?
        if (is_countable($attributes)) {
            if (count($attributes)) {
                $response = array_merge(
                    $response,
                    $attributes
                );
            }
        }

        return (empty($response) ? null : $response);
    }

    private function generarPfx($keypem, $cerpem, $pfx, $pwd){
        exec("openssl pkcs12 -export -inkey $keypem -in $cerpem -passout pass:'$pwd' > $pfx");
        if (file_exists($pfx)) {
            return true;
        }
        return false;
    }

    public function refacturarFactura($post){
        try{
            $idfacturaVieja = (int)$post["idfactura"];
            $idpedido       = (int)$post["idpedido"];

            // Paso 1: generar la nueva factura reutilizando el proceso existente
            include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");
            $clasePedidos   = new Pedidos();
            $resultFacturar = $clasePedidos->facturarPedido($post);

            if($resultFacturar["respuesta"] != "OK"){
                throw new Exception($resultFacturar["mensaje"]);
            }

            // Paso 2: recuperar el UUID de la nueva factura enlazada al pedido
            $query = "
            select f.uuid
            from tfacturas f
            inner join tpedidos p on p.idfactura = f.idfactura
            where p.idpedido = '".$idpedido."'";
            $nuevaFactura = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if(!$nuevaFactura || empty($nuevaFactura["uuid"])){
                throw new Exception("No se pudo recuperar la nueva factura generada.");
            }

            // Paso 3: obtener el idmotivo correspondiente a clave '01'
            $query = "select idmotivo from sat_tcatmotivoscancelacion where clave = '01'";
            $motivo = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if(!$motivo){
                throw new Exception("No se encontró el motivo de cancelación requerido.");
            }

            // Paso 4: cancelar la factura anterior con motivo 01 y el UUID de la nueva
            $resultCancelar = $this->cancelarFactura(array(
                "idfactura"            => $idfacturaVieja,
                "slcMotivoCancelacion" => $motivo["idmotivo"],
                "txtUUID"              => $nuevaFactura["uuid"]
            ));

            if($resultCancelar["respuesta"] != "OK"){
                throw new Exception("La nueva factura fue generada pero no se pudo cancelar la anterior: ".$resultCancelar["mensaje"]);
            }

            $respuesta = array(
                "respuesta" => "OK",
                "tipo"      => "mensajecargar",
                "titulo"    => "Refacturación exitosa",
                "mensaje"   => "Se ha generado la nueva factura y la anterior ha sido cancelada correctamente.",
                "formulario" => "formBusqueda"
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

    public function reenviarFactura($post){
        try{
            $idfactura = mysqli_real_escape_string($this->con, $post["idfactura"]);
            $correo    = $post["txtCorreo"];
            $correoAdicional = isset($post["txtCorreoAdicional"]) ? trim($post["txtCorreoAdicional"]) : "";

            $query = "
            select
                a.idfactura,
                a.idcliente,
                a.uuid,
                a.serie,
                a.folio,
                a.total,
                a.timbrado,
                b.rfc as rfc_emisor,
                d.idtienda
            from
                tfacturas a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            left join
                tpedidosfacturas pf
            on
                pf.idfactura = a.idfactura
            left join
                tpedidos c
            on
                c.idpedido = pf.idpedido
            left join
                tsucursales d
            on
                d.idsucursal = c.idsucursal
            where
                a.idfactura = '".$idfactura."'";

            $factura = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if (!$factura || empty($factura["idfactura"])) {
                throw new Exception("No se encontró la factura");
            }

            $uuid      = $factura["uuid"];
            $rfc_emisor = $factura["rfc_emisor"];
            $idcliente = $factura["idcliente"];
            $idtienda  = $factura["idtienda"];

            $base = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc_emisor."/facturas/".$uuid;

            if (!file_exists($base.".pdf")) {
                throw new Exception("No se encontró el PDF de la factura");
            }
            if (!file_exists($base.".xml")) {
                throw new Exception("No se encontró el XML de la factura");
            }

            $pdf_b64 = base64_encode(file_get_contents($base.".pdf"));
            $xml_b64 = base64_encode(file_get_contents($base.".xml"));

            // Actualizar correo del cliente (igual que al facturar)
            if ($idcliente > 0) {
                $correosArr = array_values(array_filter(array_map('trim', explode(',', $correo))));
                $correoMain = mysqli_real_escape_string($this->con, $correosArr[0] ?? '');
                $correosAdicionales = mysqli_real_escape_string($this->con, implode(',', array_slice($correosArr, 1)));
                $query = "update tclientes set correo = '".$correoMain."', correos_adicionales = '".$correosAdicionales."' where idcliente = '".$idcliente."'";
                mysqli_query($this->con, $query);
            }

            // Logo (igual que al facturar, con fallback al logo por defecto)
            $rutaLogo = $_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/".$idtienda."_logo.png";
            $logo = "data:image/png;base64,".((file_exists($rutaLogo))
                ? base64_encode(file_get_contents($rutaLogo))
                : base64_encode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png")));

            // Variables para la plantilla de correo
            $folio = $factura["serie"]."-".$factura["folio"];
            $fecha = $factura["timbrado"];
            $total = $factura["total"];

            include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/envioFactura.php");
            include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/base.php");

            include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Correos.php");
            $claseCorreos = new Correos();

            $correos = array_values(array_filter(array_map('trim', explode(',', $correo))));
            if (!empty($correoAdicional)) {
                $correosExtra = array_values(array_filter(array_map('trim', explode(',', $correoAdicional))));
                $correos = array_merge($correos, $correosExtra);
            }

            $resultado = $claseCorreos->enviarCorreo(array(
                "idtienda" => $idtienda,
                "asunto"   => "Envío de factura",
                "mensaje"  => $cuerpo,
                "correos"  => $correos,
                "adjuntos" => array(
                    array("nombre" => $uuid.".xml", "archivo" => $xml_b64),
                    array("nombre" => $uuid.".pdf", "archivo" => $pdf_b64)
                )
            ));

            if ($resultado["result"] == "success") {
                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo"      => "mensajecargar",
                    "titulo"    => "Factura reenviada",
                    "mensaje"   => "La factura se ha enviado correctamente.",
                    "formulario" => "formBusqueda"
                );
            } else {
                throw new Exception($resultado["mensaje"]);
            }
        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje"   => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

}
?>
