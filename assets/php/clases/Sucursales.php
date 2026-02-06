<?php
class Sucursales{
    private $con;
    private $claseQueries;
    private $claseLogs;
    private $claseImagen;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Logs.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Imagen.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Sucursales.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSucursales($post){
        // filtros
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        $tiposucursal = mysqli_real_escape_string($this->con,$post["tiposucursal"]);
        $almacenesusuario = mysqli_real_escape_string($this->con,$post["almacenesusuario"]);
        
        $where = "";
        $where .= (($busqueda!="") ? " and nombre like '%".$busqueda."%'" : "");

        switch ($tiposucursal) {
            case 1:
                $where .= " and idalmacen in (select idalmacen from tsucursales where find_in_set(idalmacen,'".$almacenesusuario."'))";
            break;
            case 2:
                $where .= " and idalmacen not in (select idalmacen from tsucursales where find_in_set(idalmacen,'".$almacenesusuario."'))";
            break;
            
            default:
                // 
            break;
        }

        try{

            $query = "
            select
                *
            from
                tsucursales
            where
                status = 'A'
                ".$where."
            ";

            // $this->claseQueries->guardarQuery($query);

            $sucursales = mysqli_query($this->con,$query);

            if(mysqli_num_rows($sucursales)>0){
                $respuesta = array("respuesta"=>"OK","sucursales"=>$sucursales,"query"=>$query);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron sucursales registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Sucursal.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSucursal($post){
        $idsucursal = mysqli_real_escape_string($this->con,$post["idsucursal"]);

        try{
            $query = "
            select
                *
            from
                tsucursales
            where
                idsucursal = '".$idsucursal."'
            ";
            $sucursal = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($sucursal["idsucursal"]>0){
                $respuesta = array("respuesta"=>"OK","sucursal"=>$sucursal);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del sucursal.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /** 
     * Agregar Sucursal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarSucursal($post,$files){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $idtienda = mysqli_real_escape_string($this->con,$post["slcTienda"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["slcAlmacen"]);
        $idalmacen_reorden = mysqli_real_escape_string($this->con,$post["slcAlmacenReorden"]);
        $idalmacen_produccion = mysqli_real_escape_string($this->con,$post["slcAlmacenProduccion"]);
        $razonsocial = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
        $calle = mysqli_real_escape_string($this->con,$post["txtCalle"]);
        $numexterior = mysqli_real_escape_string($this->con,$post["txtNumero"]);
        $numinterior = mysqli_real_escape_string($this->con,$post["txtNumeroInt"]);
        $colonia = mysqli_real_escape_string($this->con,$post["txtColonia"]);
        $ciudad = mysqli_real_escape_string($this->con,$post["txtCiudad"]);
        $estado = mysqli_real_escape_string($this->con,$post["txtEstado"]);
        $codigopostal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
        $telefono = mysqli_real_escape_string($this->con,$post["txtTelefono"]);
        $regimen = mysqli_real_escape_string($this->con,$post["txtRegimen"]);
        // $files para la imagen del ticket
        // img ticket

        try{
            $query = "
            insert into
                tsucursales
                (
                    nombre,
                    idtienda,
                    idalmacen,
                    idalmacen_reorden,
                    idalmacen_produccion,
                    razonsocial,
                    rfc,
                    calle,
                    numero,
                    numeroint,
                    colonia,
                    ciudad,
                    estado,
                    codigopostal,
                    telefono,
                    regimen
                )
            values
                (
                    '".$nombre."',
                    ".((!empty($idtienda)) ? "'".$idtienda."'" : "NULL").",
                    '".$idalmacen."',
                    '".$idalmacen_reorden."',
                    '".$idalmacen_produccion."',
                    '".$razonsocial."',
                    '".$rfc."',
                    '".$calle."',
                    '".$numexterior."',
                    '".$numinterior."',
                    '".$colonia."',
                    '".$ciudad."',
                    '".$estado."',
                    '".$codigopostal."',
                    '".$telefono."',
                    '".$regimen."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $idsucursal = mysqli_insert_id($this->con);

                $this->claseImagen = new Imagen();
                $this->claseImagen->subirImagen($files["imgTicket"],"/imagenes/sucursales/",$idsucursal."_".$files["imgTicket"]["name"],200,"tsucursales","idsucursal",$idsucursal,"imagen");
                $this->claseImagen->subirImagen($files["imgFormato"],"/assets/images/formatos/",$idsucursal."_".$files["imgFormato"]["name"],3300,"tsucursales","idsucursal",$idsucursal,"formato");

                $post["idactividad"] = 17;
                $post["comentarios"] = "Agrego la sucursal " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Sucursal Agregada","mensaje"=>"Se ha agregado la sucursal correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar la sucursal");
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Editar Sucursal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarSucursal($post,$files){
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $idtienda = mysqli_real_escape_string($this->con,$post["slcTienda"]);
        $idalmacen = mysqli_real_escape_string($this->con,$post["slcAlmacen"]);
        $idalmacen_reorden = mysqli_real_escape_string($this->con,$post["slcAlmacenReorden"]);
        $idalmacen_produccion = mysqli_real_escape_string($this->con,$post["slcAlmacenProduccion"]);
        $razonsocial = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
        $calle = mysqli_real_escape_string($this->con,$post["txtCalle"]);
        $numexterior = mysqli_real_escape_string($this->con,$post["txtNumero"]);
        $numinterior = mysqli_real_escape_string($this->con,$post["txtNumeroInt"]);
        $colonia = mysqli_real_escape_string($this->con,$post["txtColonia"]);
        $ciudad = mysqli_real_escape_string($this->con,$post["txtCiudad"]);
        $estado = mysqli_real_escape_string($this->con,$post["txtEstado"]);
        $codigopostal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
        $telefono = mysqli_real_escape_string($this->con,$post["txtTelefono"]);
        $regimen = mysqli_real_escape_string($this->con,$post["txtRegimen"]);

        $idsucursal = mysqli_real_escape_string($this->con,$post["idsucursal"]);
        // img ticket

        $nombresu = $this->obtenerSucursal($post)["sucursal"]["nombre"];

        try{
            $query = "
            update
                tsucursales
            set
                nombre = '".$nombre."',
                idtienda = ".((!empty($idtienda)) ? "'".$idtienda."'" : "NULL").",
                idalmacen = '".$idalmacen."',
                idalmacen_reorden = '".$idalmacen_reorden."',
                idalmacen_produccion = '".$idalmacen_produccion."',
                razonsocial = '".$razonsocial."',
                rfc = '".$rfc."',
                calle = '".$calle."',
                numero = '".$numexterior."',
                numeroint = '".$numinterior."',
                colonia = '".$colonia."',
                ciudad = '".$ciudad."',
                estado = '".$estado."',
                codigopostal = '".$codigopostal."',
                telefono = '".$telefono."',
                regimen = '".$regimen."'
            where
                idsucursal = '".$idsucursal."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $this->claseImagen = new Imagen();
                $this->claseImagen->subirImagen($files["imgTicket"],"/imagenes/sucursales/",$idsucursal."_".$files["imgTicket"]["name"],200,"tsucursales","idsucursal",$idsucursal,"imagen");
                $this->claseImagen->subirImagen($files["imgFormato"],"/assets/images/formatos/",$idsucursal."_".$files["imgFormato"]["name"],3300,"tsucursales","idsucursal",$idsucursal,"formato");

                $post["idactividad"] = 18;
                $post["comentarios"] = "Edito la sucursal " . (($nombresu!=$nombre) ? $nombresu . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Sucursal Editada","mensaje"=>"La sucursal se ha editado correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo actualizar la información de la sucursal");
            }
            

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }

    /** 
     * Eliminar Sucursal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarSucursal($post){
        $idsucursal = mysqli_real_escape_string($this->con,$post["idsucursal"]);

        try{
            $query = "
            update
                tsucursales
            set
                status = 'I'
            where
                idsucursal = '".$idsucursal."'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con,$query)) {

                $nombre = $this->obtenerSucursal($post)["sucursal"]["nombre"];
                $post["idactividad"] = 19;
                $post["comentarios"] = "Elimino la sucursal " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"Sucursal Eliminada","mensaje"=>"La sucursal se ha eliminado correctamente","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar la contraseña");
            }

        }catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }
}
?>