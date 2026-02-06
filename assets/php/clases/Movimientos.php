<?php
class Movimientos{
    private $con;
    private $claseQueries;
    private $claseProductos;
    private $claseUsuarios;
    private $claseNotificaciones;
    private $claseLogs;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Notificaciones.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();

        // aumentar el limite de caracteres que puede contener una columna group_concat
        $query = "
        SET SESSION group_concat_max_len = 1000000
        ";
        mysqli_query($this->con,$query);
    }

    /**
     * Obtener Movimientos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientos($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        $fechai = mysqli_real_escape_string($this->con,$post["txtFechaInicial"]);
        $fechaf = mysqli_real_escape_string($this->con,$post["txtFechaFinal"]);
        $idtipomovimiento = mysqli_real_escape_string($this->con,$post["slcTipoMovimiento"]);
        $idalmacenorigen = mysqli_real_escape_string($this->con,$post["slcAlmacenO"]);
        $idalmacendestino = mysqli_real_escape_string($this->con,$post["slcAlmacenD"]);
        $status = mysqli_real_escape_string($this->con,$post["slcStatusMovimiento"]);

        $idpedido = mysqli_real_escape_string($this->con,$post["idpedido"]);
        $almacenes = mysqli_real_escape_string($this->con,$post["almacenes"]);

        $pagina = (isset($post["pagina"])) ? $post["pagina"] : 1;
        $regpagina = 100;

        $where = "";
        $where .= (($busqueda!="") ? " and (idmovimientoinventario in (select idmovimientoinventario from vmovimientoproductos where producto like '%".$busqueda."%') or idmovimientoinventario = '".$busqueda."' or usuario like '%".$busqueda."%')" : "");
        $where .= (($fechai!="") ? " and date(fecha) between '".$fechai."' and '".$fechaf."'" : "");
        $where .= (($idtipomovimiento>0) ? " and idtipomovimiento = '".$idtipomovimiento."'" : "");
        $where .= (($idalmacenorigen>0) ? " and idalmacen = '".$idalmacenorigen."'" : "");
        $where .= (($idalmacendestino>0) ? " and idalmacensecundario = '".$idalmacendestino."'" : "");
        $where .= (($status>0) ? (($status==1) ? " and idtipomovimiento=3 and (autorizacion=0 or (autorizacion=2 and recepcionparcial=0))" : " and recepcionparcial=2") : "");

        $where .= (($idpedido!="") ? " and idpedido = '".$idpedido."'" : "");
        $where .= (($almacenes!="") ? " and idalmacen in (".$almacenes.")" : "");
        
        try{
            $query = "
            select
                *
            from
                vmovimientos
            where
                1=1
                ".$where."
                and ((pendiente = 1 and statuspedido != 'C') or idpedido = 0)
            order by
                idmovimientoinventario desc
            limit 
                ".(($pagina-1)*$regpagina).",".$regpagina;
            $movimientos = mysqli_query($this->con,$query);

            $query = "
            select
                count(*) as total
            from
                vmovimientos
            where
                1=1
                ".$where."
                and ((pendiente = 1 and statuspedido != 'C') or idpedido = 0)
            ";

            $numpaginas = ceil(mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"]/$regpagina);
            $maxpaginas = 3;

            if(mysqli_num_rows($movimientos)>0){
                $respuesta = array("respuesta"=>"OK","movimientos"=>$movimientos,"pagina"=>$pagina,"numpaginas"=>$numpaginas,"maxpaginas"=>$maxpaginas);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron movimientos registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Movimientos version 2. 
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientos2($post){
        // filtros
        $idsmovimientos = mysqli_real_escape_string($this->con,$post["idsmovimientos"]);
        
        $where = "";
        $where .= (($idsmovimientos!="") ? " and m.idmovimientoinventario in (".$idsmovimientos.")" : "");
        
        try{
            $query = "
            select
                m.*,
                group_concat(mp.idmovimientoinventarioproducto,',',mp.producto,'.-',mp.detalleproducto separator ':') as productos
            from
                vmovimientos m
            left join
                (
                    select
                        idmovimientoinventario,
                        idmovimientoinventarioproducto,
                        producto,
                        group_concat(color,' / ',tallas separator ';') as detalleproducto
                    from
                        (
                            select
                                idmovimientoinventario,
                                idproducto,
                                idmovimientoinventarioproducto,
                                producto,
                                color,
                                group_concat(cantidad,' - ',talla order by posicion_talla separator ' / ') as tallas
                            from
                                vmovimientoproductos m
                            where
                                1=1
                                ".$where."
                            group by
                                idmovimientoinventario,idproducto,producto,idcolor,color
                        ) m
                    where
                        1=1
                        ".$where."
                    group by
                        idmovimientoinventario,idproducto,producto
                ) mp
            on
                m.idmovimientoinventario = mp.idmovimientoinventario
            where
                1=1
                ".$where."
            group by
                m.idmovimientoinventario
            ";
            $movimientos = mysqli_query($this->con,$query);

            $query = "
            select
                count(*) as total
            from
                vmovimientos m
            where
                1=1
                ".$where."
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if($total){
                $respuesta = array("respuesta"=>"OK","movimientos"=>$movimientos,"totalmovimientos"=>$total);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron movimientos registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Movimientos version 3 (packinglist "agrupados"). 
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientos3($post){
        // filtros
        $idsmovimientos = mysqli_real_escape_string($this->con,$post["idsmovimientos"]);
        
        $where = "";
        $where .= (($idsmovimientos!="") ? " and a.idmovimientoinventario in (".$idsmovimientos.")" : "");
        
        try{
            $query = "
            select
                a.*,
                group_concat(a.color,' / ',b.tallas separator '\n') as desgloses
            from
                vmovimientoinventarioproductos a
            left join
                (
                    select
                        a.idmovimientoinventario,
                        a.idproducto,
                        a.idcolor,
                        color,
                        a.idtalla,
                        group_concat(b.cantidad,' - ',a.talla order by posicion separator ' / ') as tallas
                    from
                        vmovimientoinventarioproductos a
                    left join
                        (
                            select
                                idmovimientoinventario,
                                idproducto,
                                idtalla,
                                idcolor,
                                sum(cantidad) as cantidad
                            from
                                vmovimientoinventarioproductos a
                            where
                                1=1
                                ".$where."
                            group by
                                idproducto,idcolor,idtalla
                        ) b
                    on
                        a.idmovimientoinventario = b.idmovimientoinventario
                        and a.idproducto = b.idproducto
                        and a.idtalla = b.idtalla
                        and a.idcolor = b.idcolor
                    where
                        1=1
                        ".$where."
                    group by
                        a.idproducto,a.idcolor
                ) b
            on
                a.idmovimientoinventario = b.idmovimientoinventario
                and a.idproducto = b.idproducto
                and a.idcolor = b.idcolor
                and a.idtalla = b.idtalla
            where
                1=1
                ".$where."
            group by
                a.idproducto
            ";

            $movimientos = mysqli_query($this->con,$query);

            // if($total){
                $respuesta = array("respuesta"=>"OK","movimientos"=>$movimientos);
            // }else{
            //     $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron movimientos registrados.");
            // }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Movimiento.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimiento($post){
        $idmovimientoinventario = mysqli_real_escape_string($this->con,$post["idmovimientoinventario"]);

        try{
            $query = "
            select
                *
            from
                vmovimientos
            where
                idmovimientoinventario = '".$idmovimientoinventario."'
            ";
            $movimiento = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($movimiento["idmovimientoinventario"]>0){
                $query = "
                select
                    *
                from
                    vmovimientoproductos
                where
                    idmovimientoinventario = '".$idmovimientoinventario."'";
                $productos = mysqli_query($this->con,$query);

                $arrayProductos = array();
                while($producto = mysqli_fetch_assoc($productos)){
                    $arrayProductos[] = $producto;
                }

                $movimiento["productos"] = $arrayProductos;

                $respuesta = array("respuesta"=>"OK","movimiento"=>$movimiento);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del movimiento.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Obtener Movimiento Version 2. Recupera el movimiento junto con su respectivo detalle de productos
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimiento2($post){
        $idmovimientoinventario = mysqli_real_escape_string($this->con,$post["idmovimientoinventario"]);

        try{
            $query = "
            select
                m.*,
                group_concat(mp.producto separator '::') as productos
            from
                vmovimientos m
            left join
                (
                    select
                        idmovimientoinventario,
                        group_concat(idmovimientoinventarioproducto,';',producto,';',talla,';',color,';',cantidad,';',cantidadrecibida separator '-_-') as producto
                    from
                        vmovimientoproductos
                    where
                        idmovimientoinventario = '".$idmovimientoinventario."'
                    group by
                        idproducto
                ) mp
            on
                m.idmovimientoinventario = mp.idmovimientoinventario
            where
                m.idmovimientoinventario = '".$idmovimientoinventario."'
            group by
                m.idmovimientoinventario
            ";

            // $this->claseQueries->guardarQuery($query);

            $movimiento = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($movimiento["idmovimientoinventario"]>0){

                $respuesta = array("respuesta"=>"OK","movimiento"=>$movimiento);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del movimiento.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Autorizar Movimiento. Actualiza la autorizacion de un movimiento
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarAutorizacionMovimiento($post){
        $autorizacion = mysqli_real_escape_string($this->con,$post["autorizacion"]);
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idmovimientoinventario = mysqli_real_escape_string($this->con,$post["idmovimientoinventario"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);

        $correcto = true;

        $this->claseUsuarios = new Usuarios();

        try{

            $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"];

            $titulo = "Movimiento " . (($autorizacion==1) ? "Rechazado" : (($autorizacion==2) ? "Autorizado" : ""));
            $mensaje = "Se ha " . (($autorizacion==1) ? "rechazado" : (($autorizacion==2) ? "autorizado" : "")) . " el movimiento correctamente.";

            $mensajenotificacion = "El movimiento #".$idmovimientoinventario." ha sido " . (($autorizacion==1) ? "rechazado" : (($autorizacion==2) ? "autorizado" : "")) . " por ".$usuario["nombre"];

            $movimiento = $this->obtenerMovimiento($post)["movimiento"];

            $post["idmovimiento"] = $idmovimientoinventario;
            $post["origenmovimiento"] = "M";
            $post["tipomovimiento"] = (($autorizacion==1) ? "E" : (($autorizacion==2) ? "S" : ""));
            $post["idalmacen"] = $movimiento["idalmacen"];

            $query = "
            update
                tmovimientosinventario
            set
                idregistrosalida = '".$idusuario."',
                tiporegistrosalida = 'U',
                autorizacion = '".$autorizacion."'
            where
                idmovimientoinventario = '".$idmovimientoinventario."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con,$query)) {
                $correcto = false;
            }

            foreach ($movimiento["productos"] as $partida) {
                $post["idproducto"] = $partida["idproducto"];
                $post["idtalla"] = $partida["idtalla"];
                $post["idcolor"] = $partida["idcolor"];
                $post["cantidad"] = $partida["cantidad"];

                $this->claseProductos = new Productos();
                if ($this->claseProductos->actualizarExistencia($post)["respuesta"]=="ERROR") {
                    $correcto = false;
                }
            }

            $this->claseNotificaciones = new Notificaciones();
            $post["tipousuario"] = "U";
            $post["idtiponotificacion"] = 3;
            $post["mensaje"] = $mensaje;
            $post["idorigen"] = $idmovimientoinventario;
            $post["idalmacen"] = $idalmacen;
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }

            $post["tipousuario"] = "V";
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }

            if($correcto){

                $post["idactividad"] = 41;
                $post["comentarios"] = (($autorizacion==1) ? "Rechazo" : (($autorizacion==2) ? "Autorizo" : "")) . " el movimiento #" . $idmovimientoinventario;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>$titulo,"mensaje"=>$mensaje,"formulario" => "formRecepcion");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al actualizar el movimiento.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Movimiento.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarMovimiento($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["almacen"]);
        $idalmacens = mysqli_real_escape_string($this->con,$post["almacens"]);
        $tipomovimiento = mysqli_real_escape_string($this->con,$post["tipomovimiento"]);
        $notas = mysqli_real_escape_string($this->con,$post["txtNotas"]);

        $fecha = date("Y-m-d H:i:s");

        // recuperamos los folios
        $folio = $this->obtenerFolio($idalmacen)["folio"];
        $folio2 = $this->obtenerFolio($idalmacens)["folio"];

        // obtener usuario
        $this->claseUsuarios = new Usuarios();
        // $post["idusuario"] = $idusuario;
        $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"];

        // determinamos la autorizacion
        // si es una salida o entrada, la autorización no es necesaria, por lo tanto, automáticamente se asigna como aceptada ("2")
        // si es un traspaso, sólo si el usuario que está dando de alta el movimiento tiene asignado el almacen de salida, se autoriza automaticamente ("2"). De lo contrario, se queda en espera de autorizar ("0")
        $camposautorizacion = "";
        $valoresautorizacion = "";
        if ($tipomovimiento=="3") {
            $autorizacion = ((strpos($usuario["almacenes"], $idalmacen) !== false) ? '2' : '0');
            if ($autorizacion=='2') {
                $camposautorizacion = "
                idregistrosalida,
                tiporegistrosalida,
                ";
                $valoresautorizacion = "
                '".$idusuario."',
                'U',
                ";
            }
        } else {
            $autorizacion = '2';
        }

        $correcto = true;

        try{
            $query = "
            insert into
                tmovimientosinventario
                (
                    idusuario,
                    tipousuario,
                    idtipomovimiento,
                    idalmacen,
                    idalmacensecundario,
                    folio,
                    folio2,
                    notas,
                    fecha,
                    status,
                    ".$camposautorizacion."
                    autorizacion
                )
            values
                (
                    '".$idusuario."',
                    'U',
                    '".$tipomovimiento."',
                    '".$idalmacen."',
                    '".$idalmacens."',
                    '".$folio."',
                    '".$folio2."',
                    '".$notas."',
                    '".$fecha."',
                    'A',
                    ".$valoresautorizacion."
                    '".$autorizacion."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $idmovimientoinventario = mysqli_insert_id($this->con);

                // actualizar folios
                $post["folio"] = $folio;
                $post["idalmacen"] = $idalmacen;
                $this->actualizarFolio($post);
                $post["folio"] = $folio2;
                $post["idalmacen"] = $idalmacens;
                $this->actualizarFolio($post);

                $tipom = (($tipomovimiento==1) ? "E" : "S");

                $query = "
                insert into
                    tmovimientoinventarioproductos
                    (
                        idmovimientoinventario,
                        idproducto,
                        idcolor,
                        idtalla,
                        idalmacen,
                        idalmacensecundario,
                        idtipomovimiento,
                        cantidad
                    )
                select
                    '".$idmovimientoinventario."',
                    idproducto,
                    idcolor,
                    idtalla,
                    '".$idalmacen."',
                    '".$idalmacens."',
                    '".$tipomovimiento."',
                    cantidad
                from
                    tmovimientoinventarioproductostmp
                where
                    idusuario = '".$idusuario."'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con,$query)) {
                    $correcto = false;
                }

                // actualizamos las existencias
                $post["idmovimiento"] = $idmovimientoinventario;
                $post["origenmovimiento"] = "M";
                $post["tipomovimiento"] = (($tipomovimiento==1) ? "E" : "S");
                $post["idalmacen"] = $idalmacen;

                $partidas = $this->obtenerPartidasTMP($idusuario);
                foreach ($partidas["partidas"] as $partida) {
                    
                    $post["idproducto"] = $partida["idproducto"];
                    $post["idtalla"] = $partida["idtalla"];
                    $post["idcolor"] = $partida["idcolor"];
                    $post["cantidad"] = $partida["cantidad"];

                    // solamente si la autorizacion es igual a 2 (permitido), se actualizan existencias
                    if ($autorizacion == "2") {
                        $this->claseProductos = new Productos();
                        $this->claseProductos->actualizarExistencia($post);
                    }
                    
                }

                $query = "
                delete from
                    tmovimientoinventarioproductostmp
                where
                    idusuario = '".$idusuario."'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con,$query)) {
                    $correcto = false;
                }

                if ($correcto) {

                    $post["idactividad"] = 40;
                    $post["comentarios"] = "Agrego el movimiento #" . $idmovimientoinventario;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Movimiento Agregrado","mensaje"=>"Se ha agregado el movimiento correctamente.","formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al agregar el movimiento.");
                }

            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el movimiento.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Productos Temporales Version 1.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function obtenerPartidasTMP($idusuario) {
        $idusuario = mysqli_real_escape_string($this->con,$idusuario);

        try{
            $query = "
            select
                count(*) as total
            from
                tmovimientoinventarioproductostmp
            where
                idusuario='".$idusuario."'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($total) {
                // el query recupera los nombre de producto, color y talla, pero se podria hacer una vista
                $query = "
                select
                    *
                from
                    vmovimientoproductostmp
                where 
                    idusuario='".$idusuario."'
                ";
    
                $partidas = mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK","partidas"=>$partidas);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontraron partidas.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Obtener Productos Temporales Version 2.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function obtenerPartidasTMP2($idusuario) {
        $idusuario = mysqli_real_escape_string($this->con,$idusuario);

        try{
            $query = "
            select
                count(*) as total
            from
                tmovimientoinventarioproductostmp
            where
                idusuario='".$idusuario."'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($total) {
                // el query recupera los nombre de producto, color y talla, pero se podria hacer una vista
                $query = "
                select
                    m.*,
                    concat(m.producto,' : ',group_concat(distinct(m.color),' / ',t.tallas separator '-_-')) as lista
                from
                    vmovimientoproductostmp m
                left join
                    (
                        select
                            idproducto,
                            idcolor,
                            group_concat(cantidad,' - ',talla separator ' / ') as tallas
                        from
                            vmovimientoproductostmp
                        where 
                            idusuario = '".$idusuario."'
                        group by
                            idproducto,idcolor
                    ) t
                on
                    m.idcolor = t.idcolor
                    and m.idproducto = t.idproducto
                where
                    idusuario = '".$idusuario."'
                group by
                    idproducto
                ";
    
                $partidas = mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK","partidas"=>$partidas);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontraron partidas.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Obtener Partida Temporal. Recupera la cantidad de una partida con cierta talla y color especificados
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerPartidaTMP($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con,$post["idproducto"]);
        $idcolor = mysqli_real_escape_string($this->con,$post["idcolor"]);
        $idtalla = mysqli_real_escape_string($this->con,$post["idtalla"]);

        try{
            $query = "
            select
                *
            from
                tmovimientoinventarioproductostmp
            where
                idusuario='".$idusuario."'
                and idproducto='".$idproducto."'
                and idcolor='".$idcolor."'
                and idtalla='".$idtalla."'
            ";

            // $this->claseQueries->guardarQuery($query);

            $partida = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if ($partida["idtmp"]>0) {
                $respuesta = array("respuesta"=>"OK","partida"=>$partida);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontró la partida.");
            }
            
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Agregar Partida Temporal.
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function agregarPartidaTMP($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con,$post["idproducto"]);
        $idpartida = mysqli_real_escape_string($this->con,$post["idpartida"]);

        $correcto = true;

        $this->claseProductos = new Productos();

        try{

            $post["ordenar"] = 1;
            $colores = $this->claseProductos->obtenerColoresProducto($post);
            $tallas = $this->claseProductos->obtenerTallasProducto($post);
            foreach ($colores["colores"] as $color) {
                foreach ($tallas["tallas"] as $talla) {
                    $cantidad = mysqli_real_escape_string($this->con,$post["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]);
                    if ($cantidad>0) {
                        $query = "
                        select
                            *
                        from
                            tmovimientoinventarioproductostmp
                        where
                            idusuario = '".$idusuario."'
                            and idproducto = '".$idproducto."'
                            and idtalla = '".$talla["idtalla"]."'
                            and idcolor = '".$color["idcolor"]."'
                        ";

                        if (mysqli_num_rows(mysqli_query($this->con,$query))==0) {
                            $query = "
                            insert into
                                tmovimientoinventarioproductostmp
                                (
                                    idusuario,
                                    idpartida,
                                    idproducto,
                                    idtalla,
                                    idcolor,
                                    cantidad
                                )
                            values
                                (
                                    '".$idusuario."',
                                    '".$idpartida."',
                                    '".$idproducto."',
                                    '".$talla["idtalla"]."',
                                    '".$color["idcolor"]."',
                                    '".$cantidad."'
                                )
                            ";
                        } else {
                            $query = "
                            update
                                tmovimientoinventarioproductostmp
                            set
                                cantidad = '".$post["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]."'
                            where
                                idusuario = '".$idusuario."'
                                and idproducto = '".$idproducto."'
                                and idtalla = '".$talla["idtalla"]."'
                                and idcolor = '".$color["idcolor"]."'
                            ";
                        }

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con,$query)) {
                            $correcto = false;
                        }
                        
                    }
                }
            }
            
            if ($correcto) {
                $respuesta = array("respuesta"=>"OK","aumentaidpartida"=>true);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el producto.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Editar Partida Temporal.
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function editarPartidaTMP($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con,$post["idproducto"]);
        $idpartida = mysqli_real_escape_string($this->con,$post["idpartida"]);

        $correcto = true;

        $this->claseProductos = new Productos();

        try{

            // borrar y reinsertar
            $this->eliminarPartidaTMP($post);

            $colores = $this->claseProductos->obtenerColoresProducto($post);
            $tallas = $this->claseProductos->obtenerTallasProducto($post);
            foreach ($colores["colores"] as $color) {
                foreach ($tallas["tallas"] as $talla) {
                    $cantidad = mysqli_real_escape_string($this->con,$post["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]);
                    if ($cantidad>0) {
                        // $query = "
                        // update
                        //     tmovimientoinventarioproductostmp
                        // set
                        //     cantidad = '".$cantidad."'
                        // where
                        //     idusuario = '".$idusuario."'
                        //     and idproducto = '".$idproducto."'
                        //     and idtalla = '".$talla["idtalla"]."'
                        //     and idcolor = '".$color["idcolor"]."'
                        // ";
                        $query = "
                        insert into
                            tmovimientoinventarioproductostmp
                            (
                                idusuario,
                                idpartida,
                                idproducto,
                                idtalla,
                                idcolor,
                                cantidad
                            )
                        values
                            (
                                '".$idusuario."',
                                '".$idpartida."',
                                '".$idproducto."',
                                '".$talla["idtalla"]."',
                                '".$color["idcolor"]."',
                                '".$cantidad."'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con,$query)) {
                            $correcto = false;
                        }
                        
                    }
                }
            }            
            
            if ($correcto) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el producto.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Eliminar Partida Temporal.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function eliminarPartidasTMP($idusuario) {
        $idusuario = mysqli_real_escape_string($this->con,$idusuario);

        try{

            $query = "
            delete from
                tmovimientoinventarioproductostmp
            where
                idusuario = '".$idusuario."'
            ";

            // $this->claseQueries->guardarQuery($query);
            
            if (mysqli_query($this->con,$query)) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudieron eliminar las partidas.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
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
    public function eliminarPartidaTMP($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idpartida = mysqli_real_escape_string($this->con,$post["idpartida"]);

        try{

            $query = "
            delete from
                tmovimientoinventarioproductostmp
            where
                idpartida = '".$idpartida."'
                and idusuario = '".$idusuario."'
            ";

            $this->claseQueries->guardarQuery($query);
            
            if (mysqli_query($this->con,$query)) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el producto.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Validar Existencias Almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarExistenciasAlmacen($post){
        $idproducto = mysqli_real_escape_string($this->con,$post["idproducto"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);

        $correcto = true;

        $this->claseProductos = new Productos();

        try{
            $colores = $this->claseProductos->obtenerColoresProducto($post);
            $tallas = $this->claseProductos->obtenerTallasProducto($post);
            foreach ($colores["colores"] as $color) {
                foreach ($tallas["tallas"] as $talla) {
                    $cantidad = mysqli_real_escape_string($this->con,$post["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]);
                    if ($cantidad>0) {
                        $post["idtalla"] = $talla["idtalla"];
                        $post["idcolor"] = $color["idcolor"];
                        // $datos = $this->obtenerExistenciaProductoAlmacen($post)["existencia"];
                        $datos = $this->claseProductos->obtenerExistenciasYReservadoProducto($post);
                        $total = $datos["existencias"] - $datos["reservado"];
                        if ($total<$cantidad) {
                            $correcto = false;
                            $nombrecolor = $color["color"];
                            $nombretalla = $talla["talla"];
                            break 2;
                        }
                    }
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"La cantidad indicada para el color \"" . $nombrecolor . "\" y talla \"" . $nombretalla . "\" no posee suficientes existencias.","titulo"=>"Atención");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
        
    }

    /**
     * Obtener Folio. Recupera el folio del movimiento actual de una sucursal
     * 
     * @access private
     * @param int $idalmacen
     * @return array
     */
    private function obtenerFolio($idalmacen) {
        $idalmacen = mysqli_real_escape_string($this->con,$idalmacen);

        try{
            $query = "
            select
                foliomovimiento
            from
                tsucursales
            where
                idalmacen = '".$idalmacen."'
            ";

            $folio = mysqli_fetch_assoc(mysqli_query($this->con,$query))["foliomovimiento"] + 1;

            if ($folio>0) {
                $respuesta = array("respuesta"=>"OK","folio"=>$folio);
            } else {
                $respuesta = array("respuesta"=>"ERROR","folio"=>0,"tipo"=>"mensaje","mensaje"=>"No se encontró el folio.");
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Obtener Folio. Recupera el folio del movimiento actual de una sucursal
     * 
     * @access private
     * @param array $post
     * @return array
     */
    private function actualizarFolio($post) {
        $folio = mysqli_real_escape_string($this->con,$post["folio"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);

        try{
            $query = "
            update
                tsucursales
            set
                foliomovimiento = '".$folio."'
            where
                idalmacen = '".$idalmacen."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","folio"=>0,"tipo"=>"mensaje","mensaje"=>"No se pudo actualizar el folio.");
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Validar Movimiento. Revisa si hay al menos un producto agregado y si no hay ningún producto que exceda la existencia actual (cuando el tipo de movimiento es una salida/traspaso)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarMovimiento($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipomovimiento = mysqli_real_escape_string($this->con,$post["tipomovimiento"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);

        $correcto = true;

        try{
            // verificamos primero que haya al menos un producto
            $productos = $this->obtenerPartidasTMP($idusuario);

            if ($productos["respuesta"]=="ERROR") {
                $correcto = false;
                $mensaje = "Debes tener al menos un producto agregado.";
            }

            if ($tipomovimiento==2 || $tipomovimiento==3) {
                $this->claseProductos = new Productos();
                foreach ($productos["partidas"] as $partida) {
                    $post["idproducto"] = $partida["idproducto"];
                    $post["idcolor"] = $partida["idcolor"];
                    $post["idtalla"] = $partida["idtalla"];
                    $datos = $this->claseProductos->obtenerExistenciasYReservadoProducto($post);

                    $cantidad = $datos["existencias"] - $datos["reservado"];

                    if ($cantidad<$partida["cantidad"]) {
                        $correcto = false;
                        $mensaje = "La cantidad indicada para el color " . $partida["color"] . " y talla " . $partida["talla"] . " excede la existencia disponible";
                    }
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","titulo"=>"Atención","tipo"=>"mensaje","mensaje"=>$mensaje);
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Recibir Productos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function recibirProductos($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idmovimientoinventario = mysqli_real_escape_string($this->con,$post["idmovimientoinventario"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);

        $correcto = true;

        $this->claseProductos = new Productos();

        try {
            // se debe recorrer el arreglo de cantidades y añadir las existencias segun la cantidad especificada
            // aquellas cantidades que no hayan sido escritas o sean menor a la cantidad de la partida, se regresaran
            $recepcion = 0;
            foreach ($_POST["txtCantidad"] as $i => $cantidad) {
                $cantidad = mysqli_real_escape_string($this->con,$cantidad);
                $cantidad = ($cantidad=="" ? 0 : $cantidad);
                $idmovimientoproducto = mysqli_real_escape_string($this->con,$post["idmovimientoproducto"][$i]);

                $query = "
                select
                    *
                from
                    tmovimientoinventarioproductos
                where
                    idmovimientoinventarioproducto = '".$idmovimientoproducto."'
                ";
                $partida = mysqli_fetch_assoc(mysqli_query($this->con,$query));

                $cantdevolver = $partida["cantidad"] - $cantidad;

                // actualizamos las existencias
                $post["idmovimiento"] = $partida["idmovimientoinventario"];
                $post["origenmovimiento"] = "M";
                $post["tipomovimiento"] = "E";
                $post["idalmacen"] = $partida["idalmacensecundario"];
                
                $post["idproducto"] = $partida["idproducto"];
                $post["idtalla"] = $partida["idtalla"];
                $post["idcolor"] = $partida["idcolor"];
                $post["cantidad"] = $cantidad;

                $respuesta = $this->claseProductos->actualizarExistencia($post);
                if ($respuesta["respuesta"]=="ERROR") {
                    $correcto = false;
                }

                // si hay producto que se va a devolver, se debe guardar una notificacion
                if ($cantdevolver>0) {
                    $recepcion = 1;
                    $post["idalmacen"] = $partida["idalmacen"];
                    $post["cantidad"] = $cantdevolver;
                    
                    $respuesta = $this->claseProductos->actualizarExistencia($post);
                    if ($respuesta["respuesta"]=="ERROR") {
                        $correcto = false;
                    }

                }else{
                    $recepcion = 2;
                }
                
                $query = "
                update
                    tmovimientoinventarioproductos
                set
                    cantidadrecibida = '".$cantidad."'
                where
                    idmovimientoinventarioproducto = '".$partida["idmovimientoinventarioproducto"]."'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con,$query)) {
                    $correcto = false;
                }
            }
            $query = "
            update
                tmovimientosinventario
            set
                recepcionparcial = '".$recepcion."',
                idregistroentrada = '".$idusuario."',
                tiporegistroentrada = 'U'
            where
                idmovimientoinventario = '".$idmovimientoinventario."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con,$query)) {
                $correcto = false;
            }

            // notificacion
            if ($recepcion==1) {
                $mensaje = "El movimiento #".$idmovimientoinventario." ha sido recibido parcialmente";
            } else {
                $mensaje = "El movimiento #".$idmovimientoinventario." ha sido recibido";
            }
            $this->claseNotificaciones = new Notificaciones();
            $post["tipousuario"] = "U";
            $post["idtiponotificacion"] = 5;
            $post["mensaje"] = $mensaje;
            $post["idorigen"] = $idmovimientoinventario;
            $post["idalmacen"] = $idalmacen;
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }
            $post["tipousuario"] = "V";
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }

            if ($correcto) {

                $post["idactividad"] = 42;
                $post["comentarios"] = "Recibio los productos del movimiento #" . $idmovimientoinventario;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>"Productos Recibidos","mensaje"=>"Los productos del movimiento se han recibido exitosamente.","formulario" => "formRecepcion");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al recibir los productos.");
            }
            
        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Cancelar Movimiento.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function cancelarMovimiento($post) {
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idmovimientoinventario = mysqli_real_escape_string($this->con,$post["idmovimientoinventario"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);
        $motivo = mysqli_real_escape_string($this->con,$post["motivo"]);

        $correcto = true;

        $this->claseProductos = new Productos();

        try {

            $movimiento = $this->obtenerMovimiento($post)["movimiento"];

            // actualizamos las existencias
            $post["idmovimiento"] = $movimiento["idmovimientoinventario"];
            $post["origenmovimiento"] = "M";
            $post["tipomovimiento"] = "E";
            $post["idalmacen"] = $movimiento["idalmacen"];
            
            foreach ($movimiento["productos"] as $partida) {
                $post["idproducto"] = $partida["idproducto"];
                $post["idtalla"] = $partida["idtalla"];
                $post["idcolor"] = $partida["idcolor"];
                $post["cantidad"] = $partida["cantidad"];

                $respuesta = $this->claseProductos->actualizarExistencia($post);
                if ($respuesta["respuesta"]=="ERROR") {
                    $correcto = false;
                }
            }

            $query = "
            update
                tmovimientosinventario
            set
                idregistroentrada = '".$idusuario."',
                tiporegistroentrada = 'U',
                autorizacion = 1,
                status = 'C',
                motivo = '".$motivo."'
            where
                idmovimientoinventario = '".$movimiento["idmovimientoinventario"]."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con,$query)) {
                $correcto = false;
            }

            $mensaje = "El movimiento #".$movimiento["idmovimientoinventario"]." ha sido cancelado";

            $this->claseNotificaciones = new Notificaciones();
            $post["tipousuario"] = "U";
            $post["idtiponotificacion"] = 6;
            $post["mensaje"] = $mensaje;
            $post["idorigen"] = $idmovimientoinventario;
            $post["idalmacen"] = $idalmacen;
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }

            $post["tipousuario"] = "V";
            if ($this->claseNotificaciones->agregarNotificaciones($post)["respuesta"]=="ERROR") {
                $correcto = false;
            }

            if ($correcto) {

                $post["idactividad"] = 43;
                $post["comentarios"] = "Cancelo la recepcion de productos del movimiento #" . $idmovimientoinventario;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>"Movimiento Cancelado","mensaje"=>"Se ha cancelado la recepción de productos","formulario" => "formRecepcion");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al cancelar la recepción.");
            }
            
        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
?>