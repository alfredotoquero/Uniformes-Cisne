<?php
class SAT{

    private $con;

    public function __construct(){
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
    }

    /**
     * Obtener Usos CFDI.
     * 
     * @access public
     * @return array
     */
    public function obtenerUsosCFDI(){
        try {
            $query = "
            select
                *
            from
                sat_tcatusoscfdi
            ";

            $usoscfdi = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "usoscfdi" => $usoscfdi);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Regimenes Fiscales.
     * 
     * @access public
     * @return array
     */
    public function obtenerRegimenesFiscales(){
        try {
            $query = "
            select
                *
            from
                sat_tcatregimenfiscal
            ";

            $regimenesfiscales = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "regimenesfiscales" => $regimenesfiscales);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Métodos de Pago.
     * 
     * @access public
     * @return array
     */
    public function obtenerMetodosPago(){
        try {
            $query = "
            select
                *
            from
                sat_tcatmetodospago
            ";

            $metodospago = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "metodospago" => $metodospago);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Formas de Pago.
     * 
     * @access public
     * @return array
     */
    public function obtenerFormasPago(){
        try {
            $query = "
            select
                *
            from
                sat_tcatformaspago
            ";

            $formaspago = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "formaspago" => $formaspago);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos / Servicios.
     * 
     * @access public
     * @return array
     */
    public function obtenerProductosServicios($post){
        try {
            $busqueda = mysqli_real_escape_string($this->con, $post["palabraClave"]);

            $query = "
            select
                *
            from
                sat_tcatproductosservicios
            where
                descripcion like '%$busqueda%' or
                clave like '%$busqueda%'
            order by
                clave
            ";

            $productosservicios = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "productosservicios" => $productosservicios);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener unidades de medida.
     * 
     * @access public
     * @return array
     */
    public function obtenerUnidadesMedida($post){
        try {
            $busqueda = mysqli_real_escape_string($this->con, $post["palabraClave"]);

            $query = "
            select
                *
            from
                sat_tcatunidadesmedida
            where
                nombre like '%$busqueda%' or
                clave like '%$busqueda%'
            order by
                clave";

            $unidadesmedida = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "unidadesmedida" => $unidadesmedida);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener motivos de cancelación
     * 
     * @access public
     * @return array
     */
    public function obtenerMotivosCancelacion(){
        try {
            $query = "
            select
                *
            from
                sat_tcatmotivoscancelacion
            order by
                clave";

            $motivoscancelacion = mysqli_fetch_all(mysqli_query($this->con, $query), MYSQLI_ASSOC);

            $respuesta = array("respuesta" => "OK", "motivoscancelacion" => $motivoscancelacion);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

}