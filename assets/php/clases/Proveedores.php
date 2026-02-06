<?php
class Proveedores{
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
     * Obtener Proveedores.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProveedores($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        $ordenarpornombre = mysqli_real_escape_string($this->con,$post["ordenarpornombre"]);

        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");
        
        $orderby = "";
        $orderby .= (($ordenarpornombre==1) ? " order by nombre" : "");
        
        try{

            $query = "
            select
                count(*) as total
            from
                tproveedores
            where
                status = 1
                ".$where."
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    tproveedores
                where
                    status = 1
                    ".$where."
                ".$orderby."
                ";

                // $this->claseQueries->guardarQuery($query);
                $proveedores = mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK","proveedores"=>$proveedores);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron proveedores registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Proveedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProveedor($post){
        $idproveedor = mysqli_real_escape_string($this->con,$post["idproveedor"]);

        try{
            $query = "
            select
                *
            from
                tproveedores
            where
                idproveedor = '".$idproveedor."'
            ";
            $proveedor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($proveedor["idproveedor"]>0){
                $respuesta = array("respuesta"=>"OK","proveedor"=>$proveedor);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del proveedor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Proveedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarProveedor($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);

        try{
            $query = "
            insert into
                tproveedores
                (
                    nombre
                )
            values
                (
                    '".$nombre."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $post["idactividad"] = 52;
                $post["comentarios"] = "Agrego el proveedor " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Proveedor Agregrado","mensaje"=>"Se ha agregado el proveedor correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el proveedor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Editar Proveedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarProveedor($post){
        $idproveedor = mysqli_real_escape_string($this->con,$post["idproveedor"]);
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);

        $nombrep = $this->obtenerProveedor($post)["proveedor"]["nombre"];

        try{
            $query = "
            update
                tproveedores
            set
                nombre = '".$nombre."'
            where
                idproveedor = '".$idproveedor."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $post["idactividad"] = 53;
                $post["comentarios"] = "Edito el proveedor " . (($nombrep!=$nombre) ? $nombrep . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Proveedor Editado","mensaje"=>"Se ha editado el proveedor correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el proveedor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Eliminar Proveedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarProveedor($post){
        $idproveedor = mysqli_real_escape_string($this->con,$post["idproveedor"]);

        try{
            $query = "
            update
                tproveedores
            set
                status = 0
            where
                idproveedor = '".$idproveedor."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $nombre = $this->obtenerProveedor($post)["proveedor"]["nombre"];
                $post["idactividad"] = 54;
                $post["comentarios"] = "Elimino el proveedor " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Proveedor Eliminado","mensaje"=>"Se ha eliminado el proveedor correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el proveedor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }
    
}
?>