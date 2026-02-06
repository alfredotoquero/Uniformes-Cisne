<?php
class Solicitudes
{
    private $con;
    private $claseQueries;
    private $claseAlmacenes;
    private $claseProductos;
    private $claseCategorias;
    private $claseTallas;
    private $claseColores;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Categorias.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Tallas.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Colores.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Solicitudes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSolicitudes($post)
    {
        // filtros
        // $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);

        $where = "";
        $where .= (($idproveedor != "") ? " and idproveedor = '" . $idproveedor . "'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vsolicitudescompra
            where
                status = 0
                " . $where . "
                and ((idpedido>0 and pendiente=1) or (idpedido=0))
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vsolicitudescompra
                where
                    status = 0
                    " . $where . "
                    and ((idpedido>0 and pendiente=1) or (idpedido=0))
                order by
                    fecha desc,idproducto,producto,color,talla_posicion
                ";
                // order by
                // idproducto,producto,color,talla_posicion,fecha desc

                // $this->claseQueries->guardarQuery($query);

                $solicitudes = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "solicitudes" => $solicitudes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron solicitudes registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Solicitud.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSolicitud($post)
    {
        $idsolicitudcompra = mysqli_real_escape_string($this->con, $post["idsolicitudcompra"]);

        try {
            $query = "
            select
                *
            from
                vsolicitudescompra
            where
                idsolicitudcompra = '" . $idsolicitudcompra . "'
            ";
            $solicitud = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($solicitud["idsolicitudcompra"] > 0) {
                $respuesta = array("respuesta" => "OK", "solicitud" => $solicitud);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la solicitud.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tallas Solicitudes. Recupera el conjunto de tallas de un grupo de solicitudes
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallasSolicitudes($post)
    {
        // $where = mysqli_real_escape_string($this->con, $post["where"]);
        $where = $post["where"];
        $idsolicitudesf = $post["idsolicitudes"];

        $idsolicitudes = array();
        foreach ($idsolicitudesf as $idsolicitud) {
            $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

            array_push($idsolicitudes, $idsolicitud);
        }

        try {

            $query = "
            select
                s.*
            from
                vsolicitudescompra s
            left join 
                tcattallas t
            on
                t.idtalla = s.idtalla
            where
                s.idsolicitudcompra in (" . implode(",", $idsolicitudes) . ") " . $where . "
            group by
                s.idtalla, s.talla
            order by
                t.posicion, s.talla
            ";

            $tallas = mysqli_query($this->con, $query);

            if (mysqli_num_rows($tallas)) {
                $respuesta = array("respuesta" => "OK", "tallas" => $tallas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontró ninguna talla.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Colores Solicitudes. Recupera el conjunto de colores de un grupo de solicitudes
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColoresSolicitudes($post)
    {
        // $where = mysqli_real_escape_string($this->con, $post["where"]);
        $where = $post["where"];
        $idsolicitudesf = $post["idsolicitudes"];

        $idsolicitudes = array();
        foreach ($idsolicitudesf as $idsolicitud) {
            $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

            array_push($idsolicitudes, $idsolicitud);
        }

        try {

            $query = "
            select
                s.*
            from
                vsolicitudescompra s
            left join 
                tcatcolores c
            on
                c.idcolor = s.idcolor
            where
                s.idsolicitudcompra in (" . implode(",", $idsolicitudes) . ") " . $where . "
            group by
                s.idcolor, s.color
            order by
                c.nombre, s.color
            ";

            $colores = mysqli_query($this->con, $query);

            if (mysqli_num_rows($colores)) {
                $respuesta = array("respuesta" => "OK", "colores" => $colores);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontró ningún color.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cantidad Solicitada. Recupera el total solicitado de un par color/talla del grupo de solicitudes
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCantidadSolicitada($post)
    {
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $color = mysqli_real_escape_string($this->con, $post["color"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $talla = mysqli_real_escape_string($this->con, $post["talla"]);
        // $where = mysqli_real_escape_string($this->con, $post["where"]);
        $where = $post["where"];
        $idsolicitudesf = $post["idsolicitudes"];

        $idsolicitudes = array();
        foreach ($idsolicitudesf as $idsolicitud) {
            $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

            array_push($idsolicitudes, $idsolicitud);
        }

        try {

            $query = "
            select
                sum(cantidad) as total
            from
                vsolicitudescompra s
            where
                s.idsolicitudcompra in (" . implode(", ", $idsolicitudes) . ") " . $where . "
                and s.idtalla = '" . $idtalla . "'
                and s.talla = '" . $talla . "'
                and s.idcolor = '" . $idcolor . "'
                and s.color = '" . $color . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = array("respuesta" => "OK", "total" => $total);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontró cantidad solicitada.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Total Solicitudes. Recupera la cantidad total de solicitudes a un proveedor especifico
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTotalSolicitudes($post)
    {
        $idproveedor = mysqli_real_escape_string($this->con, $post["idproveedor"]);

        try {

            $query = "
            select
                count(*) as total
            from
                vsolicitudescompra
            where
                idproveedor = '" . $idproveedor . "'
                and status = 0
                and (pendiente = 1 or idpedido = 0)
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = array("respuesta" => "OK", "total" => $total);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontró ninguna solicitud para este proveedor.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Proveedor. Le asigna un proveedor a un conjunto de solicitudes
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarProveedor($post)
    {
        $idproveedor = mysqli_real_escape_string($this->con, $post["idproveedorasignar"]);
        // $proveedor = mysqli_real_escape_string($this->con, $post["proveedorasignar"]);
        $solicitudes = $post["chkSolicitudes"];

        $correcto = true;

        try {

            $idsolicitudes = "";
            $idserror = array();
            foreach ($solicitudes as $idsolicitud) {
                $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

                $query = "
                update
                    tsolicitudescompra
                set
                    #proveedor = '" . $proveedor . "'
                    idproveedor = '" . $idproveedor . "'
                where
                    idsolicitudcompra = '" . $idsolicitud . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                    $idserror[] = $idsolicitud;
                } else {
                    $idsolicitudes .= $idsolicitud . ",";
                }
            }
            $idsolicitudes = rtrim($idsolicitudes, ",");

            // // despues de asignar el proveedor, automáticamente se generaría la orden de compra
            // if ($this->generarOrdenCompra($post)["respuesta"] == "ERROR") {
            //     $correcto = false;
            // }

            if ($correcto) {

                $post["idactividad"] = 36;
                $post["comentarios"] = "Asigno el proveedor " . $proveedor . " a las solicitudes de compra (" . $idsolicitudes . ")";
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Solicitudes Asignadas", "mensaje" => "Se han asignado las solicitudes y se ha generado la orden de compra correctamente.", "pantalla" => "solicitudes", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al realizar esta acción.", "solicitudes_con_error" => $idserror);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Generar Orden Compra.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function generarOrdenCompra($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $proveedor = mysqli_real_escape_string($this->con, $post["proveedorasignar"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["idproveedor"]);
        $solicitudes = $post["chkSolicitudes"];

        $fecha = date("Y-m-d H:i:s");
        $correcto = true;

        try {

            if (count($solicitudes)) {
                $query = "
                insert into
                    tcompras
                    (
                        idusuario,
                        idproveedor,
                        proveedor,
                        fecha
                    )
                values
                    (
                        '" . $idusuario . "',
                        '" . $idproveedor . "',
                        '" . $proveedor . "',
                        '" . $fecha . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $idcompra = mysqli_insert_id($this->con);


                if ($idcompra > 0) {
                    $idserror = array();
                    $idsolicitudes = "";
                    foreach ($solicitudes as $idsolicitud) {
                        $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

                        $post["idsolicitudcompra"] = $idsolicitud;
                        $solicitud = $this->obtenerSolicitud($post)["solicitud"];

                        $query = "
                        insert into
                            trcompraproductos
                            (
                                idcompra,
                                idpedido,
                                idsolicitudcompra,
                                idusuario,
                                idproveedor,
                                idproducto,
                                producto,
                                idtalla,
                                talla,
                                idcolor,
                                color,
                                cantidad,
                                notas
                            )
                        values
                            (
                                '" . $idcompra . "',
                                '" . $solicitud["idpedido"] . "',
                                '" . $solicitud["idsolicitudcompra"] . "',
                                '" . $solicitud["idusuario"] . "',
                                '" . $idproveedor . "',
                                '" . $solicitud["idproducto"] . "',
                                '" . $solicitud["producto"] . "',
                                '" . $solicitud["idtalla"] . "',
                                '" . $solicitud["talla"] . "',
                                '" . $solicitud["idcolor"] . "',
                                '" . $solicitud["color"] . "',
                                '" . $solicitud["cantidad"] . "',
                                '" . $solicitud["notas"] . "'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                            $idserror[] = $idsolicitud;
                        } else {
                            $idsolicitudes .= $solicitud["idsolicitudcompra"] . ",";
                        }

                        $query = "
                        update
                            tsolicitudescompra
                        set
                            status = 1,
                            idcompra = '" . $idcompra . "'
                        where
                            idsolicitudcompra = '" . $solicitud["idsolicitudcompra"] . "'
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }

                    $idsolicitudes = rtrim($idsolicitudes, ",");
                }
            } else {
                $idproveedor = -1;
            }

            if ($correcto) {

                $post["idactividad"] = 37;
                $post["comentarios"] = "Genero la orden de compra #" . $idcompra . " para las solicitudes (" . $idsolicitudes . ")";
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Orden de Compra Generada", "mensaje" => "Se ha generado la orden de compra correctamente.", "formulario" => "formBusqueda", "pantalla" => "solicitudes", "idproveedor" => $idproveedor);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al generar la orden de compra.", "solicitudes_con_error" => $idserror);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Generar Solicitudes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function generarSolicitudes($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["txtPedido"]);
        $notas = mysqli_real_escape_string($this->con, $post["txtNotas"]);

        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        try {

            $partidas = $this->obtenerpartidasTMP($idusuario);

            foreach ($partidas["partidas"] as $partida) {
                $query = "
                insert into
                    tsolicitudescompra 
                    (
                        idusuario,
                        idpedido,
                        idproveedor,
                        idproducto,
                        producto,
                        idtalla,
                        talla,
                        idcolor,
                        color,
                        cantidad,
                        fecha,
                        notas
                    )
                values 
                    (
                        '" . $idusuario . "',
                        '" . $idpedido . "',
                        '" . $partida["idproveedor"] . "',
                        '" . $partida["idproducto"] . "',
                        '" . $partida["producto"] . "',
                        '" . $partida["idtalla"] . "',
                        '" . $partida["talla"] . "',
                        '" . $partida["idcolor"] . "',
                        '" . $partida["color"] . "',
                        '" . $partida["cantidad"] . "',
                        '" . $fecha . "',
                        '" . $notas . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            $this->eliminarPartidasTMP($idusuario);

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Solicitudes Generadas", "mensaje" => "Se han generado las solicitudes de compra correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al generar las solicitudes.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    public function solicitarProductos($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        $this->claseAlmacenes = new Almacenes();
        $this->claseProductos = new Productos();
        $this->claseTallas = new Tallas();
        $this->claseColores = new Colores();

        try {
            // $datos = "";

            $arraySolicitudes = array();
            foreach ($post["productos"] as $idalmacen => $almacenes) {
                $post["idalmacen"] = $idalmacen;
                $almacen = $this->claseAlmacenes->obtenerAlmacen($post)["almacen"]["nombre"];
                foreach ($almacenes as $idproducto => $producto) {
                    $idproducto = mysqli_real_escape_string($this->con, $idproducto);
                    $post["idproducto"] = $idproducto;
                    $datosproducto = $this->claseProductos->obtenerProducto($post)["producto"];
                    $idproveedor = $datosproducto["idproveedor"];
                    $nombreproducto = $datosproducto["nombre"];
                    foreach ($producto as $idtalla => $colores) {
                        $idtalla = mysqli_real_escape_string($this->con, $idtalla);
                        $post["idtalla"] = $idtalla;
                        $talla = $this->claseTallas->obtenerTalla($post)["talla"]["nombre"];
                        foreach ($colores as $idcolor => $cantidad) {
                            $idcolor = mysqli_real_escape_string($this->con, $idcolor);
                            $cantidad = mysqli_real_escape_string($this->con, $cantidad);
                            $post["idcolor"] = $idcolor;
                            $color = $this->claseColores->obtenerColor($post)["color"]["nombre"];
                            $notas = "Almacen: " . $almacen;
                            $query = "
                            insert into
                                tsolicitudescompra 
                                (
                                    idusuario,
                                    idalmacen,
                                    idproveedor,
                                    idproducto,
                                    producto,
                                    idtalla,
                                    talla,
                                    idcolor,
                                    color,
                                    cantidad,
                                    notas,
                                    fecha
                                )
                            values
                                (
                                    '" . $idusuario . "',
                                    '" . $idalmacen . "',
                                    '" . $idproveedor . "',
                                    '" . $idproducto . "',
                                    '" . $nombreproducto . "',
                                    '" . $idtalla . "',
                                    '" . $talla . "',
                                    '" . $idcolor . "',
                                    '" . $color . "',
                                    '" . $cantidad . "',
                                    '" . $notas . "',
                                    '" . $fecha . "'
                                )
                            ";
                            // $datos .= $query;

                            $this->claseQueries->guardarQuery($query);

                            if (mysqli_query($this->con, $query)) {
                                $arraySolicitudes[] = mysqli_insert_id($this->con);
                            }else{
                                $correcto = false;

                            }
                        }
                    }
                }
            }

            if ($correcto) {

                // Declaramos variable para saber si hay productos que se puedan surtir de inventario
                $surtible = false;

                // Verificar si existe al menos un producto que pueda ser surtido de inventario
                $query = "
                select
                    idproducto,
                    idtalla,
                    idcolor,
                    idalmacen
                from
                    tsolicitudescompra
                where
                    idsolicitudcompra in (".implode(",",$arraySolicitudes).")";
                $result = mysqli_query($this->con,$query);

                while($tmp = mysqli_fetch_assoc($result)){
                    $query = "
                    select
                        sum(existencia) as existencia
                    from
                        tproductoexistencias
                    where
                        idproducto = '".$tmp["idproducto"]."' and
                        idtalla = '".$tmp["idtalla"]."' and
                        idcolor = '".$tmp["idcolor"]."' and
                        idalmacen != '".$tmp["idalmaecn"]."' and
                        status = 1";
                    $existencia = mysqli_fetch_assoc(mysqli_query($this->con,$query))["existencia"];

                    if($existencia > 0){
                        $surtible = true;
                    }
                }

                $post["idactividad"] = 83;
                $post["comentarios"] = "Solicitó productos desde reporte de reorden";
                $this->claseLogs->registrarActividad($post);

                if($surtible){
                    $respuesta = array(
                        "respuesta" => "OK",
                        "tipo" => "abrir_nuevo_fancy",
                        "url" => "modulos/reportes/reorden/indicarmovimientosinventario.php?idsolicitudescompra=".implode(",",$arraySolicitudes)
                    );
                }else{
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Solicitudes Generadas", "mensaje" => "Se han generado las solicitudes de compra correctamente.", "formulario" => "formBusqueda");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al generar las solicitudes.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener solicitudes by ID. Obtiene la información de las solicitudes a partir de una cadena con los ID's.
     * 
     * @access public
     * @param string $idsolicitudescompra
     * @return array
     */
    public function obtenerSolicitudesByID($idsolicitudescompra)
    {
        $idsolicitudescompra = mysqli_real_escape_string($this->con, $idsolicitudescompra);

        try {

            $query = "
            select
                idsolicitudcompra,
                idalmacen,
                idproducto,
                idtalla,
                idcolor,
                cantidad
            from
                tsolicitudescompra
            where
                idsolicitudcompra in (".$idsolicitudescompra.")";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)>0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "solicitudes" => mysqli_fetch_all($result,MYSQLI_ASSOC)
                );
            }else{
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron obtener solicitudes.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Dividir Cantidad. Divide la cantidad de producto de la solicitud en dos solicitudes
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function dividirCantidad($post)
    {
        $idsolicitudcompra = mysqli_real_escape_string($this->con, $post["idsolicitudcompra"]);
        $cantidad = mysqli_real_escape_string($this->con, $post["txtCantidad"]);

        $correcto = true;

        try {

            $query = "
            select
                *
            from
                tsolicitudescompra
            where
                idsolicitudcompra = '" . $idsolicitudcompra . "'
            ";

            $solicitud = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            $query = "
            insert into
                tsolicitudescompra 
                (
                    idusuario,
                    idpedido,
                    idproveedor,
                    idproducto,
                    idcategoriaproducto,
                    producto,
                    idtalla,
                    talla,
                    idcolor,
                    color,
                    cantidad,
                    notas,
                    fecha
                )
            values
                (
                    '" . $solicitud["idusuario"] . "',
                    '" . $solicitud["idpedido"] . "',
                    '" . $solicitud["idproveedor"] . "',
                    '" . $solicitud["idproducto"] . "',
                    '" . $solicitud["idcategoriaproducto"] . "',
                    '" . $solicitud["producto"] . "',
                    '" . $solicitud["idtalla"] . "',
                    '" . $solicitud["talla"] . "',
                    '" . $solicitud["idcolor"] . "',
                    '" . $solicitud["color"] . "',
                    '" . $cantidad . "',
                    '" . $solicitud["notas"] . "',
                    '" . $solicitud["fecha"] . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $query = "
            update
                tsolicitudescompra
            set
                cantidad = cantidad - '" . $cantidad . "'
            where
                idsolicitudcompra = '" . $idsolicitudcompra . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            if ($correcto) {

                $post["idactividad"] = 35;
                $post["comentarios"] = "Dividio la cantidad de la solicitud #" . $idsolicitudcompra;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Solicitud Dividida", "mensaje" => "Se ha dividido la solicitud de compra correctamente.", "formulario" => "formBusqueda", "pantalla" => "solicitudes", "idproveedor" => $solicitud["idproveedor"]);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al dividir la solicitud.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Solicitudes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarSolicitudes($post)
    {
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);
        $idsolicitudesf = $post["chkSolicitudes"];

        $idsolicitudes = array();
        foreach ($idsolicitudesf as $idsolicitud) {
            $idsolicitud = mysqli_real_escape_string($this->con, $idsolicitud);

            array_push($idsolicitudes, $idsolicitud);
        }

        $correcto = true;

        try {

            $query = "
            update
                tsolicitudescompra
            set
                status = 2
            where
                idsolicitudcompra in (" . implode(",", $idsolicitudes) . ")
            ";

            $this->claseQueries->guardarQuery($query);

            mysqli_query($this->con, $query);

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Solicitudes Eliminadas", "mensaje" => "Se han eliminado las solicitudes de compra correctamente.", "formulario" => "formBusqueda", "pantalla" => "solicitudes", "idproveedor" => $idproveedor);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al eliminar las solicitudes.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Solicitudes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarSolicitudes($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        // $pedido es el id de pedido escrito por el usuario
        $pedido = mysqli_real_escape_string($this->con, $post["pedido"]);

        try {

            $query = "
            select
                count(*) as total
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            // si no hay ningún producto agregado, manda error
            if ($total) {
                if (trim($pedido) != "") {

                    $query = "
                    select
                        *
                    from
                        vpedidos
                    where
                        idpedido = '" . $pedido . "' and
                        status = 'A'
                    ";

                    if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                        $respuesta = array("respuesta" => "OK");
                    } else {
                        $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "El número de pedido no existe o no se encuentra activo.");
                    }
                } else {
                    $respuesta = array("respuesta" => "OK");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Debes insertar al menos un producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Temporales Version 1.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasTMP($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            select
                count(*) as total
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    s.*,
                    p.nombre as producto2,
                    c.nombre as color2,
                    t.nombre as talla2
                from
                    tproductossolicitudestmp s
                left join
                    tproductos p
                on
                    s.idproducto = p.idproducto
                left join
                    tcatcolores c
                on
                    s.idcolor = c.idcolor
                left join
                    tcattallas t
                on
                    s.idtalla = t.idtalla
                where
                    idusuario='" . $idusuario . "'
                order by
                    idsolicitudproducto
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Temporales Version 2.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasTMP2($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            select
                count(*) as total
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                // $cantidad = '';
                $query = "
                select
                    s.*,
                    concat(s.producto,' : ',group_concat(distinct(s.color),' / ',t.tallas separator '-_-')) as lista
                from
                    vproductossolicitudestmp s
                left join
                    (
                        select
                            color,
                            idsolicitudproducto,
                            group_concat(cantidad,' - ',talla order by posicion separator ' / ') as tallas
                        from
                            vproductossolicitudestmp
                        where 
                            idusuario='" . $idusuario . "'
                        group by
                            color,idsolicitudproducto
                    ) t
                on
                    s.color = t.color
                    and s.idsolicitudproducto = t.idsolicitudproducto
                where 
                    idusuario='" . $idusuario . "'
                group by
                    idsolicitudproducto
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partida Temporal.
     * 
     * @access public
     * @param array $idpartida
     * @return array
     */
    public function obtenerPartidaTMP($idpartida)
    {
        $idpartida = mysqli_real_escape_string($this->con, $idpartida);

        try {
            $query = "
            select
                *
            from
                tproductossolicitudestmp
            where
                idsolicitudproducto = '" . $idpartida . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $respuesta = array("respuesta" => "OK", "partida" => $partida);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Colores Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColoresPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idsolicitudproducto = mysqli_real_escape_string($this->con, $post["idsolicitudproducto"]);

        try {
            $query = "
            select
                color
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
                and idsolicitudproducto = '" . $idsolicitudproducto . "'
            group by
                color
            order by
                idtmp
            ";

            // $this->claseQueries->guardarQuery($query);

            $colores = mysqli_query($this->con, $query);

            if (mysqli_num_rows($colores) > 0) {
                $respuesta = array("respuesta" => "OK", "colores" => $colores);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron colores para la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cantidad Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCantidadTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idsolicitudproducto = mysqli_real_escape_string($this->con, $post["idsolicitudproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);

        try {
            $query = "
            select
                *
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
                and idsolicitudproducto = '" . $idsolicitudproducto . "'
                and idtalla = '" . $idtalla . "'
                and idcolor = '" . $idcolor . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $respuesta = array("respuesta" => "OK", "cantidad" => $partida["cantidad"]);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idsolicitudproducto = mysqli_real_escape_string($this->con, $post["idsolicitudproducto"]);
        // cantidad o cantidades
        $cantidad = mysqli_real_escape_string($this->con, $post["txtCantidad"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["slcCategoria"]);
        // nombre en texto del producto
        $producto = mysqli_real_escape_string($this->con, $post["txtProducto"]);

        // ademas, cantidades y, para cuando es producto libre, colores

        $correcto = true;

        $this->claseProductos = new Productos();

        try {
            if ($idproducto != "") {
                // recuperamos colores y tallas del producto
                $idproveedor = $this->claseProductos->obtenerProducto($post)["producto"]["idproveedor"];
                $colores = $this->claseProductos->obtenerColoresProducto($post);
                $tallas = $this->claseProductos->obtenerTallasProducto($post);

                if ($tallas["respuesta"] == "OK") {
                    foreach ($colores["colores"] as $color) {
                        foreach ($tallas["tallas"] as $talla) {
                            $cantidadct = mysqli_real_escape_string($this->con, $post["txtCantidad" . $color["idcolor"] . "-" . $talla["idtalla"]]);
                            if ($cantidadct > 0) {
                                // insertar
                                // unset($post);
                                // no recibe:
                                // idcategoriaproducto,
                                // producto,
                                // color,
                                $post["idusuario"] = $idusuario;
                                $post["idsolicitudproducto"] = $idsolicitudproducto;
                                $post["idproveedor"] = $idproveedor;
                                $post["idproducto"] = $idproducto;
                                $post["idcolor"] = $color["idcolor"];
                                $post["idtalla"] = $talla["idtalla"];
                                $post["cantidad"] = $cantidadct;
                                if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                                    $correcto = false;
                                }
                            }
                        }
                    }
                } else {
                    // unset($post);
                    // no recibe:
                    // idcategoriaproducto,
                    // producto,
                    // idcolor,
                    // color,
                    // idtalla,
                    $post["idusuario"] = $idusuario;
                    $post["idsolicitudproducto"] = $idsolicitudproducto;
                    $post["idproveedor"] = $idproveedor;
                    $post["idproducto"] = $idproducto;
                    $post["cantidad"] = $cantidad;
                    if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                        $correcto = false;
                    }
                }
            } else {
                $idproveedor = 0;
                // proceso para producto libre
                $this->claseCategorias = new Categorias();
                $tallas = $this->claseCategorias->obtenerTallasCategoria($idcategoriaproducto);
                $num = 1;
                if ($tallas["respuesta"] == "OK") {
                    while (true) {
                        if (isset($post["txtColor" . $num])) {
                            $nombrecolor = mysqli_real_escape_string($this->con, $post["txtColor" . $num]);
                            if ($nombrecolor != "") {
                                foreach ($tallas["tallas"] as $talla) {
                                    $cantidadnt = mysqli_real_escape_string($this->con, $post["txtCantidad" . $num . "-" . $talla["idtalla"]]);
                                    if ($cantidadnt > 0) {
                                        // unset($post);
                                        // no recibe:
                                        // idproducto,
                                        // idcolor,
                                        $post["idusuario"] = $idusuario;
                                        $post["idsolicitudproducto"] = $idsolicitudproducto;
                                        $post["idproveedor"] = $idproveedor;
                                        $post["producto"] = $producto;
                                        $post["idcategoriaproducto"] = $idcategoriaproducto;
                                        $post["idtalla"] = $talla["idtalla"];
                                        $post["color"] = $nombrecolor;
                                        $post["cantidad"] = $cantidadnt;
                                        if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                                            $correcto = false;
                                        }
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                        $num++;
                    }
                } else {
                    // unset($post);
                    // no recibe:
                    // idproducto,
                    // idcolor,
                    // color,
                    // idtalla,
                    $post["idusuario"] = $idusuario;
                    $post["idsolicitudproducto"] = $idsolicitudproducto;
                    $post["idproveedor"] = $idproveedor;
                    $post["producto"] = $producto;
                    $post["idcategoriaproducto"] = $idcategoriaproducto;
                    $post["cantidad"] = $cantidad;
                    if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {
                // ¿es necesario un "formulario" => "formBusqueda"?
                $respuesta = array("respuesta" => "OK", "tipo" => "cerrarfancy", "titulo" => "Partida Agregada", "mensaje" => "Se ha agregado la partida correctamente.", "aumentaidpartida" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al guardar la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        // $idproveedor = mysqli_real_escape_string($this->con,$post["slcProveedor"]);
        // cantidad o cantidades?
        $cantidad = mysqli_real_escape_string($this->con, $post["txtCantidad"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["idcategoriaselect"]);
        // nombre en texto del producto
        $producto = mysqli_real_escape_string($this->con, $post["txtProducto"]);

        // ademas, cantidades y, para cuando es producto libre, colores

        $correcto = true;

        $this->claseProductos = new Productos();

        try {

            // eliminar la partida actual, y reinsertar el producto nuevo. No hay manera sencilla de editar la partida usando solo "update"
            $this->eliminarPartidaTMP($post);

            if ($idproducto != "") {
                // recuperamos colores y tallas del producto
                $idproveedor = $this->claseProductos->obtenerProducto($post)["producto"]["idproveedor"];
                $colores = $this->claseProductos->obtenerColoresProducto($post);
                $tallas = $this->claseProductos->obtenerTallasProducto($post);

                if ($tallas["respuesta"] == "OK") {
                    foreach ($colores["colores"] as $color) {
                        foreach ($tallas["tallas"] as $talla) {
                            $cantidadct = mysqli_real_escape_string($this->con, $post["txtCantidad" . $color["idcolor"] . "-" . $talla["idtalla"]]);
                            if ($cantidadct > 0) {
                                // insertar
                                // unset($post);
                                // no recibe:
                                // idcategoriaproducto,
                                // producto,
                                // color,
                                $post["idusuario"] = $idusuario;
                                $post["idsolicitudproducto"] = $idpartida;
                                $post["idproveedor"] = $idproveedor;
                                $post["idproducto"] = $idproducto;
                                $post["idcolor"] = $color["idcolor"];
                                $post["idtalla"] = $talla["idtalla"];
                                $post["cantidad"] = $cantidadct;
                                if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                                    $correcto = false;
                                }
                            }
                        }
                    }
                } else {
                    // unset($post);
                    // no recibe:
                    // idcategoriaproducto,
                    // producto,
                    // idcolor,
                    // color,
                    // idtalla,
                    $post["idusuario"] = $idusuario;
                    $post["idsolicitudproducto"] = $idpartida;
                    $post["idproveedor"] = $idproveedor;
                    $post["idproducto"] = $idproducto;
                    $post["cantidad"] = $cantidad;
                    if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                        $correcto = false;
                    }
                }
            } else {
                $idproveedor = 0;
                $this->claseCategorias = new Categorias();
                // proceso para producto libre
                $tallas = $this->claseCategorias->obtenerTallasCategoria($idcategoriaproducto);
                $num = 1;
                if ($tallas["respuesta"] == "OK") {
                    while (true) {
                        if (isset($post["txtColor" . $num])) {
                            $nombrecolor = mysqli_real_escape_string($this->con, $post["txtColor" . $num]);
                            if ($nombrecolor != "") {
                                foreach ($tallas["tallas"] as $talla) {
                                    $cantidadnt = mysqli_real_escape_string($this->con, $post["txtCantidad" . $num . "-" . $talla["idtalla"]]);
                                    if ($cantidadnt > 0) {
                                        // unset($post);
                                        // no recibe:
                                        // idproducto,
                                        // idcolor,
                                        $post["idusuario"] = $idusuario;
                                        $post["idsolicitudproducto"] = $idpartida;
                                        $post["idproveedor"] = $idproveedor;
                                        $post["producto"] = $producto;
                                        $post["idcategoriaproducto"] = $idcategoriaproducto;
                                        $post["idtalla"] = $talla["idtalla"];
                                        $post["color"] = $nombrecolor;
                                        $post["cantidad"] = $cantidadnt;
                                        if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                                            $correcto = false;
                                        }
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                        $num++;
                    }
                } else {
                    // unset($post);
                    // no recibe:
                    // idproducto,
                    // idcolor,
                    // color,
                    // idtalla,
                    $post["idusuario"] = $idusuario;
                    $post["idsolicitudproducto"] = $idpartida;
                    $post["idproveedor"] = $idproveedor;
                    $post["producto"] = $producto;
                    $post["idcategoriaproducto"] = $idcategoriaproducto;
                    $post["cantidad"] = $cantidad;
                    if ($this->insertarPartidaTMP($post)["respuesta"] == "ERROR") {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {
                // ¿es necesario un "formulario" => "formBusqueda"?
                $respuesta = array("respuesta" => "OK", "titulo" => "Partida Editada", "mensaje" => "Se ha editado la partida correctamente.", "aumentaidpartida" => 0);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al editar la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Insertar Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function insertarPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idsolicitudproducto = mysqli_real_escape_string($this->con, $post["idsolicitudproducto"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["idproveedor"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);
        // mas datos
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        // 
        $producto = mysqli_real_escape_string($this->con, $post["producto"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["idcategoriaproducto"]);
        $color = mysqli_real_escape_string($this->con, $post["color"]);

        try {

            $query = "
            select
                idtmp
            from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
                and idsolicitudproducto = '" . $idsolicitudproducto . "'
                and idcategoriaproducto = '" . $idcategoriaproducto . "'
                and idproveedor = '" . $idproveedor . "'
                and idproducto = '" . $idproducto . "'
                and producto = '" . $producto . "'
                and idcolor = '" . $idcolor . "'
                and color = '" . $color . "'
                and idtalla = '" . $idtalla . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $query = "
                update
                    tproductossolicitudestmp
                set
                    cantidad = '" . $cantidad . "'
                where
                    idtmp = '" . $partida["idtmp"] . "'
                ";
            } else {
                $query = "
                insert into
                    tproductossolicitudestmp
                    (
                        idusuario,
                        idsolicitudproducto,
                        idcategoriaproducto,
                        idproveedor,
                        idproducto,
                        producto,
                        idcolor,
                        color,
                        idtalla,
                        cantidad
                    )
                values
                    (
                        '" . $idusuario . "',
                        '" . $idsolicitudproducto . "',
                        '" . $idcategoriaproducto . "',
                        '" . $idproveedor . "',
                        '" . $idproducto . "',
                        '" . $producto . "',
                        '" . $idcolor . "',
                        '" . $color . "',
                        '" . $idtalla . "',
                        '" . $cantidad . "'
                    )
                on duplicate key update
                    cantidad = '" . $cantidad . "'
                ";
            }

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo insertar la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Partidas Temporales.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function eliminarPartidasTMP($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            delete from
                tproductossolicitudestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al eliminar las partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Partida Temporal.
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function eliminarPartidaTMP($post)
    {
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            delete from
                tproductossolicitudestmp
            where
                idsolicitudproducto = '" . $idpartida . "'
                and idusuario = '" . $idusuario . "'
            ";

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Cargar Datos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function cargarDatos($post)
    {
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            select
                *
            from
                tproductossolicitudestmp
            where
                idsolicitudproducto = '" . $idpartida . "'
                and idusuario = '" . $idusuario . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                if ($partida["idproducto"] > 0) {
                    $respuesta = array("respuesta" => "OK", "origen" => "1", "idproveedor" => $partida["idproveedor"], "idproducto" => $partida["idproducto"], "idpartida" => $partida["idsolicitudproducto"]);
                } else {
                    $respuesta = array("respuesta" => "OK", "origen" => "2", "idproveedor" => $partida["idproveedor"], "producto" => $partida["producto"], "idcategoriaproducto" => $partida["idcategoriaproducto"], "idpartida" => $partida["idsolicitudproducto"]);
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Nombre Unico Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarNombreUnicoProducto($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombreproducto = mysqli_real_escape_string($this->con, $post["nombreproducto"]);

        try {
            $query = "
            select
                *
            from
                tproductossolicitudestmp
            where
                producto='" . $nombreproducto . "'
                and idproducto = 0
                and idusuario='" . $idusuario . "'
            ";

            if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "El nombre es repetido.");
            } else {
                $respuesta = array("respuesta" => "OK");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
