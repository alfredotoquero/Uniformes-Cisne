<?php
class Compras
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
     * Obtener Compras.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCompras($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $idproveedor = mysqli_real_escape_string($this->con, $post["slcProveedor"]);
        $fechas = mysqli_real_escape_string($this->con, $post["slcFiltroFechas"]);
        $status = mysqli_real_escape_string($this->con, $post["slcStatus"]);

        $fechas = ($fechas == 1) ? "fecha" : (($fechas == 2) ? "fecharecepcion" : "");

        $where = "";
        $where .= (($busqueda != "") ? " and (idcompra like '%" . $busqueda . "%' or idcompra in (select idcompra from tsolicitudescompra where idpedido like '%" . $busqueda . "%'))" : "");
        $where .= (($fechainicial != "") ? " and $fechas >= '" . $fechainicial . "'" : "");
        $where .= (($fechafinal != "") ? " and $fechas <= '" . $fechafinal . "'" : "");
        $where .= (($idproveedor != "" && $idproveedor > 0) ? " and idproveedor = '" . $idproveedor . "'" : "");
        switch ($status) {
            case '1':
                $where .= " and status = ''";
                break;
            case '2':
                $where .= " and status = 'P'";
                break;
            case '3':
                $where .= " and status = 'R'";
                break;

            default:
                // 
                break;
        }

        try {
            $query = "
            select
                *
            from
                vcompras
            where
                1=1
                " . $where . "
            group by
                idcompra
            order by
                fecha desc
            ";
            $this->claseQueries->guardarQuery($query);

            $compras = mysqli_query($this->con, $query);

            if (mysqli_num_rows($compras) > 0) {
                $respuesta = array("respuesta" => "OK", "compras" => $compras, "query" => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron compras registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Compra.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCompra($post)
    {
        $idcompra = mysqli_real_escape_string($this->con, $post["idcompra"]);

        try {
            $query = "
            select
                *
            from
                vcompras
            where
                idcompra = '" . $idcompra . "'
            ";
            $compra = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($compra["idcompra"] > 0) {
                $respuesta = array("respuesta" => "OK", "compra" => $compra);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la compra.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos Compra.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProductosCompra($post)
    {
        $idcompra = mysqli_real_escape_string($this->con, $post["idcompra"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vcompraproductos
            where
                idcompra = '" . $idcompra . "'
            order by
                idproducto,color,talla_posicion
            ";
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vcompraproductos
                where
                    idcompra = '" . $idcompra . "'
                order by
                    idproducto,color,talla_posicion
                ";

                // $this->claseQueries->guardarQuery($query);

                $productos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "productos" => $productos, "query" => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la compra.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Recibir Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function recibirProducto($post)
    {

        $idcompra = mysqli_real_escape_string($this->con, $post["idcompra"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["slcAlmacen"]);

        $idcompraproductos = $post["idcompraproductos"];
        $cantidades = $post["txtCantidades"];

        $post["idmovimiento"] = $idcompra;
        $post["origenmovimiento"] = "C";
        $post["tipomovimiento"] = "E";
        $post["idalmacen"] = $idalmacen;
        $fecha = date("Y-m-d H:i:s");
        $correcto = true;

        $idrecepcion = "";

        try {
            if ($tipo != 0) {
                $i = 0;
                //trecepciones
                $query = "
                insert into
                            trecepciones 
                            (
                                idcompra,
                                idusuario,
                                registro
                            )
                        values 
                            (
                                '" . $idcompra . "',
                                '" . $idusuario . "',
                                '" . $fecha . "'
                            )";
                if (mysqli_query($this->con, $query)) {
                    $idrecepcion = mysqli_insert_id($this->con);
                    $correcto = true;
                }
                foreach ($idcompraproductos as $idcompraproducto) {
                    $idcompraproducto = mysqli_real_escape_string($this->con, $idcompraproducto);
                    $cantidad = mysqli_real_escape_string($this->con, $cantidades[$i]);
                    if ($cantidad > 0) {
                        $query = "
                        update
                            trcompraproductos
                        set
                            cantidad_recibida = cantidad_recibida + " . $cantidad . "
                        where
                            idcompraproducto = '" . $idcompraproducto . "'
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }

                        $query = "
                        select
                            *
                        from
                            trcompraproductos
                        where
                            idcompraproducto = '" . $idcompraproducto . "'
                        ";

                        $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));
                        $query = "
                        insert into
                            trecepcionproductos
                            (
                                idrecepcion,
                                idcompraproducto,
                                idproducto,
                                producto,
                                idtalla,
                                talla,
                                idcolor,
                                color,
                                cantidad
                            )
                        values
                            (
                                '" . $idrecepcion . "',
                                '" . $idcompraproducto . "',
                                '" . $partida["idproducto"] . "',
                                '" . $partida["producto"] . "',
                                '" . $partida["idtalla"] . "',
                                '" . $partida["talla"] . "',
                                '" . $partida["idcolor"] . "',
                                '" . $partida["color"] . "',
                                '" . $cantidad . "'
                            )
                        ";

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                        if ($partida["idproducto"] > 0) {

                            $post["idproducto"] = $partida["idproducto"];
                            $post["idtalla"] = $partida["idtalla"];
                            $post["idcolor"] = $partida["idcolor"];
                            $post["cantidad"] = $cantidad;

                            $this->claseProductos = new Productos();
                            if ($this->claseProductos->actualizarExistencia($post)["respuesta"] == "ERROR") {
                                $correcto = false;
                            }
                        }
                    }
                    $i++;
                }
            }

            $mensaje = "Los productos de la compra han sido recibidos exitosamente";
            $titulo = "Compra Recibida";

            // tipo:
            // 0 = CANCELAR
            // 1 = RECIBIR PARCIALMENTE
            // 2 = RECIBIR Y FINALIZAR
            // 3 = RECIBIR Y SOLICITAR
            if ($tipo == 0) {
                $mensaje = "La compra ha sido cancelada exitosamente";
                $titulo = "Compra Cancelada";
                $query = "
                update
                    tcompras
                set
                    status = 'C'
                where
                    idcompra = '" . $idcompra . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            } else if ($tipo == 1) {
                // si los productos han sido totalmente recibidos, el status cambia a 'R'; si han sido parcialmente, el status cambia a 'P'
                $query = "
                select
                    count(*) as total_registros,
                    sum(cantidad = cantidad_recibida) as registros_completos
                from
                    trcompraproductos
                where
                    idcompra = '" . $idcompra . "'
                ";

                $compra = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                $query = "
                update
                    tcompras
                set
                    status = '" . (($compra["total_registros"] == $compra["registros_completos"]) ? 'R' : 'P') . "',
                    fecharecepcion = '" . $fecha . "'
                where
                    idcompra = '" . $idcompra . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            } else if ($tipo == 2) {
                $query = "
                update
                    tcompras
                set
                    status = 'R',
                    fecharecepcion = '" . $fecha . "'
                where
                    idcompra = '" . $idcompra . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                // la cantidad se actualiza (se iguala) a la cantidad recibida
                $query = "
                update
                    trcompraproductos
                set
                    cantidad = cantidad_recibida
                where
                    idcompra = '" . $idcompra . "'
                    and cantidad_recibida > 0
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    trcompraproductos
                where
                    idcompra = '" . $idcompra . "'
                    and cantidad_recibida = 0
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            } else if ($tipo == 3) {
                $query = "
                update
                    tcompras
                set
                    status = 'R',
                    fecharecepcion = '" . $fecha . "'
                where
                    idcompra = '" . $idcompra . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                // la cantidad se actualiza (se iguala) a la cantidad recibida
                $query = "
                update
                    trcompraproductos
                set
                    cantidad = cantidad_recibida
                where
                    idcompra = '" . $idcompra . "'
                    and cantidad_recibida>0
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                select
                    *
                from
                    trcompraproductos
                where
                    idcompra = '" . $idcompra . "'
                    and cantidad>cantidad_recibida
                ";

                $productos = mysqli_query($this->con, $query);

                foreach ($productos as $producto) {
                    $query = "
                    select
                        *
                    from
                        tsolicitudescompra
                    where
                        idsolicitudcompra = '" . $producto["idsolicitudcompra"] . "'
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
                            '" . $producto["idusuario"] . "',
                            '" . $producto["idpedido"] . "',
                            '" . $producto["idproveedor"] . "',
                            '" . $producto["idproducto"] . "',
                            '" . $producto["idcategoriaproducto"] . "',
                            '" . $producto["producto"] . "',
                            '" . $producto["idtalla"] . "',
                            '" . $producto["talla"] . "',
                            '" . $producto["idcolor"] . "',
                            '" . $producto["color"] . "',
                            '" . ($producto["cantidad"] - $producto["cantidad_recibida"]) . "',
                            '" . $producto["notas"] . "',
                            '" . $fecha . "'
                        )
                    ";
                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {

                $post["idactividad"] = (($tipo != 0) ? 38 : 39);
                $post["comentarios"] = "Se " . (($tipo != 0) ? "recibio" . (($tipo == 1) ? " parcialmente" : "") : "cancelo") . " la compra #" . $idcompra;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar" . (($tipo == 1) ? "2" : ""), "titulo" => $titulo, "mensaje" => $mensaje, "formulario" => (($tipo == 1) ? "formRecibir" : "formBusqueda"));
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "Ocurrió un error al recibir los productos.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    public function obtenerRecepciones($post)
    {
        $idcompra = mysqli_real_escape_string($this->con, $post["idcompra"]);
        $idrecepcion = mysqli_real_escape_string($this->con, $post["idrecepcion"]);
        $where = "";
        $where .= ($idrecepcion != "") ? "and idrecepcion='" . $idrecepcion . "'" : "";
        try {
            $query = "
            select
                *
            from
                trecepciones
            where
                idcompra = '" . $idcompra . "'
                " . $where . "
            ";
            $this->claseQueries->guardarQuery($query);

            $recepciones = mysqli_query($this->con, $query);

            if (mysqli_num_rows($recepciones) > 0) {
                $arrayRecepciones = array();
                while ($recepcion = mysqli_fetch_assoc($recepciones)) {
                    $idusuario = $recepcion["idusuario"];
                    $query = "
                    select 
                        *
                    from
                        tusuarios
                    where
                        idusuario = '" . $idusuario . "'
                    ";
                    $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query))["nombre"];
                    $recepcion["usuario"] = $usuario;

                    $arrayRecepciones[] = $recepcion;
                }
                $respuesta = array("respuesta" => "OK", "recepciones" =>  $arrayRecepciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron recepciones registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    public function obtenerRecepcionProductos($post)
    {
        $idrecepcion = mysqli_real_escape_string($this->con, $post["idrecepcion"]);
        try {
            $query = "
            select
                *
            from
                trecepcionproductos
            where
                idrecepcion = '" . $idrecepcion . "'
            ";
            $this->claseQueries->guardarQuery($query);

            $productos = mysqli_query($this->con, $query);
            if (mysqli_num_rows($productos) > 0) {
                $respuesta = array("respuesta" => "OK", "productos" => $productos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron recepciones registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Productos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarProductos($post)
    {
        $idalmacen = mysqli_real_escape_string($this->con, $post["slcAlmacen"]);

        $this->claseProductos = new Productos();

        $correcto = true;

        $fecha = date("Y-m-d H:i:s");

        try {

            foreach ($post["cantidades"] as $key => $cantidad) {
                $cantidad = mysqli_real_escape_string($this->con, $cantidad);
                $idpedido = mysqli_real_escape_string($this->con, $post["idpedidos"][$key]);
                $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificaciones"][$key]);
                $idproducto = mysqli_real_escape_string($this->con, $post["idproductos"][$key]);
                $idtalla = mysqli_real_escape_string($this->con, $post["idtallas"][$key]);
                $idcolor = mysqli_real_escape_string($this->con, $post["idcolores"][$key]);
                $idcompraproducto = mysqli_real_escape_string($this->con, $post["idcompraproductos2"][$key]);

                // actualiza pedido (statusproduccion=P)
                $query = "
                update
                    tpedidos
                set
                    statusproduccion = 'P'
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                select
                    *
                from
                    tespecificaciones
                where
                    idespecificacion = '" . $idespecificacion . "'
                ";

                $especificacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                $query = "
                select
                    *
                from
                    vespecificacionesproductos
                where
                    idespecificacion = '" . $especificacion["idespecificacion"] . "'
                    and idcolor = '" . $idcolor . "'
                    and idtalla = '" . $idtalla . "'
                    and idproducto = '" . $idproducto . "'
                ";

                $especificacionproducto = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                $query = "
                select
                    *
                from
                    vespecificacionesproductos
                where
                    idespecificacionproducto = '" . $especificacionproducto["idespecificacionproducto"] . "'
                ";

                $cantespecificacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                if ($cantidad > $cantespecificacion["cantidad"] - $cantespecificacion["cantidad_surtida"]) {
                    $cantidad = $cantespecificacion["cantidad"] - $cantespecificacion["cantidad_surtida"];
                }

                $post["idalmacen"] = $idalmacen;
                $post["idproducto"] = $idproducto;
                $post["idtalla"] = $idtalla;
                $post["idcolor"] = $idcolor;
                $cantidades = $this->claseProductos->obtenerExistenciasYReservadoProducto($post);
                $existencias = $cantidades["existencias"];
                $reservado = $cantidades["reservado"];

                if ($cantidad > $existencias - $reservado) {
                    $cantidad = $existencias - $reservado;
                }

                $query = "
                update
                    trespecificacionesproductos
                set
                    cantidad_surtida = cantidad_surtida + '" . $cantidad . "'
                where
                    idespecificacionproducto = '" . $especificacionproducto["idespecificacionproducto"] . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                update
                    trcompraproductos
                set
                    cantidad_asignada = cantidad_asignada + '" . $cantidad . "'
                where
                    idcompraproducto = '" . $idcompraproducto . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                select
                    *
                from
                    trpedidoproductos
                where
                    idpedidoproducto = '" . $especificacionproducto["idpedidoproducto"] . "'
                ";

                $desglose = mysqli_fetch_assoc(mysqli_query($this->con, $query));
                if ($desglose["idproducto"] > 0) {
                    $post["idmovimiento"] = $idpedido;
                    $post["origenmovimiento"] = "P";
                    $post["tipomovimiento"] = "S";
                    $post["idalmacen"] = $idalmacen;

                    $post["idproducto"] = $idproducto;
                    $post["idtalla"] = $idtalla;
                    $post["idcolor"] = $idcolor;
                    $post["cantidad"] = $cantidad;
                    $this->claseProductos = new Productos();
                    if ($this->claseProductos->actualizarExistencia($post)["respuesta"] == "ERROR") {
                        $correcto = false;
                    }
                } else {

                    $query = "
                    update
                        trcompraproductos
                    set
                        utilizado = 1
                    where
                        idcompraproducto = '" . $idcompraproducto . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }

                $query = "
                select
                    *
                from
                    trespecificacionesproductos
                where
                    idespecificacion = '" . $especificacion["idespecificacion"] . "'
                ";
                $terminado = true;
                $especificaciones = mysqli_query($this->con, $query);
                foreach ($especificaciones as $especificacion) {
                    $query = "
                    select
                        *
                    from
                        trpedidoproductos
                    where
                        idpedidoproducto='" . $especificacion["idpedidoproducto"] . "'
                    ";
                    $desglose = mysqli_query($this->con, $query);
                    if (mysqli_num_rows($desglose) > 0) {
                        $desglose = mysqli_fetch_assoc($desglose);
                        if ($especificacion["cantidad_surtida"] < $desglose["cantidad"]) {
                            $terminado = false;
                        }
                    }
                }

                if ($terminado) {
                    $status = 1;

                    $query = "
                    select
                        count(*) as total
                    from
                        tespecificaciones
                    where
                        idespecificacion = '" . $especificacion["idespecificacion"] . "'
                        and statusproduccion = 2
                        and statusdiseno = 5
                    ";

                    $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                    if ($total) {
                        $status = 2;
                    }

                    $query = "
                    update
                        tespecificaciones
                    set
                        statusalmacen = 1,
                        fechastatusalmacen = '" . $fecha . "',
                        status = '" . $status . "'
                    where
                        idespecificacion = '" . $especificacion["idespecificacion"] . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Productos Asignados", "mensaje" => "Se asignaron los productos correctamente.", "formulario" => "formRecibir");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al asignar los productos.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Al Menos Uno Sin Asignar. Verifica si al menos una partida no está asignada y todavía falta producto por surtir para sus especificaciones.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function alMenosUnoSinAsignar($post)
    {
        $idcompra = mysqli_real_escape_string($this->con, $post["idcompra"]);

        $correcto = false;

        try {
            $query = "
            select
                *
            from
                vcompraproductos
            where
                idcompra = '" . $idcompra . "'
            ";

            $productos = mysqli_query($this->con, $query);

            foreach ($productos as $producto) {

                if ($producto["cantidad_recibida"] > $producto["cantidad_asignada"]) {
                    $query = "
                    select
                        *
                    from
                        vespecificacionesproductos
                    where
                        idespecificacion = '" . $producto["idespecificacion"] . "'
                        and idproducto = '" . $producto["idproducto"] . "'
                        and idtalla = '" . $producto["idtalla"] . "'
                        and idcolor = '" . $producto["idcolor"] . "'
                    ";

                    $especificacionproducto = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                    if ($especificacionproducto["cantidad"] > $especificacionproducto["cantidad_surtida"]) {
                        $correcto = true;
                        break;
                    }
                }
            }

            $respuesta = $correcto;
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Esta Surtido. Verifica si la partida ya está surtida en producción.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function estaSurtido($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);

        try {
            $query = "
            select
                *
            from
                tespecificaciones
            where
                idpedido = '" . $idpedido . "'
                and idpedido != 0
                and idespecificacion in
                (
                    select
                        idespecificacion
                    from
                        vespecificacionesproductos
                    where
                        idpedido = '" . $idpedido . "'
                        and idcolor = '" . $idcolor . "'
                        and idtalla = '" . $idtalla . "'
                        and idproducto = '" . $idproducto . "'
                )
            ";

            $especificacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            $query = "
            select
                *
            from
                vespecificacionesproductos
            where
                idespecificacion = '" . $especificacion["idespecificacion"] . "'
                and idcolor = '" . $idcolor . "'
                and idtalla = '" . $idtalla . "'
                and idproducto = '" . $idproducto . "'
            ";

            $especificacionproducto = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($especificacionproducto["cantidad_surtida"] == $cantidad) {
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
     * Almacen Con Disponibilidad De Producto. Revisa si hay al menos una unidad no reservada del producto, talla y color especificado en el almacen indicado.
     * 
     * ANTES: Revisa si el almacén dispone de existencias suficientes para el producto especificado.
     * 
     * @access public
     * @param array $post
     * @return boolean
     */
    public function almacenConDisponibilidad($post)
    {
        $idalmacen = mysqli_real_escape_string($this->con, $post["slcAlmacen"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["producto"]["idproducto"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["producto"]["idtalla"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["producto"]["idcolor"]);
        // $cantidad = mysqli_real_escape_string($this->con,$post["cantidad"]);

        $this->claseProductos = new Productos();

        try {

            $post["idproducto"] = $idproducto;
            $post["idalmacen"] = $idalmacen;
            $post["idtalla"] = $idtalla;
            $post["idcolor"] = $idcolor;
            $cantidades = $this->claseProductos->obtenerExistenciasYReservadoProducto($post);

            $existencias = $cantidades["existencias"];
            $reservado = $cantidades["reservado"];

            if ($existencias - $reservado > 0) {
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
}
