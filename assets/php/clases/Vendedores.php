<?php
class Vendedores{
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
     * Obtener Vendedores.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVendedores($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");

        try{

            $query = "
            select
                *
            from
                tvendedores
            where
                status = 'A'
                ".$where."
            ";
            $vendedores = mysqli_query($this->con,$query);

            if(mysqli_num_rows($vendedores)>0){
                $respuesta = array("respuesta"=>"OK","vendedores"=>$vendedores);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron vendedores registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Vendedor.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerVendedor($post){
        $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);

        try{
            $query = "
            select
                *
            from
                tvendedores
            where
                idvendedor = '".$idvendedor."'
            ";
            $vendedor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($vendedor["idvendedor"]>0){
                $respuesta = array("respuesta"=>"OK","vendedor"=>$vendedor);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del vendedor.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /** 
     * Agregar Vendedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarVendedor($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $usuario = mysqli_real_escape_string($this->con,$post["txtUsuario"]);
        $password = mysqli_real_escape_string($this->con,$post["txtPassword"]);
        $idsucursal = mysqli_real_escape_string($this->con,$post["slcSucursal"]);
        $descuento = mysqli_real_escape_string($this->con,$post["txtDescuento"]);

        try{
            $query = "
            insert into
                tvendedores
                (
                    nombre,
                    usuario,
                    password,
                    idsucursal,
                    descuento
                )
            values
                (
                    '".$nombre."',
                    '".$usuario."',
                    '".$password."',
                    '".$idsucursal."',
                    '".$descuento."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $post["idactividad"] = 11;
                $post["comentarios"] = "Agrego al vendedor " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Vendedor Agregrado","mensaje"=>"Se ha agregado el vendedor correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el vendedor.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Editar Vendedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarVendedor($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $usuario = mysqli_real_escape_string($this->con,$post["txtUsuario"]);
        $password = mysqli_real_escape_string($this->con,$post["txtPassword"]);
        $idsucursal = mysqli_real_escape_string($this->con,$post["slcSucursal"]);
        $descuento = mysqli_real_escape_string($this->con,$post["txtDescuento"]);
        $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);

        $nombrev = $this->obtenerVendedor($post)["vendedor"]["nombre"];

        try{
            $query = "
            update
                tvendedores
            set
                nombre = '".$nombre."',
                usuario = '".$usuario."',
                ".(($password!="") ? "password = '".$password."'," : "")."
                idsucursal = '".$idsucursal."',
                descuento = '".$descuento."'
            where
                idvendedor = '".$idvendedor."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $post["idactividad"] = 12;
                $post["comentarios"] = "Edito al vendedor " . (($nombrev!=$nombre) ? $nombrev . " -> " .$nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Vendedor Editado","mensaje"=>"Se ha editado el vendedor correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el vendedor.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Eliminar Vendedor.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarVendedor($post){
        $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);

        try{
            $query = "
            update
                tvendedores
            set
                status = '0'
            where
                idvendedor = '".$idvendedor."'
            ";
            
            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $nombre = $this->obtenerVendedor($post)["vendedor"]["nombre"];
                $post["idactividad"] = 13;
                $post["comentarios"] = "Elimino al vendedor " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Vendedor Eliminado","mensaje"=>"Se ha eliminado el vendedor correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el vendedor.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

}
?>