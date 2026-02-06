<?php
class Catalogos
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
     * Obtener Catalogos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCatalogos($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        // $status = mysqli_real_escape_string($this->con,$post["slcStatus"]);

        $where = "";
        $where .= (($busqueda != "") ? " and (nombre like '%" . $busqueda . "%' or correo like '%" . $busqueda . "%' or telefono like '%" . $busqueda . "%')" : "");
        // $where .= (($status!=0) ? " and status = '".$status."'" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tcatalogos
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
                    tcatalogos
                where
                    status = '1'
                    " . $where . "
                ";

                // $this->claseQueries->guardarQuery($query);

                $catalogos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "catalogos" => $catalogos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron catalogos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Catalogo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCatalogo($post)
    {
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);

        try {
            $query = "
            select
                *
            from
                tcatalogos
            where
                idcatalogo = '" . $idcatalogo . "'
            ";
            $catalogo = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($catalogo["idcatalogo"]) {
                $respuesta = array("respuesta" => "OK", "catalogo" => $catalogo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del catalogo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Valores. Recupera los valores de un catalogo
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerValores($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);

        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");
        $where .= (($idcatalogo != "" && $idcatalogo != 0) ? " and idcatalogo = '" . $idcatalogo . "'" : "");

        try {
            $query = "
            select
                *
            from
                tvalorescatalogo
            where
                status = '1'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $valores = mysqli_query($this->con, $query);

            if (mysqli_num_rows($valores) > 0) {
                $respuesta = array("respuesta" => "OK", "valores" => $valores);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron valores registrados para este catalogo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Valor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerValor($post)
    {
        $idvalor = mysqli_real_escape_string($this->con, $post["idvalor"]);

        try {
            $query = "
            select
                *
            from
                tvalorescatalogo
            where
                idvalor = '" . $idvalor . "'
            ";
            $valor = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($valor["idvalor"] > 0) {
                $respuesta = array("respuesta" => "OK", "valor" => $valor);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del valor.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Catalogo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarCatalogo($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $multiple = mysqli_real_escape_string($this->con, $post["rdMultiple"]);

        try {
            $query = "
            insert into
                tcatalogos 
                (
                    nombre,
                    multiple
                )
            values 
                (
                    '" . $nombre . "',
                    '" . $multiple . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idcatalogo = mysqli_insert_id($this->con);

                $post["idactividad"] = 64;
                $post["comentarios"] = "Agrego el catalogo " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Catalogo Agregrado", "mensaje" => "Se ha agregado el catalogo correctamente.", "formulario" => "formBusqueda", "idcatalogo" => $idcatalogo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el catalogo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Catalogo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarCatalogo($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $multiple = mysqli_real_escape_string($this->con, $post["rdMultiple"]);

        $nombrec = $this->obtenerCatalogo($post)["catalogo"]["nombre"];

        try {
            $query = "
            update
                tcatalogos
            set
                nombre = '" . $nombre . "',
                multiple = '" . $multiple . "'
            where
                idcatalogo = '" . $idcatalogo . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $post["idactividad"] = 65;
                $post["comentarios"] = "Edito el catalogo " . (($nombrec != $nombre) ? $nombrec . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Catalogo Editado", "mensaje" => "Se ha editado el catalogo correctamente.", "formulario" => "formBusqueda", "idcatalogo" => $idcatalogo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el catalogo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Catalogo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarCatalogo($post)
    {
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);

        try {
            $query = "
            update
                tcatalogos
            set
                status = 0
            where
                idcatalogo = '" . $idcatalogo . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                // también se "eliminan" los valores asociados al catalogo
                $query = "
                update
                    tvalorescatalogo
                set
                    status = 0
                where
                    idcatalogo = '" . $idcatalogo . "'
                ";

                $this->claseQueries->guardarQuery($query);

                mysqli_query($this->con, $query);

                $nombre = $this->obtenerCatalogo($post)["catalogo"]["nombre"];
                $post["idactividad"] = 66;
                $post["comentarios"] = "Elimino el catalogo " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Catalogo Eliminado", "mensaje" => "Se ha eliminado el catalogo correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el catalogo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Valor. Agrega valor a un catalogo
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarValor($post)
    {
        // $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);

        try {
            $query = "
            insert into
                tvalorescatalogo
                (
                    nombre,
                    idcatalogo
                )
            values 
                (
                    '" . $nombre . "',
                    '" . $idcatalogo . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idcatalogo = mysqli_insert_id($this->con);

                $nombrecatalogo = $this->obtenerCatalogo($post)["catalogo"]["nombre"];
                $post["idactividad"] = 67;
                $post["comentarios"] = "Agrego el valor " . $nombre . " al catalogo " . $nombrecatalogo;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Valor Agregrado", "mensaje" => "Se ha agregado el valor correctamente.", "formulario" => "formBusqueda", "idcatalogo" => $idcatalogo);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el valor.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Valor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarValor($post)
    {
        // $idcatalogo = mysqli_real_escape_string($this->con,$post["idcatalogo"]);
        $idvalor = mysqli_real_escape_string($this->con, $post["idvalor"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);

        $nombrev = $this->obtenerValor($post)["valor"]["nombre"];

        try {
            $query = "
            update
                tvalorescatalogo
            set
                nombre = '" . $nombre . "'
            where
                idvalor = '" . $idvalor . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombrecatalogo = $this->obtenerCatalogo($post)["catalogo"]["nombre"];
                $post["idactividad"] = 68;
                $post["comentarios"] = "Edito el valor " . (($nombrev != $nombre) ? $nombrev . " -> " . $nombre : $nombre) . " del catalogo " . $nombrecatalogo;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Valor Editado", "mensaje" => "Se ha editado el valor correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el valor.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Valor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarValor($post)
    {
        $idvalor = mysqli_real_escape_string($this->con, $post["idvalor"]);

        try {
            $query = "
            update
                tvalorescatalogo
            set
                status = 0
            where
                idvalor = '" . $idvalor . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerValor($post)["valor"]["nombre"];
                $nombrecatalogo = $this->obtenerCatalogo($post)["catalogo"]["nombre"];
                $post["idactividad"] = 69;
                $post["comentarios"] = "Elimino el valor " . $nombre . " del catalogo " . $nombrecatalogo;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Valor Eliminado", "mensaje" => "Se ha eliminado el valor correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el valor.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Catalogo Formas Pago.
     * 
     * @access public
     * @return array
     */
    public function obtenerCatFormasPago()
    {

        try {
            $query = "
            select
                *
            from
                tcatformaspago
            ";

            $formaspago = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "formaspago" => $formaspago);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
