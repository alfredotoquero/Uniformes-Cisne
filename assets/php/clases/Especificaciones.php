<?php
class Especificaciones{
    private $con;
    private $claseQueries;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
    }

    /**
     * Obtener Especificaciones.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificaciones($post){
        $idpedido = mysqli_real_escape_string($this->con,$post["idpedido"]);

        // $where = "";
        // $where .= (($idpedido!="") ? " and idpedido = '".$idpedido."'" : "");
        
        try{
            $query = "
            select
                *
            from
                tespecificaciones
            where
                idpedido = '".$idpedido."'
            ";
            $especificaciones = mysqli_query($this->con,$query);

            if(mysqli_num_rows($especificaciones)>0){
                $respuesta = array("respuesta"=>"OK","especificaciones"=>$especificaciones);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron especificaciones registradas para este pedido.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Especificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificacion($post){
        $idespecificacion = mysqli_real_escape_string($this->con,$post["idespecificacion"]);

        try{
            $query = "
            select
                *
            from
                tespecificaciones
            where
                idespecificacion = '".$idespecificacion."'
            ";
            $especificacion = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($especificacion["idespecificacion"]>0){
                $respuesta = array("respuesta"=>"OK","especificacion"=>$especificacion);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información de la especificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Especificacion Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarEspecificacionTMP($post,$files){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        // FALTA CAMBIAR LOS NOMBRES DE LA DERECHA
        $nombrediseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $calcas = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $poster = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $letrero = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $otros = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $fechaentrega = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $serigrafia = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $digital = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $cortevinil = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $lonabanner = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $bordado = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $especificaciones = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $autorizaciondiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $rproduccion = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $rdiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $raprobaciondiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        // guardar las imagenes
        // imagen1
        // imagen2
        // imagen3
        // imagen4
        // imagen5

        try{

            $query = "
            insert into
                tespecificacionestmp
                (
                    idusuario,
                    nombrediseno,
                    calcas,
                    poster,
                    letrero,
                    otros,
                    fechaentrega,
                    serigrafia,
                    digital,
                    cortevinil,
                    lonabanner,
                    bordado,
                    especificaciones,
                    autorizaciondiseno,
                    rproduccion,
                    rdiseno,
                    raprobaciondiseno
                )
            values
                (
                    '".$nombrediseno."',
                    '".$calcas."',
                    '".$poster."',
                    '".$letrero."',
                    '".$otros."',
                    '".$fechaentrega."',
                    '".$serigrafia."',
                    '".$digital."',
                    '".$cortevinil."',
                    '".$lonabanner."',
                    '".$bordado."',
                    '".$especificaciones."',
                    '".$autorizaciondiseno."',
                    '".$rproduccion."',
                    '".$rdiseno."',
                    '".$raprobaciondiseno."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                if (mysqli_query($this->con,$query)) {
                    $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Especificacion Agregrada","mensaje"=>"Se ha agregado la especificacion correctamente.","formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al agregar la especificacion (existencias)");
                }
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la especificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Asignar Productos a una Especificacion Temporal. Esta funcion se puede ejecutar despues de haber asignado anteriormente, por lo que se deben tomar las consideraciones para este caso
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarProductosEspecificacionTMP($post){
        $idespecificaciontmp = mysqli_real_escape_string($this->con,$post["idespecificaciontmp"]);
        // FALTA CORREGIR LOS NOMBRES DE LA DERECHA
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idespecificaciontmp = mysqli_real_escape_string($this->con,$post["idespecificaciontmp"]);
        $idpedidoproductotmp = mysqli_real_escape_string($this->con,$post["idpedidoproductotmp"]);
        $idpartidatmp = mysqli_real_escape_string($this->con,$post["idpartidatmp"]);
        // $idproductos = mysqli_real_escape_string($this->con,$post["idproductos"]);
        // ESTO ES UN ARREGLO, ENTONCES ¿SE DEBERIA ASIGNAR TAL CUAL?
        $idproductos = $post["idproductos"];

        try{

            // hay que revisar si hay registros de productos asignados para esta especificacion, para eliminarlos antes de asignar los que se seleccionaron en esta ocasion
            $query = "
            select
                count(*) as total
            from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '".$idespecificaciontmp."'
            ";

            $tieneregistros = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($tieneregistros) {
                $query = "
                delete from
                    trespecificacionesproductostmp
                where
                    idespecificaciontmp = '".$idespecificaciontmp."'
                ";

                $this->claseQueries->guardarQuery($query);
            }
            
            $correcto = true;
            foreach ($idproductos as $idproducto) {
                // NOTA: es posible que a una especificacion se le asignen productos sin tener idpedidoproducto, pero siempre deben tener una partida (idcotizacionproductotmp, en esta tabla se llama idpartidatmp)
                $query = "
                insert into
                    trespecificacionesproductostmp
                    (
                        idusuario,
                        idespecificaciontmp,
                        idpedidoproductotmp,
                        idpartidatmp,
                    )
                values
                    (
                        '".$idusuario."',
                        '".$idespecificaciontmp."',
                        '".$idpedidoproductotmp."',
                        '".$idpartidatmp."'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con,$query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Partidas Asignadas","mensaje"=>"Las partidas fueron asignadas correctamente a la especificacion temporal","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrio un error al asignar las partidas a la especifcacion temporal");
            }
        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Editar Especificacion Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarEspecificacionTMP($post){
        // FALTA CORREGIR LOS NOMBRES DE LA DERECHA
        $idespecificaciontmp = mysqli_real_escape_string($this->con,$post["idespecificaciontmp"]);
        $nombrediseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $calcas = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $poster = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $letrero = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $otros = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $fechaentrega = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $serigrafia = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $digital = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $cortevinil = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $lonabanner = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $bordado = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $especificaciones = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $autorizaciondiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $rproduccion = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $rdiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $raprobaciondiseno = mysqli_real_escape_string($this->con,$post["idusuario"]);

        try{
            $query = "
            update
                tespecificacionestmp
            set
                nombrediseno = '".$nombrediseno."',
                calcas = '".$calcas."',
                poster = '".$poster."',
                letrero = '".$letrero."',
                otros = '".$otros."',
                fechaentrega = '".$fechaentrega."',
                serigrafia = '".$serigrafia."',
                digital = '".$digital."',
                cortevinil = '".$cortevinil."',
                lonabanner = '".$lonabanner."',
                bordado = '".$bordado."',
                especificaciones = '".$especificaciones."',
                autorizaciondiseno = '".$autorizaciondiseno."',
                rproduccion = '".$rproduccion."',
                rdiseno = '".$rdiseno."',
                raprobaciondiseno = '".$raprobaciondiseno."'
            where
                idespecificaciontmp = '".$idespecificaciontmp."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Especificacion Temporal Editada","mensaje"=>"Se ha editado la especificacion correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar la especificacion.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Eliminar Especificacion Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarEspecificacionTMP($post){
        $idespecificaciontmp = mysqli_real_escape_string($this->con,$post["idespecificaciontmp"]);

        try{
            // si la especificacion ya tiene productos asignados, primero se deben eliminar esos registros antes de eliminar la especificacion
            $query = "
            delete from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '".$idespecificaciontmp."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {
                $query = "
                delete from
                    tespecificacionestmp
                where
                    idespecificaciontmp = '".$idespecificaciontmp."'
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con,$query)) {
                    $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Especificacion Temporal Eliminada","mensaje"=>"Se ha eliminado la especificacion temporal correctamente.","formulario" => "formBusqueda");
                }else{
                    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar la especificacion temporal.");
                }
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar la especificacion temporal.");
            }
        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }
    
}
?>