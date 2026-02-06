<?php
class Queries{
    private $con;

    function __construct() {
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Guardar Query.
     * 
     * @access public
     * @param array $query
     * @return array
     */
    public function guardarQuery($query){
        $query = mysqli_real_escape_string($this->con,$query);
        $fecha = date("Y-m-d H:i:s");

        try{
            $query = str_replace("\\n",PHP_EOL,$query);
            $query = str_replace("\\r","",$query);
            $query = str_replace("\\","",$query);
            $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/todoslosquerys.txt", 'a');//opens file in append mode
			fwrite($fp, $query . " fecha: ".$fecha."\n");  
			fclose($fp);

            $respuesta = array("respuesta"=>"OK","mensaje"=>"Se guardó el query correctamente");
        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }
    
}
?>