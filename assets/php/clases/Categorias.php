<?php
class Categorias{
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
     * Obtener Categorias.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCategorias($post){

        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");

        try {
            $query = "
            select
                *
            from
                tcategoriasproductos
            where
                status = '1'
                ".$where."
            ";

            $categorias = mysqli_query($this->con,$query);

            if (mysqli_num_rows($categorias)) {
                $respuesta = array("respuesta"=>"OK","categorias"=>$categorias);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontraron categorias");
            }
            
        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Categoria.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCategoria($post){
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$post["idcategoriaproducto"]);

        try {
            $query = "
            select
                *
            from
                tcategoriasproductos
            where
                idcategoriaproducto = '".$idcategoriaproducto."'
            ";

            $categoria = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if ($categoria["idcategoriaproducto"]>0) {
                $respuesta = array("respuesta"=>"OK","categoria"=>$categoria);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontraron categorias");
            }
            
        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Agregar Categoria.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarCategoria($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);

        try{
            $query = "
            insert into
                tcategoriasproductos
                (
                    nombre
                )
            values
                (
                    '".$nombre."'
                )
            ";
            
            $this->claseQueries->guardarQuery($query);
            
            if (mysqli_query($this->con,$query)) {

                $idcategoriaproducto = mysqli_insert_id($this->con);
                $post["idcategoriaproducto"] = $idcategoriaproducto;

                // insertar las tallas 
                $correcto = $this->asignarTallas($post);

                if ($correcto) {

                    $post["idactividad"] = 70;
                    $post["comentarios"] = "Agrego la categoria " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Categoria Agregada","mensaje"=>"La Categoria se ha agregado correctamente","formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudieron asignar las tallas de la categoria");
                }
                

            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la categoria");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Editar Categoria.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarCategoria($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$post["idcategoriaproducto"]);

        $nombrec = $this->obtenerCategoria($post)["categoria"]["nombre"];

        try{
            $query = "
            update
                tcategoriasproductos
            set
                nombre = '".$nombre."'
            where
                idcategoriaproducto = '".$idcategoriaproducto."'
            ";
            
            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $query = "
                delete from
                    trcategoriaproductotallas
                where
                    idcategoriaproducto = '".$idcategoriaproducto."'
                ";

                mysqli_query($this->con,$query);

                $correcto = $this->asignarTallas($post);
    
                if ($correcto) {

                    $post["idactividad"] = 71;
                    $post["comentarios"] = "Edito la categoria " . (($nombrec!=$nombre) ? $nombrec . " -> " . $nombre : $nombre);
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Categoria Editada","mensaje"=>"La categoria se ha editado correctamente","formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudieron asignar las tallas de la categoria");
                }

            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar la categoria");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Eliminar Categoria.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarCategoria($post){
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$post["idcategoriaproducto"]);

        try{
            $query = "
            update
                tcategoriasproductos
            set
                status = '0'
            where
                idcategoriaproducto = '".$idcategoriaproducto."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                // NOTA: deberia eliminar las tallas asignadas?

                $nombre = $this->obtenerCategoria($post)["categoria"]["nombre"];
                $post["idactividad"] = 72;
                $post["comentarios"] = "Elimino la categoria " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Categoria Eliminada","mensaje"=>"La categoria se ha eliminado correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar la categoria");
            }
            
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Tiene Talla. Revisa si la categoria tiene asignada la talla
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function tieneTalla($post){
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$post["idcategoriaproducto"]);
        $idtalla = mysqli_real_escape_string($this->con,$post["idtalla"]);

        try{
            $query = "
            select
                count(*) as total
            from
                trcategoriaproductotallas
            where
                idcategoriaproducto = '".$idcategoriaproducto."' and
                idtalla = '".$idtalla."'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($total) {
                $respuesta = array("respuesta"=>"OK");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontró la talla asignada a esta categoria");
            }
            
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Asignar Tallas. Asigna las tallas de una Categoria
     * 
     * @access private
     * @param array $post
     * @return array
     */
    private function asignarTallas($post){
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$post["idcategoriaproducto"]);
        // $tallas = mysqli_real_escape_string($this->con,$post["chkTallas"]);
        $tallas = $post["chkTallas"];

        $correcto = true;
        try{

            foreach ($tallas as $idtalla) {
                $idtalla = mysqli_real_escape_string($this->con,$idtalla);
                
                $query = "
                insert into
                    trcategoriaproductotallas
                    (
                        idcategoriaproducto,
                        idtalla
                    )
                values
                    (
                        '".$idcategoriaproducto."',
                        '".$idtalla."'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con,$query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {
                $respuesta = true;
            }else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Ocurrió un error al asignar las tallas.");
            }
                        
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Obtener Tallas Categoria.
     * 
     * NOTA: ¿requerrirá recuperar tallas en orden de posicion?
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTallasCategoria($idcategoriaproducto,$ordenar = 0){
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$idcategoriaproducto);
        $ordenar = mysqli_real_escape_string($this->con,$ordenar);

        $orderby = "";
        $orderby .= (($ordenar == 1) ? " order by posicion " : "");

        try{

            $query = "
            select
                count(*) as total
            from
                tcattallas
            where
                idtalla in 
                (
                    select
                        idtalla
                    from
                        trcategoriaproductotallas
                    where
                        idcategoriaproducto = '".$idcategoriaproducto."'
                        and status = 1
                )
                ".$orderby."
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];
            
            if ($total) {

                $query = "
                select
                    *
                from
                    tcattallas
                where
                    idtalla in 
                    (
                        select
                            idtalla
                        from
                            trcategoriaproductotallas
                        where
                            idcategoriaproducto = '".$idcategoriaproducto."'
                            and status = 1
                    )
                    ".$orderby."
                ";

                // $this->claseQueries->guardarQuery($query);

                $tallas = mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK","tallas"=>$tallas);
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se encontraron tallas.");
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /**
     * Tiene Tallas.
     * 
     * @access public
     * @param int $idcategoriaproducto
     * @return boolean
     */
    public function tieneTallas($idcategoriaproducto) {
        $idcategoriaproducto = mysqli_real_escape_string($this->con,$idcategoriaproducto);

        try{
            $query = "
            select
                count(*) as total
            from
                trcategoriaproductotallas
            where
                idcategoriaproducto = '".$idcategoriaproducto."'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con,$query))["total"];

            if ($total) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
            
        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

}
?>