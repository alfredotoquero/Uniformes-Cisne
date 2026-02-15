<?php
class Emisores{
    private $con;
    private $claseQueries;
    private $claseLogs;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Emisores.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEmisores($post = array()){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        
        $where = "";
        $where .= (($busqueda!="") ? " and (razon_social like '%".$busqueda."%' or rfc like '%".$busqueda."%')" : "");

        try{

            $query = "
            select
                *
            from
                temisores
            where
                status = 1
                ".$where;

            $emisores = mysqli_query($this->con,$query);

            if(mysqli_num_rows($emisores)>0){
                $respuesta = array("respuesta"=>"OK","emisores"=>$emisores);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron emisores registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Emisor.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEmisor($post){
        $idemisor = mysqli_real_escape_string($this->con,$post["idemisor"]);

        try{
            $query = "
            select
                *
            from
                temisores
            where
                idemisor = '".$idemisor."'
            ";
            $emisor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($emisor["idemisor"]>0){
                $respuesta = array("respuesta"=>"OK","emisor"=>$emisor);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del emisor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /** 
     * Agregar Emisor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarEmisor($post,$files){
        $razon_social = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
        $codigo_postal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
        $idregimenfiscal = mysqli_real_escape_string($this->con,$post["slcRegimenFiscal"]);
        $serie = mysqli_real_escape_string($this->con,$post["txtSerie"]);
        $folio = mysqli_real_escape_string($this->con,$post["txtFolio"]);

        try{
            $query = "
            insert
            into
                temisores
            (
                razon_social,
                rfc,
                codigo_postal,
                idregimenfiscal,
                serie,
                folio
            ) values (
                '".$razon_social."',
                '".$rfc."',
                '".$codigo_postal."',
                '".$idregimenfiscal."',
                '".$serie."',
                '".$folio."'
            )";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $idemisor = mysqli_insert_id($this->con);

                // se crea la carpeta del emisor
                mkdir($_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc."/sat", 0775, true);
                mkdir($_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc."/facturas", 0775, true);
                mkdir($_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc."/pagos", 0775, true);
                mkdir($_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc."/notas_credito", 0775, true);
                mkdir($_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfc."/notas_debito", 0775, true);

                // se suben los archivos del certificado y la llave
                $post["idemisor"] = $idemisor;
                $respuesta = $this->subirCertificado($post,$files);
                if($respuesta["result"] == "error"){
                    throw new Exception($respuesta["mensaje"]);
                }

                $post["idactividad"] = 85;
                $post["comentarios"] = "Agrego al emisor " . $razon_social;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Emisor Agregado","mensaje"=>"Se ha agregado al emisor correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar al emisor");
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Editar Emisor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarEmisor($post,$files){
        $razon_social = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
        $codigo_postal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
        $idregimenfiscal = mysqli_real_escape_string($this->con,$post["slcRegimenFiscal"]);
        $serie = mysqli_real_escape_string($this->con,$post["txtSerie"]);
        $folio = mysqli_real_escape_string($this->con,$post["txtFolio"]);

        $idemisor = mysqli_real_escape_string($this->con,$post["idemisor"]);

        $nombreem = $this->obtenerEmisor($post)["emisor"]["razon_social"];

        try{
            $query = "
            update
                temisores
            set
                razon_social = '".$razon_social."',
                rfc = '".$rfc."',
                idregimenfiscal = '".$idregimenfiscal."',
                codigo_postal = '".$codigo_postal."',
                serie = '".$serie."',
                folio = '".$folio."'
            where
                idemisor = '".$idemisor."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {
                if(isset($files["flCertificado"]) && $files["flCertificado"]["tmp_name"]!="" && $files["flCertificado"]["tmp_name"]!=NULL && isset($files["flLlave"]) && $files["flLlave"]["tmp_name"]!="" && $files["flLlave"]["tmp_name"]!=NULL){
                    $respuesta = $this->subirCertificado($post,$files);
                    if($respuesta["result"] == "error"){
                        throw new Exception($respuesta["mensaje"]);
                    }
                }

                $post["idactividad"] = 87;
                $post["comentarios"] = "Edito al emisor " . (($nombreem!=$razon_social) ? $nombreem . " -> " . $razon_social : $razon_social);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Emisor Editado","mensaje"=>"El emisor se ha editado correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo actualizar la información del emisor");
            }
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Eliminar Sucursal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarEmisor($post){
        $idemisor = mysqli_real_escape_string($this->con,$post["idemisor"]);

        try{
            $query = "
            update
                temisores
            set
                status = 0
            where
                idemisor = '".$idemisor."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $nombre = $this->obtenerEmisor($post)["emisor"]["razon_social"];
                $post["idactividad"] = 86;
                $post["comentarios"] = "Elimino al emisor " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Emisor eliminado","mensaje"=>"El emisor se ha eliminado correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar al emisor");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    private function subirCertificado($post,$files){
        try{
            $keypwd = $post['txtPassword'];
            $ruta = $_SERVER["DOCUMENT_ROOT"]."/emisores/".$post["txtRFC"]."/sat/";

            if(file_exists($ruta."certificado.cer")){
                unlink($ruta."certificado.cer");
            }
			$array = $this->subirArchivo($files['flCertificado'], $ruta, 'certificado');
			if ($array['result'] == 'error') {
				throw new Exception($array["mensaje"]);
			}

            if(file_exists($ruta."llave.key")){
                unlink($ruta."llave.key");
            }
			$array = $this->subirArchivo($files['flLlave'], $ruta, 'llave');
            if ($array['result'] == 'error') {
				throw new Exception($array["mensaje"]);
			}

			if (file_exists($ruta . 'llave.key.pem')) {
				unlink($ruta . 'llave.key.pem');
			}
			if (file_exists($ruta . 'certificado.cer.pem')) {
				unlink($ruta . 'certificado.cer.pem');
			}

			if ($this->generarKeyPem($ruta . 'llave.key', $keypwd, $ruta . 'llave.key.pem')) {
				if (filesize($ruta . 'llave.key.pem') == 0) {
					// Si el archivo .key.pem no se generó correctamente se eliminan los archivos.
					unlink($ruta . 'llave.key.pem');
					unlink($ruta . 'llave.key');
					unlink($ruta . 'certificado.cer.pem');
					unlink($ruta . 'certificado.cer');
                    throw new Exception("La contraseña del certificado introducido es incorrecta.");
				} else {
					$query = "
					update 
						temisores
					set
						keypwd = AES_ENCRYPT('$keypwd','emisor')
					where
						idemisor='" . $post['idemisor'] . "'
					";
					if (!mysqli_query($this->con, $query)) {
						throw new Exception("Ocurrio un error al subir la contraseña de la llave");
					}else{
                        $respuesta = array(
                            "result" => "success",
                            "mensaje" => "Se ha subido el certificado correctamente"
                        );
                    }
				}
			} else {
				throw new Exception("La contraseña del certificado introducido es incorrecta.");
			}
        }catch(Exception $e){
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
    * Sube un archivo al servidor con el nombre específicado en la ruta especificada.
    *
    * @param file $archivo      Archivo que se subirá al servidor.
    * @param string $ruta       Ruta en donde se subirá el archivo.
    * @param string $nombre     Nombre que se utilizará para renombrar el archivo en el servidor.
    * @return array             Arreglo con el resultado de la operación.
    */
    private function subirArchivo($archivo, $ruta, $nombre){

        $res = explode(".", $archivo['name']);
        $extension = $res[count($res) - 1];
        $nombre .= "." . $extension; //renombrarlo como nosotros queremos

        if (is_uploaded_file($archivo['tmp_name'])) {
            copy($archivo['tmp_name'], $ruta . $nombre);
            return array("result" => "success", "mensaje" => "¡El archivo se subio Correctamente!");
        } else {
            return array("result" => "error", "mensaje" => "Hubo un error al subir el archivo...");
        }
    }

    /**
     * @param $key      Path del archivo KEY
     * @param $pwd      Clave de la KEY
     * @param $pem      Ruta del nuevo archivo
     * @return bool     Resultado de la creacion/consulta de existencia
     */
    public function generarKeyPem($key, $pwd, $pem){
        //el archivo ya existe??
        if (!file_exists($pem)) {
            //crearlo :v
            //local
            //exec(".\openssl\openssl pkcs8 -inform DER -in $key -passin pass:$pwd > $pem 2>&1",$out);
            //VPS
            $pwd = str_replace('$', '\$', $pwd);
            exec("openssl pkcs8 -inform DER -in $key -passin pass:'$pwd' > $pem");   //Genera solo PRIVATE KEY
            exec("openssl rsa -in $pem -out $pem");   //Pasarla a RSA PRIVATE KEY
            if (file_exists($pem)) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * certopem
     * @param  string $cerpath  Ruta del archvio .cer
     * @param  string $pathfile Ruta del archivo a generar
     * @return bool           True|False en caso de haber generado el archivo
     */
    //exec("openssl\openssl x509 -inform DER -outform PEM -in $pathcer -pubkey -out $pathcerpem");            //Local
    public function generarCerPem($cerpath, $pathfile){
        //existe el archivo?
        if (!file_exists($pathfile)) {
            //echo "No existe";
            //NO existe, crearlo
            //exec(".\openssl\openssl x509 -in $cerpath -inform DER -out $pathfile",$out);
            exec("openssl x509 -in $cerpath -inform DER -out $pathfile");
            if (file_exists($pathfile)) {
                return true;
            }
            return false;
        }
        return true;
    }
}
?>