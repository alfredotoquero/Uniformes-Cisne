<?php
class TarjetasRegalo
{
    private $con;
    private $claseQueries;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Tarjetas Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTarjetasRegalo($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $activas = mysqli_real_escape_string($this->con, $post["activas"]);

        $where = "";
        $where .= (($busqueda != "") ? " and codigo like '%" . $busqueda . "%'" : "");
        $where .= " and activa = '" . $activas . "'";

        try {

            $query = "
            select
                count(*) as total
            from
                ttarjetasregalo
            where
                status = '1'
                " . $where . "
            ";
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    ttarjetasregalo
                where
                    status = '1'
                    " . $where . "
                ";

                $tarjetasregalo = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tarjetasregalo" => $tarjetasregalo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron tarjetas de regalo registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tarjeta de Regalo.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTarjetaRegalo($post)
    {
        $codigo = mysqli_real_escape_string($this->con, $post["codigo"]);

        try {
            $query = "
            select
                *
            from
                ttarjetasregalo
            where
                codigo = '" . $codigo . "'
            ";
            $tarjetaregalo = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($tarjetaregalo["codigo"] != "") {
                $respuesta = array("respuesta" => "OK", "tarjetaregalo" => $tarjetaregalo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la tarjeta de regalo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Agregar Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTarjetaRegalo($post)
    {
        $codigo = mysqli_real_escape_string($this->con, $post["txtCodigo"]);
        $vigencia = date('Y-m-d', strtotime(date("Y-m-d") . " + 365 day"));

        try {
            $query = "
            insert into
                ttarjetasregalo
                (
                    codigo,
                    activa,
                    vigencia
                )
            values
                (
                    '" . $codigo . "',
                    '1',
                    '" . $vigencia . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 58;
                $post["comentarios"] = "Agrego la tarjeta de regalo " . $codigo;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tarjeta de Regalo Agregrada", "mensaje" => "Se ha agregado la tarjeta de regalo correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar la tarjeta de regalo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Activar Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function activarTarjetasRegalo($post)
    {
        $tarjetas = $post["chkTarjetas"];

        $correcto = true;

        try {
            foreach ($tarjetas as $codigo) {

                $codigo = mysqli_real_escape_string($this->con, $codigo);

                $query = "
                update
                    ttarjetasregalo
                set
                    activa = 1
                where
                    codigo = '" . $codigo . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {

                $post["idactividad"] = 84;
                $post["comentarios"] = "Activo tarjetas de regalo";
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tarjetas de Regalo Activadas", "mensaje" => "Se han activado las tarjetas de regalo correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron activar las tarjetas de regalo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Reactivar Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function reactivarTarjetasRegalo($post)
    {
        // $tarjetas = mysqli_real_escape_string($this->con,$post["chkTarjetas"]);
        $tarjetas = $post["chkTarjetas"];

        $correcto = true;

        try {
            foreach ($tarjetas as $codigo) {

                $codigo = mysqli_real_escape_string($this->con, $codigo);

                $query = "
                update
                    ttarjetasregalo
                set
                    utilizada = 0,
                    vigencia = DATE_ADD(vigencia, interval 1 year)
                where
                    codigo = '" . $codigo . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {

                $post["idactividad"] = 59;
                $post["comentarios"] = "Reactivo tarjetas de regalo";
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tarjetas de Regalo Reactivadas", "mensaje" => "Se han reactivado las tarjetas de regalo correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron reactivar las tarjetas de regalo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Reactivar Venta Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function reactivarVentaTarjetasRegalo($post)
    {
        // $tarjetas = mysqli_real_escape_string($this->con,$post["chkTarjetas"]);
        $tarjetas = $post["chkTarjetas"];

        $correcto = true;

        try {
            foreach ($tarjetas as $codigo) {

                $codigo = mysqli_real_escape_string($this->con, $codigo);

                $query = "
                update
                    ttarjetasregalo
                set
                    status = '0'
                where
                    codigo = '" . $codigo . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {

                $post["idactividad"] = 60;
                $post["comentarios"] = "Reactivo ventas de tarjetas de regalo";
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tarjetas de Regalo FALTA_TEXTO", "mensaje" => "Se FALTA_TEXTO correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No FALTA_TEXTO las tarjetas de regalo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Tarjetas de Regalo (método Excel). Agrega múltiples tarjetas de regalo a partir de un archivo Excel.
     * 
     * @access public
     * @param array $files
     * @return array
     */
    public function agregarTarjetasExcel($files) {

        mysqli_autocommit($this->con, false);

        $arrayCodigos = array();
        $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/txts/pruebafiles".date("m").".txt", 'a');

        fwrite($fp,print_r($files,true));
        // obtenemos la extensión del archivo
        $extension = explode(".", $files['flCodigos']['name'])[1];
        fwrite($fp, "Extension: " . $extension . "\n");

        // validamos qué método seguir según la extensión
        if ($extension == "csv") {
            if (($handle = fopen($files["flCodigos"]["tmp_name"], "r")) !== FALSE) {
                $linea = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($linea > 0) {
                        $arrayCodigos[] = $data[$posicion];
                    } else {
                        $aux = 0;
                        foreach ($data as $cabecera) {
                            // fwrite($fp,"cabecera: " . strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($cabecera))) . "\n");
                            if (strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($cabecera))) == "CODIGOS") {
                                $posicion = $aux;
                                $bandera = true;
                            }
                            $aux++;
                        }
                        if (!$bandera) {
                            $respuesta = array(
                                "respuesta" => "ERROR",
                                "mensaje" => "La cabecera CODIGOS no fue encontrada."
                            );
                            return $respuesta;
                        }
                    }
                    $linea++;
                }
                fclose($handle);
            }
        }else {
            include($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/vendor/simplexlsx/simplexlsx.class.php");
            $ruta = $_SERVER["DOCUMENT_ROOT"] . "/archivos/codigostrexcel/";    // Ruta donde se van a guardar el excel
            $x = $this->subirArchivo($files['flCodigos'], $ruta);
            fwrite($fp,print_r($x,true));
            $xlsx = new SimpleXLSX($x['ruta']);
            fwrite($fp,print_r($xlsx->rows(),true));
            $count = 0;
            //Se leen los datos del excel y se toman las columnas que se van a necesitar
            foreach ($xlsx->rows() as $fields) {
                if ($count > 0) {
                    $arrayCodigos[] = $fields[$posicion];
                } else {
                    $aux = 0;
                    foreach ($fields as $cabecera) {
                        fwrite($fp,"cabecera: " . strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $cabecera)) . "\n");
                        if (strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $cabecera)) == "CODIGOS") {
                            $posicion = $aux;
                            $bandera = true;
                        }
                        $aux++;
                    }
                    if (!$bandera) {
                        $respuesta = array(
                            "respuesta" => "ERROR",
                            "mensaje" => "La cabecera CODIGOS no fue encontrada."
                        );
                        return $respuesta;
                    }
                }
                $count++;
            }
            fwrite($fp, "codigos: \n" . print_r($arrayCodigos,true));
        }

        // agregamos cada uno de los codigos de tarjeta de regalo
        foreach ($arrayCodigos as $codigo) {
            // si la tarjeta de regalo ya ha sido insertada, se actualiza el campo utilizada y se incrementa la vigencia en 1 año

            $query = "
            insert into
                ttarjetasregalo
                (
                    codigo,
                    activa,
                    vigencia
                )
            values
                (
                    '" . $codigo . "',
                    '1',
                    DATE_ADD(CURDATE(), interval 1 year)
                )
            on duplicate key update
                utilizada = 0,
                activa = 1,
                vigencia = DATE_ADD(vigencia, interval 1 year)
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                mysqli_rollback($this->con);
                mysqli_autocommit($this->con, true);
                $respuesta = array(
                    "respuesta" => "ERROR",
                    "mensaje" => "Ocurrió un error al agregar las tarjetas de regalo"
                );
                return $respuesta;
            }
        }

        fclose($fp);

        // mysqli_rollback($this->con);
        mysqli_commit($this->con);
        mysqli_autocommit($this->con, true);

        $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tarjetas de Regalo Agregadas","mensaje"=>"Se han agregado las tarjetas de regalo correctamente.","formulario"=>"formBusqueda");

        return $respuesta;
    }

    /**
     * Subir Archivo. NOTA: moverla a una clase general
     * 
     * @access public
     * @param array $archivo
     * @param string $ruta
     * @return array
     */
    private function subirArchivo($archivo, $ruta){
        $res = explode(".", $archivo['name']);
        $extension = $res[count($res) - 1];
        $nombre = "c" . date("YmdHis") . "." . $extension; //renombrarlo como nosotros queremos

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $rutaExcel = $ruta . $nombre;

        if (is_uploaded_file($archivo['tmp_name'])) {
            copy($archivo['tmp_name'], $ruta . $nombre);
            $mensaje = "¡El archivo se subio Correctamente!";
            $texto = "";

            return array("respuesta" => "success", "mensaje" => $mensaje, "texto" => $texto, "ruta" => $rutaExcel);
        } else {
            $respuesta = "ERROR";
            $mensaje = "¡Ups!";
            $texto = "Hubo un error al subir el archivo...";
            unlink($rutaExcel);
            return $array = array("respuesta" => $respuesta, "mensaje" => $mensaje, "texto" => $texto);
        }

    }
}
