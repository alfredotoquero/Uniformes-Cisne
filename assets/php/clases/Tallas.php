<?php
class Tallas
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
     * Obtener Tallas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallas($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $portipotalla = mysqli_real_escape_string($this->con, $post["portipotalla"]);
        $orden = mysqli_real_escape_string($this->con, $post["orden"]);


        $orderby = "";
        $orderby .= (($orden == '1') ? " order by nombre" : (($orden == '2') ? " order by posicion" : ""));
        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");

        try {

            if ($portipotalla > 0) {
                $query = "
                select 
                    *
                from
                    (
                        select
                            *,
                            1 as tipo
                        from
                            tcattallas
                        where
                            status = 1
                            and nombre not regexp '[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-]'
                            " ./*$where.*/ "
                        union
                        select
                            *,
                            2 as tipo
                        from
                            tcattallas
                        where
                            status = 1
                            and nombre regexp '[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ]' and nombre not regexp '[-]'
                            " ./*$where.*/ "
                        union
                        select
                            *,
                            3 as tipo
                        from
                            tcattallas
                        where
                            status = 1
                            and nombre regexp '[-]'
                            " ./*$where.*/ "
                    ) a
                order by tipo, posicion
                ";
            } else {
                $query = "
                select
                    *
                from
                    tcattallas
                where
                    status = 1
                    " . $where . "
                    " . $orderby . "
                ";
            }


            // $this->claseQueries->guardarQuery($query);

            $tallas = mysqli_query($this->con, $query);

            if (mysqli_num_rows($tallas) > 0) {
                $respuesta = array("respuesta" => "OK", "tallas" => $tallas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron tallas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tallas Categoria. Recupera las tallas de una categoria especifica
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallasCategoria($post)
    {
        $idcategoria = mysqli_real_escape_string($this->con, $post["idcategoria"]);

        try {

            $query = "
            select
                count(*) as total
            from
                tcattallas
            where
                idtalla in (select idtalla from trcategoriaproductotallas where idcategoriaproducto = '" . $idcategoria . "' and status = 1)
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    tcattallas
                where
                    idtalla in (select idtalla from trcategoriaproductotallas where idcategoriaproducto = '" . $idcategoria . "' and status = 1)
                order by
                    posicion
                ";

                $tallas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tallas" => $tallas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron tallas para esta categoria.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Talla.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTalla($post)
    {
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);

        try {
            $query = "
            select
                *
            from
                tcattallas
            where
                idtalla = '" . $idtalla . "'
            ";
            $talla = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($talla["idtalla"] > 0) {
                $respuesta = array("respuesta" => "OK", "talla" => $talla);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la talla.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Talla.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTalla($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);

        try {
            $query = "
            insert into
                tcattallas
                (
                    nombre
                )
            values
                (
                    '" . $nombre . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 26;
                $post["comentarios"] = "Agrego la talla " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Talla Agregrada", "mensaje" => "Se ha agregado la talla correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar la talla.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Talla.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarTalla($post)
    {
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);

        $nombret = $this->obtenerTalla($post)["talla"]["nombre"];

        try {
            $query = "
            update
                tcattallas
            set
                nombre = '" . $nombre . "'
            where
                idtalla = '" . $idtalla . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 27;
                $post["comentarios"] = "Edito la talla " . (($nombret != $nombre) ? $nombret . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Talla Editada", "mensaje" => "Se ha editado la talla correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar la talla.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Talla.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarTalla($post)
    {
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);

        try {

            $respuesta = $this->tieneExistenciaAsignada($idtalla);

            if (!$respuesta["respuesta"]) {
                $query = "
                update
                    tproductoexistencias
                set
                    status = 0
                where
                    idtalla = '" . $idtalla . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con, $query)) {
                    $query = "
                    update
                        tcattallas
                    set
                        status = 0
                    where
                        idtalla = '" . $idtalla . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    mysqli_query($this->con, $query);

                    $nombre = $this->obtenerTalla($post)["talla"]["nombre"];
                    $post["idactividad"] = 28;
                    $post["comentarios"] = "Elimino la talla " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Talla Eliminada", "mensaje" => "Se ha eliminado la talla correctamente.", "formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la talla.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Los siguientes productos tienen existencias con esta talla: " . $respuesta["lista_productos"] . ".");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * 
     * Verifica si la talla tiene existencia asignada.
     * 
     * si la respuesta es positiva, regresa una lista con, a lo sumo, 5 nombres de productos (+ la cantidad restante, si es que hay) que tienen existencia con esa talla
     * 
     * @access private
     * @param array $idtalla
     * @return array
     */
    private function tieneExistenciaAsignada($idtalla)
    {

        try {
            // si la talla tiene al menos un registro con existencia mayor a cero (tproductoexistencias)
            $query = "
            select
                *
            from
                tproductoexistencias
            where
                idtalla = " . $idtalla . "
                and existencia > 0
            group by
                idproducto
            ";
            $productos = mysqli_query($this->con, $query);

            if (mysqli_num_rows($productos) > 0) {
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

    /**
     * Actualiza los valores de posicion de las tallas que tienen un valor de posicion en el intervalo dado ()
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarPosicionesTallas($post)
    {
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $posicionantigua = mysqli_real_escape_string($this->con, $post["posicionantigua"]);
        $posicionnueva = mysqli_real_escape_string($this->con, $post["posicionnueva"]);

        try {

            if ($posicionantigua > $posicionnueva) {
                for ($i = $posicionantigua; $i < $posicionnueva; $i++) {
                    $query = "
                    update
                        tcattallas
                    set
                        posicion = $i
                    where
                        posicion = " . ($i + 1) . "
                    ";

                    $this->claseQueries->guardarQuery($query);

                    mysqli_query($this->con, $query);
                }
            } else {
                for ($i = $posicionantigua; $i > $posicionnueva; $i--) {
                    $query = "
                    update
                        tcattallas
                    set
                        posicion = $i
                    where
                        posicion = " . ($i - 1) . "
                    ";

                    $this->claseQueries->guardarQuery($query);

                    mysqli_query($this->con, $query);
                }
            }

            $query = "
            update
                tcattallas
            set
                posicion = '" . ($posicionnueva) . "'
            where
                idtalla = '" . $idtalla . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                // qué tipo de mensaje debe ser aqui? hay muchos: reload, href, mensaje, mensajehref, mensajereload, mensajecargar, mensajeventana 
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo actualizar la posicion de la talla.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
