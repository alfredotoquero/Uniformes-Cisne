<?php
class Menu{
    private $con;

    function __construct() {
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Obtener Menu.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function obtenerMenu($idusuario){
        $idusuario = mysqli_real_escape_string($this->con,$idusuario);

        try{
            $query = "
            select
                *
            from
                tcategoriassecciones
            where
                idcategoria in
                (
                select 
                    idcategoria
                from
                    tsecciones
                where
                    idseccion in 
                    (
                    select
                        idseccion
                    from
                        trusuariosecciones
                    where
                        idusuario = '".$idusuario."'
                    )
                )
            ";
            $categorias = mysqli_query($this->con,$query);

            $arrayCategorias = array();
            while($categoria = mysqli_fetch_assoc($categorias)){
                $query = "
                select
                    *
                from
                    tsecciones
                where
                    idcategoria = '".$categoria["idcategoria"]."' and
                    idseccion in
                    (
                    select
                        idseccion
                    from
                        trusuariosecciones
                    where
                        idusuario = '".$idusuario."'
                    )
                order by
                    posicion
                ";
                $secciones = mysqli_query($this->con,$query);

                $arraySecciones = array();
                while($seccion = mysqli_fetch_assoc($secciones)){
                    array_push($arraySecciones,array(
                        "nombre" => $seccion["nombre"],
                        "url" => $seccion["url"],
                        "icono" => $seccion["icon2"]
                    ));
                }

                array_push($arrayCategorias,array(
                    "nombre" => $categoria["nombre"],
                    "secciones" => $arraySecciones
                ));
            }
            
            $respuesta = array("respuesta"=>"OK","categorias"=>$arrayCategorias);
        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código 101: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

}
?>