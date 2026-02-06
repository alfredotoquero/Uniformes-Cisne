<?php
class TiposProducto{
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
     * Obtener Tipos de Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTiposProducto($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda!="") ? " and a.nombre like '%".$busqueda."%'" : "");
        
        try{
            $query = "
            select
                a.idtipoproducto,
                a.nombre,
                a.idproductoservicio,
                b.clave as productoservicio,
                b.descripcion as descripcion_productoservicio,
                a.idunidadmedida,
                c.clave as unidadmedida,
                c.nombre as descripcion_unidadmedida,
                a.status
            from
                ttiposproducto a
            left join
                sat_tcatproductosservicios b
            on
                b.idproductoservicio = a.idproductoservicio
            left join
                sat_tcatunidadesmedida c
            on
                c.idunidadmedida = a.idunidadmedida
            where
                a.status = 1
                ".$where."
            order by
                a.nombre";
            $tiposproducto = mysqli_query($this->con,$query);

            if(mysqli_num_rows($tiposproducto)>0){
                $respuesta = array("respuesta"=>"OK","tiposproducto"=>$tiposproducto);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron tipos de producto registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Tipo de Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTipoProducto($post){
        $idtipoproducto = mysqli_real_escape_string($this->con,$post["idtipoproducto"]);

        try{
            $query = "
            select
                a.*,
                concat(b.clave,' - ',b.descripcion) as descripcion_productoservicio,
                concat(c.clave,' - ',c.nombre) as descripcion_unidadmedida
            from
                ttiposproducto a
            left join
                sat_tcatproductosservicios b
            on
                b.idproductoservicio = a.idproductoservicio
            left join
                sat_tcatunidadesmedida c
            on
                c.idunidadmedida = a.idunidadmedida
            where
                a.idtipoproducto = '".$idtipoproducto."'
            ";
            $tipoproducto = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($tipoproducto["idtipoproducto"]>0){
                $respuesta = array("respuesta"=>"OK","tipoproducto"=>$tipoproducto);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del tipo de producto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Tipo de Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTipoProducto($post){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $idproductoservicio = mysqli_real_escape_string($this->con,$post["slcProductoServicio"]);
        $idunidadmedida = mysqli_real_escape_string($this->con,$post["slcUnidadMedida"]);

        try{
            $query = "
            insert into
                ttiposproducto
                (
                    nombre,
                    idproductoservicio,
                    idunidadmedida
                )
            values
                (
                    '".$nombre."',
                    '".$idproductoservicio."',
                    '".$idunidadmedida."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $post["idactividad"] = 55;
                $post["comentarios"] = "Agrego el tipo de producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Producto Agregrado","mensaje"=>"Se ha agregado el tipo de producto correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el tipo de producto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Editar Tipo de Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarTipoProducto($post){
        $idtipoproducto = mysqli_real_escape_string($this->con,$post["idtipoproducto"]);
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $idproductoservicio = mysqli_real_escape_string($this->con,$post["slcProductoServicio"]);
        $idunidadmedida = mysqli_real_escape_string($this->con,$post["slcUnidadMedida"]);

        $nombret = $this->obtenerTipoProducto($post)["tipoproducto"]["nombre"];

        try{
            $query = "
            update
                ttiposproducto
            set
                nombre = '".$nombre."',
                idproductoservicio = '".$idproductoservicio."',
                idunidadmedida = '".$idunidadmedida."'
            where
                idtipoproducto = '".$idtipoproducto."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $post["idactividad"] = 56;
                $post["comentarios"] = "Edito el tipo de producto " . (($nombret!=$nombre) ? $nombret . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Producto Editado","mensaje"=>"Se ha editado el tipo de producto correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el tipo de producto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Eliminar Tipo de Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarTipoProducto($post){
        $idtipoproducto = mysqli_real_escape_string($this->con,$post["idtipoproducto"]);

        try{
            $query = "
            update
                ttiposproducto
            set
                status = 0
            where
                idtipoproducto = '".$idtipoproducto."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $nombre = $this->obtenerTipoProducto($post)["tipoproducto"]["nombre"];
                $post["idactividad"] = 57;
                $post["comentarios"] = "Elimino el tipo de producto " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Tipo de Producto Eliminado","mensaje"=>"Se ha eliminado el tipo de producto correctamente.","formulario" => "formBusqueda");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el tipo de producto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }
    
}
?>