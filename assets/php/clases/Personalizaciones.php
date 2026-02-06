<?php
class Personalizaciones
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
     * Obtener Personalizaciones.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPersonalizaciones($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");

        try {
            $query = "
            select
                *
            from
                tcatpersonalizaciones
            where
                status = 1
                " . $where . "
            order by
                nombre";

            
            // $this->claseQueries->guardarQuery($query);

            $personalizaciones = mysqli_query($this->con, $query);

            if (mysqli_num_rows($personalizaciones) > 0) {
                $respuesta = array("respuesta" => "OK", "personalizaciones" => $personalizaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron personalizaciones registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Personalizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPersonalizacion($post)
    {
        $idpersonalizacion = mysqli_real_escape_string($this->con, $post["idpersonalizacion"]);

        try {
            $query = "
            select
                *
            from
                tcatpersonalizaciones
            where
                idpersonalizacion = '" . $idpersonalizacion . "'
            ";
            $personalizacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($personalizacion["idpersonalizacion"] > 0) {
                $respuesta = array("respuesta" => "OK", "personalizacion" => $personalizacion);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la personalizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Personalizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarPersonalizacion($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $precio = mysqli_real_escape_string($this->con, $post["txtPrecio"]);

        try {
            $query = "
            insert into
                tcatpersonalizaciones
                (
                    nombre,
                    precio
                )
            values
                (
                    '" . $nombre . "',
                    '" . $precio . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Personalización Agregrada", "mensaje" => "Se ha agregado la personalización correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar la personalización.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Personalizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarPersonalizacion($post)
    {
        $idpersonalizacion = mysqli_real_escape_string($this->con, $post["idpersonalizacion"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $precio = mysqli_real_escape_string($this->con, $post["txtPrecio"]);

        try {
            $query = "
            update
                tcatpersonalizaciones
            set
                nombre = '" . $nombre . "',
                precio = '" . $precio . "'
            where
                idpersonalizacion = '" . $idpersonalizacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Personalización Editada", "mensaje" => "Se ha editado la personalización correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar la personalización.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Personalizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarPersonalizacion($post)
    {
        $idpersonalizacion = mysqli_real_escape_string($this->con, $post["idpersonalizacion"]);

        try {
            $query = "
            update
                tcatpersonalizaciones
            set
                status = 0
            where
                idpersonalizacion = '" . $idpersonalizacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Personalización Eliminada", "mensaje" => "Se ha eliminado la personalización correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la personalización.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
