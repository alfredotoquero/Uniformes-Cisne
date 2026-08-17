<?php
class SAT{

    private $con;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Obtener Usos CFDI.
     * 
     * @access public
     * @return array
     */
    public function obtenerUsosCFDI(){
        try {
            $query = "
            select
                *
            from
                sat_tcatusoscfdi
            ";

            $usoscfdi = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "usoscfdi" => $usoscfdi);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Regimenes Fiscales.
     * 
     * @access public
     * @return array
     */
    public function obtenerRegimenesFiscales(){
        try {
            $query = "
            select
                *
            from
                sat_tcatregimenfiscal
            ";

            $regimenesfiscales = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "regimenesfiscales" => $regimenesfiscales);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Métodos de Pago.
     * 
     * @access public
     * @return array
     */
    public function obtenerMetodosPago(){
        try {
            $query = "
            select
                *
            from
                sat_tcatmetodospago
            ";

            $metodospago = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "metodospago" => $metodospago);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Formas de Pago.
     * 
     * @access public
     * @return array
     */
    public function obtenerFormasPago(){
        try {
            $query = "
            select
                *
            from
                sat_tcatformaspago
            ";

            $formaspago = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "formaspago" => $formaspago);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos / Servicios.
     * 
     * @access public
     * @return array
     */
    public function obtenerProductosServicios($post){
        try {
            $busqueda = mysqli_real_escape_string($this->con, $post["palabraClave"]);

            $query = "
            select
                *
            from
                sat_tcatproductosservicios
            where
                descripcion like '%$busqueda%' or
                clave like '%$busqueda%'
            order by
                clave
            ";

            $productosservicios = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "productosservicios" => $productosservicios);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener unidades de medida.
     * 
     * @access public
     * @return array
     */
    public function obtenerUnidadesMedida($post){
        try {
            $busqueda = mysqli_real_escape_string($this->con, $post["palabraClave"]);

            $query = "
            select
                *
            from
                sat_tcatunidadesmedida
            where
                nombre like '%$busqueda%' or
                clave like '%$busqueda%'
            order by
                clave";

            $unidadesmedida = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "unidadesmedida" => $unidadesmedida);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener motivos de cancelación
     * 
     * @access public
     * @return array
     */
    public function obtenerMotivosCancelacion(){
        try {
            $query = "
            select
                *
            from
                sat_tcatmotivoscancelacion
            order by
                clave";

            $motivoscancelacion = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "motivoscancelacion" => $motivoscancelacion);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Arma la ruta del XML de un comprobante contemplando que algunos se guardaron con el
     * RFC del emisor con "&" reemplazado por "_". Si ninguna de las dos variantes existe
     * devuelve la primera, para que el mensaje de error apunte a la ruta esperada.
     *
     * @access public
     * @param string $rutabase  Ruta absoluta donde vive la carpeta /emisores
     * @param string $rfc       RFC del emisor
     * @param string $carpeta   Subcarpeta del emisor: "facturas" o "pagos"
     * @param string $uuid      UUID del comprobante
     * @return string
     */
    public function rutaXMLComprobante($rutabase, $rfc, $carpeta, $uuid){
        $rutas = array(
            $rutabase."/emisores/".$rfc."/".$carpeta."/".$uuid.".xml",
            $rutabase."/emisores/".str_replace("&","_",$rfc)."/".$carpeta."/".$uuid.".xml"
        );

        foreach($rutas as $ruta){
            if(file_exists($ruta)){
                return $ruta;
            }
        }

        return $rutas[0];
    }

    /**
     * Consulta ante el SAT el estatus de un CFDI ya timbrado.
     *
     * Los tres datos que exige el servicio de consulta (sello, UUID y total) se leen del XML
     * y no de la base: el sello solo existe ahí, y el total tiene que ser el que quedó
     * timbrado, no el que hoy tenga el registro.
     *
     * @access public
     * @param string $rutaXml       Ruta absoluta del XML del comprobante
     * @param string $rfc_emisor    RFC del emisor
     * @param string $rfc_receptor  RFC del receptor
     * @return array    "respuesta" OK|ERROR y, cuando es OK, "resultado" con el desenlace:
     *                  "cancelado", "en_proceso", "activo" o "sin_cambio" (este último
     *                  cuando el SAT responde una combinación no contemplada).
     */
    public function consultarEstatusCFDI($rutaXml, $rfc_emisor, $rfc_receptor){
        try {
            $xml = (file_exists($rutaXml)) ? simplexml_load_file($rutaXml) : false;

            if($xml === false){
                throw new Exception("No se pudo leer el XML del comprobante (".$rutaXml.")");
            }

            $arrayXml = $this->XMLNode($xml, $xml->getNamespaces(true));

            $uuid = $arrayXml["Complemento"]["TimbreFiscalDigital"]["UUID"];
            $selloCFD = $arrayXml["Complemento"]["TimbreFiscalDigital"]["SelloCFD"];
            $total = $arrayXml["Total"];

            if(empty($uuid) || empty($selloCFD)){
                throw new Exception("El XML del comprobante no contiene timbre fiscal digital");
            }

            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "tipoComprobante" => "statusCfdi",
                "selloCFD" => $selloCFD,
                "uuid" => $uuid,
                "rfc_emisor" => $rfc_emisor,
                "rfc_receptor" => $rfc_receptor,
                "total" => $total
            );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.xptk.app/timbrador/index.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($datos),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                )
            ));

            $respuestacruda = curl_exec($curl);
            $errorcurl = curl_error($curl);
            curl_close($curl);

            if($respuestacruda === false){
                throw new Exception("No se pudo contactar al servicio de consulta del SAT: ".$errorcurl);
            }

            $response = json_decode($respuestacruda,true);

            if(!is_array($response)){
                throw new Exception("El servicio de consulta del SAT devolvió una respuesta no válida");
            }

            if($response["result"] != "success"){
                throw new Exception($response["text"] ?? $response["mensaje"] ?? "El SAT no pudo resolver la consulta del CFDI");
            }

            $estado = trim($response["Estado"] ?? "");
            $estatuscancelacion = trim($response["EstatusCancelacion"] ?? "");

            // Sin importar el tipo ni el motivo de la cancelación, para el sistema solo hay
            // tres desenlaces: el CFDI ya se canceló, la solicitud sigue esperando respuesta
            // del receptor, o la solicitud murió y el comprobante sigue vivo.
            if($estado == "Cancelado"){
                $resultado = "cancelado";
                $mensaje = "El SAT confirma que el CFDI está cancelado.";
            }else if($estatuscancelacion == "En proceso"){
                $resultado = "en_proceso";
                $mensaje = "La cancelación sigue en proceso: el receptor todavía no acepta ni rechaza la solicitud.";
            }else if($estatuscancelacion == "Solicitud rechazada"){
                $resultado = "activo";
                $mensaje = "El receptor rechazó la solicitud de cancelación; el CFDI sigue vigente.";
            }else if($estado == "Vigente" && $estatuscancelacion == ""){
                $resultado = "activo";
                $mensaje = "El CFDI está vigente ante el SAT y no tiene ninguna solicitud de cancelación en curso.";
            }else{
                // No se toca el registro: es preferible dejarlo en proceso y revisarlo a mano
                // que adivinar un estatus a partir de una respuesta que no conocemos.
                $resultado = "sin_cambio";
                $mensaje = "El SAT respondió una combinación no contemplada (Estado: '".$estado."', Estatus de cancelación: '".$estatuscancelacion."'). No se modificó el registro.";
            }

            $respuesta = array(
                "respuesta" => "OK",
                "resultado" => $resultado,
                "mensaje" => $mensaje,
                "uuid" => $uuid,
                "estado" => $estado,
                "estatus_cancelacion" => $estatuscancelacion,
                "es_cancelable" => $response["EsCancelable"] ?? "",
                "codigo_estatus" => $response["CodigoEstatus"] ?? ""
            );
        } catch (Exception $e) {
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        } finally {
            return $respuesta;
        }
    }

    /**
     * Convierte un nodo SimpleXML en un array asociativo recorriendo todos los namespaces
     * del CFDI, de modo que los nodos del complemento (tfd, pago20, etc.) queden accesibles
     * por nombre igual que los del comprobante.
     *
     * @access public
     * @param SimpleXMLElement $XMLNode
     * @param array $ns   Namespaces del documento
     * @return array|null
     */
    public function XMLNode($XMLNode, $ns){
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

}