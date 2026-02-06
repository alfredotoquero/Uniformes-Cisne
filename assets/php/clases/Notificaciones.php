<?php
class Notificaciones{
    private $con;
    private $claseQueries;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
    }

    /**
     * Obtener Notificaciones. Las notificaciones se obtienen unicamente por usuario, no todas (de momento)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerNotificaciones($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $sinleer = mysqli_real_escape_string($this->con,$post["sinleer"]);
        $obtenerrecientes = mysqli_real_escape_string($this->con,$post["obtenerrecientes"]);
        $tipo = mysqli_real_escape_string($this->con,$post["tipo"]);

        $where = "";
        $where2 = "";
        $limit = "";

        $where .= (($sinleer) ? " and leida = 0" : "");
        $where .= (($obtenerrecientes) ? " and TIMESTAMPDIFF(SECOND,created_at,now()) < 30 and notificada = 0 " : "");
        $where2 .= (($obtenerrecientes) ? " and TIMESTAMPDIFF(SECOND,created_at,now()) < 30 and notificada = 0 " : "");
        
        $limit .= (($tipo=="campana") ? " limit 10" : "");

        try{
            
            $query = "
            select
                sum(case when leida = 0 then 1 else 0 end) as no_leidas,
                sum(case when atendida = 0 then 1 else 0 end) as no_atendidas
            from
                vnotificaciones
            where
                idusuario = '".$idusuario."'
                and tipousuario = '".$tipousuario."'
                ".$where2."
            ";

            // $this->claseQueries->guardarQuery($query);

            $noleidas = mysqli_fetch_assoc(mysqli_query($this->con,$query))["no_leidas"];
            $noatendidas = mysqli_fetch_assoc(mysqli_query($this->con,$query))["no_atendidas"];

            if($noatendidas){

                $query = "
                select
                    *
                from
                    vnotificaciones
                where
                    idusuario = '".$idusuario."'
                    and tipousuario = '".$tipousuario."'
                    and atendida = 0
                    ".$where."
                order by
                    idnotificacion desc
                ".$limit."
                ";

                // $this->claseQueries->guardarQuery($query);

                $notificaciones = mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK","notificaciones"=>$notificaciones,"cant_notificaciones"=>$noatendidas,"cant_notificaciones_no_leidas"=>$noleidas);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No hay notificaciones registradas sin atender para este usuario.","cant_notificaciones"=>$noatendidas,"cant_notificaciones_no_leidas"=>$noleidas);
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Notificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerNotificacion($post){
        $idnotificacion = mysqli_real_escape_string($this->con,$post["idnotificacion"]);

        try{
            $query = "
            select
                *
            from
                vnotificaciones
            where
                idnotificacion = '".$idnotificacion."'
            ";
            $notificacion = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($notificacion["idnotificacion"]>0){
                $respuesta = array("respuesta"=>"OK","notificacion"=>$notificacion);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información de la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Notificaciones. Agrega las notificaciones a los usuarios a los que les corresponde
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarNotificaciones($post){
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $idtiponotificacion = mysqli_real_escape_string($this->con,$post["idtiponotificacion"]);
        $mensaje = mysqli_real_escape_string($this->con,$post["mensaje"]);
        $idorigen = mysqli_real_escape_string($this->con,$post["idorigen"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["idalmacen"]);
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        // $fecha = date("Y-m-d H:i:s");

        $wherev = "";
        $whereu = "";
        $wherev .= (($idalmacen!="") ? " and idsucursal in 
                        (
                            select
                                idsucursal
                            from
                                tsucursales
                            where
                                idalmacen = '".$idalmacen."'
                        ) " : "");
        $wherev .= (($idusuario!="") ? " and idvendedor = '".$idusuario."'" : "");
        $whereu .= (($idalmacen!="") ? " and FIND_IN_SET('".$idalmacen."',almacenes) " : "");
        $whereu .= (($idusuario!="") ? " and idusuario = '".$idusuario."' " : "");
        
        try{

            if ($tipousuario=="V") {
                $query = "
                insert into
                    tnotificaciones
                    (
                        idusuario,
                        tipousuario,
                        idtiponotificacion,
                        mensaje,
                        idorigen
                    )
                select 
                    idvendedor,
                    '".$tipousuario."',
                    '".$idtiponotificacion."',
                    '".$mensaje."',
                    '".$idorigen."'
                from
                    tvendedores
                where
                    1=1
                    ".$wherev."
                ";
            }else if($tipousuario=="U") {
                $query = "
                    insert into
                        tnotificaciones
                        (
                            idusuario,
                            tipousuario,
                            idtiponotificacion,
                            mensaje,
                            idorigen
                        )
                    select 
                        idusuario,
                        '".$tipousuario."',
                        '".$idtiponotificacion."',
                        '".$mensaje."',
                        '".$idorigen."'
                    from
                        tusuarios
                    where
                        1=1
                        ".$whereu."
                ";
                
            }

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","mensaje"=>"Las notificaciones se guardaron correctamente");
                // $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Notificacion Agregrada","mensaje"=>"Se ha agregado la notificacion correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudieron agregar las notificaciones.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Agregar Notificaciones ADSB. Agrega las notificaciones a los usuarios a los que les corresponde (tipousuario o trusuariosecciones solicitud de compra)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarNotificacionesADSB($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $idtiponotificacion = mysqli_real_escape_string($this->con,$post["idtiponotificacion"]);
        $mensaje = mysqli_real_escape_string($this->con,$post["mensaje"]);
        $idorigen = mysqli_real_escape_string($this->con,$post["idorigen"]);

        $where = "";
        $where = (($tipousuario==1) ? " and tipousuario like '%A%'" : (($tipousuario==2) ? " and tipousuario like '%B%'" : (($tipousuario==3) ? " and (tipousuario like '%S%' or tipousuario like '%D%')" : (($tipousuario==4) ? " and idusuario in (select idusuario from trusuariosecciones where idseccion = 4 and sololectura = 0)" :  ""))));

        try{

            $query = "
                insert into
                    tnotificaciones
                    (
                        idusuario,
                        tipousuario,
                        idtiponotificacion,
                        mensaje,
                        idorigen
                    )
                select 
                    idusuario,
                    'U',
                    '".$idtiponotificacion."',
                    '".$mensaje."',
                    '".$idorigen."'
                from
                    tusuarios
                where
                    status = 'A'
                    and idusuario != '".$idusuario."'
                    ".$where."
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                // $respuesta = array("respuesta"=>"OK","mensaje"=>"Las notificaciones se guardaron correctamente");
                $respuesta = true;
            }else{
                // $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudieron agregar las notificaciones.");
                $respuesta = false;
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar Como Notificada. Marca una notificacion especifica como notificada.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoNotificada($post){
        $idnotificacion = mysqli_real_escape_string($this->con,$post["idnotificacion"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            update
                tnotificaciones
            set
                notificada = 1,
                fecha_notificada = '".$fecha."'
            where
                idnotificacion = '".$idnotificacion."'
                and notificada = 0
            ";

            // $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    // /**
    //  * Marcar Diez Como Notificadas. Marca, a lo sumo, diez notificaciones como notificadas y leidas (esto sucedería al dar clic en la campana para consultar las diez notificaciones más recientes). Pueden actualizarse menos de diez si es que alguna ya ha aparecido como mensaje toast
    //  * 
    //  * @access public
    //  * @param array $post
    //  * @return array
    //  */
    // public function marcarDiezComoNotificadas($post){
    //     $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
    //     $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
    //     $fecha = date("Y-m-d H:i:s");

    //     try{
    //         $query = "
    //         select
    //             idnotificacion
    //         from
    //             tnotificaciones
    //         where
    //             idusuario = '".$idusuario."'
    //             and tipousuario = '".$tipousuario."'
    //         order by
    //             idnotificacion
    //         limit
    //             10
    //         ";

    //         $notificaciones = mysqli_query($this->con,$query);

    //         while ($notificacion = mysqli_fetch_assoc($notificaiones)) {
    //             $query = "
    //             update
    //                 tnotificaciones
    //             set
    //                 notificada = 1,
    //                 fecha_notificada = '".$fecha."'
    //             where
    //                 idnotificacion = '".$notificacion["idnotificacion"]."'
    //                 and notificada = 0
    //             ";

    //             // $this->claseQueries->guardarQuery($query);

    //             mysqli_query($this->con,$query);
    //         }

    //         if(mysqli_query($this->con,$query)){
    //             $respuesta = array("respuesta"=>"OK");
    //         }else{
    //             $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
    //         }

    //     }catch(Exception $e){
    //         $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
    //     }finally{
    //         return $respuesta;
    //     }
    // }

    /**
     * Marcar Como Notificada. Marca una notificacion especifica como notificada.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoNotificadas($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            update
                tnotificaciones
            set
                notificada = 1,
                fecha_notificada = '".$fecha."'
            where
                idusuario = '".$idusuario."'
                and tipousuario = '".$tipousuario."'
                and notificada = 0
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar Como Leida.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoLeida($post){
        $idnotificacion = mysqli_real_escape_string($this->con,$post["idnotificacion"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            update
                tnotificaciones
            set
                leida = 1,
                fecha_leida = '".$fecha."'
            where
                idnotificacion = '".$idnotificacion."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar Diez Como Leidas. Marca las diez notificaciones más recientes (las que aparecen al abrir la campana) de un usuario como leidas. Pueden actualizarse menos de diez si es que alguna ya ha aparecido como mensaje toast
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarDiezComoLeidas($post){
        // $idnotificacion = mysqli_real_escape_string($this->con,$post["idnotificacion"]);
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            select
                idnotificacion
            from
                tnotificaciones
            where
                idusuario = '".$idusuario."'
                and tipousuario = '".$tipousuario."'
                and atendida = 0
            order by
                idnotificacion
            limit
                10
            ";

            $notificaciones = mysqli_query($this->con,$query);

            foreach ($notificaciones as $notificacion) {
                $query = "
                update
                    tnotificaciones
                set
                    leida = 1,
                    notificada = 1,
                    fecha_leida = '".$fecha."',
                    fecha_notificada = '".$fecha."'
                where
                    idnotificacion = '".$notificacion["idnotificacion"]."'
                    and leida = 0
                ";

                $this->claseQueries->guardarQuery($query);

                mysqli_query($this->con,$query);
            }

            if(mysqli_query($this->con,$query)){
                // $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda");
                $respuesta = array("respuesta"=>"OK");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar Como Leidas. Marca todas las notificaciones de un usuario como leidas. Se actualizan solamente aquellas que no habían sido marcadas anteriormente
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoLeidas($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            update
                tnotificaciones
            set
                leida = 1,
                fecha_leida = '".$fecha."'
            where
                idusuario = '".$idusuario."'
                and tipousuario = '".$tipousuario."'
                and leida = 0
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar una notificacion como atendida.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoAtendida($post){
        $idnotificacion = mysqli_real_escape_string($this->con,$post["idnotificacion"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = "
            update
                tnotificaciones
            set
                atendida = 1,
                fecha_atendida = '".$fecha."'
            where
                idnotificacion = '".$idnotificacion."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda","pantalla"=>"notificaciones");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Marcar Como Atendidas. Marca todas las notificaciones de un usuario como atendidas
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function marcarComoAtendidas($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $tipousuario = mysqli_real_escape_string($this->con,$post["tipousuario"]);
        $fecha = date("Y-m-d H:i:s");

        try{
            // $query = "
            // update
            //     tnotificaciones
            // set
            //     atendida = 0
            // where
            //     idusuario = '5'
            //     and atendida = 1
            // ";
            $query = "
            update
                tnotificaciones
            set
                atendida = 1,
                fecha_atendida = '".$fecha."'
            where
                idusuario = '".$idusuario."'
                and tipousuario = '".$tipousuario."'
                and atendida = 0
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"cargar","formulario" => "formBusqueda","pantalla"=>"notificaciones");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la notificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }
    
}
?>