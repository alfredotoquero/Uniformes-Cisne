<?php
class TipoCambio{
    private $con;
    private $claseQueries;
    private $claseLogs;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Tipo de Cambio.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTipoCambio(){
        
        try{
            $query = "
            select
                *
            from
                tcatformaspago
            where
                idformapago = 2
            ";

            $tipocambio = mysqli_query($this->con,$query);

            if(mysqli_num_rows($tipocambio)){
                $tipocambio = mysqli_fetch_assoc($tipocambio);

                $respuesta = array("respuesta"=>"OK","tipocambio"=>$tipocambio);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar el tipo de cambio.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Actualizar Tipo de Cambio.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarTipoCambio($post){
        $tipocambio = mysqli_real_escape_string($this->con,$post["txtCambio"]);
        
        try{
            $query = "
            update
                tcatformaspago
            set
                pesos = '".$tipocambio."'
            where
                idformapago = 2
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $post["idactividad"] = 80;
                $post["comentarios"] = "Actualizo el tipo de cambio a " . $tipocambio;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecerrarfancy","titulo"=>"Tipo de Cambio Actualizado","mensaje"=>"Se actualizó correctamente el tipo de cambio","formulario" => "formCambio");
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo actualizar el tipo de cambio.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }
    
}
?>