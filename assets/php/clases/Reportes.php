<?php
class Reportes
{
    public $con;
    private $claseQueries;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();

        // aumentar el limite de caracteres que puede contener un group_concat
        $query = "
        SET SESSION group_concat_max_len = 1000000
        ";
        mysqli_query($this->con, $query);
    }

    /**
     * Obtener Ventas (ttickets).
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVentas($post)
    {
        // filtros
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $idvendedor = mysqli_real_escape_string($this->con, $post["slcVendedor"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        // NOTA: este dato se necesita para cortegeneral.php y detalleventas.php. de momento estamos reutilizando la funcion, si no pasa nada, se usara asi
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $idticket = mysqli_real_escape_string($this->con, $post["idticket"]);

        $where = "";
        if ($idcorte != "" && $idcorte != 0) {
            $where .= " and idcorte = '" . $idcorte . "'";
        } else if ($idticket != "" && $idticket != 0) {
            $where .= " and t.idticket = '" . $idticket . "'";
        } else {
            // $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");
            $where .= (($idsucursal != "" && $idsucursal != 0) ? " and idsucursal = '" . $idsucursal . "'" : "");
            $where .= (($idvendedor != "") ? " and idvendedor = '" . $idvendedor . "'" : "");
            $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
            $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");
        }

        try {
            $query = "
            select
                count(*) as total
            from
                vtickets t
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
                    vtickets t
                left join
                    (
                        select
                            idcuenta,
                            tipocuenta
                        from
                            tcuentas
                    ) as c
                on
                    t.idcuenta = c.idcuenta
                left join
                    (
                        select
                            idticket, 
                            group_concat(distinct concat(idformapago,':',cast(total as char)),':',nombre separator '-') as formaspago
                        from
                            (
                                select
                                    t.idticket,
                                    f.idformapago,
                                    c.nombre,
                                    sum(f.monto) as total
                                from
                                    tformaspagoticket f
                                left join
                                    ttickets t
                                on
                                    f.idticket = t.idticket
                                left join
                                    tcatformaspago c
                                on
                                    f.idformapago = c.idformapago
                                group by
                                    t.idticket,f.idformapago
                                having
                                    total > 0
                            ) totalesporticket
                        group by idticket        
                    ) as totales
                on
                    t.idticket = totales.idticket
                left join
                    (
                        select
                            t.idcuenta as id,
                            idticket,
                            idvendedor,
                            group_concat(cantidad separator ',') as cantidades,
                            group_concat(idproducto separator ',') as idproductos,
                            group_concat(producto separator '-_-') as productos,
                            group_concat(idcolor separator ',') as idcolores,
                            group_concat(color separator '-_-') as colores,
                            group_concat(idtalla separator ',') as idtallas,
                            group_concat(talla separator '-_-') as tallas,
                            group_concat(c.subtotal separator ',') as subtotales,
                            group_concat(c.iva separator ',') as ivas,
                            group_concat(c.total separator ',') as totales,
                            group_concat(c.descuento separator ',') as descuentos
                        from
                            ttickets t
                        inner join
                            vrcuentaproductos c
                        on
                            t.idcuenta = c.idcuenta
                        group by
                            t.idticket
                        union all
                        select
                            t.idpedido,
                            idticket,
                            idvendedor,
                            group_concat(cantidad separator ',') as cantidades,
                            group_concat(idproducto separator ',') as idproductos,
                            group_concat(producto separator '-_-') as productos,
                            group_concat(idcolor separator ',') as idcolores,
                            group_concat(color separator '-_-') as colores,
                            group_concat(idtalla separator ',') as idtallas,
                            group_concat(talla separator '-_-') as tallas,
                            group_concat(subtotal separator ',') as subtotales,
                            group_concat(iva separator ',') as ivas,
                            group_concat(total separator ',') as totales,
                            group_concat(descuento separator ',') as descuentos
                        from
                            ttickets t
                        inner join
                            vrpedidoproductos p
                        on
                            t.idpedido = p.idpedido
                        group by
                            t.idticket
                    ) as listado_productos
                on
                    t.idticket = listado_productos.idticket
                where
                    1=1
                    " . $where . "
                order by
                    fecha desc
                ";

                $ventas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "ventas" => $ventas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron ventas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener tipo de impresion del ticket
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTipoImpresionTicket($post)
    {
        $idticket = mysqli_real_escape_string($this->con, $post["idticket"]);
        $idcuenta = mysqli_real_escape_string($this->con, $post["idcuenta"]);
        $tipocuenta = mysqli_real_escape_string($this->con, $post["tipocuenta"]);

        $tipoimpresion = 1;

        try {

            if ($tipocuenta == "A") {
                $query = "
                select
                    count(*) as total
                from
                    ttickets
                where
                    idcuenta = '" . $idcuenta . "'
                    and idticket < '" . $idticket . "'
                    and status = 'A'
                ";

                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                if ($total) {
                    $tipoimpresion = 3;
                } else {
                    $tipoimpresion = 2;
                }
            } else if ($tipocuenta == "") {
                $tipoimpresion = 4;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $tipoimpresion;
        }
    }

    /**
     * Obtener Ventas por Sucursal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVentasPorSucursal($post)
    {
        // filtros
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $sucursales = $post["slcSucursales"];

        $idsucursales = array();
        if (isset($post["slcSucursales"])) {
            foreach ($sucursales as $idsucursal) {
                $idsucursal = mysqli_real_escape_string($this->con, $idsucursal);
                array_push($idsucursales, $idsucursal);
            }
        }

        $wheresucursal = "";
        $wheresucursal2 = "";
        $wheresucursal .= ((count($idsucursales) > 0) ? " and idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");
        $wheresucursal2 .= ((count($idsucursales) > 0) ? " and s.idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");
        $where = "";
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");

        try {
            $query = "
            select
                count(*) as total
            from
                tsucursales
            where
                status = 'A'
                " . $wheresucursal . "
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($total) {
                $query = "
                select
                    *
                from
                    tsucursales s
                left join
                    (
                        select
                            d.idsucursal,
                            sum(c.total) as totaldevoluciones
                        from
                            vrcuentaproductos c
                        left join
                            vdevoluciones d
                        on
                            c.idcuentaproducto = d.idcuentaproducto
                        where
                            1=1
                            " . $where . "
                        group by
                            d.idsucursal
                    ) as devoluciones
                on
                    s.idsucursal = devoluciones.idsucursal
                left join
                    (
                        select
                            idticket,
                            idsucursal,
                            sum(total) as totalventas
                        from
                            ttickets
                        where
                            1=1
                            " . $where . "
                        group by
                            idsucursal
                    ) as ventas
                on
                    s.idsucursal = ventas.idsucursal
                left join
                    (
                        select
                            idsucursal, 
                            group_concat(distinct concat(idformapago,':',cast(total as char)) separator '-') as formaspago
                        from
                            (
                                select
                                    t.idsucursal,idformapago,sum(f.monto) as total
                                from
                                    ttickets t
                                left join
                                    tformaspagoticket f
                                on
                                    t.idticket = f.idticket
                                where
                                    1=1
                                    " . $where . "
                                group by
                                    t.idsucursal,f.idformapago
                                having
                                    total > 0
                            ) totales
                        group by idsucursal
                    ) as totalesporsucursal
                on
                    s.idsucursal = totalesporsucursal.idsucursal
                where
                    status = 'A'
                    " . $wheresucursal2 . "
                group by 
                    s.idsucursal
                ";


                $ventas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "ventas" => $ventas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron ventas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos Vendidos (vproductosvendidos).
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVentasProductos($post)
    {
        // filtros
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $idvendedor = mysqli_real_escape_string($this->con, $post["slcVendedor"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["slcProducto"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);

        $where = "";
        $where .= (($idsucursal != "" && $idsucursal != 0) ? " and idsucursal = '" . $idsucursal . "'" : "");
        $where .= (($idvendedor != "" && $idvendedor != 0) ? " and idvendedor = '" . $idvendedor . "'" : "");
        $where .= (($idproducto != "" && $idproducto != 0) ? " and idproducto = '" . $idproducto . "'" : "");
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");

        try {
            $query = "
            select
                count(*) as total
            from
                vproductosvendidos
            where
                1=1
                " . $where . "
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    sucursal,
                    vendedor,
                    fecha,
                    producto,
                    color,
                    talla,
                    cantidad,
                    total,
                    ticket
                from
                    vproductosvendidos
                where
                    1=1
                    " . $where . "
                order by
                    fecha desc,
                    producto,
                    color,
                    talla
                ";

                $ventas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "productos" => $ventas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron ventas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cortes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCortes($post)
    {
        // filtros
        $idvendedor = mysqli_real_escape_string($this->con, $post["slcVendedor"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $sucursales = $post["slcSucursales"];

        $idsucursales = array();
        if (isset($post["slcSucursales"])) {
            foreach ($sucursales as $idsucursal) {
                $idsucursal = mysqli_real_escape_string($this->con, $idsucursal);
                array_push($idsucursales, $idsucursal);
            }
        }

        $where = "";
        $wherecorte = "";
        $wheresucursal = "";
        $wheredevolucion = "";

        $wheresucursal .= ((count($idsucursales) > 0) ? " and idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");
        $where .= (($idvendedor != "" && $idvendedor != 0) ? " and idvendedor = '" . $idvendedor . "'" : "");
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");
        $wheredevolucion .= (($idvendedor != "" && $idvendedor != 0) ? " and idvendedor = '" . $idvendedor . "'" : "");
        $wheredevolucion .= (($fechainicial != "") ? " and date(fechainicial) >= '" . $fechainicial . "'" : " and date(fechainicial) >= '" . date("Y-m-d") . "'");
        $wheredevolucion .= (($fechafinal != "") ? " and date(fechainicial) <= '" . $fechafinal . "'" : " and date(fechainicial) <= '" . date("Y-m-d") . "'");
        $wherecorte .= (($fechainicial != "") ? " and date(fechainicial) >= '" . $fechainicial . "'" : " and date(fechainicial) >= '" . date("Y-m-d") . "'");
        $wherecorte .= (($fechafinal != "") ? " and date(fechainicial) <= '" . $fechafinal . "'" : " and date(fechainicial) <= '" . date("Y-m-d") . "'");

        try {
            $query = "
            select
                count(*) as total
            from
                vcortessucursales
            where
                1=1
                " . $wherecorte . "
            order by
                idcorte desc
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    c.*,
                    ifnull(devoluciones.totaldevoluciones,0) as totaldevoluciones,
                    ifnull(totales.formaspago,0) as formaspago,
                    ifnull(ventas.totalventas,0) as totalventas
                from
                    vcortessucursales c
                left join
                    (
                        select
                            d.idcorte,
                            sum(cp.total) as totaldevoluciones
                        from
                            vrcuentaproductos cp
                        left join
                            vdevoluciones d
                        on
                            cp.idcuentaproducto = d.idcuentaproducto
                        left join
                            tcortessucursales cs
                        on
                            d.idcorte = cs.idcorte
                        where
                            1=1
                            " . $wheredevolucion . "
                        group by
                            d.idcorte
                    ) as devoluciones
                on
                    c.idcorte = devoluciones.idcorte
                left join
                    (
                        select
                            idcorte, 
                            group_concat(distinct concat(idformapago,':',cast(total as char)),':',nombre separator '-') as formaspago
                        from
                            (
                                select
                                    t.idcorte,
                                    f.idformapago,
                                    c.nombre,
                                    sum(f.monto) as total
                                from
                                    tformaspagoticket f
                                left join
                                    ttickets t
                                on
                                    f.idticket = t.idticket
                                left join
                                    tcatformaspago c
                                on
                                    f.idformapago = c.idformapago
                                group by
                                    t.idcorte,f.idformapago
                                having
                                    total > 0
                            ) totalesporcorte
                        group by idcorte        
                    ) as totales
                on
                    c.idcorte = totales.idcorte
                left join
                    (
                        select
                            idticket,
                            idcorte,
                            sum(total) as totalventas
                        from
                            ttickets
                        where
                            1=1
                            " . $where . "
                        group by
                            idcorte
                    ) as ventas
                on
                    c.idcorte = ventas.idcorte
                where
                    1=1
                    " . $wheresucursal . "
                    " . $wherecorte . "
                order by
                    c.idcorte desc
                limit
                    100
                ";

                $cortes = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "cortes" => $cortes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron cortes registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Corte.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCorte($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["idsucursal"]);
        $buscarcorteanterior = mysqli_real_escape_string($this->con, $post["buscarcorteanterior"]);

        $where = "";

        if ($buscarcorteanterior == 1) {
            $where .= " and idsucursal = '" . $idsucursal . "' and idcorte < '" . $idcorte . "' and status = 'T' order by idcorte desc limit 1";
        } else {
            $where .= (($idcorte != "") ? " and idcorte = '" . $idcorte . "'" : "");
        }

        try {
            $query = "
            select
                *
            from
                vcortessucursales
            where
                1=1
                " . $where . "
            ";


            $corte = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($corte["idcorte"] > 0) {
                $respuesta = array("respuesta" => "OK", "corte" => $corte);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontro el corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Detalle Corte.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerDetalleCorte($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vcortessucursales
            where
                idcorte = '" . $idcorte . "'
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                /*
                recuperar:
                -datos de la cuenta
                    -tickets
                        -productos asociados a la cuenta del ticket
                */
                $query = "
                select
                    *
                from
                    vcortessucursales c
                left join
                    (
                        select
                            t.idcorte,
                            group_concat(t.idticket separator ',') as tickets,
                            group_concat(t.idcuenta separator ',') as cuentas,
                            group_concat(c.tipocuenta separator ',') as tiposcuenta,
                            group_concat(t.idpedido separator ',') as pedidos,
                            group_concat(t.folio separator ',') as folios,
                            group_concat(t.fecha separator ',') as fechas,
                            group_concat(t.total separator ',') as totales,
                            group_concat(formaspago.formaspago separator ',') as formaspago,
                            group_concat(productoscuenta.productoscuenta separator ',') as productoscuentas
                        from
                            ttickets t
                        left join
                            (
                                select
                                    idticket,
                                    group_concat(f.idformapago,':',c.nombre,':',f.monto order by f.idformapago separator '_') as formaspago
                                from
                                    tformaspagoticket f
                                left join
                                    tcatformaspago c
                                on
                                    f.idformapago = c.idformapago
                                where
                                    monto > 0
                                group by
                                    idticket
                            ) as formaspago
                        on
                            t.idticket = formaspago.idticket
                        left join
                            tcuentas c
                        on
                            t.idcuenta = c.idcuenta
                        left join
                            (
                                select
                                    idcuenta,
                                    group_concat(cantidad,'-_-',producto,' (TALLA: ',ifnull(talla,''),' COLOR: ',ifnull(color,''),')','-_-',total separator ';') as productoscuenta
                                from
                                    vrcuentaproductos
                                group by
                                    idcuenta
                            ) as productoscuenta
                        on
                            t.idcuenta = productoscuenta.idcuenta
                        group by
                            t.idcorte
                    ) as tickets
                on
                    c.idcorte = tickets.idcorte
                where
                    c.idcorte = '" . $idcorte . "'
                group by
                    c.idcorte
                ";


                $corte = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                $respuesta = array("respuesta" => "OK", "corte" => $corte);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontro la informacion del corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Detalle Devoluciones Corte. Obtiene datos de las devoluciones de un corte especifico
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerDetalleDevolucionesCorte($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vtickets 
            where
                idcuenta in
                (
                    select
                        idcuenta
                    from
                        vrcuentaproductos
                    where
                        idcuentaproducto in
                        (
                            select
                                idcuentaproducto
                            from
                                tdevoluciones
                            where
                                idcorte='" . $idcorte . "'
                        )
                )
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                // por si quieres obtener resultados, busca con estos cortes (12,33,77,79,80,84,96,92,103,114,120,137,132,136,138,143,141,145)
                $query = "
                select
                    *
                from
                    vtickets t
                left join
                    (
                        select
                            idcuenta,
                            sum(total) as totaldevolucion
                        from
                            vrcuentaproductos
                        where
                            idcuentaproducto in 
                            (
                                select
                                    idcuentaproducto
                                from
                                    tdevoluciones
                            )
                        group by
                            idcuenta
                    ) as totaldevoluciones
                on
                    t.idcuenta = totaldevoluciones.idcuenta
                where
                    t.idcuenta in
                    (
                        select
                            idcuenta
                        from
                            vrcuentaproductos
                        where
                            idcuentaproducto in
                            (
                                select
                                    idcuentaproducto
                                from
                                    tdevoluciones
                                where
                                    idcorte='" . $idcorte . "'
                            )
                    )
                order by
                    t.idcuenta desc
                ";


                $tickets = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tickets" => $tickets);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron devoluciones para este corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Detalle Devoluciones Ticket. Obtiene datos de las devoluciones de un ticket especifico
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerDetalleDevolucionesTicket($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $idcuenta = mysqli_real_escape_string($this->con, $post["idcuenta"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vrcuentaproductos  
            where
                idcuenta = '" . $idcuenta . "'
                and idcuentaproducto in
                (
                    select
                        idcuentaproducto
                    from
                        tdevoluciones
                    where
                        idcorte = '" . $idcorte . "'
                )
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vtickets t
                left join
                    (
                        select
                            idcuenta,
                            group_concat(vcp.cantidad,'-_-',vcp.producto,',',ifnull(vcp.talla,''),',',ifnull(vcp.color,''),'-_-',p.precio,'-_-',vcp.descuento,'-_-',vcp.total,'-_-',vcp.subtotal,'-_-',vcp.iva separator ';') as productoscuenta
                        from
                            vrcuentaproductos vcp
                        left join
                            tproductos p
                        on
                            vcp.idproducto = p.idproducto
                        group by
                            idcuenta
                    ) as partidas
                on
                    t.idcuenta = partidas.idcuenta
                where
                    t.idcuenta in
                    (
                        select
                            idcuenta
                        from
                            vrcuentaproductos
                        where
                            idcuenta = '" . $idcuenta . "'
                            and idcuentaproducto in
                            (
                                select
                                    idcuentaproducto
                                from
                                    tdevoluciones
                                where
                                    idcorte = '" . $idcorte . "'
                            )
                    )
                ";


                $ticket = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                $respuesta = array("respuesta" => "OK", "ticket" => $ticket);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron devoluciones para este corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Historial Pagos Ticket
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerHistorialPagosTicket($post)
    {
        // filtros
        $idcuenta = mysqli_real_escape_string($this->con, $post["idcuenta"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vtickets  
            where
                idcuenta = '" . $idcuenta . "'
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vtickets  
                where
                    idcuenta = '" . $idcuenta . "'
                order by
                    idticket desc
                ";


                $pagos = mysqli_query($this->con, $query);

                $query = "
                select
                    *
                from
                    tcuentas  
                where
                    idcuenta = '" . $idcuenta . "'
                ";
                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                $respuesta = array("respuesta" => "OK", "pagos" => $pagos, "totalcuenta" => $total);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron pagos para esta cuenta.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Primer Pago Apartado. Revisa si este ticket corresponde al primer pago de apartado
     * 
     * @access public
     * @param array $post
     * @return boolean
     */
    public function primerPagoApartado($post)
    {
        $idcuenta = mysqli_real_escape_string($this->con, $post["idcuenta"]);
        $idticket = mysqli_real_escape_string($this->con, $post["idticket"]);

        try {
            $query = "
            select
                count(*) as total
            from
                ttickets
            where
                idcuenta = '" . $idcuenta . "'
                and idticket < '" . $idticket . "'
                and status = 'A'
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total == 0) {
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

    /**
     * Obtener Movimientos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientos($post)
    {
        // filtros
        $idproducto = mysqli_real_escape_string($this->con, $post["slcProducto"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);

        $where = "";
        $wherev = "";
        $where .= (($idproducto != "" && $idproducto != 0) ? " and idproducto = '" . $idproducto . "'" : "");
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
        $wherev .= (($fechainicial != "") ? " and date(v.fecha) >= '" . $fechainicial . "'" : " and date(v.fecha) >= '" . date("Y-m-d") . "'");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");
        $wherev .= (($fechafinal != "") ? " and date(v.fecha) <= '" . $fechafinal . "'" : " and date(v.fecha) <= '" . date("Y-m-d") . "'");

        try {
            $query = "
            select
                count(*) as total
            from
                vproductomovimientos
            where
                1=1
                " . $where . "
            order by
                idproductomovimiento desc
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    v.*
                from
                    vproductomovimientos v
                left join
                    ttickets t
                on
                    v.idmovimiento = t.idticket
                where
                    1=1
                    " . $wherev . "
                order by
                    idproductomovimiento desc
                ";

                $movimientos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "movimientos" => $movimientos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron movimientos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Inventario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerInventario($post)
    {
        // filtros
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $sucursales = $post["slcSucursales"];

        $idsucursales = array();
        if (isset($post["slcSucursales"])) {
            foreach ($sucursales as $idsucursal) {
                $idsucursal = mysqli_real_escape_string($this->con, $idsucursal);
                array_push($idsucursales, $idsucursal);
            }
        }

        $where = "";
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : " and date(fecha) >= '" . date("Y-m-d") . "'");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : " and date(fecha) <= '" . date("Y-m-d") . "'");
        $wheresucursal = "";
        $wheresucursal .= ((count($idsucursales) > 0) ? " and s.idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tsucursales s
            where
                status = 'A'
                " . $wheresucursal . "
            ";


            $totalsucursal = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            $query = "
            select
                count(*) as total
            from
                vproductomovimientos s
            where
                1=1
                " . $wheresucursal . "
            ";


            $totalinventario = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($totalsucursal && $totalinventario) {
                $query = "
                select
                    *
                from
                    tsucursales s
                left join
                    (
                        select
                            idsucursal,
                            group_concat(fecha order by idproductomovimiento separator ',') as fechas,
                            group_concat(producto order by idproductomovimiento separator ',') as productos,
                            group_concat(origenmovimiento order by idproductomovimiento separator ',') as origenmovimientos,
                            group_concat(tipomovimiento order by idproductomovimiento separator ',') as tipomovimientos,
                            group_concat(almacen order by idproductomovimiento separator ',') as almacenes,
                            group_concat(cantidad order by idproductomovimiento separator ',') as cantidades,
                            group_concat(existencias order by idproductomovimiento separator ',') as existencias,
                            group_concat(existenciasalmacen order by idproductomovimiento separator ',') as existenciasalmacenes
                        from
                            vproductomovimientos
                        where
                            1=1
                            " . $where . "
                        group by
                            idsucursal
                    ) as productos
                on
                    s.idsucursal = productos.idsucursal 
                where
                    s.status = 'A'
                    " . $wheresucursal . "
                group by
                    s.idsucursal
                ";


                $sucursales = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "sucursales" => $sucursales);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron movimientos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Reorden.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerReorden($post)
    {
        // filtros
        $idproducto = mysqli_real_escape_string($this->con, $post["slcProducto"]);
        $almacenes = $post["slcAlmacenes"];

        $idsalmacenes = array();
        if (isset($post["slcAlmacenes"])) {
            foreach ($almacenes as $idalmacen) {
                $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);
                array_push($idsalmacenes, $idalmacen);
            }
        }

        try {
            $query = "
            select
                a.idproducto,
                a.producto,
                a.idcolor,
                a.color,
                a.idtalla,
                a.talla,
                a.idalmacen,
                a.almacen,
                sum(a.existencia + ifnull(b.cantidad,0)) as existencia,
                sum(a.stock_minimo) as stock_minimo,
                sum(a.stock_minimo) - sum(a.existencia+ifnull(b.cantidad,0)) as cantidad_solicitada,
                a.status
            from
                vproductoexistencias a
            left join
                (
                select
                    c.idproducto,
                    c.idcolor,
                    c.idtalla,
                    c.idalmacen,
                    sum(if(c.status = 0,c.cantidad,if(e.status != 'C',d.cantidad-d.cantidad_recibida,0))) as cantidad,
                        d.idsolicitudcompra
                from
                    tsolicitudescompra c
                    left join
                        trcompraproductos d
                    on
                        d.idsolicitudcompra = c.idsolicitudcompra
                    left join
                        tcompras e
                    on
                        e.idcompra = d.idcompra
                    where
                        c.status < 2
                group by
                    c.idproducto,
                    c.idcolor,
                    c.idtalla,
                    c.idalmacen
                having
                    cantidad > 0
                ) b
            on
                b.idproducto = a.idproducto and
                b.idcolor = a.idcolor and
                b.idtalla = a.idtalla and
                b.idalmacen = a.idalmacen
            where
                a.status = 1 and
                a.idalmacen in (" . (implode(",", $idsalmacenes)) . ")
                " . (($idproducto != "" && $idproducto > 0) ? " and a.idproducto = '" . $idproducto . "'" : "") . "
            group by
                a.idalmacen,
                a.idproducto,
                a.idcolor,
                a.idtalla
            having
                sum(a.existencia+ifnull(b.cantidad,0)) < sum(a.stock_minimo) and
                sum(a.stock_minimo) > 0
            ";


            $productos = mysqli_query($this->con, $query);

            $almacenes = array();
            $idalmacen = 0;
            $almacen = array();
            while ($producto = mysqli_fetch_assoc($productos)) {
                if ($idalmacen != $producto["idalmacen"]) {
                    if ($idalmacen > 0) {
                        $almacenes[] = array(
                            "idalmacen" => $almacen["idalmacen"],
                            "almacen" => $almacen["almacen"],
                            "productos" => $arrayProductos
                        );
                    }
                    $idalmacen = $producto["idalmacen"];
                    $arrayProductos = array();
                }
                $arrayProductos[] = $producto;
                $almacen = array(
                    "idalmacen" => $producto["idalmacen"],
                    "almacen" => $producto["almacen"]
                );
            }

            if ($idalmacen > 0) {
                $almacenes[] = array(
                    "idalmacen" => $almacen["idalmacen"],
                    "almacen" => $almacen["almacen"],
                    "productos" => $arrayProductos
                );
            }

            if (count($almacenes) > 0) {
                $respuesta = array("respuesta" => "OK", "almacenes" => $almacenes, 'query' => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron productos registrados.", 'query' => $query);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Generar Solicitudes. A partir del reporte de reorden, se generan las solicitudes (en este caso, todos los productos son siempre de inventario)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function generarSolicitudes($post)
    {
        // filtros
        $idproducto = mysqli_real_escape_string($this->con, $post["slcProducto"]);
        $almacenes = $post["slcAlmacenes"];

        $fecha = date("Y-m-d H:i:s");

        $idsalmacenes = array();
        if (isset($post["slcAlmacenes"])) {
            foreach ($almacenes as $idalmacen) {
                $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);
                array_push($idsalmacenes, $idalmacen);
            }
        }

        try {
            $query = "
            insert into
                tsolicitudescompra
                (
                    idusuario,
                    idproveedor,
                    idproducto,
                    producto,
                    idcolor,
                    color,
                    idtalla,
                    talla,
                    cantidad,
                    fecha
                )
            select
                '" . $idusuario . "',
                b.idproveedor,
                a.idproducto,
                a.producto,
                a.idcolor,
                a.color,
                a.idtalla,
                a.talla,
                sum(a.stock_minimo) - sum(a.existencia) as cantidad_solicitada,
                '" . $fecha . "'
            from
                vproductoexistencias a
            left join
                tproductos b
            on
                a.idproducto = b.idproducto
            where
                a.status = 1
                and a.idalmacen in (" . (implode(",", $idsalmacenes)) . ")
                " . (($idproducto > 0) ? " and a.idproducto = '" . $idproducto . "'" : "") . "
            group by
                a.idalmacen,
                a.idproducto,
                a.idcolor,
                a.idtalla
            having
                sum(existencia) < sum(stock_minimo)
            ";


            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Solicitudes de Compra Generadas", "mensaje" => "Las solicitudes se generaron correctamente.", "formulario" => "formBusqueda");
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
     * Obtener Cuentas por Cobrar.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCXC($post)
    {
        // filtros
        $idcliente = mysqli_real_escape_string($this->con, $post["slcCliente"]);
        $sucursales = $post["slcSucursales"];

        $idsucursales = array();
        if (isset($post["slcSucursales"])) {
            foreach ($sucursales as $idsucursal) {
                $idsucursal = mysqli_real_escape_string($this->con, $idsucursal);
                array_push($idsucursales, $idsucursal);
            }
        }

        $where = " and status = 'A'";
        $wherep = " and p.status = 'A'";
        // $where = "";
        // $wherep = "";
        $wherepedido = "";
        $wherepedidop = "";
        $wheresucursal = "";
        $wheresucursals = "";
        $wherepedido .= (($idcliente != 0) ? " and idcliente = '" . $idcliente . "'" : "");
        $wherepedidop .= (($idcliente != 0) ? " and p.idcliente = '" . $idcliente . "'" : "");
        $wheresucursal .= ((count($idsucursales) > 0) ? " and idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");
        $wheresucursals .= ((count($idsucursales) > 0) ? " and s.idsucursal in (" . (implode(",", $idsucursales)) . ")" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                tsucursales
            where
                idsucursal in
                    (
                        select
                            idsucursal
                        from
                            tpedidos
                        where 
                            abonado < total
                            " . $where . "
                    )
                " . $where . "
                " . $wheresucursal . "
            ";


            $totalsucursal = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            $query = "
            select
                count(*) as total
            from
                vpedidos
            where
                abonado < total
                " . $where . "
                " . $wherepedido . "
            ";


            $totalpedido = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($totalsucursal && $totalpedido) {
                // group_concat(idpedido order by idpedido desc separator ',') as idpedidos,
                $query = "
                select
                    *
                from
                    tsucursales s
                left join
                    (
                        select
                            idsucursal,
                            group_concat(fecha order by idpedido separator '*,') as fechas,
                            group_concat(idpedido order by idpedido separator '*,') as idpedidos,
                            group_concat(if(p.idcliente > 0, c.nombre, p.cliente) order by idpedido separator '*,') as clientes,
                            group_concat(total order by idpedido separator '*,') as totales,
                            group_concat(abonado order by idpedido separator '*,') as abonados,
                            group_concat(p.status order by idpedido separator '*,') as statuspedidos,
                            group_concat(statusproduccion order by idpedido separator '*,') as statusproduccion
                        from
                            vpedidos p
                        left join
                            tclientes c
                        on
                            p.idcliente = c.idcliente
                        where
                            p.abonado < p.total
                            " . $wherep . "
                            " . $wherepedidop . "
                        group by
                            idsucursal
                    ) as pedidos
                on
                    s.idsucursal = pedidos.idsucursal 
                where
                    s.idsucursal in
                    (
                        select
                            idsucursal
                        from
                            tpedidos
                        where 
                            abonado < total
                            " . $where . "
                    )
                    " . $where . "
                    " . $wheresucursals . "
                group by
                    s.idsucursal
                ";

                file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/prub0.txt", $query);
                $sucursales = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "sucursales" => $sucursales);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron cuentas por cobrar.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Corte General.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCorteGeneral($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["idsucursal"]);
        $buscarcorteanterior = mysqli_real_escape_string($this->con, $post["buscarcorteanterior"]);

        $where = "";

        $where .= (($buscarcorteanterior == 1) ? " and idsucursal = '" . $idsucursal . "' and idcorte < '" . $idcorte . "' and status = 'T' order by idcorte desc limit 1" : " and idcorte = '" . $idcorte . "'");

        try {
            $query = "
            select
                *
            from
                vcortessucursales
            where
                1=1
                " . $where . "
            ";


            $corte = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($corte["idcorte"] > 0) {
                $respuesta = array("respuesta" => "OK", "corte" => $corte);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontro el corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tickets.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTickets($post)
    {
        // filtros
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        // tipo: 1 con idpedido, 0 sin idpedido
        $where = "";
        $where .= (($tipo == 1) ? " and idpedido > 0" : " and idpedido = 0");

        try {
            $query = "
            select
                count(*) as total
            from
                vtickets t
            where
                idcorte = '" . $idcorte . "'
                " . $where . "
            ";


            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vtickets t
                where
                    idcorte = '" . $idcorte . "'
                    " . $where . "
                ";

                $tickets = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tickets" => $tickets, "query" => $query);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron tickets.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Corregir Arqueo Corte.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function corregirArqueoCorte($post)
    {
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $feria = mysqli_real_escape_string($this->con, $post["txtFeria"]);

        $correcto = true;

        try {
            $query = "
            select
                *
            from
                tcatformaspago
            ";

            $formaspago = mysqli_query($this->con, $query);

            foreach ($formaspago as $formapago) {
                $total = mysqli_real_escape_string($this->con, $post["txtFormapagoArqueoFancy" . $formapago["idformapago"]]);

                $query = "
                update
                    tcortesucursal_formaspago_arqueo
                set
                    total = '" . $total . "'
                where
                    idcorte = '" . $idcorte . "'
                    and idformapago = '" . $formapago["idformapago"] . "'
                ";

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            $query = "
            update
                tcortessucursales
            set
                feria = '" . $feria . "'
            where
                idcorte = '" . $idcorte . "'
            ";

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $corte = $this->obtenerCorte($post)["corte"];

            $post["buscarcortesiguiente"] = 1;
            $post["idsucursal"] = $corte["idsucursal"];
            // obtener corte siguiente y actualizar el fondoinicial
            $idcortesiguiente = $this->obtenerCorte($post)["corte"]["idcorte"];

            $query = "
            update
                tcortessucursales
            set
                fondoinicial = '" . $feria . "'
            where
                idcorte = '" . $idcortesiguiente . "'
            ";

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecerrarfancy", "titulo" => "Corte Corregido", "mensaje" => "Los datos de arqueo del corte se han actualizado exitosamente.");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar los arqueos del corte.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Monto Arqueo Corte.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMontoArqueoCorte($post)
    {
        $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);
        $idformapago = mysqli_real_escape_string($this->con, $post["idformapago"]);

        try {
            $query = "
            select
                total
            from
                tcortesucursal_formaspago_arqueo
            where
                idcorte = '" . $idcorte . "'
                and idformapago = '" . $idformapago . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            $respuesta = array("respuesta" => "OK", "monto" => $total);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}