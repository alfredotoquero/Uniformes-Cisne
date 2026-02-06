<?php 
class Logs{
    private $con;

    public function __construct() {
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }
	
    /**
     * Obtener Usuarios.
     * 
     * @access public
     * @return array
     */
	public function obtenerUsuarios(){

        try {
            $query = "
            select
                *
            from
                tusuarios
            ";			
            
            $usuarios = mysqli_query($this->con,$query);
            
            $respuesta = array("respuesta"=>"OK","usuarios"=>$usuarios);

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Usuario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerUsuario($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);

        try {
            $query = "
            select
                *
            from
                tusuarios
            where
                idusuario and
                status = 1
            ";
            
            $result = mysqli_query($this->con,$query);

            $array = array();

            return  mysqli_fetch_array($result);

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Logs Filtrado.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerLogsFiltrado($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idactividad = mysqli_real_escape_string($this->con,$post["idactividad"]);
        $fechaI = mysqli_real_escape_string($this->con,$post["fechainicial"]);
        $fechaF = mysqli_real_escape_string($this->con,$post["fechafinal"]);
        
        $where = "";

        if ($fechaI != '' & $fechaF != '') {
            $fechaI = explode("/",$fechaI);
            $fechaI = $fechaI[2]."-".$fechaI[1]."-".$fechaI[0];
            $fechaF = explode("/",$fechaF);
            $fechaF = $fechaF[2]."-".$fechaF[1]."-".$fechaF[0];
            $where .= " AND DATE(fecha) >= '$fechaI' AND DATE(fecha) <= '$fechaF'";  
        }
        if($idusuario != '0' && $idusuario != null){
            $where .= " AND idusuario = $idusuario";
        }
        if($idactividad != '0' && $idactividad != null){
            $where .= " AND actividad = $idactividad";
        }

        try {
            $query = "
            select
                *
            from
                tlog
            where
                1=1
                ".$where."
            ";

            $logs = mysqli_query($this->con,$query);

            $respuesta = array("respuesta"=>"OK","logs"=>$logs);

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Catálogo Actividades.
     * 
     * @access public
     * @return array
     */
    public function obtenerCatActividades(){

        try {
            $query = "
            select
                *
            from
                tcatactividadeslog
            ";

            $actividades = mysqli_query($this->con,$query);
            
            $respuesta = array("respuesta"=>"OK","actividades"=>$actividades);

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Info Actividad.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerInfoActividad($post){
        $idactividad = mysqli_real_escape_string($this->con,$post["idactividad"]);

        try {
            $query = "
            select
                *
            from
                tcatactividadeslog
            where
                idactividad = '".$idactividad."'
            ";

            $actividad = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if ($actividad["idactividad"]>0) {
                $respuesta = array("respuesta"=>"OK","actividad"=>$actividad);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontró la actividad.");
            }

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    function primerDia() {
        /** Primer dia del mes actual **/
        $month = date('m');
        $year = date('Y');
        return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
    }

    function ultimoDia() {
        /** Ultimo dia del mes actual **/
        $month = date('m');
        $year = date('Y');
        $day = date("d", mktime(0,0,0, $month+1, 0, $year));
        return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
    }

    /**
     * Registrar Actividad. Registra la accion de un usuario
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function registrarActividad($post){
        $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idactividad = mysqli_real_escape_string($this->con,$post["idactividad"]);
        $comentarios = mysqli_real_escape_string($this->con,$post["comentarios"]);

        try {
            $query = "
            insert into
                tlog
                (
                    idusuario,
                    idactividad,
                    comentarios
                )
            values
                (
                    '".$idusuario."',
                    '".$idactividad."',
                    '".$comentarios."'
                )
            ";

            if (mysqli_query($this->con,$query)) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se registró la actividad");
            }

        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }
}
?>
