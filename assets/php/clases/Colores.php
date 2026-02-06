<?php
class Colores
{
    private $con;
    private $claseQueries;
    private $claseProductos;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Colores.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColores($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tcatcolores
            where
                status = 1
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    tcatcolores
                where
                    status = 1
                    " . $where . "
                order by
                    nombre
                ";
                $colores = mysqli_query($this->con, $query);
                $respuesta = array("respuesta" => "OK", "colores" => $colores);
            } else {

                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron colores registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Color.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColor($post)
    {
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);

        try {
            $query = "
            select
                *
            from
                tcatcolores
            where
                idcolor = '" . $idcolor . "'
            ";
            $color = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($color["idcolor"] > 0) {
                $respuesta = array("respuesta" => "OK", "color" => $color);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del color.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Color.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarColor($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idcolorpadre = mysqli_real_escape_string($this->con, $post["slcColorPadre"]);

        try {
            $query = "
            insert into
                tcatcolores
                (
                    nombre,
                    idcolorpadre
                )
            values
                (
                    '" . $nombre . "',
                    '" . $idcolorpadre . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 23;
                $post["comentarios"] = "Agrego el color " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Color Agregrado", "mensaje" => "Se ha agregado el color correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el color.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Color.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarColor($post)
    {
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idcolorpadre = mysqli_real_escape_string($this->con, $post["slcColorPadre"]);

        $nombrec = $this->obtenerColor($post)["color"]["nombre"];

        try {
            $query = "
            update
                tcatcolores
            set
                nombre = '" . $nombre . "',
                idcolorpadre = '" . $idcolorpadre . "'
            where
                idcolor = '" . $idcolor . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 24;
                $post["comentarios"] = "Edito el color " . (($nombrec != $nombre) ? $nombrec . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Color Editado", "mensaje" => "Se ha editado el color correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el color.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Color.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarColor($post)
    {
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);

        try {

            $respuesta = $this->tieneExistenciaAsignada($idcolor);

            if (!$respuesta["respuesta"]) {
                $query = "
                update
                    tproductoexistencias
                set
                    status = 0
                where
                    idcolor = '" . $idcolor . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con, $query)) {
                    $query = "
                    update
                        tcatcolores
                    set
                        status = 0
                    where
                        idcolor = '" . $idcolor . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    mysqli_query($this->con, $query);

                    $nombre = $this->obtenerColor($post)["color"]["nombre"];
                    $post["idactividad"] = 25;
                    $post["comentarios"] = "Elimino el color " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Color Eliminado", "mensaje" => "Se ha eliminado el color correctamente.", "formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el color.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Los siguientes productos tienen existencias con este color: " . $respuesta["lista_productos"] . ".");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * 
     * Verifica si el color tiene existencia asignada.
     * 
     * si la respuesta es positiva, regresa una lista con, a lo sumo, 5 nombres de productos (+ la cantidad restante, si es que hay) que tienen existencia con ese color
     * 
     * @access private
     * @param array $idcolor
     * @return array
     */
    private function tieneExistenciaAsignada($idcolor)
    {
        // $idcolor = mysqli_real_escape_string($this->con,$idcolor);

        try {
            // si el color tiene al menos un registro con existencia mayor a cero (tproductoexistencias)
            $query = "
            select
                *
            from
                tproductoexistencias
            where
                idcolor = " . $idcolor . "
                and existencia > 0
            group by
                idproducto
            ";
            $productos = mysqli_query($this->con, $query);

            if (mysqli_num_rows($productos) > 0) {
                // $respuesta = array("respuesta"=>"OK");
                $lista5Productos = "";
                $i = 1;
                while ($producto = mysqli_fetch_assoc($productos)) {
                    if ($i >= 6) {
                        break;
                    }
                    $_POST["idproducto"] = $producto["idproducto"];
                    $this->claseProductos = new Productos();
                    $nombre = $this->claseProductos->obtenerProducto($_POST)["producto"]["nombre"];
                    $lista5Productos .= $nombre . ", ";
                    $i++;
                }
                $lista5Productos = substr($lista5Productos, 0, -2);
                if ($i > 5) {
                    $lista5Productos .= " Y " . (mysqli_num_rows($productos) - 5) . " productos más";
                }

                $respuesta = array("respuesta" => true, "lista_productos" => $lista5Productos);
            } else {
                $respuesta = array("respuesta" => false);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
