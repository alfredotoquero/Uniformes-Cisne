<?php
class Tiendas
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
     * Obtener Tiendas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTiendas($post = array())
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        $where = (($busqueda != "") ? " and nombre like '%" . $busqueda . "%' " : "");

        try {
            $query = "
            select
                *
            from
                ttiendas
            where
                status = 1
            " . $where . "
            order by
                nombre";
            // $this->claseQueries->guardarQuery($query);

            $tiendas = mysqli_query($this->con, $query);

            if (mysqli_num_rows($tiendas) > 0) {
                $respuesta = array("respuesta" => "OK", "tiendas" => $tiendas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron tiendas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tienda.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTienda($post)
    {
        $idtienda = mysqli_real_escape_string($this->con, $post["idtienda"]);

        try {
            $query = "
            select
                *
            from
                ttiendas
            where
                idtienda = '" . $idtienda . "'
            ";
            $tienda = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($tienda["idtienda"] > 0) {
                $respuesta = array("respuesta" => "OK", "tienda" => $tienda);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la tienda.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Tienda.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTienda($post,$files)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $correo = mysqli_real_escape_string($this->con,$post["txtSMTP_Correo"]);
        $password = mysqli_real_escape_string($this->con,$post["txtSMTP_Password"]);
        $nombre_smtp = mysqli_real_escape_string($this->con,$post["txtSMTP_Nombre"]);

        try {
            $query = "
            insert into
                ttiendas
                (
                    nombre,
                    smtp_correo,
                    smtp_password,
                    smtp_nombre
                )
            values
                (
                    '" . $nombre . "',
                    '" . $correo . "',
                    aes_encrypt('" . $password . "','smtp'),
                    '" . $nombre_smtp . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                // subimos el archivo del logo en caso de que se haya agregado
                if(isset($files["imgLogo"]) && $files["imgLogo"]["tmp_name"]!="" && $files["imgLogo"]["tmp_name"]!=NULL){
                    $idtienda = mysqli_insert_id($this->con);
                    $this->subirArchivo($files["imgLogo"],$_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/",$idtienda."_logo");
                }

                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tienda Agregrada", "mensaje" => "Se ha agregado la tienda correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar la tienda.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Tienda.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarTienda($post,$files)
    {
        $idtienda = mysqli_real_escape_string($this->con, $post["idtienda"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $correo = mysqli_real_escape_string($this->con,$post["txtSMTP_Correo"]);
        $password = mysqli_real_escape_string($this->con,$post["txtSMTP_Password"]);
        $nombre_smtp = mysqli_real_escape_string($this->con,$post["txtSMTP_Nombre"]);

        try {
            $query = "
            update
                ttiendas
            set
                nombre = '" . $nombre . "',
                smtp_correo = '".$correo."',
                smtp_nombre = '".$nombre_smtp."'
                ".((!empty($password)) ? ",smtp_password = aes_encrypt('".$password."','smtp')" : "")."
            where
                idtienda = '" . $idtienda . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                // subimos el archivo del logo en caso de que se haya agregado
                if(isset($files["imgLogo"]) && $files["imgLogo"]["tmp_name"]!="" && $files["imgLogo"]["tmp_name"]!=NULL){
                    $this->subirArchivo($files["imgLogo"],$_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/",$idtienda."_logo");
                }

                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tienda Editada", "mensaje" => "Se ha editado la tienda correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar la tienda.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Tienda.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarTienda($post)
    {
        $idtienda = mysqli_real_escape_string($this->con, $post["idtienda"]);

        try {
            $query = "
            update
                ttiendas
            set
                status = 0
            where
                idtienda = '" . $idtienda . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Tienda Eliminada", "mensaje" => "Se ha eliminado la tienda correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la tienda.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
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
}
