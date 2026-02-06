<?php
class TiposTarjetaRegalo{
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
     * Obtener Tipos de Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTiposTarjetaRegalo($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");

        try{

            $query = "
            select
                *
            from
                tcattipostarjetaregalo
            where
                status = 'A'
                ".$where."
            ";
            $tipostarjetaregalo = mysqli_query($this->con,$query);

            if(mysqli_num_rows($tipostarjetaregalo)>0){
                $respuesta = array("respuesta"=>"OK","tipostarjetaregalo"=>$tipostarjetaregalo);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron tipos de tarjeta de regalo registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Tipo de Tarjeta de Regalo.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTipoTarjetaRegalo($post){
        $idtipo = mysqli_real_escape_string($this->con,$post["idtipo"]);

        try{
            $query = "
            select
                *
            from
                tcattipostarjetaregalo
            where
                idtipo = '".$idtipo."'
            ";
            $tipotarjetaregalo = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($tipotarjetaregalo["idtipo"]>0){
                $respuesta = array("respuesta"=>"OK","tipotarjetaregalo"=>$tipotarjetaregalo);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del tipo de tarjeta de regalo.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /** 
     * Agregar Tipo de Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTipoTarjetaRegalo($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $precio = mysqli_real_escape_string($this->con,$post["txtPrecio"]);

        try{
            $query = "
            insert into
                tcattipostarjetaregalo
                (
                    nombre,
                    precio
                )
            values
                (
                    '".$nombre."',
                    '".$precio."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $post["idactividad"] = 61;
                $post["comentarios"] = "Agrego el tipo de tarjeta de regalo " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Tarjeta de Regalo Agregrado","mensaje"=>"Se ha agregado el tipo de tarjeta de regalo correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el tipo de tarjeta de regalo.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Editar Tipo de Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarTipoTarjetaRegalo($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $precio = mysqli_real_escape_string($this->con,$post["txtPrecio"]);
        $idtipo = mysqli_real_escape_string($this->con,$post["idtipo"]);

        $nombret = $this->obtenerTipoTarjetaRegalo($post)["tipotarjetaregalo"]["nombre"];

        try{
            $query = "
            update
                tcattipostarjetaregalo
            set
                nombre = '".$nombre."',
                precio = '".$precio."'
            where
                idtipo = '".$idtipo."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $post["idactividad"] = 62;
                $post["comentarios"] = "Edito el tipo de tarjeta de regalo " . (($nombret!=$nombre) ? $nombret . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Tarjeta de Regalo Editado","mensaje"=>"Se ha editado el tipo de tarjeta de regalo correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el tipo de tarjeta de regalo.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Eliminar Tipo de Tarjeta de Regalo.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarTipoTarjetaRegalo($post){
        $idtipo = mysqli_real_escape_string($this->con,$post["idtipo"]);

        try{
            $query = "
            update
                tcattipostarjetaregalo
            set
                status = 'I'
            where
                idtipo = '".$idtipo."'
            ";
            
            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $nombre = $this->obtenerTipoTarjetaRegalo($post)["tipotarjetaregalo"]["nombre"];
                $post["idactividad"] = 63;
                $post["comentarios"] = "Elimino el tipo de tarjeta de regalo " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Tarjeta de Regalo Eliminado","mensaje"=>"Se ha eliminado el tipo de tarjeta de regalo correctamente.","formulario" => "formBusqueda");
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el tipo de tarjeta de regalo.");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

}
?>