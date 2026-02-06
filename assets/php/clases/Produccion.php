<?php
class Produccion
{
    private $con;
    private $claseQueries;
    private $claseImagen;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Imagen.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseImagen = new Imagen();
    }

    /**
     * Obtener Clientes Produccion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerClientesProduccion($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $pagina = mysqli_real_escape_string($this->con, $post["pagina"]);
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $surtido = mysqli_real_escape_string($this->con, $post["slcSurtido"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        // NOTA: los valores de $pendiente no corresponden con el campo con el que se compara
        // $pendiente = mysqli_real_escape_string($this->con,$post["slcPendiente"]);
        $orden = mysqli_real_escape_string($this->con, $post["slcOrden"]);

        try {
            $iconos = array("stop", "play", "check");

            $query = "
            select
                *
            from
                tusuarios
            where
                idusuario = '" . $idusuario . "'
            ";
            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($usuario["tipousuario"] != "ADSB") {
                if (strpos($usuario["tipousuario"], "A") === false) {
                    if (strpos($usuario["tipousuario"], "D") !== false || strpos($usuario["tipousuario"], "S") !== false) {
                        $where = " and serigrafia = 1";
                        $where2 = " and a.serigrafia = 1";
                    }
                    if (strpos($usuario["tipousuario"], "B") !== false) {
                        $where .= " and bordado = 1";
                        $where2 .= " and a.bordado = 1";
                    }
                }
            }

            $pagina = (isset($pagina)) ? $pagina : 1;
            $regpagina = 20;

            $wherec = "";
            if ($busqueda != "") {
                $wherec .= " and (b.cliente like '%" . $busqueda . "%' or b.idpedido = '" . $busqueda . "')";
            }
            if (isset($surtido) and $surtido != -1) {
                $wherec .= " and a.statusalmacen = '" . $surtido . "'";
            }
            if (isset($idsucursal) and $idsucursal != 0) {
                $wherec .= " and b.idsucursal = '" . $idsucursal . "'";
            }
            // if(isset($pendiente) and $pendiente!=-1){
            //     $wherec .= " and a.status = '".$pendiente."'";
            // }

            $orderby = "clientes.fechaentrega";
            if (isset($orden) and $orden != -1) {
                $orderby = " clientes.idpedido " . $orden;
            }


            // NOTA: posible query para obtener los iconos de los diferentes status por cliente directamente
            // $query = "
            // select
            //     clientes.*,
            //     group_concat(clientes.idpedido ORDER BY clientes.idpedido) as pedidos
            // from
            //     (
            //     select
            //         b.idpedido,
            //         b.idcliente,
            //         b.cliente,
            //         a.idespecificacion,
            //         a.fechaentrega,
            //         case
            //             when a.statusdiseno > 0 and a.statusdiseno < 5 then 'play'
            //             when a.statusdiseno = 5 then 'check'
            //             when a.statusdiseno = 0 then 'stop'
            //         end as icono_diseno,
            //         case
            //             when a.statusdiseno > 0 and a.statusdiseno < 5 then 'warning'
            //             when a.statusdiseno = 5 then 'success'
            //             when a.statusdiseno = 0 then 'danger'
            //         end as status_diseno,
            //         case
            //             when a.statusproduccion > 0 and a.statusproduccion < 2 then 'play'
            //             when a.statusproduccion = 2 or a.statusproduccion = 5 then 'check'
            //             when a.statusproduccion = 0 then 'stop'
            //         end as icono_produccion,
            //         case
            //             when a.statusproduccion > 0 and a.statusproduccion < 2 then 'warning'
            //             when a.statusproduccion = 2 or a.statusproduccion = 5 then 'success'
            //             when a.statusproduccion = 0 then 'danger'
            //         end as status_produccion,
            //         ifnull(case
            //             when a.cantidadsurtida = 0 then 'stop'
            //             when a.cantidadrequerida > a.cantidadsurtida then 'play'
            //             when a.cantidadrequerida = a.cantidadsurtida then 'check'
            //         end,0) as icono_almacen,
            //         ifnull(case
            //             when a.cantidadsurtida = 0 then 'danger'
            //             when a.cantidadrequerida > a.cantidadsurtida then 'warning'
            //             when a.cantidadrequerida = a.cantidadsurtida then 'success'
            //         end,0) as status_almacen
            //     from
            //         vespecificaciones a
            //     left join
            //         vpedidos b
            //     on
            //         b.idpedido = a.idpedido
            //     where
            //         a.statusproduccion != 2 and
            //         b.statusproduccion != 'T' and
            //         b.pendiente = 1 and
            //         b.status not in ('C','E')
            //         ".$wherec."
            //         ".$where2."
            //     order by
            //         a.fechaentrega
            //     ) as clientes
            // group by
            //     clientes.idcliente,
            //     clientes.cliente
            //     order by
            //         ".$orderby."
            //     limit 
            //         ".(($pagina-1)*$regpagina).",".$regpagina;

            $query = "
            select
                clientes.*,
                group_concat(clientes.idpedido ORDER BY clientes.idpedido) as pedidos
            from
                (
                select
                    b.idpedido,
                    b.idcliente,
                    b.cliente,
                    a.idespecificacion,
                    a.fechaentrega
                from
                    tespecificaciones a
                left join
                    vpedidos b
                on
                    b.idpedido = a.idpedido
                where
                    a.status != 2 and
                    b.statusproduccion != 'T' and
                    b.pendiente = 1 and
                    b.status not in ('C','E')
                    " . $wherec . "
                    " . $where2 . "
                order by
                    a.fechaentrega
                ) as clientes
            group by
                clientes.idcliente,
                clientes.cliente
            order by
                " . $orderby . "
            limit 
                " . (($pagina - 1) * $regpagina) . "," . $regpagina;

            $clientes = mysqli_query($this->con, $query);

            $query = "
            select
                count(*) as total
            from
                (
                    select
                        clientes.*,
                        group_concat(clientes.idpedido ORDER BY clientes.idpedido) as pedidos
                    from
                        (
                        select
                            b.idpedido,
                            b.idcliente,
                            b.cliente,
                            a.idespecificacion,
                            a.fechaentrega
                        from
                            tespecificaciones a
                        left join
                            vpedidos b
                        on
                            b.idpedido = a.idpedido
                        where
                            a.status != 2 and
                            b.statusproduccion != 'T' and
                            b.pendiente = 1 and
                            b.status not in ('C','E')
                            " . $wherec . "
                            " . $where2 . "
                        order by
                            a.fechaentrega
                        ) as clientes
                    group by
                        clientes.idcliente,
                        clientes.cliente
                ) a
            ";

            $numpaginas = ceil(mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"] / $regpagina);
            $maxpaginas = 3;

            $arrayClientes = array();
            while ($cliente = mysqli_fetch_assoc($clientes)) {
                $cliente["pedidos"] = array_unique(explode(",", $cliente["pedidos"]));
                $cliente["total_pedidos"] = count($cliente["pedidos"]);

                if ($cliente["idcliente"] > 0) {
                    // //status diseño
                    // $query = "
                    // select
                    //     *
                    // from
                    //     tespecificaciones
                    // where
                    //     rdiseno = 1 and
                    //     statusdiseno > 0 and
                    //     statusproduccion != 2
                    //     " . $where . " and
                    //     idpedido in
                    //     (
                    //         select
                    //             idpedido
                    //         from
                    //             tpedidos
                    //         where
                    //             idcliente = '" . $cliente["idcliente"] . "'
                    //             and statusproduccion != 'T'
                    //             and pendiente = 1
                    //     )
                    // ";

                    // if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                    //     $query = "
                    //     select
                    //         *
                    //     from
                    //         tespecificaciones
                    //     where
                    //         rdiseno = 1 and
                    //         statusdiseno < 5 and
                    //         statusproduccion != 2
                    //         " . $where . " and
                    //         idpedido in
                    //         (
                    //             select
                    //                 idpedido
                    //             from
                    //                 tpedidos
                    //             where
                    //                 idcliente = '" . $cliente["idcliente"] . "'
                    //                 and statusproduccion != 'T'
                    //                 and pendiente = 1
                    //         )
                    //     ";

                    //     // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba2.txt",$query);

                    //     if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                    //         $cliente["icono_diseno"] = $iconos[1];
                    //         $cliente["status_diseno"] = "warning";
                    //     } else {
                    //         $cliente["icono_diseno"] = $iconos[2];
                    //         $cliente["status_diseno"] = "success";
                    //     }
                    // } else {
                    //     $query = "
                    //     select
                    //         *
                    //     from
                    //         tespecificaciones
                    //     where
                    //         rdiseno = 1
                    //         and statusdiseno < 5
                    //         " . $where . " and
                    //         idpedido in
                    //         (
                    //             select
                    //                 idpedido
                    //             from
                    //                 tpedidos
                    //             where
                    //                 idcliente = '" . $cliente["idcliente"] . "'
                    //                 and statusproduccion != 'T'
                    //                 and pendiente = 1
                    //         )
                    //     ";

                    //     // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba3.txt",$query);

                    //     if (mysqli_num_rows(mysqli_query($this->con, $query)) == 0) {
                    //         $cliente["icono_diseno"] = $iconos[2];
                    //         $cliente["status_diseno"] = "success";
                    //     } else {
                    //         $cliente["icono_diseno"] = $iconos[0];
                    //         $cliente["status_diseno"] = "danger";
                    //     }
                    // }

                    //status produccion
                    $query = "
                    select
                        *
                    from
                        tespecificaciones
                    where
                        statusproduccion < 2
                        " . $where . " and
                        idpedido in
                        (
                            select
                                idpedido
                            from
                                tpedidos
                            where
                                idcliente = '" . $cliente["idcliente"] . "'
                                and statusproduccion != 'T'
                                and pendiente = 1
                        )
                    ";

                    if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                        $query = "
                        select
                            *
                        from
                            tespecificaciones
                        where
                            statusproduccion = 1
                            " . $where . " and
                            idpedido in
                            (
                                select
                                    idpedido
                                from
                                    tpedidos
                                where
                                    idcliente = '" . $cliente["idcliente"] . "'
                                    and statusproduccion != 'T'
                                    and pendiente = 1
                            )
                        ";

                        if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                            $cliente["icono_produccion"] = $iconos[1];
                            $cliente["status_produccion"] = "warning";
                        } else {
                            $cliente["icono_produccion"] = $iconos[0];
                            $cliente["status_produccion"] = "danger";
                        }
                    } else {
                        $cliente["icono_produccion"] = $iconos[2];
                        $cliente["status_produccion"] = "success";
                    }

                    //status almacen
                    $query = "
                    select
                        sum(cantidad_surtida) as cantidadsurtida
                    from
                        trespecificacionesproductos
                    where
                        idespecificacion in
                        (
                            select
                                idespecificacion
                            from
                                tespecificaciones
                            where
                                status != 2
                                " . $where . " and
                                idpedido in
                                (
                                    select
                                        idpedido
                                    from
                                        vpedidos
                                    where
                                        idcliente = '" . $cliente["idcliente"] . "'
                                        and statusproduccion != 'T'
                                        and pendiente = 1
                                )
                        )
                    ";

                    // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba6.txt",$query);

                    // $this->claseQueries->guardarQuery($query);
                    $cantidadsurtida = mysqli_fetch_assoc(mysqli_query($this->con, $query))["cantidadsurtida"];
                    if ($cantidadsurtida > 0) {
                        $query = "
                        select
                            sum(cantidad) as cantidadrequerida
                        from
                            trpedidoproductos
                        where
                            idpedidoproducto in
                            (
                                select
                                    idpedidoproducto
                                from
                                    trespecificacionesproductos
                                where
                                    idespecificacion in
                                    (
                                        select 
                                            idespecificacion 
                                        from 
                                            tespecificaciones 
                                        where
                                            status != 2
                                            " . $where . " and 
                                            idpedido in 
                                            (
                                                select 
                                                    idpedido 
                                                from 
                                                    vpedidos 
                                                where 
                                                    idcliente = '" . $cliente["idcliente"] . "'
                                                    and statusproduccion != 'T'
                                                    and pendiente = 1
                                            )
                                    )
                            )
                        ";

                        // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba7.txt",$query);

                        $cantidadrequerida = mysqli_fetch_assoc(mysqli_query($this->con, $query))["cantidadrequerida"];
                        if ($cantidadrequerida > $cantidadsurtida) {
                            $cliente["icono_almacen"] = $iconos[1];
                            $cliente["status_almacen"] = "warning";
                        } else {
                            $cliente["icono_almacen"] = $iconos[2];
                            $cliente["status_almacen"] = "success";
                        }
                    } else {
                        $cliente["icono_almacen"] = $iconos[0];
                        $cliente["status_almacen"] = "danger";
                    }

                    $cliente["url"] = "/" . $cliente["idcliente"];
                } else {
                    // //status diseño
                    // $query = "
                    // select
                    //     *
                    // from
                    //     tespecificaciones
                    // where
                    //     rdiseno = 1 and
                    //     statusdiseno > 0 and
                    //     statusproduccion != 2
                    //     " . $where . " and
                    //     idpedido in
                    //     (
                    //         select
                    //             idpedido
                    //         from 
                    //             vpedidos
                    //         where
                    //             idcliente = '" . $cliente["idcliente"] . "'
                    //             and cliente = '" . $cliente["cliente"] . "'
                    //             and statusproduccion != 'T'
                    //             and pendiente = 1
                    //     )
                    // ";

                    // if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                    //     $query = "
                    //     select
                    //         *
                    //     from
                    //         tespecificaciones
                    //     where
                    //         rdiseno=1 and
                    //         statusdiseno < 5 and
                    //         statusproduccion!=2
                    //         " . $where . " and
                    //         idpedido in
                    //         (
                    //             select
                    //                 idpedido
                    //             from
                    //                 vpedidos
                    //             where
                    //                 idcliente = '" . $cliente["idcliente"] . "'
                    //                 and cliente = '" . $cliente["cliente"] . "'
                    //                 and statusproduccion != 'T'
                    //                 and pendiente = 1
                    //         )
                    //     ";

                    //     // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba9.txt",$query);

                    //     if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                    //         $cliente["icono_diseno"] = $iconos[1];
                    //         $cliente["status_diseno"] = "warning";
                    //     } else {
                    //         $cliente["icono_diseno"] = $iconos[2];
                    //         $cliente["status_diseno"] = "success";
                    //     }
                    // } else {
                    //     $query = "
                    //     select 
                    //         * 
                    //     from 
                    //         tespecificaciones 
                    //     where 
                    //         rdiseno=1 
                    //         " . $where . " and 
                    //         idpedido in 
                    //         (
                    //             select 
                    //                 idpedido 
                    //             from 
                    //                 vpedidos 
                    //             where 
                    //                 idcliente = '" . $cliente["idcliente"] . "'
                    //                 and cliente = '" . $cliente["cliente"] . "'
                    //                 and statusproduccion != 'T'
                    //                 and pendiente = 1
                    //         )
                    //     ";

                    //     // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba10.txt",$query);
                    //     if (mysqli_num_rows(mysqli_query($this->con, $query)) == 0) {
                    //         $cliente["icono_diseno"] = $iconos[2];
                    //         $cliente["status_diseno"] = "success";
                    //     } else {
                    //         $cliente["icono_diseno"] = $iconos[0];
                    //         $cliente["status_diseno"] = "danger";
                    //     }
                    // }

                    //status produccion
                    $query = "
                    select
                        *
                    from
                        tespecificaciones
                    where
                        statusproduccion < 2
                        " . $where . " and
                        idpedido in
                        (
                            select
                                idpedido
                            from
                                vpedidos
                            where
                                idcliente = '" . $cliente["idcliente"] . "'
                                and cliente = '" . $cliente["cliente"] . "'
                                and statusproduccion != 'T'
                                and pendiente = 1
                        )
                    ";

                    // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba11.txt",$query);

                    if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                        $query = "
                        select
                            *
                        from
                            tespecificaciones
                        where
                            statusproduccion = 1
                            " . $where . " and
                            idpedido in
                            (
                                select
                                    idpedido
                                from
                                    vpedidos
                                where
                                    idcliente = '" . $cliente["idcliente"] . "'
                                    and cliente = '" . $cliente["cliente"] . "'
                                    and statusproduccion != 'T'
                                    and pendiente = 1
                            )
                        ";

                        // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba12.txt",$query);

                        if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                            $cliente["icono_produccion"] = $iconos[1];
                            $cliente["status_produccion"] = "warning";
                        } else {
                            $cliente["icono_produccion"] = $iconos[0];
                            $cliente["status_produccion"] = "danger";
                        }
                    } else {
                        $cliente["icono_produccion"] = $iconos[2];
                        $cliente["status_produccion"] = "success";
                    }

                    //status almacen
                    $query = "
                    select 
                        sum(cantidad_surtida) as cantidadsurtida 
                    from 
                        trespecificacionesproductos 
                    where 
                        idespecificacion in 
                        (
                            select 
                                idespecificacion 
                            from 
                                tespecificaciones 
                            where 
                                statusproduccion != 2
                                " . $where . " and 
                                idpedido in 
                                (
                                    select 
                                        idpedido 
                                    from 
                                        vpedidos 
                                    where 
                                        pendiente = 1
                                        and statusproduccion != 'T'
                                        and idcliente = '" . $cliente["idcliente"] . "'
                                        and cliente = '" . $cliente["cliente"] . "'
                                )
                        )
                    ";

                    // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba13.txt",$query);
                    $cantidadsurtida = mysqli_fetch_assoc(mysqli_query($this->con, $query))["cantidadsurtida"];
                    if ($cantidadsurtida > 0) {
                        $query = "
                        select 
                            sum(cantidad) as cantidadrequerida 
                        from 
                            trpedidoproductos 
                        where 
                            idpedidoproducto in 
                            (
                                select 
                                    idpedidoproducto 
                                from 
                                    trespecificacionesproductos 
                                where 
                                    idespecificacion in 
                                    (
                                        select 
                                            idespecificacion 
                                        from 
                                            tespecificaciones 
                                        where 
                                            statusproduccion != 2
                                            " . $where . " and 
                                            idpedido in 
                                            (
                                                select 
                                                    idpedido 
                                                from 
                                                    vpedidos 
                                                where 
                                                    pendiente = 1
                                                    and statusproduccion != 'T'
                                                    and idcliente = '" . $cliente["idcliente"] . "'
                                                    and cliente = '" . $cliente["cliente"] . "'
                                            )
                                    )
                            )
                        ";

                        // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba14.txt",$query);
                        $cantidadrequerida = mysqli_fetch_assoc(mysqli_query($this->con, $query))["cantidadrequerida"];
                        if ($cantidadrequerida > $cantidadsurtida) {
                            $cliente["icono_almacen"] = $iconos[1];
                            $cliente["status_almacen"] = "warning";
                        } else {
                            $cliente["icono_almacen"] = $iconos[2];
                            $cliente["status_almacen"] = "success";
                        }
                    } else {
                        $cliente["icono_almacen"] = $iconos[0];
                        $cliente["status_almacen"] = "danger";
                    }

                    $cliente["url"] = "/" . $cliente["idcliente"] . "/" . $this->encodeURIComponent(str_replace(" ", "-", $cliente["cliente"]));
                }

                // if($cliente["idcliente"]>0){

                $query = "
                    select
                        sum(a.cantidad) as cantidad,
                        sum(b.cantidad_surtida) as cantidad_surtida,
                        sum(b.cantidad_surtida)/sum(a.cantidad)*100 as porcentaje
                    from
                        trpedidoproductos a
                    left join
                        trespecificacionesproductos b
                    on
                        a.idpedidoproducto = b.idpedidoproducto
                    left join
                        tespecificaciones c
                    on
                        b.idespecificacion = c.idespecificacion
                    where
                        c.status < 2
                        and c.idespecificacion in
                        (
                            select
                                idespecificacion
                            from
                                tespecificaciones
                            where
                                idpedido in
                                (
                                    select
                                        idpedido
                                    from
                                        vpedidos
                                    where
                                        idcliente = '" . $cliente["idcliente"] . "'
                                        and cliente = '" . $cliente["cliente"] . "'
                                        and statusproduccion != 'T'
                                        and pendiente = 1
                                )
                        )
                    ";
                // $cantidades = mysqli_fetch_assoc(mysqli_query($this->con,$query));
                // }else{
                //     $query = "
                //     select 
                //         sum(cantidad) as total, 
                //         sum(surtido+disponible_surtir) as totaldisponible 
                //     from 
                //         vdisponibilidadproductos 
                //     where 
                //         idespecificacion in 
                //         (
                //             select 
                //                 idespecificacion 
                //             from 
                //                 tespecificaciones 
                //             where 
                //                 idpedido in 
                //                 (
                //                     select 
                //                         idpedido 
                //                     from 
                //                         vpedidos 
                //                     where 
                //                         idcliente = '".$cliente["idcliente"]."'
                //                         and cliente = '".$cliente["cliente"]."'
                //                         and pendiente = 1
                //                 )
                //         )";
                //     $cantidades = mysqli_fetch_assoc(mysqli_query($this->con,$query));
                // }

                // $cliente["porcentaje_almacen"] = ($cantidades["total"]>0) ? (($cantidades["totaldisponible"]/$cantidades["total"])*100) : 0;
                $cliente["porcentaje_almacen"] = mysqli_fetch_assoc(mysqli_query($this->con, $query))["porcentaje"];

                $arrayClientes[] = $cliente;
            }

            if (count($arrayClientes) > 0) {
                $respuesta = array("respuesta" => "OK", "clientes" => $arrayClientes, "pagina" => $pagina, "numpaginas" => $numpaginas, "maxpaginas" => $maxpaginas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron clientes con ordenes de producción.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Clientes Produccion Version 2.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerClientesProduccion2($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $surtido = mysqli_real_escape_string($this->con, $post["slcSurtido"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $pendiente = mysqli_real_escape_string($this->con, $post["slcPendiente"]);
        $orden = mysqli_real_escape_string($this->con, $post["slcOrden"]);

        $wherec = "";
        $where = "";

        try {

            // obtener los clientes y sus respectivos pedidos
            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, "select * from tusuarios where idusuario = '" . $idusuario . "'"));
            if ($usuario["tipousuario"] != "ADSB") {
                if (strpos($usuario["tipousuario"], "A") === false) {
                    if (strpos($usuario["tipousuario"], "D") !== false || strpos($usuario["tipousuario"], "S") !== false) {
                        $where .= " and a.serigrafia = 1";
                    }
                    if (strpos($usuario["tipousuario"], "B") !== false) {
                        $where .= " and a.bordado = 1";
                    }
                }
            }

            if ($busqueda != "") {
                $wherec .= " and (b.cliente like '%" . $busqueda . "%' or b.idpedido = '" . $busqueda . "')";
            }
            if (isset($surtido) and $surtido != -1) {
                $wherec .= " and a.statusalmacen = '" . $surtido . "'";
            }
            if (isset($idsucursal) and $idsucursal != '' and $idsucursal != 0) {
                $wherec .= " and b.idsucursal = '" . $idsucursal . "'";
            }
            if (isset($pendiente) and $pendiente != -1) {
                $wherec .= " and a.status = '" . $pendiente . "'";
            }

            $orderby = "clientes.fechaentrega";
            if (isset($orden) and $orden != -1) {
                $orderby = " clientes.idpedido " . $orden;
            }

            $query = "
            select
                clientes.*,
                group_concat(clientes.idpedido ORDER BY clientes.idpedido) as pedidos
            from
                (
                select
                    b.idpedido,
                    b.idcliente,
                    b.cliente,
                    a.idespecificacion,
                    a.fechaentrega
                from
                    tespecificaciones a
                left join
                    vpedidos b
                on
                    b.idpedido = a.idpedido
                where
                    a.status != 2
                    and b.statusproduccion != 'T'
                    and b.pendiente = 1
                    and b.status not in ('C','E')
                    " . $wherec . "
                    " . $where . "
                order by
                    a.fechaentrega
                ) as clientes
            group by
                clientes.idcliente,
                clientes.cliente
            order by
                " . $orderby . "
            ";

            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/prueba.txt",$query);

            $clientes = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "clientes" => $clientes);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Especificaciones.
     * 
     * @access public
     * @param array $post
     * @param array $session
     * @return array
     */
    public function obtenerEspecificaciones($post, $session)
    {
        try {
            $iconos = array("stop", "play", "check");
            $where = "";
            $query = "
            select 
                * 
            from 
                tusuarios 
            where 
                idusuario = '" . $session["usuario"]["idusuario"] . "'";
            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query));
            if ($usuario["tipousuario"] != "ADSB") {
                if (strpos($usuario["tipousuario"], "A") === false) {
                    if (strpos($usuario["tipousuario"], "D") !== false || strpos($usuario["tipousuario"], "S") !== false) {
                        $where .= " and serigrafia = 1";
                    }
                    if (strpos($usuario["tipousuario"], "B") !== false) {
                        $where .= " and bordado = 1";
                    }
                }
            }

            if (isset($post["idcliente"]) && $post["idcliente"] > 0) {
                $especificaciones = $this->obtenerEspecificacionesCliente($post["idcliente"], $where);
            } else {
                $where .= " and status != 2";
                $especificaciones = $this->obtenerEspecificacionesSinCliente($post["cliente"], $where);
            }

            $arrayEspecificaciones = array();
            foreach ($especificaciones["especificaciones"] as $especificacion) {
                // //status diseño
                // if($especificacion["statusdiseno"]>0){
                //     if($especificacion["statusdiseno"]<5){
                //         $especificacion["icono_diseno"] = $iconos[1];
                //         $especificacion["status_diseno"] = "warning";
                //     }else{
                //         $especificacion["icono_diseno"] = $iconos[2];
                //         $especificacion["status_diseno"] = "success";
                //     }
                // }else{
                //     $especificacion["icono_diseno"] = $iconos[0];
                //     $especificacion["status_diseno"] = "danger";
                // }

                // //status produccion
                // if($especificacion["statusproduccion"]>0){
                //     if($especificacion["statusproduccion"]<2){
                //         $especificacion["icono_produccion"] = $iconos[1];
                //         $especificacion["status_produccion"] = "warning";
                //     }else{
                //         $especificacion["icono_produccion"] = $iconos[2];
                //         $especificacion["status_produccion"] = "success";
                //     }
                // }else{
                //     $especificacion["icono_produccion"] = $iconos[0];
                //     $especificacion["status_produccion"] = "danger";
                // }

                // //status almacen
                // $query = "
                // select 
                //     * 
                // from 
                //     trespecificacionesproductos 
                // where 
                //     idespecificacion = '".$especificacion["idespecificacion"]."'
                //     and idpedidoproducto != 0";
                // if($especificacion["cantidadsurtida"]>0 or mysqli_num_rows(mysqli_query($this->con,$query))==0){
                //     $query = "
                //     select 
                //         * 
                //     from 
                //         trespecificacionesproductos 
                //     where 
                //         idespecificacion = '".$especificacion["idespecificacion"]."'
                //         and idpedidoproducto != 0";
                //     if($especificacion["cantidadrequerida"]>$especificacion["cantidadsurtida"] and mysqli_num_rows(mysqli_query($this->con,$query))>0){
                //         $especificacion["icono_almacen"] = $iconos[1];
                //         $especificacion["status_almacen"] = "warning";
                //     }else{
                //         $especificacion["icono_almacen"] = $iconos[2];
                //         $especificacion["status_almacen"] = "success";
                //     }
                // }else{
                //     $especificacion["icono_almacen"] = $iconos[0];
                //     $especificacion["status_almacen"] = "danger";
                // }

                $query = "
                select
                    sum(b.cantidad_surtida)/sum(a.cantidad)*100 as porcentaje
                from
                    trpedidoproductos a
                left join
                    trespecificacionesproductos b
                on
                    a.idpedidoproducto = b.idpedidoproducto
                where
                    idespecificacion = '" . $especificacion["idespecificacion"] . "'
                ";
                $especificacion["porcentaje_almacen"] = mysqli_fetch_assoc(mysqli_query($this->con, $query))["porcentaje"];

                $query = "
                select 
                    * 
                from 
                    tsolicitudesdiseno 
                where 
                    idespecificacion = '" . $especificacion["idespecificacion"] . "' 
                order by 
                    idsolicituddiseno desc";
                $especificacion["idsolicituddiseno"] = mysqli_fetch_assoc(mysqli_query($this->con, $query))["idsolicituddiseno"];

                $query = "
                select 
                    count(*) as total 
                from 
                    trespecificacionesproductos 
                where 
                    idespecificacion = '" . $especificacion["idespecificacion"] . "'
                    and idpedidoproducto != 0";
                $especificacion["asignar_productos"] = (mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"] > 0) ? true : false;

                $arrayEspecificaciones[] = $especificacion;
            }

            if (count($arrayEspecificaciones) > 0) {
                $respuesta = array("respuesta" => "OK", "especificaciones" => $arrayEspecificaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron especificaciones para el cliente seleccionado.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /*
    Devuelve un listado de las especificaciones de un cliente.

    @access public
    @param int $idcliente
    @param string $filtro
    @return array
    */
    public function obtenerEspecificacionesCliente($idcliente, $filtro, $orderby = "fechaentrega,idespecificacion")
    {
        $query = "
        select
            *,
            #case
            #    when statusdiseno > 0 and statusdiseno < 5 then 'play'
            #    when statusdiseno = 5 then 'check'
            #    when statusdiseno = 0 then 'stop'
            #end as icono_diseno,
            #case
            #    when statusdiseno > 0 and statusdiseno < 5 then 'warning'
            #    when statusdiseno = 5 then 'success'
            #    when statusdiseno = 0 then 'danger'
            #end as status_diseno,
            case
                when statusproduccion > 0 and statusproduccion < 2 then 'play'
                when statusproduccion = 2 or statusproduccion = 5 then 'check'
                when statusproduccion = 0 then 'stop'
            end as icono_produccion,
            case
                when statusproduccion > 0 and statusproduccion < 2 then 'warning'
                when statusproduccion = 2 or statusproduccion = 5 then 'success'
                when statusproduccion = 0 then 'danger'
            end as status_produccion,
            ifnull(case
                when cantidadsurtida = 0 then 'stop'
                when cantidadrequerida > cantidadsurtida then 'play'
                when cantidadrequerida = cantidadsurtida then 'check'
            end,0) as icono_almacen,
            ifnull(case
                when cantidadsurtida = 0 then 'danger'
                when cantidadrequerida > cantidadsurtida then 'warning'
                when cantidadrequerida = cantidadsurtida then 'success'
            end,0) as status_almacen
        from
            vespecificaciones
        where
            idpedido in 
            (
                select
                    idpedido
                from
                    vpedidos
                where
                    status = 'A'
            ) and
            status != 2 and
            pendiente = 1
            " . $filtro . " and
            idcliente = '" . $idcliente . "'
        order by
            " . $orderby;

        // $this->claseQueries->guardarQuery($query);

        return $this->getEspecificaciones($query);
    }

    /*
    Devuelve un listado de las especificaciones de un cliente no registrado.

    @access public
    @param int $cliente
    @param string $filtro
    @return array
    */
    public function obtenerEspecificacionesSinCliente($cliente, $filtro, $orderby = "fechaentrega,idespecificacion")
    {
        // statusproduccion!=2 and
        $query = "
        select
            *,
            #case
            #    when statusdiseno > 0 and statusdiseno < 5 then 'play'
            #    when statusdiseno = 5 then 'check'
            #    when statusdiseno = 0 then 'stop'
            #end as icono_diseno,
            #case
            #    when statusdiseno > 0 and statusdiseno < 5 then 'warning'
            #    when statusdiseno = 5 then 'success'
            #    when statusdiseno = 0 then 'danger'
            #end as status_diseno,
            case
                when statusproduccion > 0 and statusproduccion < 2 then 'play'
                when statusproduccion = 2 or statusproduccion = 5 then 'check'
                when statusproduccion = 0 then 'stop'
            end as icono_produccion,
            case
                when statusproduccion > 0 and statusproduccion < 2 then 'warning'
                when statusproduccion = 2 or statusproduccion = 5 then 'success'
                when statusproduccion = 0 then 'danger'
            end as status_produccion,
            ifnull(case
                when cantidadsurtida = 0 then 'stop'
                when cantidadrequerida > cantidadsurtida then 'play'
                when cantidadrequerida = cantidadsurtida then 'check'
            end,0) as icono_almacen,
            ifnull(case
                when cantidadsurtida = 0 then 'danger'
                when cantidadrequerida > cantidadsurtida then 'warning'
                when cantidadrequerida = cantidadsurtida then 'success'
            end,0) as status_almacen
        from
            vespecificaciones
        where
            pendiente = 1
            " . $filtro . "
            and idpedido in
            (
                select
                    idpedido
                from
                    vpedidos
                where
                    idcliente = 0
                    and cliente = '" . $cliente . "'
                    and status = 'A'
            )
        order by
            " . $orderby . "
        ";
        return $this->getEspecificaciones($query);
    }

    /*
    Devuelve las especificaciones que cumplan con las condiciones del query, así como sus desgloses (productos).

    @access public
    @param string $query
    @return array
    */
    private function getEspecificaciones($query)
    {
        $resultados = array();
        $especificaciones = mysqli_query($this->con, $query);
        if (mysqli_num_rows($especificaciones) > 0) {
            while ($especificacion = mysqli_fetch_assoc($especificaciones)) {
                $queryDesglose = "
                select
                    *
                from
                    vespecificacionesproductos
                where
                    idespecificacion = '" . $especificacion["idespecificacion"] . "'";

                $resultadosDesglose = array();
                $desgloses = mysqli_query($this->con, $queryDesglose);
                while ($desglose = mysqli_fetch_assoc($desgloses)) {
                    $resultadosDesglose[] = $desglose;
                }
                $especificacion["desgloses"] = $resultadosDesglose;
                $resultados[] = $especificacion;
            }
            return array("respuesta" => "OK", "especificaciones" => $resultados);
        } else {
            return array("respuesta" => "ERROR", "mensaje" => "ERROR: No se pudieron recuperar las especificaciones.");
        }
    }

    /*
    Devuelve la existencia de un producto libre (sin idproducto).

    @access public
    @param string $post
    @return array
    */
    public function getExistenciasLibre($post)
    {
        try {
            $query = "select * from trcompraproductos where producto = '" . $post["nombre"] . "' and idcolor = '" . $post["idcolor"] . "'" . " and idtalla = '" . $post["idtalla"] . "'" . " and utilizado = 0" . " and idpedido = " . $post["idpedido"];
            $result = mysqli_query($this->con, $query);
            $query = "select cantidad_surtida from trespecificacionesproductos where idespecificacionproducto = " . $post["idespecificacionproducto"];
            $cantidadSurtida = mysqli_fetch_assoc(mysqli_query($this->con, $query))["cantidad_surtida"];
            if ($result) {
                $existencias = mysqli_fetch_assoc($result);
                $existencias["cantidad_surtida"] = $cantidadSurtida;
                if ($existencias) {
                    return array("respuesta" => "OK", "existencias" => $existencias);
                } else {
                    return array("respuesta" => "ERROR", "mensaje" => "ERROR: No se pudieron recuperar las existencias.");
                }
            } else {
                return array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . mysqli_error($this->con));
            }
        }
        catch (Exception $e) {
            return array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        }
    }

    public function getProductosPendientesEspecificacion($idespecificacion,$idalmacen)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $idespecificacion);
        $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);

        try {
            $query = "
            select
                *
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'";
            $productos = mysqli_query($this->con, $query);

            $arrayProductos = array();
            while ($producto = mysqli_fetch_assoc($productos)) {
                $des = mysqli_fetch_assoc(mysqli_query($this->con, "select * from vrpedidoproductos where idpedidoproducto='" . $producto["idpedidoproducto"] . "'"));
                $requerido += (int)$des["cantidad"];
                $solicitado += (int)$producto["cantidad_surtida"];
                if ($producto["cantidad_surtida"] < $des["cantidad"]) {
                    if ($des["idproducto"] != "0") {
                        $talla = mysqli_fetch_assoc(mysqli_query($this->con, "select * from tcattallas where idtalla = '" . $des["idtalla"] . "'"));
                        $color = mysqli_fetch_assoc(mysqli_query($this->con, "select * from tcatcolores where idcolor = '" . $des["idcolor"] . "'"));
                        $nombre = mysqli_fetch_assoc(mysqli_query($this->con, "select * from tproductos where idproducto = '" . $des["idproducto"] . "'"))["nombre"];

                        //se obtiene la cantidad necesaria, se obtiene la existencia y en caso de que la cantidad sea menor a la existencia, la cantidad sugerida será el maximo en existencia.
                        $cantidad = $des["cantidad"] - $producto["cantidad_surtida"];
                        $existencia = mysqli_fetch_assoc(mysqli_query($this->con, "select * from tproductoexistencias where idproducto = '" . $des["idproducto"] . "' and idtalla = '" . $des["idtalla"] . "' and idcolor = '" . $des["idcolor"] . "' and idalmacen='".$idalmacen."' and status = 1"))["existencia"];
                    } else {
                        $talla["nombre"] = $des["talla"];
                        $talla["idtalla"] = $des["idtalla"];
                        $color["nombre"] = $des["color"];
                        $color["idcolor"] = 0;
                        $nombre = $des["producto"];

                        $cantidad = $des["cantidad"] - $producto["cantidad_surtida"];
                        //query
                        $validacion = mysqli_num_rows(mysqli_query($this->con, "select * from trcompraproductos where idproducto = 0 and producto = '" . $des["producto"] . "' and talla = '" . $des["talla"] . "' and color = '" . $des["color"] . "' and cantidad = '" . $des["cantidad"] . "' and utilizado = 0 and idcompra in  (select idcompra  from tsolicitudescompra  where  idpedido in (select idpedido  from trpedidoproductos where idpedidoproducto in (select idpedidoproducto from trespecificacionesproductos where idespecificacionproducto = '" . $producto["idespecificacionproducto"] . "')))"));
                        $existencia = ($validacion > 0) ? $cantidad : 0;
                    }

                    $arrayProductos[] = array(
                        "idespecificacionproducto" => $producto["idespecificacionproducto"],
                        "idproducto" => $des["idproducto"],
                        "nombre" => $nombre,
                        "talla" => $talla["nombre"],
                        "idtalla" => $talla["idtalla"],
                        "color" => $color["nombre"],
                        "idcolor" => $color["idcolor"],
                        "cantidad" => $cantidad,
                        "total" => $des["cantidad"],
                        "existencia" => $existencia
                    );
                }
            }

            $respuesta = array("respuesta" => "OK", "productos" => $arrayProductos, "requerido" => $requerido, "solicitado" => $solicitado);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos Produccion Especificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function getProductosProduccionEspecificacion($idespecificacion)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $idespecificacion);

        try {
            $query = "
            select
                *
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'
                and status != 2";
            $desgloses = mysqli_query($this->con, $query);

            $arrayProductos = array();
            while ($desglose = mysqli_fetch_assoc($desgloses)) {
                $query = "
                select
                    *
                from
                    trpedidoproductos
                where
                    idpedidoproducto = '" . $desglose["idpedidoproducto"] . "'";
                $des = mysqli_query($this->con, $query);
                if (mysqli_num_rows($des) > 0) {
                    $des = mysqli_fetch_assoc($des);
                    if ($des["idproducto"] != "0") {
                        $query = "
                        select
                            *
                        from
                            tcattallas
                        where
                            idtalla = '" . $des["idtalla"] . "'";
                        $talla = mysqli_fetch_assoc(mysqli_query($this->con, $query))["nombre"];

                        $query = "
                        select
                            *
                        from
                            tcatcolores
                        where
                            idcolor = '" . $des["idcolor"] . "'";
                        $color = mysqli_fetch_assoc(mysqli_query($this->con, $query))["nombre"];

                        $query = "
                        select
                            *
                        from
                            tproductos
                        where
                            idproducto = '" . $des["idproducto"] . "'";
                        $nombre = mysqli_fetch_assoc(mysqli_query($this->con, $query))["nombre"];
                    } else {
                        $talla = $des["talla"];
                        $color = $des["color"];
                        $nombre = $des["producto"];
                    }

                    $arrayProductos[] = array(
                        "idespecificacionproducto" => $desglose["idespecificacionproducto"],
                        "nombre" => $nombre . " | Talla: " . $talla . ", Color: " . $color,
                        "status" => $desglose["status"],
                        "habilitado" => ($des["cantidad"] == $desglose["cantidad_surtida"] and $desglose["status"] == 0) ? 1 : 0
                    );
                } else {
                    $producto = mysqli_fetch_assoc(mysqli_query($con, "select * from trcotizacionproductos where idcotizacionproducto in (select idcotizacionproducto from trespecificacionesproductos where idespecificacionproducto = '" . $desglose["idespecificacionproducto"] . "')"));
                    if ($producto["idproducto"] != 0) {
                        $nombre = mysqli_fetch_assoc(mysqli_query($con, "select * from tproductos where idproducto = '" . $producto["idproducto"] . "'"))["nombre"];
                    } else {
                        $nombre = $producto["producto"];
                    }

                    $arrayProductos[] = array(
                        "idespecificacionproducto" => $desglose["idespecificacionproducto"],
                        "nombre" => $nombre . " | Talla: " . $talla . ", Color: " . $color,
                        "status" => $desglose["status"],
                        "habilitado" => ($desglose["status"] == 0) ? 1 : 0
                    );
                }
            }

            $query = "
            select
                *
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'
                and status = 0";
            $produccion = (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) ? 1 : 0;

            $respuesta = array("respuesta" => "OK", "productos" => $arrayProductos, "produccion" => $produccion);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /*
    Asigna los productos de un almacen al pedido (actualiza existencias).

    @access public
    @param string $query
    @return array
    */
    public function asignarProductos($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["slcAlmacen"]);
        $idespecificacionesproducto = $post["idespecificacionproducto"];
        $cantidades = $post["txtCantidad"];

        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        $this->claseProductos = new Productos();

        try {
            // actualiza pedido (status=P)
            $query = "
            update
                tpedidos
            set
                statusproduccion = 'P'
            where
                idpedido in 
                (
                    select 
                        idpedido
                    from
                        tespecificaciones
                    where
                        idespecificacion = '" . $idespecificacion . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $i = 0;
            foreach ($idespecificacionesproducto as $idespecificacionproducto) {

                if ($cantidades[$i] > 0) {

                    $query = "
                    update
                        trespecificacionesproductos
                    SET
                        cantidad_surtida = cantidad_surtida + " . $cantidades[$i] . ",
                        fecha_ultima_asignacion = CURRENT_TIMESTAMP
                    where
                        idespecificacionproducto = '" . $idespecificacionproducto . "'
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
                        idpedidoproducto in
                        (
                            select
                                idpedidoproducto
                            from
                                trespecificacionesproductos
                            where
                                idespecificacionproducto = '" . $idespecificacionproducto . "'
                        )
                    ";

                    $desglose = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                    $especificacion = mysqli_fetch_assoc(mysqli_query($this->con,"select * from trespecificacionesproductos where idespecificacionproducto = '".$idespecificacionproducto."'"));
                    $idpedido = mysqli_fetch_assoc(mysqli_query($this->con, "select idpedido from trpedidoproductos where idpedidoproducto = '" . $especificacion["idpedidoproducto"] . "'"))["idpedido"];

                    $query = "
                    insert
                    into
                        tasignacionproductospedido
                    (
                        idpedido,
                        idespecificacionproducto,
                        idusuario,
                        idalmacen,
                        cantidad
                    ) values (
                        '".$idpedido."',
                        '".$idespecificacionproducto."',
                        '".$idusuario."',
                        '".$idalmacen."',
                        '".$cantidades[$i]."'
                    )";
                    mysqli_query($this->con,$query);
                    
                    // $desglose = mysqli_fetch_assoc(mysqli_query($this->con,"select * from trpedidoproductos where idpedidoproducto = '".$especificacion["idpedidoproducto"]."'"));
                    if ($desglose["idproducto"] > 0) {
                        $post["idmovimiento"] = $idpedido;
                        $post["origenmovimiento"] = "P";
                        $post["tipomovimiento"] = "S";
                        $post["idalmacen"] = $idalmacen;

                        $post["idproducto"] = $desglose["idproducto"];
                        $post["idtalla"] = $desglose["idtalla"];
                        $post["idcolor"] = $desglose["idcolor"];
                        $post["cantidad"] = $cantidades[$i];
                        $this->claseProductos = new Productos();
                        if ($this->claseProductos->actualizarExistencia($post)["respuesta"] == "ERROR") {
                            $correcto = false;
                        }

                    } else {
                        //validar lo solicitado contra lo asignado y actualizar "utilizado"
                        $asignado = mysqli_fetch_assoc(mysqli_query($this->con, "select * from trespecificacionesproductos where idespecificacionproducto = '" . $idespecificacionproducto . "'"))["cantidad_surtida"];
                        mysqli_query($this->con, "update trcompraproductos set utilizado = 1 where cantidad = '" . $asignado . "' and idcompra in (select idcompra  from tsolicitudescompra  where  idpedido in (select idpedido  from trpedidoproductos where idpedidoproducto in (select idpedidoproducto from trespecificacionesproductos where idespecificacionproducto = '" . $idespecificacionproducto . "')))");
                    }
                }

                $i++;
            }

            $query = "
            select
                *
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'
            ";
            $terminado = true;
            $especificaciones = mysqli_query($this->con, $query);
            while ($especificacion = mysqli_fetch_assoc($especificaciones)) {
                $query = "
                select
                    *
                from
                    trpedidoproductos
                where
                    idpedidoproducto = '" . $especificacion["idpedidoproducto"] . "'
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
                    idespecificacion = '" . $idespecificacion . "'
                    and statusproduccion = 2
                    and statusdiseno >= 1
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
                    idespecificacion = '" . $idespecificacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                mysqli_query($this->con, $query);
            }

            $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Producto asignado", "mensaje" => "Se ha asignado el producto correctamente.");
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    // /**
    //  * Iniciar Diseno.
    //  * 
    //  * @access public
    //  * @param array $post
    //  * @return array
    //  */
    // public function iniciarDiseno($post)
    // {
    //     $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

    //     $fecha = date("Y-m-d H:i:s");

    //     try {
    //         $query = "
    //         select
    //             *
    //         from
    //             tespecificaciones
    //         where
    //             raprobaciondiseno = 1 and
    //             idespecificacion = '" . $idespecificacion . "'";

    //         if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
    //             $query = "
    //             update
    //                 tespecificaciones 
    //             set
    //                 statusdiseno = '1',
    //                 fechastatusdiseno = '" . $fecha . "',
    //                 status = 1
    //             where
    //                 idespecificacion = '" . $idespecificacion . "'
    //             ";

    //             $this->claseQueries->guardarQuery($query);

    //             if (!mysqli_query($this->con, $query)) {
    //                 $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo iniciar el diseño (especificaciones).");
    //                 $error = true;
    //             }
    //         } else {
    //             $query = "
    //             update
    //                 tespecificaciones
    //             set
    //                 statusdiseno = '4',
    //                 statusaprobacion = 2,
    //                 fechastatusdiseno = '" . $fecha . "',
    //                 status = 1
    //             where
    //                 idespecificacion = '" . $idespecificacion . "'
    //             ";

    //             $this->claseQueries->guardarQuery($query);

    //             if (!mysqli_query($this->con, $query)) {
    //                 $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo iniciar el diseño (especificaciones).");
    //                 $error = true;
    //             }
    //         }

    //         if (!$error) {
    //             $query = "
    //             update
    //                 tpedidos
    //             set
    //                 statusproduccion = 'P'
    //             where
    //                 idpedido in 
    //                 (
    //                     select
    //                         idpedido 
    //                     from
    //                         tespecificaciones
    //                     where
    //                         idespecificacion = '" . $idespecificacion . "'
    //                 )";
    //             if (mysqli_query($this->con, $query)) {
    //                 $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Diseño iniciado", "mensaje" => "Se ha iniciado el proceso de diseño correctamente.");
    //             } else {
    //                 $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo iniciar el diseño");
    //             }
    //         }
    //     } catch (Exception $e) {
    //         $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
    //     } finally {
    //         return $respuesta;
    //     }
    // }

    /**
     * Enviar Diseno.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function enviarDiseno($post, $files)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $comentarios = mysqli_real_escape_string($this->con, $post["txtComentarios"]);

        $fecha = date("Y-m-d H:i:s");

        try {
            // se actualiza el statusdiseno de la especificacion y statusaprobacion para indicar que el administrador ya puede revisar el diseño
            // si se eligio no pasar por la aprobación del diseño, se omiten esos pasos
            // $query = "
            // select
            //     *
            // from
            //     tespecificaciones
            // where
            //     idespecificacion = '" . $idespecificacion . "'
            //     and raprobaciondiseno = 0";

            // if (mysqli_num_rows(mysqli_query($this->con, $query))) {
            //     $query = "
            //     update
            //         tespecificaciones
            //     set
            //         statusdiseno = 4,
            //         statusaprobacion = 2,
            //         fechastatusdiseno = '" . $fecha . "',
            //         fechastatusaprobacion = '" . $fecha . "',
            //         status = 1
            //     where
            //         idespecificacion = '" . $idespecificacion . "'";
            //     mysqli_query($this->con, $query);
            // } else {
                $query = "
                update
                    tespecificaciones
                set
                    statusdiseno = 2,
                    statusaprobacion = 1,
                    fechastatusdiseno = '" . $fecha . "',
                    fechastatusaprobacion = '" . $fecha . "',
                    status = 1
                where
                    idespecificacion = '" . $idespecificacion . "'";
                mysqli_query($this->con, $query);
                // NOTA: si rechazo una vez el diseño, no hay diferencia en los status que lo distinga de cuando el diseño fue iniciado (statusdiseno=1, statusaprobacion=0)
            // }

            // se inserta un registro en la tabla tsolicitudesdiseno
            $query = "
            insert into
                tsolicitudesdiseno
                (
                    idusuario,
                    idespecificacion,
                    comentarios
                )
            values
                (
                    '" . $idusuario . "',
                    '" . $idespecificacion . "',
                    '" . $comentarios . "'
                )
            ";
            mysqli_query($this->con, $query);

            $idsolicitud = mysqli_insert_id($this->con);

            if ($idsolicitud > 0) {
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/imagenes/disenos/" . $idsolicitud . "/", 0777, true);

                // NOTA: es posible que se suban dos imagenes con el mismo nombre, y que al resubir solo una de ellas, las dos sufran cambios. Hay que probarlo para corroborar
                $this->claseImagen->subirImagen($files["imgDiseno1"], "/imagenes/disenos/" . $idsolicitud . "/", $files["imgDiseno1"]["name"], 2000, "tsolicitudesdiseno", "idsolicituddiseno", $idsolicitud, "imagen1");
                $this->claseImagen->subirImagen($files["imgDiseno2"], "/imagenes/disenos/" . $idsolicitud . "/", $files["imgDiseno2"]["name"], 2000, "tsolicitudesdiseno", "idsolicituddiseno", $idsolicitud, "imagen2");

                $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Diseño enviado", "mensaje" => "Se ha enviado el diseño correctamente.");
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo enviar el diseño");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Aprobar Diseno
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function aprobarDiseno($post)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        $fecha = date("Y-m-d H:i:s");

        try {
            $query = "
            update
                tespecificaciones
            set
                statusdiseno = 4,
                statusaprobacion = 2,
                fechastatusdiseno = '" . $fecha . "',
                fechastatusaprobacion = '" . $fecha . "',
                status = 1
            where
                idespecificacion = '" . $idespecificacion . "'";
            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Diseño aprobado", "mensaje" => "Se ha aprobado el diseño correctamente.");
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo aprobar el diseño");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Rechazar Diseno.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function rechazarDiseno($post)
    {
        $razon = mysqli_real_escape_string($this->con, $post["txtRazon"]);
        $idsolicituddiseno = mysqli_real_escape_string($this->con, $post["idsolicituddiseno"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        $fecha = date("Y-m-d H:i:s");

        try {
            $query = "
            update
                tsolicitudesdiseno
            set
                comentariosrechazo = '" . $razon . "',
                status = 'R'
            where
                idsolicituddiseno = '" . $idsolicituddiseno . "'";
            if (mysqli_query($this->con, $query)) {

                $query = "
                update
                    tespecificaciones
                set
                    statusdiseno = 1,
                    statusaprobacion = 0,
                    fechastatusdiseno = '" . $fecha . "',
                    fechastatusaprobacion = '" . $fecha . "',
                    status = 1 
                where
                    idespecificacion = '" . $idespecificacion . "'";
                if (mysqli_query($this->con, $query)) {
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Diseño rechazado", "mensaje" => "Se ha rechazado el diseño correctamente.");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo rechazar el diseño.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo rechazar el diseño.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Diseno.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function getDiseno($idsolicituddiseno)
    {
        $idsolicituddiseno = mysqli_real_escape_string($this->con, $idsolicituddiseno);

        try {
            $query = "
            select
                *
            from
                tsolicitudesdiseno
            where
                idsolicituddiseno = '" . $idsolicituddiseno . "'";
            $diseno = mysqli_query($this->con, $query);
            if (mysqli_num_rows($diseno) > 0) {
                $respuesta = array("respuesta" => "OK", "diseno" => mysqli_fetch_assoc($diseno));
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar el diseño.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    // /**
    //  * Terminar Diseno.
    //  * 
    //  * @access public
    //  * @param array $post
    //  * @return array
    //  */
    // public function terminarDiseno($post)
    // {
    //     $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

    //     $fecha = date("Y-m-d H:i:s");

    //     $status = 1;

    //     try {
    //         $query = "
    //         select
    //             count(*) as total
    //         from
    //             tespecificaciones
    //         where
    //             idespecificacion = '" . $idespecificacion . "'
    //             and rproduccion = 0
    //             and statusalmacen = 1
    //         ";

    //         $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

    //         if ($total) {
    //             $status = 2;
    //         }

    //         $query = "
    //         update
    //             tespecificaciones
    //         set
    //             statusdiseno = '5',
    //             fechastatusdiseno = '" . $fecha . "',
    //             status = '" . $status . "'
    //         where
    //             idespecificacion = '" . $idespecificacion . "'
    //         ";

    //         if (mysqli_query($this->con, $query)) {
    //             $respuesta = array("respuesta" => "OK", "tipo" => "mensajereload", "titulo" => "Diseño terminado", "mensaje" => "Se ha terminado el proceso de diseño correctamente.");
    //         } else {
    //             $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo terminar el diseño.");
    //         }
    //     } catch (Exception $e) {
    //         $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
    //     } finally {
    //         return $respuesta;
    //     }
    // }

    /**
     * Asignar Produccion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarProduccion($post)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $desgloses = $post["chkDesglose"];

        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        try {

            foreach ($desgloses as $especificacionproducto) {
                $especificacionproducto = mysqli_real_escape_string($this->con, $especificacionproducto);

                $query = "
                update
                    trespecificacionesproductos
                set
                    status = 1,
                    fecha_inicio_produccion = '" . $fecha . "'
                where
                    idespecificacionproducto = '" . $especificacionproducto . "'
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
                tespecificaciones
            where
                idespecificacion = '" . $idespecificacion . "'
            ";

            $especificacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($especificacion["statusproduccion"] == 0) {
                $query = "
                update
                    tespecificaciones
                set
                    statusproduccion = 1,
                    fechastatusproduccion = '" . $fecha . "'
                where
                    idespecificacion = '" . $idespecificacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "reload");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al asignar la producción.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
        // $respuesta = array("respuesta"=>"OK","tipo"=>"reload");
        // return $respuesta;
    }

    /**
     * Terminar Producción.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function terminarProduccion($post)
    {
        $idespecificacionproducto = mysqli_real_escape_string($this->con, $post["idespecificacionproducto"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        try {


            // cambiar el status de la especificacionproducto 
            // mostrar el texto "terminado"
            // verificar si el producto al que se le ha cambiado el estado, era el último de la especificacion. Si es el caso, se actualiza statusproduccion a 2

            $query = "
            update
                trespecificacionesproductos
            set
                status = 2,
                fecha_fin_produccion = '" . $fecha . "'
            where
                idespecificacionproducto = '" . $idespecificacionproducto . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            // $mensaje = "Se ha terminado un producto de la especificacion #".$idespecificacion;
            // $idalmacen = 4;
            // $claseNotificaciones = new Notificaciones($con);
            // $claseNotificaciones->agregarNotificaciones("U",7,$mensaje,$idespecificacion,$idalmacen);

            $query = "
            select
                ifnull(count(*),0) as total
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'
                and status != 2
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            // niveles de terminado:
            // 0: partida terminada, pero la especificacion aún no terminado
            // 1: especificacion terminada, pero pedido aún no ha terminado
            // 2: pedido terminado, pero el cliente aún tiene otros pedidos sin terminar
            // 3: todos los pedidos del cliente terminados
            $tipoterminado = 0;
            if ($total == 0) {
                $tipoterminado = 1;
                // falta actualizar algun otro campo?
                $query = "
                update
                    tespecificaciones
                set
                    statusproduccion = 2,
                    fechastatusproduccion = '" . $fecha . "',
                    status = 2
                where
                    idespecificacion = '" . $idespecificacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                if ($idcliente > 0) {
                    // segun yo, la condicion dice esto: si todas las especificaciones del cliente han finalizado produccion, o si las especificaciones del pedido...
                    $query = "
                    select
                        ifnull(count(*),0) as total
                    from
                        tespecificaciones
                    where
                        status != 2
                        and idespecificacion in
                        (
                            select
                                idespecificacion
                            from
                                trespecificacionesproductos
                            where
                                idpedidoproducto in
                                (
                                    select
                                        idpedidoproducto
                                    from
                                        trpedidoproductos
                                    where
                                        idpedido in
                                        (
                                            select
                                                idpedido
                                            from
                                                tpedidos
                                            where
                                                idcliente in
                                                (
                                                    select
                                                        idcliente
                                                    from
                                                        tclientes
                                                    where
                                                        idcliente = '" . $idcliente . "'
                                                )
                                        )
                                )
                        )
                    ";
                    // idpedido = '".$idpedido."'
                } else {
                    $query = "
                    select
                        0 as total
                    ";
                }

                // $this->claseQueries->guardarQuery($query);

                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                if ($total == 0) {
                    $tipoterminado = "2";
                    $query = "
                    update
                        tpedidos
                    set
                        statusproduccion = 'T'
                    where
                        idpedido = '" . $idpedido . "'
                    ";
                    // $query = "
                    // update
                    //     tpedidos
                    // set
                    //     statusproduccion = 'T'
                    // where
                    //     idpedido in
                    //     (
                    //         select
                    //             idpedido
                    //         from
                    //             trpedidoproductos
                    //         where
                    //             idpedidoproducto in
                    //             (
                    //                 select
                    //                    idpedidoproducto
                    //                 from
                    //                     trespecificacionesproductos
                    //                 where
                    //                     idespecificacion = '".$idespecificacion."'
                    //             )
                    //     )
                    // ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                    // }else {
                    //     $tipoterminado = "1";
                }
                // $mensaje = "Ha finalizado la produccion de la especificacion";
                // $claseNotificaciones->agregarNotificaciones("U",10,$mensaje,$idorigen,$idalmacen);
            }

            if ($tipoterminado == 0) {
                $respuesta = array("respuesta" => "OK", "tipo" => "cargar", "formulario" => "formProduccion");
            } else if ($tipoterminado == 1) {
                // $respuesta = array("respuesta"=>"OK","tipo"=>"mensajereload","titulo"=>"Producción de Especificación Terminada","mensaje"=>"La producción de esta especificación ha terminado.");
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajereload","titulo"=>"Producción de Especificación Terminada","mensaje"=>"La producción de la especificación se ha completado exitosamente.");
                // } else if ($tipoterminado==2) {
                //     $respuesta = array("respuesta"=>"OK","tipo"=>"mensajereload","titulo"=>"Producción de Pedido Terminada","mensaje"=>"La producción de este pedido ha terminado.");
            } else {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Producción del Cliente Terminada", "mensaje" => "Toda la producción de pedidos de este cliente ha terminado.", "formulario" => "formProduccion");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Finalizar Produccion
     * 
     * @access public
     */
    public function finalizarProduccion($post){
        // se marca la especificacion como terminada, pero no sus partidas. No hay motivo de finalizacion
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $fecha = date("Y-m-d H:i:s");

        try {
            $query = "
            update
                tespecificaciones
            set
                status = 2,
                statusproduccion = 2,
                fechastatusproduccion = '".$fecha."'
            where
                idespecificacion = '".$idespecificacion."'
            ";

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "reload");
            }else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar la especificación.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Compras Especificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerComprasEspecificacion($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        try {
            // NOTA: ¿deberia ser con la tabla o la vista?
            $query = "
            select
                count(*) as total
            from
                tsolicitudescompra
            where
                idpedido = '" . $idpedido . "'
                and idproducto in
                (
                    select
                        idproducto
                    from
                        vespecificacionesproductos
                    where
                        idespecificacion = '" . $idespecificacion . "'
                )
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vsolicitudescompra
                where
                    idpedido = '" . $idpedido . "'
                    and idproducto in
                    (
                        select
                            idproducto
                        from
                            vespecificacionesproductos
                        where
                            idespecificacion = '" . $idespecificacion . "'
                    )
                ";

                $solicitudes = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "productos" => $solicitudes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron productos en las solicitudes de compra.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Movimientos Especificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientosEspecificacion($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        try {
            // NOTA: ¿deberia ser con la tabla o la vista?
            $query = "
            select
                count(*) as total
            from
                vmovimientoproductos
            where
                idpedido = '" . $idpedido . "'
                and pendiente = 1
                and idproducto in
                (
                    select
                        idproducto
                    from
                        vespecificacionesproductos
                    where
                        idespecificacion = '" . $idespecificacion . "'
                )
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    mp.*,
                    m.autorizacion,
                    m.recepcionparcial
                from
                    vmovimientoproductos mp
                left join
                    vmovimientos m
                on
                    mp.idmovimientoinventario = m.idmovimientoinventario 
                where
                    mp.idpedido = '" . $idpedido . "'
                    and mp.pendiente = 1
                    and mp.idproducto in
                    (
                        select
                            idproducto
                        from
                            vespecificacionesproductos
                        where
                            idespecificacion = '" . $idespecificacion . "'
                    )
                ";

                $movimientos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "productos" => $movimientos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron productos.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Idpedidos. Recupera todos los pedidos de un cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerIdpedidos($post)
    {
        $cliente = mysqli_real_escape_string($this->con, $post["cliente"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        try {
            $query = "
            select
                idpedido
            from
                vpedidos
            where
                idcliente = '" . $idcliente . "'
                and cliente = '" . $cliente . "'
            ";

            $idpedidos = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "idpedidos" => $idpedidos);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    private function encodeURIComponent($str) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')','%C3%81'=>'A','%C3%89'=>'E','%C3%8D'=>'I','%C3%91'=>'N','%C3%93'=>'O','%C3%9A'=>'U','%C3%A1'=>'a','%C3%A9'=>'e','%C3%AD'=>'i','%C3%B1'=>'n','%C3%B3'=>'o','%C3%BA'=>'u');
        return strtr(rawurlencode($str), $revert);
    }
}
