<?php
class Parametros
{
    private $con;
    private $claseQueries;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Parametros.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function getParametros($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        $where = (($busqueda != "") ? " where titulo like '%" . $busqueda . "%' or descripcion like '%".$busqueda."%'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                tparametros
            ".$where;

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    tparametros
                ".$where;

                $parametros = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "parametros" => $parametros);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron parametros registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código 001 " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Actualizar parametros
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function updateParametros($post)
    {
        try {
            foreach ($post as $key=>$value) {

                $value = mysqli_real_escape_string($this->con, $value);

                $query = "
                update
                    tparametros
                set
                    parametro = '".$value."'
                where
                    idparametro = '" . $key . "'";

                mysqli_query($this->con, $query);
            }

            $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Parámetros guardados", "mensaje" => "Se han guardado los parámetros correctamente.", "formulario" => "formBusqueda");
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
