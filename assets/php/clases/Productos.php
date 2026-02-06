<?php
class Productos
{
    private $con;
    private $claseQueries;
    private $claseCatalogos;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Catalogos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Productos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProductos($post)
    {


        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        // $idalmacen = mysqli_real_escape_string($this->con,$post["slcAlmacen"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["slcTalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["slcColor"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);
        $conexistencias = mysqli_real_escape_string($this->con, $post["slcExistencias"]);
        $preciomin = mysqli_real_escape_string($this->con, $post["txtPrecioMin"]);
        $preciomax = mysqli_real_escape_string($this->con, $post["txtPrecioMax"]);
        // $status = mysqli_real_escape_string($this->con,$post["slcStatus"]);
        $ordenar = mysqli_real_escape_string($this->con, $post["ordenar"]);

        $idproductos = $post["idproductos"];

        $almacenes = $post["slcAlmacenes"];


        $idalmacenes = array();
        if (isset($post["slcAlmacenes"])) {
            foreach ($almacenes as $idalmacen2) {
                $idalmacen2 = mysqli_real_escape_string($this->con, $idalmacen2);
                array_push($idalmacenes, $idalmacen2);
            }
        }




        $pagina = (isset($post["pagina"])) ? mysqli_real_escape_string($this->con, $post["pagina"]) : 1;
        $regpagina = 100;

        $orderby = "";
        $orderby .= (($ordenar == "") ? " order by nombre" : "");

        $where = "";
        $where2 = "";
        $whereexistencias = "";
        $whereids = "";
        if (isset($post["idproductos"])) {
            $whereids .= " and (";
            $idproductos = explode(",", $idproductos);
            foreach ($idproductos as $idproducto) {
                $idproducto = mysqli_real_escape_string($this->con, $idproducto);
                $whereids .= "idproducto = '" . $idproducto . "' or ";
            }
            $whereids = substr($whereids, 0, strlen($whereids) - 4);
            $whereids .= ")";
        }
        $where .= (($busqueda != "") ? " and (nombre like '%" . $busqueda . "%' or codigobarras like '%" . $busqueda . "%')" : "");
        $where .= (($idproveedor != 0 && $idproveedor != "") ? " and idproveedor = '" . $idproveedor . "'" : "");
        $where .= (($preciomin != "") ? " and precio >= '" . $preciomin . "'" : "");
        $where .= (($preciomax != "") ? " and precio <= '" . $preciomax . "'" : "");
        // $where .= (($status!=0) ? " and status = '".$status."'" : "");
        // $where2 .= (($idalmacen!=0) ? " and idalmacen = '".$idalmacen."' " : "");
        // $where2 .= ((!empty($idalmacenes)) ? " and idalmacen in (".(implode(",",$idalmacenes)).")" : "");
        $where .= ((count($idalmacenes) > 0) ? " and idproducto in (select idproducto from trproductoalmacenes where idalmacen in (" . (implode(",", $idalmacenes)) . ")" . $whereids . ")" : "");
        $where2 .= ((count($idalmacenes) > 0) ? " and idproducto in (select idproducto from trproductoalmacenes where idalmacen in (" . (implode(",", $idalmacenes)) . ")" . $whereids . ")" : "");
        $where2 .= (($idtalla != "" && $idtalla != 0) ? " and idtalla = '" . $idtalla . "' " : "");
        $where2 .= (($idcolor != "" && $idcolor != 0) ? " and idcolor = '" . $idcolor . "' " : "");
        $whereexistencias .= (($conexistencias == 2) ? " and idproducto not in
        (
            select
                idproducto
            from
                tproductoexistencias
            where
                status = 1
                and existencia > 0
        )" : (($conexistencias != "" && $conexistencias == 1) ? "and idproducto in
        (
            select
                idproducto
            from
                tproductoexistencias
            where
                status = 1
                and existencia > 0
                " . $where2 . "
        )" : ""));
        // NOTA: ¿con o sin existencias se refiere a que no maneja existencias o a que tiene existencia = 0? (897,639,640,901,642,889,900,884)


        try {
            $query = "
            select
                count(*) as total
            from
                tproductos
            where
                status = 1
                " . $where . "
                " . $whereexistencias . "
                " . $whereids . "
            ";

            $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total > 0) {
                if (isset($post["pagina"])) {
                    $query = "
                    select
                        *
                    from
                        tproductos
                    where
                        status = 1
                        " . $where . "
                        " . $whereexistencias . "
                        " . $whereids . "
                    " . $orderby . "
                    limit
                        " . (($pagina - 1) * $regpagina) . "," . $regpagina . "
                    ";
                    $numpaginas = ceil($total / $regpagina);
                    $maxpaginas = 3;
                } else {
                    $query = "
                    select
                        *
                    from
                        tproductos
                    where
                        status = 1
                        " . $where . "
                        " . $whereexistencias . "
                        " . $whereids . "
                    " . $orderby . "
                    ";
                }

                file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebl4.txt", $query);

                $productos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "productos" => $productos, "pagina" => $pagina, "numpaginas" => $numpaginas, "maxpaginas" => $maxpaginas);
                // $respuesta = array("respuesta" => "OK", "productos" => $productos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron productos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        try {
            $query = "
            select
                a.*,
                concat(b.clave,' - ',b.descripcion) as descripcion_productoservicio,
                concat(c.clave,' - ',c.nombre) as descripcion_unidadmedida
            from
                tproductos a
            left join
                sat_tcatproductosservicios b
            on
                b.idproductoservicio = a.idproductoservicio
            left join
                sat_tcatunidadesmedida c
            on
                c.idunidadmedida = a.idunidadmedida
            where
                a.idproducto = '" . $idproducto . "'";

            $producto = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($producto["idproducto"] > 0) {
                $respuesta = array("respuesta" => "OK", "producto" => $producto);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Variante Precio.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVariantePrecio($post)
    {
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        try {
            $query = "
            select
                *
            from
                tproductovariantesprecio
            where
                idproducto = '" . $idproducto . "'
                and idtalla = '" . $idtalla . "'
            ";

            $variante = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($variante["idproducto"] > 0) {
                $respuesta = array("respuesta" => "OK", "variante" => $variante);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontró la variante de precio del producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tallas Variante. Recupera todas las tallas con variante de precio definida para un producto
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallasVariante($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        try {
            $query = "
            select
                count(*) as total
            from
                tproductovariantesprecio
            where
                idproducto = '" . $idproducto . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];
            if ($total) {
                $query = "
                select
                    *
                from
                    tproductovariantesprecio
                where
                    idproducto = '" . $idproducto . "'
                ";

                $tallas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tallas" => $tallas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Guardar Variantes. Guarda las variantes de precio del producto
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function guardarVariantes($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        $correcto = true;

        try {
            $query = "
            delete from
                tproductovariantesprecio
            where
                idproducto = '" . $idproducto . "'
            ";

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $post["ordenar"] == 1;
            $tallas = $this->obtenerTallasProducto($post);
            foreach ($tallas["tallas"] as $talla) {
                $variante = mysqli_real_escape_string($this->con, $post["txtVariante" . $talla["idtalla"]]);
                if ($variante > 0) {

                    $query = "
                    insert into
                        tproductovariantesprecio
                        (
                            idproducto,
                            idtalla,
                            variante
                        )
                    values
                        (
                            '" . $idproducto . "',
                            '" . $talla["idtalla"] . "',
                            '" . $variante . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {

                $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
                $post["idactividad"] = 79;
                $post["comentarios"] = "Guardo las variantes de precio del producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Variantes Asignadas", "mensaje" => "Las variantes de precio se han asignado correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "Ocurrió un error al actualizar las variantes de precio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarProducto($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idtipoproducto = mysqli_real_escape_string($this->con, $post["slcTipoProducto"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);
        $claveproveedor = mysqli_real_escape_string($this->con, $post["txtClaveProveedor"]);
        $idproductoservicio = mysqli_real_escape_string($this->con, $post["slcProductoServicio"]);
        $idunidadmedida = mysqli_real_escape_string($this->con, $post["slcUnidadMedida"]);
        $tipo = mysqli_real_escape_string($this->con, $post["slcTipo"]);
        $tienda = mysqli_real_escape_string($this->con, $post["rdTienda"]);
        $precio = mysqli_real_escape_string($this->con, $post["txtPrecio"]);
        $codigobarras = mysqli_real_escape_string($this->con, $post["txtCodigo"]);
        $tarjetaregalo = mysqli_real_escape_string($this->con, $post["rdTarjetaRegalo"]);

        try {
            $query = "
            insert into
                tproductos
                (
                    nombre,
                    idtipoproducto,
                    idproveedor,
                    claveproveedor,
                    idproductoservicio,
                    idunidadmedida,
                    tipo,
                    tienda,
                    precio,
                    codigobarras,
                    tarjetaregalo
                )
            values
                (
                    '" . $nombre . "',
                    '" . $idtipoproducto . "',
                    '" . $idproveedor . "',
                    '" . $claveproveedor . "',
                    '" . $idproductoservicio . "',
                    '" . $idunidadmedida . "',
                    '" . $tipo . "',
                    '" . $tienda . "',
                    '" . $precio . "',
                    '" . $codigobarras . "',
                    '" . $tarjetaregalo . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idproducto = mysqli_insert_id($this->con);

                $post["idproducto"] = $idproducto;
                $this->asignarAlmacenes($post);
                $this->asignarColoresYTallas($post);
                // $this->asignarCatalogos($post);

                $post["idactividad"] = 14;
                $post["comentarios"] = "Agrego el producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Producto Agregrado", "mensaje" => "Se ha agregado el producto correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarProducto($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idtipoproducto = mysqli_real_escape_string($this->con, $post["slcTipoProducto"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);
        $claveproveedor = mysqli_real_escape_string($this->con, $post["txtClaveProveedor"]);
        $idproductoservicio = mysqli_real_escape_string($this->con, $post["slcProductoServicio"]);
        $idunidadmedida = mysqli_real_escape_string($this->con, $post["slcUnidadMedida"]);
        $tipo = mysqli_real_escape_string($this->con, $post["slcTipo"]);
        $tienda = mysqli_real_escape_string($this->con, $post["rdTienda"]);
        $precio = mysqli_real_escape_string($this->con, $post["txtPrecio"]);
        $codigobarras = mysqli_real_escape_string($this->con, $post["txtCodigo"]);
        $tarjetaregalo = mysqli_real_escape_string($this->con, $post["rdTarjetaRegalo"]);

        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        $nombrep = $this->obtenerProducto($post)["producto"]["nombre"];

        try {
            $query = "
            update
                tproductos
            set
                nombre = '" . $nombre . "',
                idtipoproducto = '" . $idtipoproducto . "',
                idproveedor = '" . $idproveedor . "',
                claveproveedor = '" . $claveproveedor . "',
                idproductoservicio = '" . $idproductoservicio . "',
                idunidadmedida = '" . $idunidadmedida . "',
                tipo = '" . $tipo . "',
                tienda = '" . $tienda . "',
                precio = '" . $precio . "',
                codigobarras = '" . $codigobarras . "',
                tarjetaregalo = '" . $tarjetaregalo . "'
            where
                idproducto = '" . $idproducto . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idproducto"] = $idproducto;
                if (isset($post["chkAlmacenes"]) && sizeof($post["chkAlmacenes"]) > 0) {
                    $this->asignarAlmacenes($post);
                }
                if (isset($post["idcolores"]) && sizeof($post["idcolores"]) > 0) {
                    $this->asignarColoresYTallas($post);
                }
                // $this->asignarCatalogos($post);

                $post["idactividad"] = 15;
                $post["comentarios"] = "Edito el producto " . (($nombrep != $nombre) ? $nombrep . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Producto Editado", "mensaje" => "Se ha editado el producto correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        try {
            $query = "
            update
                tproductos
            set
                status = 0
            where
                idproducto = '" . $idproducto . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
                $post["idactividad"] = 16;
                $post["comentarios"] = "Elimino el producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Producto Eliminado", "mensaje" => "Se ha eliminado el producto correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Almacenes. Asigna los almacenes a un producto
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarAlmacenes($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $almacenes = $post["chkAlmacenes"];

        try {
            $correcto = true;

            $query = "
            delete from
                trproductoalmacenes
            where
                idproducto = '" . $idproducto . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            foreach ($almacenes as $idalmacen) {
                $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);

                $query = "
                insert into
                    trproductoalmacenes
                    (
                        idproducto,
                        idalmacen
                    )
                values
                    (
                        '" . $idproducto . "',
                        '" . $idalmacen . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {

                // if (condition) {
                //     # code...
                // }
                // if (!$this->asignarProductoExistencias($colores, $tallas, $idproducto)) {

                // }

                $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
                $post["idactividad"] = 50;
                $post["comentarios"] = "Asigno almacenes del producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Almacenes Asignados", "mensaje" => "Los almacenes del producto han sido asignados correctamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los almacenes al producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Asignar Colores y Tallas. Asigna los colores y tallas de un producto
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarColoresYTallas($post)
    {

        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        // colores y tallas se tendran en una estructura de una tabla
        $colores = $post["idcolores"];
        $tallas = $post["idtallas"];


        $correcto = true;

        try {
            $query = " select
                count(*) as totalalmacenes
            from
                trproductoalmacenes
            where
                idproducto = '" . $idproducto . "'
            ";

            $totalalmacenes = mysqli_fetch_assoc(mysqli_query($this->con, $query))["totalalmacenes"];

            if ($totalalmacenes > 0) {
                if (!$this->asignarProductoExistencias($colores, $tallas, $idproducto)) {
                    $correcto = false;
                }

                if ($correcto) {

                    $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
                    $post["idactividad"] = 49;
                    $post["comentarios"] = "Asigno colores y tallas del producto " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Colores y Tallas Asignadas", "mensaje" => "Se han asignado los colores y tallas del producto correctamente.", "formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar las tallas y colores al producto.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los colores y tallas ya que el producto no cuenta con almacenes asignados. ");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Catalogos. Asigna los catalogos de un producto
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarCatalogos($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        // los nombres de los catalogos son variables, por lo que no se pueden asignar aquí

        try {
            $correcto = true;

            $query = "
            delete from
                trproductovalores
            where
                idproducto = '" . $idproducto . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $values = "";
            $this->claseCatalogos = new Catalogos();
            $catalogos = $this->claseCatalogos->obtenerCatalogos($post);
            foreach ($catalogos["catalogos"] as $catalogo) {
                if ($catalogo["multiple"]) {
                    $idvalores = $post[$catalogo["nombre"] . $catalogo["idcatalogo"]];
                    $valores = array();
                    foreach ($idvalores as $idvalor) {
                        $idvalor = mysqli_real_escape_string($this->con, $idvalor);

                        array_push($valores, "
                        (
                            '" . $idproducto . "',
                            '" . $catalogo["idcatalogo"] . "',
                            '" . $idvalor . "'
                        )
                        ");
                    }

                    $values .= implode(",", $valores) . ",";
                } else {
                    $idvalor = mysqli_real_escape_string($this->con, $post[$catalogo["nombre"] . $catalogo["idcatalogo"]]);
                    if ($idvalor != 0) {
                        $values .= "
                        (
                            '" . $idproducto . "',
                            '" . $catalogo["idcatalogo"] . "',
                            '" . $idvalor . "'
                        )
                        ,";
                    }
                }
            }

            $values = rtrim(trim($values), ',');

            if ($values != "") {
                $query = "
                insert into
                    trproductovalores
                    (
                        idproducto,
                        idcatalogo,
                        idvalor
                    )
                values
                    " . $values . "
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {

                $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
                $post["idactividad"] = 51;
                $post["comentarios"] = "Asigno catalogos del producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Catalogos Asignados", "mensaje" => "Los catalogos del producto han sido asignados correctamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los catalogos al producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Producto Existencias. Da de alta (o actualiza) los registros en tproductoexistencias
     * 
     * @access private
     * @param array $colores
     * @param array $tallas
     * @param int $idproducto
     * @return boolean
     */
    private function asignarProductoExistencias($colores, $tallas, $idproducto)
    {

        $correcto = true;

        try {

            $post["idproducto"] = $idproducto;
            $almacenes = $this->obtenerAlmacenesProducto($post)["almacenes"];
            foreach ($almacenes as $almacen) {
                foreach ($colores as $idcolor) {
                    $idcolor = mysqli_real_escape_string($this->con, $idcolor);
                    foreach ($tallas as $idtalla) {
                        $idtalla = mysqli_real_escape_string($this->con, $idtalla);

                        // al insertar en esta tabla, se deben ignorar los registros que vayan a ser repetidos
                        $query = "
                        insert into
                            tproductoexistencias
                            (
                                idproducto,
                                idcolor,
                                idtalla,
                                idalmacen
                            )
                        values
                            (
                                '" . $idproducto . "',
                                '" . $idcolor . "',
                                '" . $idtalla . "',
                                '" . $almacen["idalmacen"] . "'
                            )
                        on duplicate key update
                            status = 1
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                }
            }

            $productosexistencias = $this->obtenerExistenciasProducto($idproducto);
            foreach ($productosexistencias as $producto) {
                foreach ($colores as $idcolor) {
                    $idcolor = mysqli_real_escape_string($this->con, $idcolor);
                    foreach ($tallas as $idtalla) {
                        $idtalla = mysqli_real_escape_string($this->con, $idtalla);
                        if ($producto["idcolor"] == $idcolor && $producto["idtalla"] == $idtalla) {
                            continue 3;
                        }
                    }
                }
                if ($producto["idcolor"] == 0 && $producto["idtalla"] == 0) {
                    continue;
                }
                $query = "
                select
                    count(*) as total
                from
                    tproductoexistencias
                where
                    idproducto = '" . $producto["idproducto"] . "'
                    and idcolor = '" . $producto["idcolor"] . "'
                    and idtalla = '" . $producto["idtalla"] . "'
                    and existencia > 0
                ";

                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                if ($total == 0) {
                    $query = "
                    update
                        tproductoexistencias
                    set
                        status = 0
                    where
                        idproducto = '" . $producto["idproducto"] . "'
                        and idcolor = '" . $producto["idcolor"] . "'
                        and idtalla = '" . $producto["idtalla"] . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }

                $query = "
                select
                    count(*) as total
                from
                    tproductoexistencias
                where
                    idproducto = '" . $producto["idproducto"] . "'
                    and idcolor = '" . $producto["idcolor"] . "'
                    and idtalla = '" . $producto["idtalla"] . "'
                    and existencia > 0
                ";

                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                if ($total == 0) {
                    $query = "
                    update
                        tproductoexistencias
                    set
                        status = 0
                    where
                        idproducto = '" . $producto["idproducto"] . "'
                        and idtalla = '" . $producto["idtalla"] . "'
                        and idcolor = '" . $producto["idcolor"] . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            // if ($correcto) {
            //     $respuesta = true;
            // } else {
            //     $respuesta = false;
            // }

            $respuesta = $correcto;
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Sucursales de un Producto
     * 
     * @access public
     * @param int $idproducto
     * @return array
     */
    public function obtenerSucursalesProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        try {
            $query = "
            select
                *
            from
                tsucursales
            where
                idalmacen in
                (
                    select
                        idalmacen
                    from
                        trproductoalmacenes
                    where
                        idproducto = '" . $idproducto . "'
                )
                and status = 'A'
                
            ";

            $sucursales = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "sucursales" => $sucursales);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Almacenes Producto.
     * 
     * @access public
     * @param int $idproducto
     * @return array
     */
    public function obtenerAlmacenesProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);

        $almacenes = $post["slcAlmacenes"];
        $idalmacenes = array();
        if (isset($post["slcAlmacenes"])) {
            foreach ($almacenes as $idalmacen2) {
                $idalmacen2 = mysqli_real_escape_string($this->con, $idalmacen2);
                array_push($idalmacenes, $idalmacen2);
            }
        }
        $where = "";
        $where .= ((count($idalmacenes) > 0) ? " and a.idalmacen in (" . (implode(",", $idalmacenes)) . ")" : "");
        try {
            $query = "
            select
                pa.idproducto as idproducto,
                pa.idalmacen as idalmacen,
                a.nombre as almacen
            from
                trproductoalmacenes pa
            left join
                talmacenes a
            on
                pa.idalmacen = a.idalmacen
            where
                idproducto = '" . $idproducto . "'
                " . $where . "
            ";


            $almacenes = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "almacenes" => $almacenes);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Colores de un Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColoresProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $ordenar = mysqli_real_escape_string($this->con, $post["ordenar"]);

        $where = "";
        $where .= (($idcolor != "" && $idcolor != 0) ? " and a.idcolor = '" . $idcolor . "'" : "");
        $orderby = (($ordenar == '1') ? " order by b.nombre" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tproductoexistencias a
            where
                idproducto = '" . $idproducto . "'
                and a.status = 1
                " . $where . "
            ";
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total > 0) {
                $query = "
                select
                    a.idproducto as idproducto,
                    a.idcolor as idcolor,
                    b.nombre as color
                from
                    tproductoexistencias a
                left join
                    tcatcolores b
                on
                    a.idcolor = b.idcolor
                where
                    idproducto = '" . $idproducto . "'
                    and a.status = 1
                    " . $where . "
                group by
                    a.idcolor
                " . $orderby . "
                ";

                $colores = mysqli_query($this->con, $query);
                $this->claseQueries->guardarQuery($query);
                $respuesta = array("respuesta" => "OK", "colores" => $colores, "query" => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron colores para este producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tallas de un Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallasProducto($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $ordenar = mysqli_real_escape_string($this->con, $post["ordenar"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);

        $where = "";
        $where .= (($idtalla != "" && $idtalla != 0) ? " and a.idtalla = '" . $idtalla . "'" : "");

        $orderby = (($ordenar == '1') ? " order by posicion " : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tproductoexistencias a
            where
                idproducto = '" . $idproducto . "'
                and a.status = 1
                " . $where . "
            ";
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total > 0) {
                $query = "
                select
                    a.idproducto as idproducto,
                    a.idtalla as idtalla,
                    b.nombre as talla
                from
                    tproductoexistencias a
                left join
                    tcattallas b
                on
                    a.idtalla = b.idtalla
                where
                    idproducto = '" . $idproducto . "'
                    and a.idtalla > 0
                    and a.status = 1
                    " . $where . "
                group by
                    a.idtalla
                " . $orderby . "
                ";

                $tallas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tallas" => $tallas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron tallas para este producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Catalogos de un Producto.
     * 
     * @access public
     * @param int $idproducto
     * @return array
     */
    public function obtenerCatalogosProducto($idproducto)
    {
        $idproducto = mysqli_real_escape_string($this->con, $idproducto);

        try {
            $query = "
            select
                count(*) as total
            from
                trproductovalores
            where
                idproducto = '" . $idproducto . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    trproductovalores
                where
                    idproducto = '" . $idproducto . "'
                group by
                    idcatalogo
                ";

                $catalogos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "catalogos" => $catalogos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron catalogos para este producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Tiene Asignado Almacen. Revisa si el producto tiene asignado al menos un almacen.
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function tieneAlmacenAsignado($post)
    {

        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        try {
            $query = "
            select
                count(*) as asignado
            from
                trproductoalmacenes
            where
                idproducto = '" . $idproducto . "'
                and idalmacen = '" . $idalmacen . "'
            ";


            $asignado = mysqli_fetch_assoc(mysqli_query($this->con, $query))["asignado"];
            if ($asignado > 0) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Tiene Asignado Catalogo. Revisa si el producto tiene asignado el catalogo
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function tieneCatalogoAsignado($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idcatalogo = mysqli_real_escape_string($this->con, $post["idcatalogo"]);
        $idvalor = mysqli_real_escape_string($this->con, $post["idvalor"]);

        try {
            $query = "
            select
                count(*) as asignado
            from
                trproductovalores
            where
                idproducto = '" . $idproducto . "'
                and idcatalogo = '" . $idcatalogo . "'
                and idvalor = '" . $idvalor . "'
            ";

            $asignado = mysqli_fetch_assoc(mysqli_query($this->con, $query))["asignado"];
            if ($asignado) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Tiene Asignado Color. Revisa si el producto tiene asignado el color
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function tieneColorAsignado($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);


        try {
            $query = "
            select
                count(*) as asignado
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and idcolor = '" . $idcolor . "'
                and status = 1
            ";
            $asignado = mysqli_fetch_assoc(mysqli_query($this->con, $query))["asignado"];
            if ($asignado) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Tiene Asignada Talla. Revisa si el producto tiene asignada la talla
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function tieneTallaAsignada($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);

        try {
            $query = "
            select
                count(*) as asignado
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and idtalla = '" . $idtalla . "'
                and status = 1
            ";

            $asignado = mysqli_fetch_assoc(mysqli_query($this->con, $query))["asignado"];
            if ($asignado) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Existencias Producto. Obtiene todos los registros guardados en la tabla de existencias de un producto
     * 
     * @access private
     * @param array $post
     * @return bool
     */
    private function obtenerExistenciasProducto($idproducto)
    {

        try {
            $query = "
            select
                count(*) as total
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and status = 1
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];
            if ($total) {
                $query = "
                select
                    idproducto,
                    idcolor,
                    idtalla
                from
                    tproductoexistencias
                where
                    idproducto = '" . $idproducto . "'
                    and status = 1
                ";

                $existencias = mysqli_query($this->con, $query);

                $respuesta = $existencias;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Kardex. Recupera el historial de movimientos de existencias.
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function obtenerKardex($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["slcTalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["slcColor"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["slcAlmacen"]);
        $busqueda = mysqli_real_escape_string($this->con, $post["txtFolio"]);

        $where = "";
        $where .= (($busqueda != "") ? " and folio like '%" . $busqueda . "%'" : "");
        $where .= (($idproducto != 0 && $idproducto != "") ? " and idproducto = '" . $idproducto . "'" : "");
        $where .= (($idalmacen != "" && $idalmacen != 0) ? " and idalmacen = '" . $idalmacen . "'" : "");
        $where .= (($idtalla != "" && $idtalla != 0) ? " and idtalla = '" . $idtalla . "'" : "");
        $where .= (($idcolor != "" && $idcolor != 0) ? " and idcolor = '" . $idcolor . "'" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                vproductomovimientos
            where
                1=1
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];
            if ($total) {
                $query = "
                select
                    *
                from
                    vproductomovimientos
                where
                    1=1
                    " . $where . "
                order by
                    idproductomovimiento desc
                ";

                $movimientos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "movimientos" => $movimientos, "query" => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontro historial para este producto");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Existencias Y Reservado Producto. Recupera el total y lo reservado de un producto en un almacen
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerExistenciasYReservadoProducto($post)
    {

        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);
        $sincolortalla = mysqli_real_escape_string($this->con, $post["sincolortalla"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);

        $almacenes = $post["slcAlmacenes"];


        $idalmacenes = array();
        if (isset($post["slcAlmacenes"])) {
            foreach ($almacenes as $idalmacen2) {
                $idalmacen2 = mysqli_real_escape_string($this->con, $idalmacen2);
                array_push($idalmacenes, $idalmacen2);
            }
        }

        $where = "";
        $whereproducto = "";

        $whereproducto = (($idproducto != "" && $idproducto != 0) ? " and idproducto = '" . $idproducto . "'" : "");
        $where .= (($idproducto != "" &&  $idproducto != 0) ? " and idproducto = '" . $idproducto . "'" : "");
        if (count($idalmacenes) > 0) {
            $where .= " and idalmacen in (" . implode(",", $idalmacenes) . ")";
        } else {
            $where .= (($idalmacen != "") ? " and idalmacen = '" . $idalmacen . "'" : "");
        }
        if (isset($sincolortalla) && $sincolortalla != 1) {
            $where .= (($idcolor != "") ? " and idcolor = '" . $idcolor . "'" : " and idtalla in (select idtalla from tproductoexistencias where 1=1 " . $whereproducto . " and status = 1) ");
            $where .= (($idtalla != "") ? " and idtalla = '" . $idtalla . "'" : " and idcolor in (select idcolor from tproductoexistencias where 1=1 " . $whereproducto . " and status = 1) ");
        }



        try {

            $query = "
            select
                sum(existencia) as existencias,
                stock_minimo,
                stock_ideal
            from
                tproductoexistencias
            where
                status = 1
                " . $where . "
            ";
            $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/txts/prueba.txt", 'w');
            fwrite($fp,$query);
            fclose($fp);

            $result = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // $existencias = (($result["existencias"]>0) ? $result["existencias"] : 0);
            $existencias =  $result["existencias"];
            $stock_minimo = $result["stock_minimo"];
            $stock_ideal = $result["stock_ideal"];

            $query = "
            select
                ifnull(sum(cantidad),0) as reservado
            from
                tmovimientoinventarioproductos
            where
                idtipomovimiento = '3'
                and idmovimientoinventario in
                (
                    select
                        idmovimientoinventario
                    from
                        vmovimientos
                    where
                        autorizacion = 0
                        and statuspedido != 'C'
                )
                " . $where . "
            ";
            $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/txts/prueba.txt", 'a');
            fwrite($fp,$query);
            fclose($fp);

            $reservado = mysqli_fetch_assoc(mysqli_query($this->con, $query))["reservado"];

            $respuesta = array("existencias" => $existencias, "reservado" => $reservado, "stock_minimo" => $stock_minimo, "stock_ideal" => $stock_ideal);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Actualiza el stock mínimo de cada producto separado por talla, color y almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarStockMinimo($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        $correcto = true;

        try {
            $tallas = $this->obtenerTallasProducto($post);
            $colores = $this->obtenerColoresProducto($post);

            $i = 0;
            foreach ($colores["colores"] as $color) {
                foreach ($tallas["tallas"] as $talla) {
                    $stocks = mysqli_real_escape_string($this->con, $post["txtStockMinimo"][$i]);
                    // if ($stocks!="") {

                    if (strpos($stocks, "/") !== false) {
                        $stocks = explode("/", $stocks);
                        $stock_minimo = $stocks[0];
                        $stock_ideal = $stocks[1];
                    } else {
                        $stock_minimo = $stocks;
                        $stock_ideal = 0;
                    }

                    $query = "
                        update
                            tproductoexistencias
                        set
                            stock_minimo = '" . $stock_minimo . "',
                            stock_ideal = '" . $stock_ideal . "'
                        where
                            idproducto = '" . $idproducto . "'
                            and idcolor = '" . $color["idcolor"] . "'
                            and idtalla = '" . $talla["idtalla"] . "'
                            and idalmacen = '" . $idalmacen . "'
                            and status = 1
                        ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                    // }

                    $i++;
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Stock Mínimo Actualizado", "mensaje" => "Se ha actualizado el stock mínimo correctamente.", "formulario" => "formBusqueda2");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar el stock mínimo.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Maneja Tallas o Colores. Indica si el producto maneja tallas o colores
     * 
     * @access public
     * @param array $post
     * @return bool
     */
    public function manejaTallasColores($post)
    {

        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        $where = "";

        $where .= (($idproducto != "" && $idproducto != 0) ? " and idproducto = '" . $idproducto . "'" : "");
        $where .= (($idalmacen != "" && $idalmacen != 0) ? " and idalmacen = '" . $idalmacen . "'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                tproductoexistencias
            where
                (idcolor>0 or idtalla>0)
                and status = 1
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total > 0) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "error", "mensajeerror" => $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Actualizar Existencia. Actualiza la existencia de un producto, asi como su kardex (tproductomovimientos)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarExistencia($post)
    {
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);
        $tipomovimiento = mysqli_real_escape_string($this->con, $post["tipomovimiento"]);

        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);
        $origenmovimiento = mysqli_real_escape_string($this->con, $post["origenmovimiento"]);
        $idmovimiento = mysqli_real_escape_string($this->con, $post["idmovimiento"]);

        $fecha = date("Y-m-d H:i:s");
        $correcto = true;

        try {
            $query = "
            update
                tproductoexistencias
            set
                #existencia = greatest(existencia " . (($tipomovimiento == "E") ? "+" : (($tipomovimiento == "S") ? "-" : "")) . " " . $cantidad . ",0)
                existencia = existencia " . (($tipomovimiento == "E") ? "+" : (($tipomovimiento == "S") ? "-" : "")) . " " . $cantidad . "
            where
                idproducto = '" . $idproducto . "'
                and idcolor = '" . $idcolor . "'
                and idtalla = '" . $idtalla . "'
                and idalmacen = '" . $idalmacen . "'
                and status = 1
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            // kardex
            $query = "
            select
                sum(existencia) as existencia
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and idcolor = '" . $idcolor . "'
                and idtalla = '" . $idtalla . "'
                and status = 1
            ";

            $existencias = mysqli_fetch_assoc(mysqli_query($this->con, $query))["existencia"];

            $query = "
            select
                existencia
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and idcolor = '" . $idcolor . "'
                and idtalla = '" . $idtalla . "'
                and idalmacen = '" . $idalmacen . "'
                and status = 1
            ";

            $existenciasalmacen = mysqli_fetch_assoc(mysqli_query($this->con, $query))["existencia"];

            $query = "
            insert into
                tproductomovimientos
                (
                    idusuario,
                    idproducto,
                    idtalla,
                    idcolor,
                    idalmacen,
                    origenmovimiento,
                    tipomovimiento,
                    idmovimiento,
                    cantidad,
                    existencias,
                    existenciasalmacen,
                    fecha
                )
            values
                (
                    '" . $idusuario . "',
                    '" . $idproducto . "',
                    '" . $idtalla . "',
                    '" . $idcolor . "',
                    '" . $idalmacen . "',
                    '" . $origenmovimiento . "',
                    '" . $tipomovimiento . "',
                    '" . $idmovimiento . "',
                    '" . $cantidad . "',
                    '" . $existencias . "',
                    '" . $existenciasalmacen . "',
                    '" . $fecha . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "mensaje" => "Se actualizó la existencia correctamente.");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar la existencia.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Maneja Existencias.
     * 
     * @access public
     * @param array $idproducto
     * @return boolean
     */
    public function manejaExistencias($idproducto)
    {
        $idproducto = mysqli_real_escape_string($this->con, $idproducto);

        try {
            $query = "
            select
                count(*) as total
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and status = 1
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Siguiente Codigo Barras. Recupera el código más pequeño disponible
     * 
     * @access public
     * @return array
     */
    public function obtenerSiguienteCodigoBarras()
    {
        try {

            $query = "
            select
                max(cast(codigobarras as unsigned)) as maxcode
            from
                tproductos
            ";

            $encontrado = false;
            $maxcode = mysqli_fetch_assoc(mysqli_query($this->con, $query))["maxcode"];
            $i = $maxcode;
            $digitos = 0;
            while ($i > 10) {
                $i /= 10;
                $digitos += 1;
            }
            for ($i = 1; $i < $maxcode; $i++) {

                $query = "
                select
                    *
                from
                    tproductos
                where
                    cast(codigobarras as unsigned) = '" . $i . "'
                    and codigobarras regexp '^[0-9]+$'
                ";
                $codigo = mysqli_query($this->con, $query);
                if (mysqli_num_rows($codigo) == 0) {
                    // $j = 0
                    $digitos2 = 0;
                    $diferencia = $digitos - $digitos2;
                    $ceros = "";
                    for ($k = 0; $k < $diferencia; $k++) {
                        $ceros .= "0";
                    }
                    $codigobarras = $ceros . strval($i);
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                $query = "
                select
                    max(codigobarras) + 1 as codigobarras
                from
                    tproductos
                where
                    tarjetaregalo = 0
                ";
                $codigobarras = mysqli_fetch_assoc(mysqli_query($this->con, $query))["codigobarras"];
            }

            $respuesta = array("respuesta" => "OK", "codigobarras" => $codigobarras);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * 
     * Verifica si la talla del producto tiene existencia asignada.
     * 
     * si la respuesta es positiva, regresa una lista con, a lo sumo, 5 nombres de productos (+ la cantidad restante, si es que hay) que tienen existencia con esa talla
     * 
     * @access private
     * @param array $idtalla
     * @return array
     */
    private function tieneExistenciaAsignadaTalla($idtalla)
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
                    $post["idproducto"] = $producto["idproducto"];
                    $nombre = $this->obtenerProducto($post)["producto"]["nombre"];
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
     * Tiene Existencias.
     * 
     * @access public
     * @param array $idproducto
     * @return array
     */
    public function tieneExistencias($idproducto)
    {
        $idproducto = mysqli_real_escape_string($this->con, $idproducto);

        try {
            $query = "
            select
                count(*) as total
            from
                tproductoexistencias
            where
                idproducto = '" . $idproducto . "'
                and status = 1
                and existencia > 0
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
