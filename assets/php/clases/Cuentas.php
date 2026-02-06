<?php
class Cuentas{
    private $con;
    private $claseQueries;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
    }

    /**
     * Obtener Cuentas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCuentas($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");
        
        try{
            $query = "
            select
                *
            from
                tcuentas
            where
                status = 'P'
                ".$where."
            ";
            $cuentas = mysqli_query($this->con,$query);

            if(mysqli_num_rows($cuentas)>0){
                $respuesta = array("respuesta"=>"OK","cuentas"=>$cuentas);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron cuentas pendientes registradas.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Cuenta.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCuenta($post){
        $idcuenta = mysqli_real_escape_string($this->con,$post["idcuenta"]);

        try{
            $query = "
            select
                *
            from
                tcuentas
            where
                idcuenta = '".$idcuenta."'
            ";
            $cuenta = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($cuenta["idcuenta"]>0){
                $respuesta = array("respuesta"=>"OK","cuenta"=>$cuenta);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información de la cuenta.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Entregar Cuenta.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function entregarCuenta($post){
        $idcuenta = mysqli_real_escape_string($this->con,$post["idcuenta"]);

        try{
            $query = "
            update
                tcuentas
            set
                status = 'E'
            where
                idcuenta = '".$idcuenta."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Cuenta Entregada","mensaje"=>"Se ha entregado la cuenta correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo entregar la cuenta.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }
    
}
