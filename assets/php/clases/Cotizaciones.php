<?php
class Cotizaciones
{
    private $con;
    private $claseQueries;
    private $claseClientes;
    private $claseContactos;
    private $claseProductos;
    private $claseSucursales;
    private $claseAlmacenes;
    private $claseCategorias;
    private $claseNotificaciones;
    private $claseUsuarios;
    private $claseImagen;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Contactos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Categorias.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Notificaciones.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Imagen.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();

        // aumentar el limite de caracteres que puede contener un group_concat
        $query = "
        SET SESSION group_concat_max_len = 1000000
        ";
        mysqli_query($this->con, $query);
    }

    /**
     * Obtener Cotizaciones.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCotizaciones($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        // $idsucursal = mysqli_real_escape_string($this->con,$post["slcSucursal"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $status = mysqli_real_escape_string($this->con, $post["slcStatus"]);
        $tipo = mysqli_real_escape_string($this->con, $post["slcTipo"]);

        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);

        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        $pagina = (isset($post["pagina"])) ? $post["pagina"] : 1;
        $regpagina = 100;

        $where = "";
        $where .= (($busqueda != "") ? " and (cliente like '%" . $busqueda . "%' or idcotizacion like '%" . $busqueda . "%' or contacto like '%" . $busqueda . "%' or usuario like '%" . $busqueda . "%' or idcotizacion in
        (
            WITH RECURSIVE tablarecursiva (idcotizacion,idcotizacionpadre) AS 
            (
                SELECT
                    idcotizacion,
                    idcotizacionpadre
                FROM
                    vcotizaciones
                WHERE
                    idcotizacion like '%".$busqueda."%'
                UNION ALL
                SELECT
                    a.idcotizacion,
                    a.idcotizacionpadre
                FROM
                    vcotizaciones a
                INNER JOIN tablarecursiva b ON
                    a.idcotizacionpadre = b.idcotizacion
            )
            SELECT
                DISTINCT b.idcotizacion
            FROM
                tablarecursiva b
            WHERE
                b.idcotizacion = 
                (
                    SELECT
                        MAX(idcotizacion)
                    FROM
                        tablarecursiva
                )
        ))" : "");
        // $where .= (($idsucursal!=0) ? " and idsucursal = '".$idsucursal."'" : "");
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : "");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : "");
        $where .= (($status != "" && $status != 0) ? " and status = '" . $status . "'" : "");
        $where .= (($tipo == "A") ? " and idcotizacion in (select idcotizacion from tpedidos)" : "");
        $where .= (($idcliente != "") ? " and idcliente = '" . $idcliente . "'" : "");
        $where .= (($idsucursal != "") ? " and idsucursal = '" . $idsucursal . "'" : "");
        $where .= ((!empty($idtienda)) ? " and idcliente in (select idcliente from tclientes where idtienda = '" . $idtienda . "')" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vcotizaciones
            where
                status != 'M'
                and status != 'C'
                " . $where;
            $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];
            $numpaginas = ceil($total / $regpagina);
            $maxpaginas = 3;

            if ($total) {

                $query = "
                select
                    *
                from
                    vcotizaciones
                where
                    status != 'M'
                    and status != 'C'
                    " . $where . "
                order by
                    fecha desc
                limit 
                    " . (($pagina - 1) * $regpagina) . "," . $regpagina;
                $cotizaciones = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "cotizaciones" => $cotizaciones, "pagina" => $pagina, "numpaginas" => $numpaginas, "maxpaginas" => $maxpaginas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron cotizaciones registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Historial Cotizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCotizacionMasReciente($post)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);

        try {

            // primero nos aseguramos de que esta cotizacion sea la mas reciente del historial
            $post["idcotizacion"] = $idcotizacion;
            $cotizacionp = $this->obtenerCotizacion($post)["cotizacion"];
            while ($this->esCotizacionPadre($cotizacionp["idcotizacion"])) {
                $post["idcotizacion"] = $cotizacionp["idcotizacion"];
                $cotizacionp = $this->obtenerCotizacion($post, "padre")["cotizacion"];
            }

            $respuesta = array("respuesta" => "OK", "cotizacion" => $cotizacionp);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cotizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCotizacion($post, $tipo = "")
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);

        try {
            $query = "
            select
                *
            from
                vcotizaciones
            where
                idcotizacion$tipo = '" . $idcotizacion . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $cotizacion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($cotizacion["idcotizacion"] > 0) {
                $respuesta = array("respuesta" => "OK", "cotizacion" => $cotizacion);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información de la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidas($post, $tipo = 1)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);
        // $idpedido = mysqli_real_escape_string($this->con,$post["idpedido"]);

        $where = "";
        $where .= (($tipo == 1) ? " and idcotizacion = '" . $idcotizacion . "'" : (($tipo == 2) ? " and idpedido = '" . $idpedido . "'" : ""));
        // and idpedido = 0
        try {
            $query = "
            select
                *
            from
                vrcotizacionproductos
            where
                1=1
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $partidas = mysqli_query($this->con, $query);

            if (mysqli_num_rows($partidas)) {
                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron partidas en la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Verifica si la cotizacion tiene cotizacion padre (historial de cotizaciones).
     * 
     * @access public
     * @param array $post
     * @return array
     * 
     */
    public function esCotizacionPadre($idcotizacion)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $idcotizacion);

        try {
            $query = "
            select
                *
            from
                vcotizaciones
            where
                idcotizacionpadre = '" . $idcotizacion . "'
            ";

            if (mysqli_num_rows((mysqli_query($this->con, $query)))) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Verifica si la cotizacion tiene cotizacion padre (historial de cotizaciones).
     * 
     * @access public
     * @param array $post
     * @return array
     * 
     */
    public function tieneHistorial($idcotizacionpadre, $idcotizacion)
    {
        $idcotizacionpadre = mysqli_real_escape_string($this->con, $idcotizacionpadre);
        $idcotizacion = mysqli_real_escape_string($this->con, $idcotizacion);

        try {
            $query = "
            select
                *
            from
                vcotizaciones
            where
                idcotizacion = '" . $idcotizacionpadre . "'
                or idcotizacionpadre = '" . $idcotizacion . "'
            ";

            if (mysqli_num_rows((mysqli_query($this->con, $query)))) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Guardar Cotizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function guardarCotizacion($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $noguardarcliente = mysqli_real_escape_string($this->con, $post["chkNoGuardarCliente"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $cliente = mysqli_real_escape_string($this->con, $post["slcCliente"]);
        $correo = mysqli_real_escape_string($this->con, $post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con, $post["txtTelefono"]);
        $idcontacto = mysqli_real_escape_string($this->con, $post["idcontacto"]);
        $contacto = mysqli_real_escape_string($this->con, $post["slcContacto"]);
        $correocontacto = mysqli_real_escape_string($this->con, $post["txtCorreoC"]);
        $telefonocontacto = mysqli_real_escape_string($this->con, $post["txtTelefonoC"]);
        $fecha = date("Y-m-d H:i:s");
        $opcionvigencia = mysqli_real_escape_string($this->con, $post["slcVigencia"]);
        $vigencia = date("Y-m-d H:i:s", strtotime("+" . $opcionvigencia . " days", strtotime(date("Y-m-d"))));
        $incluyeiva = mysqli_real_escape_string($this->con, $post["chkIncluyeIva"]);
        $tasaiva = mysqli_real_escape_string($this->con, $post["slcIVA"]);
        $subtotalizar = mysqli_real_escape_string($this->con, $post["chkSubtotalizar"]);
        $subtotal = mysqli_real_escape_string($this->con, $post["subtotal"]);
        $iva = mysqli_real_escape_string($this->con, $post["iva"]);
        $total = mysqli_real_escape_string($this->con, $post["total"]);
        // $pendiente = (($total==0) ? '1' : '0');
        $idcotizacionpadre = mysqli_real_escape_string($this->con, $post["idcotizacionpadre"]);
        $linea1 = mysqli_real_escape_string($this->con, $post["txtLinea1"]);
        $linea2 = mysqli_real_escape_string($this->con, $post["txtLinea2"]);
        $linea3 = mysqli_real_escape_string($this->con, $post["txtLinea3"]);
        $linea4 = mysqli_real_escape_string($this->con, $post["txtLinea4"]);
        $linea5 = mysqli_real_escape_string($this->con, $post["txtLinea5"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);
        $puesto = mysqli_real_escape_string($this->con, $post["txtPuesto"]);

        $permitir_coincidencia = mysqli_real_escape_string($this->con, $post["permitir_coincidencia"]);

        try {
            $campos = "";
            $valores = "";
            // al agregar cliente, si no se selecciona el checkbox de "no registrar al cliente", se guardan los datos en la cotizacion (tcotizaciones) en lugar de la tabla de clientes
            if ($noguardarcliente > 0) {
                $campos = "cliente,correocliente,telefonocliente,contacto,correocontacto,telefonocontacto,";
                $valores = "'" . $cliente . "','" . $correo . "','" . $telefono . "','" . $contacto . "','" . $correocontacto . "','" . $telefonocontacto . "',";
            } else {
                $this->claseContactos = new Contactos();
                $arraycontacto = array("idcliente" => $idcliente, "txtContacto" => $contacto, "txtCorreo" => $correocontacto, "txtTelefono" => $telefonocontacto, "txtPuesto" => $puesto);
                if ($idcliente == 0) {

                    $this->claseClientes = new Clientes();
                    $coincidencias = $this->claseClientes->buscarCoincidenciasClientes(array(
                        "nombre" => $cliente,
                        "correo" => $correo,
                        "telefono" => $telefono
                    ));

                    if($coincidencias["respuesta"]=="ERROR" || $permitir_coincidencia==1){
                        if ($cliente != "" && $cliente != "0") {
                            // $this->claseClientes = new Clientes();
                            $arraycliente = array("txtNombre" => $cliente, "txtCorreo" => $correo, "txtTelefono" => $telefono, "idusuario" => $idusuario, "slcCiudad" => $idciudad, "slcTienda" => $idtienda);
                            $idcliente = $this->claseClientes->agregarCliente($arraycliente)["idcliente"];

                            $arraycontacto["idcliente"] = $idcliente;

                            if ($idcontacto == 0 && $contacto != "0" && $contacto != "") {
                                $idcontacto = $this->claseContactos->agregarContacto($arraycontacto)["idcontacto"];
                            }
                        } else {
                            throw new Exception("Debes ingresar un cliente.");
                        }
                    }else{
                        $respuesta = array("respuesta" => "OK", "tipo" => "abrir_nuevo_fancy", "url" => "/modulos/cotizaciones/coincidencias.php?idusuario=".$idusuario."&idsucursal=".$idsucursal."&noguardarcliente=".$noguardarcliente."&idcliente=".$idcliente."&cliente=".urlencode($cliente)."&correo=".urlencode($correo)."&telefono=".urlencode($telefono)."&idcontacto=".$idcontacto."&contacto=".urlencode($contacto)."&correocontacto=".urlencode($correocontacto)."&telefonocontacto=".urlencode($telefonocontacto)."&opcionvigencia=".$opcionvigencia."&incluyeiva=".$incluyeiva."&tasaiva=".$tasaiva."&subtotalizar=".$subtotalizar."&subtotal=".$subtotal."&iva=".$iva."&total=".$total."&idcotizacionpadre=".$idcotizacionpadre."&linea1=".urlencode($linea1)."&linea2=".urlencode($linea2)."&linea3=".urlencode($linea3)."&linea4=".urlencode($linea4)."&linea5=".urlencode($linea5)."&idciudad=".$idciudad."&idtienda=".$idtienda."&puesto=".urlencode($puesto));
                        return;
                    }
                } else if ($idcontacto == 0 && $contacto != "0" && $contacto != "") {
                    $idcontacto = $this->claseContactos->agregarContacto($arraycontacto)["idcontacto"];
                }
            }

            if ($idcotizacionpadre > 0) {

                $this->actualizarStatusCotizacion($idcotizacionpadre, "M");

                $campos .= "idcotizacionpadre,";
                $valores .= "'" . $idcotizacionpadre . "',";
            }

            $query = "
            insert into
                tcotizaciones 
                (
                    idcliente,
                    idcontacto,
                    " . $campos . "
                    idusuario,
                    subtotal,
                    iva,
                    total,
                    subtotalizar,
                    incluyeiva,
                    tasaiva,
                    fecha,
                    idsucursal,
                    fechavigencia,
                    idvigencia,
                    linea1,
                    linea2,
                    linea3,
                    linea4,
                    linea5
                )
            values 
                (
                    '" . $idcliente . "',
                    '" . $idcontacto . "',
                    " . $valores . "
                    '" . $idusuario . "',
                    '" . $subtotal . "',
                    '" . $iva . "',
                    '" . $total . "',
                    '" . $subtotalizar . "',
                    '" . $incluyeiva . "',
                    '" . $tasaiva . "',
                    '" . $fecha . "',
                    '" . $idsucursal . "',
                    '" . $vigencia . "',
                    '" . $opcionvigencia . "',
                    '" . $linea1 . "',
                    '" . $linea2 . "',
                    '" . $linea3 . "',
                    '" . $linea4 . "',
                    '" . $linea5 . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idcotizacion = mysqli_insert_id($this->con);

                $post["idactividad"] = 29;
                $post["comentarios"] = "Agrego la cotizacion #" . $idcotizacion;
                if ($idcotizacionpadre > 0) {
                    $post["idactividad"] = 30;
                    $post["comentarios"] = "Modifico la cotizacion #" . $idcotizacionpadre . " (#" . $idcotizacion . ")";
                }

                // ¿se necesitara crear una funcion separada para la insercion de las partidas? en este caso, se insertan a traves de un select, pero es posible que tambien se necesiten insertar de una en una
                $query = "
                insert into
                    trcotizacionproductos
                    (
                        idcotizacion,
                        idproducto,
                        producto,
                        idcategoriaproducto,
                        idtipoproducto,
                        cantidad,
                        precio,
                        serigrafia1,
                        serigrafia2,
                        serigrafia3,
                        personalizadonumero,
                        personalizadonombre,
                        bordado1,
                        bordado2,
                        bordado3,
                        bordado4,
                        bordadoespecial,
                        personalizado1linea,
                        personalizado2lineas,
                        personalizado3lineas,
                        sxl,
                        2xl,
                        3xl,
                        observaciones
                    )
                select
                    '" . $idcotizacion . "',
                    idproducto,
                    producto,
                    idcategoriaproducto,
                    idtipoproducto,
                    cantidad,
                    precio,
                    serigrafia1,
                    serigrafia2,
                    serigrafia3,
                    personalizadonumero,
                    personalizadonombre,
                    bordado1,
                    bordado2,
                    bordado3,
                    bordado4,
                    bordadoespecial,
                    personalizado1linea,
                    personalizado2lineas,
                    personalizado3lineas,
                    sxl,
                    2xl,
                    3xl,
                    observaciones
                from
                    trcotizacionproductostmp
                where
                    idusuario = '" . $idusuario . "'
                ";

                $this->claseQueries->guardarQuery($query);

                mysqli_query($this->con, $query);

                // actividad
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Cotizacion Agregrada", "mensaje" => "Se ha agregado la cotizacion correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Modificar Array Movimientos
     * 
     * @access private
     * @param array $movimientos
     * @param array $arrayMovimiento
     * @return array
     */
    private function modificarArrayMovimientos($movimientos, $arrayMovimiento)
    {
        $insertar = true;
        $arrayMovimientos = array();
        if (count($movimientos) > 0) {
            foreach ($movimientos as $movimiento) {
                if ($movimiento["idalmacen"] == $arrayMovimiento["idalmacen"]) {
                    $arrayM = array();
                    foreach ($movimiento["movimientos"] as $mov) {
                        array_push($arrayM, $mov);
                    }
                    array_push($arrayM, $arrayMovimiento["movimiento"]);
                    array_push($arrayMovimientos, array("idalmacen" => $movimiento["idalmacen"], "movimientos" => $arrayM));
                    $insertar = false;
                } else {
                    array_push($arrayMovimientos, array("idalmacen" => $movimiento["idalmacen"], "movimientos" => $movimiento["movimientos"]));
                }
            }
        }

        if ($insertar) {
            $arrayM = array();
            array_push($arrayM, $arrayMovimiento["movimiento"]);
            array_push($arrayMovimientos, array("idalmacen" => $arrayMovimiento["idalmacen"], "movimientos" => $arrayM));
        }

        return $arrayMovimientos;
    }

    /**
     * Convertir una Cotizacion a Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function convertirAPedido($post)
    {

        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"] ?? "");
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"] ?? "");
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"] ?? "");
        $idcontacto = mysqli_real_escape_string($this->con, $post["idcontacto"] ?? "");
        $opcionvigencia = mysqli_real_escape_string($this->con, $post["slcVigencia"] ?? "");
        $vigencia = date("Y-m-d H:i:s", strtotime("+" . $opcionvigencia . " days", strtotime(date("Y-m-d"))));
        // $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        $incluyeiva = mysqli_real_escape_string($this->con, $post["chkIncluyeIva"] ?? "");
        $tasaiva = mysqli_real_escape_string($this->con, $post["slcIVA"] ?? "");
        $subtotal = mysqli_real_escape_string($this->con, $post["subtotal"] ?? "");
        $iva = mysqli_real_escape_string($this->con, $post["iva"] ?? "");
        $total = mysqli_real_escape_string($this->con, $post["total"] ?? "");
        $linea1 = mysqli_real_escape_string($this->con, $post["txtLinea1"] ?? "");
        $linea2 = mysqli_real_escape_string($this->con, $post["txtLinea2"] ?? "");
        $linea3 = mysqli_real_escape_string($this->con, $post["txtLinea3"] ?? "");
        $linea4 = mysqli_real_escape_string($this->con, $post["txtLinea4"] ?? "");
        $linea5 = mysqli_real_escape_string($this->con, $post["txtLinea5"] ?? "");
        $pendiente = (($total == 0) ? '1' : '0');

        $fecha = date("Y-m-d H:i:s");

        $this->claseUsuarios = new Usuarios();
        $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"];

        $this->claseSucursales = new Sucursales();

        $correcto = true;

        try {

            $post["idsucursal"] = $idsucursal;
            $idalmacensucursal = $this->claseSucursales->obtenerSucursal($post)["sucursal"]["idalmacen_produccion"];

            // $post["idcotizacion"] = $cotizacionpadre["idcotizacion"];
            $cotizacion = $this->obtenerCotizacion($post)["cotizacion"];

            $cotizacionpadre = $this->obtenerCotizacion($post)["cotizacion"];
            while ($this->esCotizacionPadre($cotizacionpadre["idcotizacion"])) {
                $post["idcotizacion"] = $cotizacionpadre["idcotizacion"];
                $cotizacionpadre = $this->obtenerCotizacion($post, "padre")["cotizacion"];
            }

            $this->actualizarStatusCotizacion($cotizacionpadre["idcotizacion"], "M");
            $this->actualizarStatusCotizacion($idcotizacion, "");

            $query = "
            insert into
                tpedidos
                (
                    idcotizacion,
                    idusuario,
                    idsucursal,
                    idcliente,
                    idcontacto,
                    incluyeiva,
                    tasaiva,
                    subtotal,
                    iva,
                    total,
                    fecha,
                    fechavigencia,
                    idvigencia,
                    linea1,
                    linea2,
                    linea3,
                    linea4,
                    linea5,
                    status,
                    cliente,
                    correocliente,
                    telefonocliente,
                    contacto,
                    correocontacto,
                    telefonocontacto
                )
            values
                (
                    '" . $idcotizacion . "',
                    '" . $idusuario . "',
                    '" . $idsucursal . "',
                    '" . $idcliente . "',
                    '" . $idcontacto . "',
                    '" . $incluyeiva . "',
                    '" . $tasaiva . "',
                    '" . $subtotal . "',
                    '" . $iva . "',
                    '" . $total . "',
                    '" . $fecha . "',
                    '" . $vigencia . "',
                    '" . $opcionvigencia . "',
                    '" . $linea1 . "',
                    '" . $linea2 . "',
                    '" . $linea3 . "',
                    '" . $linea4 . "',
                    '" . $linea5 . "',
                    'A',
                    '" . $cotizacion["cliente"] . "',
                    '" . $cotizacion["correocliente"] . "',
                    '" . $cotizacion["telefonocliente"] . "',
                    '" . $cotizacion["contacto"] . "',
                    '" . $cotizacion["correocontacto"] . "',
                    '" . $cotizacion["telefonocontacto"] . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idpedido = mysqli_insert_id($this->con);

                $query = "
                delete from
                    trcotizacionproductos
                where
                    idcotizacion = '" . $idcotizacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $idcotizacionproductostmp = array();
                $idcotizacionproductos = array();
                $partidas = $this->obtenerPartidasTMP($idusuario);
                foreach ($partidas["partidas"] as $partida) {
                    $query = "
                    insert into
                        trcotizacionproductos
                        (
                            idcotizacion,
                            idpedido,
                            idproducto,
                            producto,
                            idcategoriaproducto,
                            idtipoproducto,
                            cantidad,
                            precio,
                            serigrafia1,
                            serigrafia2,
                            serigrafia3,
                            personalizadonumero,
                            personalizadonombre,
                            bordado1,
                            bordado2,
                            bordado3,
                            bordado4,
                            bordadoespecial,
                            personalizado1linea,
                            personalizado2lineas,
                            personalizado3lineas,
                            sxl,
                            2xl,
                            3xl,
                            observaciones
                        ) 
                    values
                        (
                            '" . $idcotizacion . "',
                            '" . $idpedido . "',
                            '" . $partida["idproducto"] . "',
                            '" . $partida["producto"] . "',
                            '" . $partida["idcategoriaproducto"] . "',
                            '" . ($partida["idtipoproducto"] ?? "0") . "',
                            '" . $partida["cantidad"] . "',
                            '" . $partida["precio"] . "',
                            '" . $partida["serigrafia1"] . "',
                            '" . $partida["serigrafia2"] . "',
                            '" . $partida["serigrafia3"] . "',
                            '" . $partida["personalizadonumero"] . "',
                            '" . $partida["personalizadonombre"] . "',
                            '" . $partida["bordado1"] . "',
                            '" . $partida["bordado2"] . "',
                            '" . $partida["bordado3"] . "',
                            '" . $partida["bordado4"] . "',
                            '" . $partida["bordadoespecial"] . "',
                            '" . $partida["personalizado1linea"] . "',
                            '" . $partida["personalizado2lineas"] . "',
                            '" . $partida["personalizado3lineas"] . "',
                            '" . $partida["sxl"] . "',
                            '" . $partida["2xl"] . "',
                            '" . $partida["3xl"] . "',
                            '" . $partida["observaciones"] . "'
                        ) 
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }

                    $idcotizacionproducto = mysqli_insert_id($this->con);

                    // se insertan el permanente y el temporal en los arreglos
                    array_push($idcotizacionproductostmp, $partida["idtmp"]);
                    array_push($idcotizacionproductos, $idcotizacionproducto);
                }

                // a productos que no esten en la tabla de trpedidoproductos y tengan idproducto = 0 se les hace esto
                $partidas = $this->obtenerPartidasTMP($idusuario, 1);
                foreach ($partidas["partidas"] as $partida) {
                    $query = "
                    insert into
                        tsolicitudescompra
                        (
                            idusuario,
                            idpedido,
                            idproveedor,
                            idproducto,
                            producto,
                            idcategoriaproducto,
                            cantidad,
                            fecha
                        ) 
                    values 
                        (
                            '" . $idusuario . "',
                            '" . $idpedido . "',
                            '0',
                            '0',
                            '" . $partida["producto"] . "',
                            '" . $partida["idcategoriaproducto"] . "',
                            '" . $partida["cantidad"] . "',
                            '" . $fecha . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }

                $this->claseProductos = new Productos();

                $idpedidoproductostmp = array();
                $idpedidoproductos = array();
                $desgloses = $this->obtenerDesglosesUsuarioTMP($post);
                foreach ($desgloses["desgloses"] as $desglose) {
                    $indice = array_search($desglose["idcotizacionproducto"], $idcotizacionproductostmp);
                    $query = "
                    insert into
                        trpedidoproductos
                        (
                            idpedido,
                            idcotizacionproducto,
                            idproducto,
                            producto,
                            idcategoriaproducto,
                            idtipoproducto,
                            idtalla,
                            talla,
                            idcolor,
                            color,
                            cantidad,
                            precio
                        )
                    values
                        (
                            '" . $idpedido . "',
                            '" . $idcotizacionproductos[$indice] . "',
                            '" . $desglose["idproducto"] . "',
                            '" . $desglose["producto"] . "',
                            '" . $desglose["idcategoriaproducto"] . "',
                            '" . ($desglose["idtipoproducto"] ?? "0") . "',
                            '" . $desglose["idtalla"] . "',
                            '" . $desglose["talla"] . "',
                            '" . $desglose["idcolor"] . "',
                            '" . $desglose["color"] . "',
                            '" . $desglose["cantidad"] . "',
                            '" . $desglose["precio"] . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }

                    $idpedidoproducto = mysqli_insert_id($this->con);

                    array_push($idpedidoproductostmp, $desglose["idtmp"]);
                    array_push($idpedidoproductos, $idpedidoproducto);
                }

                $notificarbordado = false;
                $notificarserigrafia = false;
                $especificaciones = $this->obtenerEspecificacionesTMP($post);
                foreach ($especificaciones["especificaciones"] as $especificacion) {

                    $query = "
                    insert into
                        tespecificaciones
                        (
                            idusuario,
                            idpedido,
                            nombrediseno,
                            fechaentrega,
                            serigrafia,
                            digital,
                            bordado,
                            especificaciones,
                            rproduccion,
                            statusdiseno
                            " . (($especificacion["rproduccion"] == 0) ? ",statusproduccion" : "") . "
                        )
                    values
                        (
                            '" . $idusuario . "',
                            '" . $idpedido . "',
                            '" . $especificacion["nombrediseno"] . "',
                            '" . $especificacion["fechaentrega"] . "',
                            '" . $especificacion["serigrafia"] . "',
                            '" . $especificacion["digital"] . "',
                            '" . $especificacion["bordado"] . "',
                            '" . $especificacion["especificaciones"] . "',
                            '" . $especificacion["rproduccion"] . "',
                            '1'
                            " . (($especificacion["rproduccion"] == 0) ? ",'2'" : "") . "
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }

                    $idespecificacion = mysqli_insert_id($this->con);

                    $notificarbordado = ($especificacion["bordado"] == 1) ? true : $notificarbordado;
                    $notificarserigrafia = ($especificacion["serigrafia"] == 1) ? true : $notificarserigrafia;

                    // ya que se haya creado un idespecificacion permanente, se creará la carpeta respectiva en la que irá la imagen que antes estaba en la zona temporal, y se colocará ahí

                    $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificaciones/" . $especificacion["idtmp"] . "/";
                    $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $idespecificacion . "/";

                    mkdir($destino, 0777, true);
                    rename($origen . $especificacion["imagen1"], $destino . $especificacion["imagen1"]);
                    rename($origen . $especificacion["imagen2"], $destino . $especificacion["imagen2"]);
                    rename($origen . $especificacion["imagen3"], $destino . $especificacion["imagen3"]);
                    rename($origen . $especificacion["imagen4"], $destino . $especificacion["imagen4"]);
                    rename($origen . $especificacion["imagen5"], $destino . $especificacion["imagen5"]);
                    // asignar los nombres de las imagenes en la tabla permanente
                    $query = "
                    update
                        tespecificaciones
                    set
                        imagen1 = '" . $especificacion["imagen1"] . "',
                        imagen2 = '" . $especificacion["imagen2"] . "',
                        imagen3 = '" . $especificacion["imagen3"] . "',
                        imagen4 = '" . $especificacion["imagen4"] . "',
                        imagen5 = '" . $especificacion["imagen5"] . "'
                    where
                        idespecificacion = '" . $idespecificacion . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }

                    $post["idespecificacion"] = $especificacion["idtmp"];
                    $partidas = $this->obtenerPartidasEspecificacionTMP($post);
                    foreach ($partidas["partidas"] as $partida) {
                        // recupera el indice del arreglo temporal del elemento de la iteracion correspondiente
                        $indice = array_search($partida["idpedidoproductotmp"], $idpedidoproductostmp);
                        $indice2 = array_search($partida["idpartidatmp"], $idcotizacionproductostmp);
                        // si no tiene idpedidoproducto temporal, entonces no se inserta nada en ese campo
                        if ($partida["idpedidoproductotmp"] != 0) {
                            $query = "
                            insert into
                                trespecificacionesproductos
                                (
                                    idespecificacion,
                                    idpedidoproducto,
                                    idcotizacionproducto
                                )
                            values
                                (
                                    '" . $idespecificacion . "',
                                    '" . $idpedidoproductos[$indice] . "',
                                    '" . $idcotizacionproductos[$indice2] . "'
                                )
                            ";
                        } else {
                            $query = "
                            insert into
                                trespecificacionesproductos
                                (
                                    idespecificacion,
                                    idcotizacionproducto
                                )
                            values
                                (
                                    '" . $idespecificacion . "',
                                    '" . $idcotizacionproductos[$indice2] . "'
                                )
                            ";
                        }

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }

                    // Si la especificacion no contiene ninguna partida desglosada, no tiene que realizar ninguna operacion en "asignar productos" (produccion)
                    if (!$this->especificacionConPartidasDesglosadas($idespecificacion)) {
                        // si además la especificacion no requiere produccion ni diseño, automáticamente debe terminarse (no aparecerá en producción ni siquiera al activar el pedido)
                        $status = 1;

                        $query = "
                        select
                            count(*) as total
                        from
                            tespecificaciones
                        where
                            idespecificacion = '" . $idespecificacion . "'
                            and statusproduccion = 2
                            and statusdiseno >= 1
                        ";

                        $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                        if ($total) {
                            $status = 2;
                        }

                        $query = "
                        update
                            tespecificaciones
                        set
                            statusalmacen = 1,
                            fechastatusalmacen = '" . $fecha . "',
                            status = '" . $status . "'
                        where
                            idespecificacion = '" . $idespecificacion . "'
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                }

                $arrayMovimientos = array();
                $generarsolicitudcompra = false;
                foreach ($desgloses["desgloses"] as $desglose) {
                    $post["idproducto"] = $desglose["idproducto"];
                    $producto = $this->claseProductos->obtenerProducto($post);
                    if ($producto["respuesta"] == "OK") {
                        $idproveedor = $producto["producto"]["idproveedor"];
                    } else {
                        $idproveedor = 0;
                    }

                    $indice = array_search($desglose["idtmp"], $idpedidoproductostmp);

                    $query = "
                    select
                        *
                    from
                        trespecificacionesproductos
                    where
                        idpedidoproducto = '" . $idpedidoproductos[$indice] . "'
                    ";

                    $idespecificacion = 0;
                    if (mysqli_num_rows(mysqli_query($this->con, $query))) {
                        $idespecificacion = mysqli_fetch_assoc(mysqli_query($this->con, $query))["idespecificacion"];
                    }

                    if ($desglose["idproducto"] > 0) {

                        $cantidadalmacenes = 0;

                        $this->claseAlmacenes = new Almacenes();
                        $post["tipoalmacen"] = 1;
                        $almacenes = $this->claseAlmacenes->obtenerAlmacenes($post);
                        foreach ($almacenes["almacenes"] as $almacen) {

                            $cantidadalmacenes += intval($post[$desglose["idtmp"] . "-" . $almacen["idalmacen"]]);

                            if ($idalmacensucursal != $almacen["idalmacen"] && intval($post[$desglose["idtmp"] . "-" . $almacen["idalmacen"]]) > 0) {
                                $arrayMovimientos = $this->modificarArrayMovimientos($arrayMovimientos, array("idalmacen" => $almacen["idalmacen"], "movimiento" => array("idproducto" => $desglose["idproducto"], "idcolor" => $desglose["idcolor"], "idtalla" => $desglose["idtalla"], "cantidad" => $post[$desglose["idtmp"] . "-" . $almacen["idalmacen"]])));
                            }
                        }

                        $this->claseSucursales = new Sucursales();
                        $sucursales = $this->claseSucursales->obtenerSucursales($post);
                        foreach ($sucursales["sucursales"] as $sucursal) {

                            $cantidadalmacenes += intval($post[$desglose["idtmp"] . "-" . $sucursal["idalmacen"]]);

                            if ($idalmacensucursal != $sucursal["idalmacen"] && intval($post[$desglose["idtmp"] . "-" . $sucursal["idalmacen"]]) > 0) {
                                $arrayMovimientos = $this->modificarArrayMovimientos($arrayMovimientos, array("idalmacen" => $sucursal["idalmacen"], "movimiento" => array("idproducto" => $desglose["idproducto"], "idcolor" => $desglose["idcolor"], "idtalla" => $desglose["idtalla"], "cantidad" => $post[$desglose["idtmp"] . "-" . $sucursal["idalmacen"]])));
                            }
                        }

                        if ($cantidadalmacenes < $desglose["cantidad"]) {

                            $query = "
                            insert into
                                tsolicitudescompra
                                (
                                    idusuario,
                                    idpedido,
                                    " . (($idespecificacion != 0) ? "idespecificacion," : "") . "
                                    idproveedor,
                                    idproducto,
                                    producto,
                                    idcategoriaproducto,
                                    idtalla,
                                    talla,
                                    idcolor,
                                    color,
                                    cantidad,
                                    fecha
                                )
                            values
                                (
                                    '" . $idusuario . "',
                                    '" . $idpedido . "',
                                    " . (($idespecificacion != 0) ? "'$idespecificacion'," : "") . "
                                    '" . $idproveedor . "',
                                    '" . $desglose["idproducto"] . "',
                                    '" . $desglose["producto"] . "',
                                    '" . $desglose["idcategoriaproducto"] . "',
                                    '" . $desglose["idtalla"] . "',
                                    '" . $desglose["talla"] . "',
                                    '" . $desglose["idcolor"] . "',
                                    '" . $desglose["color"] . "',
                                    '" . ($desglose["cantidad"] - $cantidadalmacenes) . "',
                                    '" . $fecha . "'
                                )
                            ";

                            $this->claseQueries->guardarQuery($query);

                            if (!mysqli_query($this->con, $query)) {
                                $correcto = false;
                            }

                            $generarsolicitudcompra = true;
                        }
                    } else {
                        $query = "
                        insert into
                            tsolicitudescompra
                            (
                                idusuario,
                                idpedido,
                                " . (($idespecificacion != 0) ? "idespecificacion," : "") . "
                                idproveedor,
                                idproducto,
                                producto,
                                idcategoriaproducto,
                                idtalla,
                                talla,
                                idcolor,
                                color,
                                cantidad,
                                fecha
                            )
                        values
                            (
                                '" . $idusuario . "',
                                '" . $idpedido . "',
                                " . (($idespecificacion != 0) ? "'$idespecificacion'," : "") . "
                                '" . $idproveedor . "',
                                '" . $desglose["idproducto"] . "',
                                '" . $desglose["producto"] . "',
                                '" . $desglose["idcategoriaproducto"] . "',
                                '" . $desglose["idtalla"] . "',
                                '" . $desglose["talla"] . "',
                                '" . $desglose["idcolor"] . "',
                                '" . $desglose["color"] . "',
                                '" . $desglose["cantidad"] . "',
                                '" . $fecha . "'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }

                        $generarsolicitudcompra = true;
                    }
                }

                //se generan los movimientos al inventario necesarios
                foreach ($arrayMovimientos as $arrayMovimiento) {

                    $idalmacen = $arrayMovimiento["idalmacen"];
                    $movimientos = $arrayMovimiento["movimientos"];

                    // recuperamos los folios
                    $folio1 = $this->obtenerFolio($idalmacen)["folio"];
                    $folio2 = $this->obtenerFolio($idalmacensucursal)["folio"];

                    if (count($movimientos) > 0) {
                        // $autorizacion = (($total>0) ? '0' : ((strpos($usuario["almacenes"], $idalmacen) !== false) ? '2' : '0'));
                        // insertar el registro en tmovimientosinventario
                        $query = "
                        insert into
                            tmovimientosinventario
                            (
                                idusuario,
                                tipousuario,
                                idpedido,
                                idtipomovimiento,
                                idalmacen,
                                idalmacensecundario,
                                folio,
                                folio2,
                                notas,
                                fecha,
                                status,
                                autorizacion
                            )
                        values
                            (
                                '" . $idusuario . "',
                                'U',
                                '" . $idpedido . "',
                                '3',
                                '" . $idalmacen . "',
                                '" . $idalmacensucursal . "',
                                '" . $folio1 . "',
                                '" . $folio2 . "',
                                'Pedido #" . $idpedido . "',
                                '" . $fecha . "',
                                'A',
                                '0',
                            )
                        ";
                        // '".$autorizacion."'

                        $this->claseQueries->guardarQuery($query);

                        if (mysqli_query($this->con, $query)) {

                            $idmovimientoinventario = mysqli_insert_id($this->con);

                            // actualizar folios
                            $post["folio"] = $folio1;
                            $post["idalmacen"] = $idalmacen;
                            $this->actualizarFolio($post);
                            $post["folio"] = $folio2;
                            $post["idalmacen"] = $idalmacensucursal;
                            $this->actualizarFolio($post);

                            $tipomovimiento = "S";

                            foreach ($movimientos as $movimiento) {
                                // insertar la lista de productos agregados en tmovimientoinventarioproductos
                                $query = "
                                insert into
                                    tmovimientoinventarioproductos
                                    (
                                        idmovimientoinventario,
                                        idproducto,
                                        idcolor,
                                        idtalla,
                                        idalmacen,
                                        idalmacensecundario,
                                        idtipomovimiento,
                                        cantidad
                                    )
                                values
                                    (
                                        '" . $idmovimientoinventario . "',
                                        '" . $movimiento["idproducto"] . "',
                                        '" . $movimiento["idcolor"] . "',
                                        '" . $movimiento["idtalla"] . "',
                                        '" . $idalmacen . "',
                                        '" . $idalmacensucursal . "',
                                        '3',
                                        '" . $movimiento["cantidad"] . "'
                                    )
                                ";

                                $this->claseQueries->guardarQuery($query);

                                if (!mysqli_query($this->con, $query)) {
                                    $correcto = false;
                                }

                                // // solo si el pedido se puede autorizar de inmediato, se deben mover existencias
                                // if ($autorizacion=='2') {
                                //     $post["idproducto"] = $movimiento["idproducto"];
                                //     $post["idtalla"] = $movimiento["idproducto"];
                                //     $post["idcolor"] = $movimiento["idproducto"];
                                //     $post["cantidad"] = $movimiento["idproducto"];
                                //     $post["tipomovimiento"] = "S";

                                //     $post["idusuario"] = $idusuario;
                                //     $post["idalmacen"] = $idalmacen;
                                //     $post["origenmovimiento"] = "M";
                                //     $post["idmovimiento"] = $idmovimientoinventario;
                                //     $this->claseProductos->actualizarExistencia($post);
                                // }
                            }
                        }
                    }
                }

                // NOTIFICACIONES
                $post["idtiponotificacion"] = 1;
                $post["mensaje"] = "La cotizacion #" . $idcotizacion . " ha sido convertida a pedido por " . $usuario["nombre"];
                $post["idorigen"] = $idpedido;

                $this->claseNotificaciones = new Notificaciones();
                //almacenistas
                $post["tipousuario"] = 1;
                if (!$this->claseNotificaciones->agregarNotificacionesADSB($post)) {
                    $correcto = false;
                }
                //bordado
                if ($notificarbordado) {
                    $post["tipousuario"] = 2;
                    if (!$this->claseNotificaciones->agregarNotificacionesADSB($post)) {
                        $correcto = false;
                    }
                }
                //serigrafia y diseño
                if ($notificarserigrafia) {
                    $post["tipousuario"] = 3;
                    if (!$this->claseNotificaciones->agregarNotificacionesADSB($post)) {
                        $correcto = false;
                    }
                }
                if ($generarsolicitudcompra) {
                    //solicitudes de compra
                    $post["tipousuario"] = 4;
                    $post["mensaje"] = "Se ha solicitado producto para el pedido #" . $idpedido;
                    if (!$this->claseNotificaciones->agregarNotificacionesADSB($post)) {
                        $correcto = false;
                    }
                }
                // FIN NOTIFICACIONES

                if ($correcto) {
                    $this->eliminarPartidasTMP($idusuario);
                    $this->eliminarDesglosesTMP($idusuario);
                    $this->eliminarEspecificacionesTMP($idusuario);

                    $post["idactividad"] = 31;
                    $post["comentarios"] = "Convirtio la cotizacion #" . $idcotizacion . " a pedido (#" . $idpedido . ")";
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Cotizacion Convertida a Pedido", "mensaje" => "Se ha convertido la cotizacion correctamente.");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al convertir la cotización.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo convertir la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Actualiza el status de la Cotizacion.
     * 
     * @access private
     * @param int $idcotizacion
     * @param string $status
     * @return array
     */
    private function actualizarStatusCotizacion($idcotizacion, $status)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $idcotizacion);
        $status = mysqli_real_escape_string($this->con, $status);

        try {
            $query = "
            update
                tcotizaciones
            set
                status = '" . $status . "'
            where
                idcotizacion = '" . $idcotizacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Status Actualizado", "mensaje" => "Se ha actualizado el status de la cotizacion correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo actualizar la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Cancelar Cotizacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function cancelarCotizacion($post)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);

        $correcto = true;

        try {
            $query = "
            update
                tcotizaciones
            set
                status = 'C'
            where
                idcotizacion = '" . $idcotizacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            if ($correcto) {

                $post["idactividad"] = 32;
                $post["comentarios"] = "Cancelo la cotizacion #" . $idcotizacion;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Cotizacion Cancelada", "mensaje" => "Se ha cancelado la cotizacion correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo cancelar la cotizacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Resolver Tipo de Producto de una partida.
     * 
     * Si la partida viene del inventario (idproducto > 0) el tipo se toma del catalogo,
     * porque la pantalla mantiene oculto el select de tipo en ese origen. Para las
     * partidas libres se limpia el valor recibido (puede llegar "undefined" o "null"
     * desde el javascript) y se conserva solo si es un tipo de producto existente.
     * 
     * @access private
     * @param int $idproducto
     * @param mixed $idtipoproducto
     * @return string
     */
    private function resolverIdTipoProducto($idproducto, $idtipoproducto)
    {
        $idproducto = (int) $idproducto;

        if ($idproducto > 0) {
            $query = "
            select
                idtipoproducto
            from
                tproductos
            where
                idproducto = '" . $idproducto . "'
            ";

            $producto = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($producto && $producto["idtipoproducto"] > 0) {
                return $producto["idtipoproducto"];
            }
        }

        $idtipoproducto = is_numeric($idtipoproducto) ? (int) $idtipoproducto : 0;

        if ($idtipoproducto > 0) {
            $query = "
            select
                idtipoproducto
            from
                ttiposproducto
            where
                idtipoproducto = '" . $idtipoproducto . "'
            ";

            if (!mysqli_num_rows(mysqli_query($this->con, $query))) {
                $idtipoproducto = 0;
            }
        }

        return (string) $idtipoproducto;
    }

    /**
     * Agregar Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarPartidaTMP($post)
    {
        // $idproductotmp = mysqli_real_escape_string($this->con,$post["idproductotmp"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $producto = mysqli_real_escape_string($this->con, $post["producto"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["idcategoriaproducto"]);
        $idtipoproducto = mysqli_real_escape_string($this->con, $this->resolverIdTipoProducto($idproducto, $post["idtipoproducto"] ?? "0"));
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);
        $precio = mysqli_real_escape_string($this->con, $post["precio"]);

        $serigrafia1 = mysqli_real_escape_string($this->con, $post["txtSerigrafia1"]);
        $serigrafia2 = mysqli_real_escape_string($this->con, $post["txtSerigrafia2"]);
        $serigrafia3 = mysqli_real_escape_string($this->con, $post["txtSerigrafia3"]);
        $personalizadonumero = mysqli_real_escape_string($this->con, $post["chkPersonalizadoNumero"]);
        $personalizadonombre = mysqli_real_escape_string($this->con, $post["chkPersonalizadoNombre"]);
        $bordado1 = mysqli_real_escape_string($this->con, $post["chkBordado1"]);
        $bordado2 = mysqli_real_escape_string($this->con, $post["chkBordado2"]);
        $bordado3 = mysqli_real_escape_string($this->con, $post["chkBordado3"]);
        $bordado4 = mysqli_real_escape_string($this->con, $post["chkBordado4"]);
        $bordadoespecial = mysqli_real_escape_string($this->con, $post["chkBordadoEspecial"]);
        $personalizado1linea = mysqli_real_escape_string($this->con, $post["chkLinea1"]);
        $personalizado2lineas = mysqli_real_escape_string($this->con, $post["chkLinea2"]);
        $personalizado3lineas = mysqli_real_escape_string($this->con, $post["chkLinea3"]);
        $sxl = mysqli_real_escape_string($this->con, $post["chkSXL"]);
        $xl2 = mysqli_real_escape_string($this->con, $post["chk2XL"]);
        $xl3 = mysqli_real_escape_string($this->con, $post["chk3XL"]);
        $observaciones = mysqli_real_escape_string($this->con, $post["txtObservaciones"]);

        try {
            $query = "
            insert into
                trcotizacionproductostmp
                (
                    idusuario,
                    idproducto,
                    producto,
                    idcategoriaproducto,
                    idtipoproducto,
                    cantidad,
                    precio,
                    serigrafia1,
                    serigrafia2,
                    serigrafia3,
                    personalizadonumero,
                    personalizadonombre,
                    bordado1,
                    bordado2,
                    bordado3,
                    bordado4,
                    bordadoespecial,
                    personalizado1linea,
                    personalizado2lineas,
                    personalizado3lineas,
                    sxl,
                    2xl,
                    3xl,
                    observaciones
                )
            values
                (
                    '" . $idusuario . "',
                    '" . $idproducto . "',
                    '" . $producto . "',
                    '" . $idcategoriaproducto . "',
                    '" . $idtipoproducto . "',
                    '" . $cantidad . "',
                    '" . $precio . "',
                    '" . $serigrafia1 . "',
                    '" . $serigrafia2 . "',
                    '" . $serigrafia3 . "',
                    '" . $personalizadonumero . "',
                    '" . $personalizadonombre . "',
                    '" . $bordado1 . "',
                    '" . $bordado2 . "',
                    '" . $bordado3 . "',
                    '" . $bordado4 . "',
                    '" . $bordadoespecial . "',
                    '" . $personalizado1linea . "',
                    '" . $personalizado2lineas . "',
                    '" . $personalizado3lineas . "',
                    '" . $sxl . "',
                    '" . $xl2 . "',
                    '" . $xl3 . "',
                    '" . $observaciones . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $idcotizacionproductotmp = mysqli_insert_id($this->con);

                $respuesta = array("respuesta" => "OK", "tipo" => "cerrarfancycargar", "formulario" => "formCotizacion", "pantalla" => "cotizacion", "idcotizacionproductotmp" => $idcotizacionproductotmp);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarPartidaTMP($post)
    {
        // FALTAN DE ACTUALIZAR LOS NOMBRES DE LA DERECHA
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $producto = mysqli_real_escape_string($this->con, $post["producto"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["idcategoriaproducto"]);
        $idtipoproducto = mysqli_real_escape_string($this->con, $this->resolverIdTipoProducto($idproducto, $post["idtipoproducto"] ?? "0"));
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);
        $precio = mysqli_real_escape_string($this->con, $post["precio"]);
        $serigrafia1 = mysqli_real_escape_string($this->con, $post["txtSerigrafia1"]);
        $serigrafia2 = mysqli_real_escape_string($this->con, $post["txtSerigrafia2"]);
        $serigrafia3 = mysqli_real_escape_string($this->con, $post["txtSerigrafia3"]);
        $personalizadonumero = mysqli_real_escape_string($this->con, $post["chkPersonalizadoNumero"]);
        $personalizadonombre = mysqli_real_escape_string($this->con, $post["chkPersonalizadoNombre"]);
        $bordado1 = mysqli_real_escape_string($this->con, $post["chkBordado1"]);
        $bordado2 = mysqli_real_escape_string($this->con, $post["chkBordado2"]);
        $bordado3 = mysqli_real_escape_string($this->con, $post["chkBordado3"]);
        $bordado4 = mysqli_real_escape_string($this->con, $post["chkBordado4"]);
        $bordadoespecial = mysqli_real_escape_string($this->con, $post["chkBordadoEspecial"]);
        $personalizado1linea = mysqli_real_escape_string($this->con, $post["chkLinea1"]);
        $personalizado2lineas = mysqli_real_escape_string($this->con, $post["chkLinea2"]);
        $personalizado3lineas = mysqli_real_escape_string($this->con, $post["chkLinea3"]);
        $sxl = mysqli_real_escape_string($this->con, $post["chkSXL"]);
        $xl2 = mysqli_real_escape_string($this->con, $post["chk2XL"]);
        $xl3 = mysqli_real_escape_string($this->con, $post["chk3XL"]);
        $observaciones = mysqli_real_escape_string($this->con, $post["txtObservaciones"]);

        try {
            $query = "
            update
                trcotizacionproductostmp
            set
                idproducto = '" . $idproducto . "',
                producto = '" . $producto . "',
                idcategoriaproducto = '" . $idcategoriaproducto . "',
                idtipoproducto = '" . $idtipoproducto . "',
                cantidad = '" . $cantidad . "',
                precio = '" . $precio . "',
                serigrafia1 = '" . $serigrafia1 . "',
                serigrafia2 = '" . $serigrafia2 . "',
                serigrafia3 = '" . $serigrafia3 . "',
                personalizadonumero = '" . $personalizadonumero . "',
                personalizadonombre = '" . $personalizadonombre . "',
                bordado1 = '" . $bordado1 . "',
                bordado2 = '" . $bordado2 . "',
                bordado3 = '" . $bordado3 . "',
                bordado4 = '" . $bordado4 . "',
                bordadoespecial = '" . $bordadoespecial . "',
                personalizado1linea = '" . $personalizado1linea . "',
                personalizado2lineas = '" . $personalizado2lineas . "',
                personalizado3lineas = '" . $personalizado3lineas . "',
                sxl = '" . $sxl . "',
                2xl = '" . $xl2 . "',
                3xl = '" . $xl3 . "',
                observaciones = '" . $observaciones . "'
            where
                idtmp = '" . $idpartida . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "cerrarfancycargar", "titulo" => "Producto Editado", "mensaje" => "Se ha editado el producto correctamente.", "formulario" => "formCotizacion", "pantalla" => "cotizacion");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }


    /**
     * Agregar Partidas Temporales. Pasa las partidas de la tabla final a la temporal (esto sucede cuando se modifica una cotizacion)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarPartidasTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);

        try {
            $query = "
            insert into
                trcotizacionproductostmp
                (
                    idusuario,
                    idproducto,
                    producto,
                    idcategoriaproducto,
                    idtipoproducto,
                    cantidad,
                    precio,
                    serigrafia1,
                    serigrafia2,
                    serigrafia3,
                    personalizadonumero,
                    personalizadonombre,
                    bordado1,
                    bordado2,
                    bordado3,
                    bordado4,
                    bordadoespecial,
                    personalizado1linea,
                    personalizado2lineas,
                    personalizado3lineas,
                    sxl,
                    2xl,
                    3xl,
                    observaciones
                )
            select
                '" . $idusuario . "',
                idproducto,
                producto,
                idcategoriaproducto,
                idtipoproducto,
                cantidad,
                precio,
                serigrafia1,
                serigrafia2,
                serigrafia3,
                personalizadonumero,
                personalizadonombre,
                bordado1,
                bordado2,
                bordado3,
                bordado4,
                bordadoespecial,
                personalizado1linea,
                personalizado2lineas,
                personalizado3lineas,
                sxl,
                2xl,
                3xl,
                observaciones
            from
                trcotizacionproductos
            where
                idcotizacion = '" . $idcotizacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "cerrarfancycargar", "titulo" => "Productos Agregados", "mensaje" => "Se han agregado los productos a la cotizacion correctamente.", "formulario" => "formCotizacion", "pantalla" => "cotizacion");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el producto.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Partidas Temporales.
     * 
     * @access public
     * @param int $idusuario
     * @return array
     */
    public function eliminarPartidasTMP($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            delete from
                trcotizacionproductostmp
            where
                idusuario = '" . $idusuario . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "formulario" => "formCotizacion");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron eliminar las partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Partida Temporal.
     * 
     * @access public
     * @param int $idpartida
     * @return array
     */
    public function eliminarPartidaTMP($idpartida)
    {
        $idpartida = mysqli_real_escape_string($this->con, $idpartida);

        try {
            $query = "
            delete from
                trcotizacionproductostmp
            where
                idtmp = '" . $idpartida . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "formulario" => "formCotizacion");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Recupera los productos temporales insertados de un usuario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasTMP($idusuario, $tipo = 0)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);
        // tipo puede ser 0 (encuentra todas las partidas), puede ser 1 (encuentra solo las partidas que tengan idproducto = 0 y no esten en la tabla de trpedidoproductostmp) o puede ser 2 (encuentra solo las partidas que no estén ya asociadas a un idespecificacion)

        $where = "";
        $where .= (($tipo == 1) ? " and idproducto = 0 and idtmp not in (select idcotizacionproducto from trpedidoproductostmp where idusuario = '" . $idusuario . "')" : "");
        $where .= (($tipo == 2) ? " and idtmp not in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '" . $idusuario . "')" : "");
        // $where .= (($tipo==3) ? " and idtmp in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '".$idusuario."' and idespecificaciontmp = '".$especificacion["idtmp"]."')" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                trcotizacionproductostmp
            where
                idusuario = '" . $idusuario . "'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    trcotizacionproductostmp
                where
                    idusuario = '" . $idusuario . "'
                    " . $where . "
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Desgloses Usuario. (trpedidoproductostmp) Recupera todos los desgloses de un usuario
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerDesglosesUsuarioTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            select
                count(*) as total
            from
                trpedidoproductostmp
            where
                idusuario = '" . $idusuario . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vrpedidoproductostmp
                where
                    idusuario = '" . $idusuario . "'
                ";

                $desgloses = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "desgloses" => $desgloses);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron desgloses.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Desgloses Partida. (trpedidoproductostmp)
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerDesglosesPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);

        try {
            $query = "
            select
                count(*) as total
            from
                trpedidoproductostmp
            where
                idcotizacionproducto = '" . $idpartida . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vrpedidoproductostmp 
                where
                    idcotizacionproducto = '" . $idpartida . "'
                ";

                $desgloses = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "desgloses" => $desgloses);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron desgloses.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Temporales (trpedidoproductostmp).
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerDesglosesPartidaTMP2($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        // $where = "";
        // $where .= (($tipo==1) ? "and a.idtmp not in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '".$idusuario."' and idespecificaciontmp != '".$idespecificacion."')" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                vrcotizacionproductostmp
            where
                idusuario = '" . $idusuario . "'
                and idtmp = '" . $idpartida . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    a.idtmp,
                    a.producto,
                    a.cantidad,
                    b.lista
                from
                    vrcotizacionproductostmp a
                left join
                    (
                        select
                            c.idcotizacionproducto,
                            group_concat(distinct(c.color),' : ',d.tallas separator ';') as lista
                        from
                            vrpedidoproductostmp c
                        left join
                            (
                                select
                                    idcolor,
                                    color,
                                    group_concat(talla,' - ',cantidad order by posicion separator ' / ') as tallas
                                from
                                    vrpedidoproductostmp
                                where
                                    idcotizacionproducto = '" . $idpartida . "'
                                group by
                                    idcolor,color
                            ) d
                        on
                            c.idcolor = d.idcolor
                            and c.color = d.color
                        group by
                            c.idcotizacionproducto
                        order by
                            c.idtmp
                    ) b
                on
                    b.idcotizacionproducto = a.idtmp
                where
                    a.idusuario = '" . $idusuario . "'
                    and a.idtmp = '" . $idpartida . "'
                ";

                // $this->claseQueries->guardarQuery($query);

                $desgloses = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "desgloses" => $desgloses);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron desgloses.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Temporales Version 2. (trpedidoproductostmp)
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerDesglosesPartidasTMP2($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        $where = "";
        $where .= (($tipo == 1) ? "and a.idtmp not in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '" . $idusuario . "' and idespecificaciontmp != '" . $idespecificacion . "')" : "and a.idtmp not in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '" . $idusuario . "')");

        try {
            $query = "
            select
                count(*) as total
            from
                vrcotizacionproductostmp a
            where
                a.idusuario = '" . $idusuario . "'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    a.idtmp,
                    a.producto,
                    a.cantidad,
                    b.desgloses
                from
                    vrcotizacionproductostmp a
                left join
                    (
                        select
                            c.idcotizacionproducto,
                            group_concat(distinct(c.color),' : ',d.tallas separator ';') as desgloses
                        from
                            vrpedidoproductostmp c
                        left join
                            (
                                select
                                    idcolor,
                                    color,
                                    idcotizacionproducto,
                                    group_concat(talla,' - ',cantidad order by posicion separator ' / ') as tallas
                                from
                                    vrpedidoproductostmp
                                where
                                    idusuario = '" . $idusuario . "'
                                group by
                                    idcotizacionproducto,idcolor,color
                            ) d
                        on
                            c.idcolor = d.idcolor
                            and c.color = d.color
                            and c.idcotizacionproducto = d.idcotizacionproducto
                        where
                            idusuario = '" . $idusuario . "'
                        group by
                            c.idcotizacionproducto
                        order by
                            c.idtmp
                    ) b
                on
                    b.idcotizacionproducto = a.idtmp
                where
                    a.idusuario = '" . $idusuario . "'
                #group by
                    #idtmp
                    " . $where . "
                ";

                // $this->claseQueries->guardarQuery($query);

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partida Temporal.
     * 
     * @access public
     * @param array $idpartida
     * @return array
     */
    public function obtenerPartidaTMP($idpartida)
    {
        $idpartida = mysqli_real_escape_string($this->con, $idpartida);

        try {
            $query = "
            select
                *
            from
                trcotizacionproductostmp
            where
                idtmp = '" . $idpartida . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $respuesta = array("respuesta" => "OK", "partida" => $partida);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Convertida a Pedido. Revisa si la cotizacion (o una de su historial) ya ha sido convertida a pedido
     * 
     * @access public
     * @param array $post
     * @return boolean
     */
    public function convertidaAPedido($post)
    {
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);

        // $where = "";

        // obtener una lista con todos los id's de cotizacion si ha sido modificada
        // si la lista esta vacia, el query marca error, por eso agregamos -1
        $idcotizaciones = array("-1");

        try {

            $cotizacionpadre = $this->obtenerCotizacionMasReciente($post)["cotizacion"];
            // $cotizacionpadre = $this->obtenerCotizacion($post)["cotizacion"];

            while ($this->obtenerCotizacion($post)["respuesta"] == "OK") {
                array_push($idcotizaciones, $cotizacionpadre["idcotizacion"]);

                $post["idcotizacion"] = $cotizacionpadre["idcotizacionpadre"];
                $cotizacionpadre = $this->obtenerCotizacion($post)["cotizacion"];
            }

            $query = "
            select
                count(*) as total
            from
                tpedidos
            where
                idcotizacion in (" . implode(",", $idcotizaciones) . ")
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Nombre Unico Producto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarNombreUnicoProducto($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombreproducto = mysqli_real_escape_string($this->con, $post["nombreproducto"]);

        try {
            $query = "
            select
                *
            from
                trcotizacionproductostmp
            where
                producto = '" . $nombreproducto . "'
                and idusuario = '" . $idusuario . "'
            ";

            if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0 && $nombreproducto != "") {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "El nombre es repetido.");
            } else {
                $respuesta = array("respuesta" => "OK");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Recuperar Especificaciones.
     * 
     * @access public
     * @param int $idpartida
     * @return array
     */
    public function recuperarEspecificacionesTMP($idpartida)
    {
        $idpartida = mysqli_real_escape_string($this->con, $idpartida);

        try {
            $query = "
            select
                *
            from
                trcotizacionproductostmp
            where
                idtmp = '" . $idpartida . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $respuesta = array("respuesta" => "OK", "idpartida" => $partida["idtmp"], "idproducto" => $partida["idproducto"], "producto" => $partida["producto"], "idcategoriaproducto" => $partida["idcategoriaproducto"], "idtipoproducto" => $partida["idtipoproducto"], "cantidad" => $partida["cantidad"], "precio" => $partida["precio"], "serigrafia1" => $partida["serigrafia1"], "serigrafia2" => $partida["serigrafia2"], "serigrafia3" => $partida["serigrafia3"], "personalizadonumero" => $partida["personalizadonumero"], "personalizadonombre" => $partida["personalizadonombre"], "bordado1" => $partida["bordado1"], "bordado2" => $partida["bordado2"], "bordado3" => $partida["bordado3"], "bordado4" => $partida["bordado4"], "bordadoespecial" => $partida["bordadoespecial"], "personalizado1linea" => $partida["personalizado1linea"], "personalizado2lineas" => $partida["personalizado2lineas"], "personalizado3lineas" => $partida["personalizado3lineas"], "sxl" => $partida["sxl"], "xl2" => $partida["2xl"], "xl3" => $partida["3xl"], "observaciones" => $partida["observaciones"]);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Correo Repetido.
     * 
     * @access public
     * @param string $correo
     * @return array
     */
    public function validarCorreoRepetido($correo)
    {
        $correo = mysqli_real_escape_string($this->con, $correo);

        try {
            $query = "
            select
                *
            from
                tclientes
            where
                status = 1
                and correo = '" . $correo . "'
            ";

            if (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) {
                $respuesta = array("respuesta" => "REPETIDO");
            } else {
                $respuesta = array("respuesta" => "NOREPETIDO");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cantidad Desglose Temporal. Recupera la cantidad de un desglose con cierta talla y color especificados
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function obtenerCantidadTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $idcolor = mysqli_real_escape_string($this->con, $post["idcolor"]);
        $color = mysqli_real_escape_string($this->con, $post["color"]);
        $idtalla = mysqli_real_escape_string($this->con, $post["idtalla"]);

        $where = "";
        $where .= (($color != "") ? " and color = '" . $color . "'" : "");

        try {
            $query = "
            select
                *
            from
                trpedidoproductostmp
            where
                idusuario = '" . $idusuario . "'
                and idcotizacionproducto = '" . $idpartida . "'
                and idproducto = '" . $idproducto . "'
                and idcolor = '" . $idcolor . "'
                and idtalla = '" . $idtalla . "'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idtmp"] > 0) {
                $respuesta = array("respuesta" => "OK", "cantidad" => $partida["cantidad"]);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Guardar Desglose Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function guardarDesgloseTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idproducto = mysqli_real_escape_string($this->con, $post["idproducto"]);
        $producto = mysqli_real_escape_string($this->con, $post["producto"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $idcategoriaproducto = mysqli_real_escape_string($this->con, $post["idcategoriaproducto"]);

        $correcto = true;

        try {

            // $this->eliminarDesglosesTMP($idusuario);
            // $post["idpar"]
            $this->eliminarDesglosesPartidaTMP($post);

            $partida = $this->obtenerPartidaTMP($idpartida)["partida"];

            if ($idproducto > 0) {
                $this->claseProductos = new Productos();
                $post["ordenar"] = 1;
                $colores = $this->claseProductos->obtenerColoresProducto($post)["colores"];
                $tallas = $this->claseProductos->obtenerTallasProducto($post);
            } else {
                $colores = array();
                $num = 1;
                while (true) {
                    if (isset($post["txtColor" . $num])) {
                        $color = mysqli_real_escape_string($this->con, $post["txtColor" . $num]);
                        if ($color != "") {
                            $colores[] = array("idcolor" => 0, "color" => $color);
                        }
                    } else {
                        break;
                    }
                    $num++;
                }
                $this->claseCategorias = new Categorias();
                $tallas = $this->claseCategorias->obtenerTallasCategoria($idcategoriaproducto, 1);
            }

            $num = 1;
            foreach ($colores as $color) {
                foreach ($tallas["tallas"] as $talla) {

                    if ($color["idcolor"] == 0) {
                        $valor = $num;
                        $idcolor = 0;
                    } else {
                        $valor = $color["idcolor"];
                        $idcolor = $color["idcolor"];
                    }

                    $cantidad = mysqli_real_escape_string($this->con, $post["txtCantidad" . $valor . "-" . $talla["idtalla"]]);
                    if ($cantidad > 0) {

                        $query = "
                        insert into
                            trpedidoproductostmp
                            (
                                idusuario,
                                idcotizacionproducto,
                                idproducto,
                                producto,
                                idtalla,
                                idcolor,
                                color,
                                cantidad,
                                precio
                            )
                            values
                            (
                                '" . $idusuario . "',
                                '" . $idpartida . "',
                                '" . $idproducto . "',
                                '" . $producto . "',
                                '" . $talla["idtalla"] . "',
                                '" . $idcolor . "',
                                '" . $color["color"] . "',
                                '" . $cantidad . "',
                                '" . $partida["precio"] . "'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                }
                $num++;
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el desglose.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Desgloses Temporales. Borra todos los desgloses de una partida
     * 
     * @access public
     * @param array $idusuario
     * @return array
     */
    public function eliminarDesglosesTMP($idusuario)
    {
        // $idpartida = mysqli_real_escape_string($this->con,$idpartida);
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            delete from
                trpedidoproductostmp
            where
                idusuario = '" . $idusuario . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron eliminar los desgloses de la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Desgloses Temporales. Borra todos los desgloses de una partida
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarDesglosesPartidaTMP($post)
    {
        // $idpartida = mysqli_real_escape_string($this->con,$idpartida);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);

        try {
            $query = "
            delete from
                trpedidoproductostmp
            where
                idcotizacionproducto = '" . $idpartida . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron eliminar los desgloses de la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Subir Imagen
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function subirImagenTMP($imagen, $ruta, $nombre, $tamanomax, $tabla = "", $campoid = "", $idusuario = "", $campo = "")
    {
        // $asd = mysqli_real_escape_string($this->con,$asd);

        try {
            $this->claseImagen = new Imagen();

            $this->claseImagen->subirImagen($imagen, $ruta, $nombre, $tamanomax, $tabla, $campoid, $idusuario, $campo);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Especificacion Temporales. Recupera las partidas asignadas a una especificacion
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasEspecificacionTMP($post)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        try {
            $query = "
            select
                *
            from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '" . $idespecificacion . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $partidas = mysqli_query($this->con, $query);

            if (mysqli_num_rows($partidas) > 0) {
                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Recupera las partidas asociadas a una especificacion (trcotizacionproductostmp).
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasEspecificacionTMP2($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);

        try {

            $query = "
            select
                count(*) as total
            from
                trcotizacionproductostmp
            where
                idusuario = '" . $idusuario . "'
                and idtmp in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '" . $idusuario . "' and idespecificaciontmp = '" . $idespecificacion . "')
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    trcotizacionproductostmp
                where
                    idusuario = '" . $idusuario . "'
                    and idtmp in (select idpartidatmp from trespecificacionesproductostmp where idusuario = '" . $idusuario . "' and idespecificaciontmp = '" . $idespecificacion . "')
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Tiene Partida Especificacion Temporales. Verifica si una partida esta asignada a una especificacion
     * 
     * @access public
     * @param int $idespecificacion
     * @param int $idpartida
     * @return array
     */
    public function tienePartidaEspecificacionTMP($idespecificacion, $idpartida)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $idespecificacion);
        $idpartida = mysqli_real_escape_string($this->con, $idpartida);

        try {
            $query = "
            select
                *
            from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '" . $idespecificacion . "'
                and idpartidatmp = '" . $idpartida . "'
            ";

            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($partida["idpartidatmp"] > 0) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Especificaciones Temporales.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificacionesTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            select
                *
            from
                tespecificacionestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            // $this->claseQueries->guardarQuery($query);

            $especificaciones = mysqli_query($this->con, $query);

            if (mysqli_num_rows($especificaciones) > 0) {
                $respuesta = array("respuesta" => "OK", "especificaciones" => $especificaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron especificaciones.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignar Desglose.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarDesgloseTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $imagen1 = mysqli_real_escape_string($this->con, $post["imagen1"]);
        $imagen2 = mysqli_real_escape_string($this->con, $post["imagen2"]);
        $imagen3 = mysqli_real_escape_string($this->con, $post["imagen3"]);
        $imagen4 = mysqli_real_escape_string($this->con, $post["imagen4"]);
        $imagen5 = mysqli_real_escape_string($this->con, $post["imagen5"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        $serigrafia = mysqli_real_escape_string($this->con, $post["chkSerigrafia"]);
        $digital = mysqli_real_escape_string($this->con, $post["chkDigital"]);
        $requiereproduccion = mysqli_real_escape_string($this->con, $post["rdRequiereProduccion"]);
        $bordado = mysqli_real_escape_string($this->con, $post["chkBordado"]);
        $especificaciones = mysqli_real_escape_string($this->con, $post["txtEspecificaciones"]);

        $correcto = true;

        try {

            $query = "
            insert into
                tespecificacionestmp 
                (
                    idusuario,
                    nombrediseno,
                    fechaentrega,
                    serigrafia,
                    digital,
                    bordado,
                    especificaciones,
                    rproduccion
                )
            values 
                (
                    '" . $idusuario . "',
                    '" . $nombre . "',
                    '" . $fecha . "',
                    '" . $serigrafia . "',
                    '" . $digital . "',
                    '" . $bordado . "',
                    '" . $especificaciones . "',
                    '" . $requiereproduccion . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $idespecificacion = mysqli_insert_id($this->con);

            if ($idespecificacion > 0) {
                $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/usuarioesptmp/" . $idusuario . "/";
                $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $idespecificacion . "/";

                mkdir($destino, 0777, true);
                rename($origen . $imagen1, $destino . $imagen1);
                rename($origen . $imagen2, $destino . $imagen2);
                rename($origen . $imagen3, $destino . $imagen3);
                rename($origen . $imagen4, $destino . $imagen4);
                rename($origen . $imagen5, $destino . $imagen5);
                // asignar los nombres de las imagenes en la tabla temporal
                $query = "
                update
                    tespecificacionestmp
                set
                    imagen1 = '" . $imagen1 . "',
                    imagen2 = '" . $imagen2 . "',
                    imagen3 = '" . $imagen3 . "',
                    imagen4 = '" . $imagen4 . "',
                    imagen5 = '" . $imagen5 . "'
                where
                    idtmp = '" . $idespecificacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            foreach ($post["cotizacionproductos"] as $idtmp) {
                // al pasar de desgloses a productos, el idtmp es el de la cotizacion temporal
                // a partir de ese id, se deben obtener los ids de trpedidoproductostmp
                $post["idpartida"] = $idtmp;
                $desgloses = $this->obtenerDesglosesPartidaTMP($post);

                // ahora la partida no necesita estar desglosada para poder ser agregada
                // si no está desglosada, sólo se inserta un registro en trespecificacionesproductostmp con idpedidoproductotmp igual a cero
                // si está desglosada, se insertan tantos registros como desgloses 
                if ($desgloses["respuesta"] == "OK") {
                    foreach ($desgloses["desgloses"] as $desglose) {
                        $query = "
                        insert into
                            trespecificacionesproductostmp
                            (
                                idusuario,
                                idespecificaciontmp,
                                idpedidoproductotmp,
                                idpartidatmp
                            )
                        values 
                            (
                                '" . $idusuario . "',
                                '" . $idespecificacion . "',
                                '" . $desglose["idtmp"] . "',
                                '" . $idtmp . "'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                } else {
                    $query = "
                    insert into
                        trespecificacionesproductostmp
                        (
                            idusuario,
                            idespecificaciontmp,
                            idpartidatmp
                        )
                    values
                        (
                            '" . $idusuario . "',
                            '" . $idespecificacion . "',
                            '" . $idtmp . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK");
                // $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"TITULO","mensaje"=>"MENSAJE","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "titulo" => "Atención", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los desgloses a la especificacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo #AsignarDesglose:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Reasignar Desglose.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function reasignarDesgloseTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $imagen1 = mysqli_real_escape_string($this->con, $post["imagen1"]);
        $imagen2 = mysqli_real_escape_string($this->con, $post["imagen2"]);
        $imagen3 = mysqli_real_escape_string($this->con, $post["imagen3"]);
        $imagen4 = mysqli_real_escape_string($this->con, $post["imagen4"]);
        $imagen5 = mysqli_real_escape_string($this->con, $post["imagen5"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        $serigrafia = mysqli_real_escape_string($this->con, $post["chkSerigrafia"]);
        $digital = mysqli_real_escape_string($this->con, $post["chkDigital"]);
        $requiereproduccion = mysqli_real_escape_string($this->con, $post["rdRequiereProduccion"]);
        $bordado = mysqli_real_escape_string($this->con, $post["chkBordado"]);
        $especificaciones = mysqli_real_escape_string($this->con, $post["txtEspecificaciones"]);

        $correcto = true;

        try {

            $query = "
            delete from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '" . $idespecificacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            mysqli_query($this->con, $query);

            $query = "
            update
                tespecificacionestmp
            set
                nombrediseno = '" . $nombre . "',
                fechaentrega = '" . $fecha . "',
                serigrafia = '" . $serigrafia . "',
                digital = '" . $digital . "',
                bordado = '" . $bordado . "',
                especificaciones = '" . $especificaciones . "',
                rproduccion = '" . $requiereproduccion . "'
            where
                idtmp = '" . $idespecificacion . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            if ($idespecificacion > 0) {
                $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $idespecificacion . "/";
                $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/usuarioesptmp/" . $idusuario . "/";
                mkdir($destino, 0777, true);
                rename($origen . $imagen1, $destino . $imagen1);
                rename($origen . $imagen2, $destino . $imagen2);
                rename($origen . $imagen3, $destino . $imagen3);
                rename($origen . $imagen4, $destino . $imagen4);
                rename($origen . $imagen5, $destino . $imagen5);
                // asignar los nombres de las imagenes en la tabla temporal
                $query = "
                update
                    tespecificacionestmp
                set
                    " . (($imagen1 != "") ? "imagen1 = '" . $imagen1 . "'," : "idtmp = idtmp,") . "
                    " . (($imagen2 != "") ? "imagen2 = '" . $imagen2 . "'," : "idtmp = idtmp,") . "
                    " . (($imagen3 != "") ? "imagen3 = '" . $imagen3 . "'," : "idtmp = idtmp,") . "
                    " . (($imagen4 != "") ? "imagen4 = '" . $imagen4 . "'," : "idtmp = idtmp,") . "
                    " . (($imagen5 != "") ? "imagen5 = '" . $imagen5 . "'" : "idtmp = idtmp") . "
                where
                    idtmp = '" . $idespecificacion . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            foreach ($post["cotizacionproductos"] as $idtmp) {
                // al pasar de desgloses a productos, el idtmp es el de la cotizacion temporal
                // a partir de ese id, se deben obtener los ids de trpedidoproductostmp
                $post["idpartida"] = $idtmp;
                $desgloses = $this->obtenerDesglosesPartidaTMP($post);

                // ahora la partida no necesita estar desglosada para poder ser agregada
                // si no está desglosada, sólo se inserta un registro en trespecificacionesproductostmp con idpedidoproductotmp igual a cero
                // si está desglosada, se insertan tantos registros como desgloses 
                if ($desgloses["respuesta"] == "OK") {
                    foreach ($desgloses["desgloses"] as $desglose) {
                        $query = "
                        insert into
                            trespecificacionesproductostmp
                            (
                                idusuario,
                                idespecificaciontmp,
                                idpedidoproductotmp,
                                idpartidatmp
                            )
                        values 
                            (
                                '" . $idusuario . "',
                                '" . $idespecificacion . "',
                                '" . $desglose["idtmp"] . "',
                                '" . $idtmp . "'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                } else {
                    $query = "
                    insert into
                        trespecificacionesproductostmp
                        (
                            idusuario,
                            idespecificaciontmp,
                            idpartidatmp
                        )
                    values
                        (
                            '" . $idusuario . "',
                            '" . $idespecificacion . "',
                            '" . $idtmp . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK");
                // $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar","titulo"=>"TITULO","mensaje"=>"MENSAJE","formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "titulo" => "Atención", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los desgloses a la especificacion.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Especificaciones Temporales.
     * 
     * @access public
     * @param array $idusuario
     * @return array
     */
    public function eliminarEspecificacionesTMP($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            delete from
                tespecificacionestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            mysqli_query($this->con, $query);

            $query = "
            delete from
                trespecificacionesproductostmp
            where
                idusuario = '" . $idusuario . "'
            ";

            mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK");
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Especificacion Temporal.
     * 
     * @access public
     * @param array $idespecificacion
     * @return array
     */
    public function eliminarEspecificacionTMP($idespecificacion)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $idespecificacion);

        try {

            $query = "
            delete from
                trespecificacionesproductostmp
            where
                idespecificaciontmp = '" . $idespecificacion . "'
            ";

            mysqli_query($this->con, $query);

            $query = "
            delete from
                tespecificacionestmp
            where
                idtmp = '" . $idespecificacion . "'
            ";

            mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK");
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * validar Cantidades.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarCantidades($post)
    {
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);
        $cantidad = mysqli_real_escape_string($this->con, $post["cantidad"]);

        try {
            // validar que la cantidad no se excede de lo que le resta a la partida
            $total = 0;
            $query = "
            select
                *
            from
                trcotizacionproductostmp
            where
                idtmp = '" . $idpartida . "'
            ";
            $partida = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // se consiguen todas las cantidades de los desgloses anteriores de esta partida
            $query = "
            select
                *
            from
                trpedidoproductostmp
            where
                idcotizacionproducto = '" . $partida["idtmp"] . "'
            ";
            $desgloses = mysqli_query($this->con, $query);
            while ($desglose = mysqli_fetch_assoc($desgloses)) {
                $total += intval($desglose["cantidad"]);
            }

            if (intval($cantidad) > intval($partida["cantidad"])) {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "titulo" => "ATENCION", "mensaje" => "La cantidad introducida excede lo que queda de esta partida", "dato" => "Total: " . $cantidad . " partida: " . $partida["cantidad"]);
            } else {
                $respuesta = array("respuesta" => "OK");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Folio. Recupera el folio del movimiento actual de una sucursal
     * 
     * @access public
     * @param int $idalmacen
     * @return array
     */
    public function obtenerFolio($idalmacen)
    {
        $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);

        try {
            $query = "
            select
                foliomovimiento
            from
                tsucursales
            where
                idalmacen = '" . $idalmacen . "'
            ";

            $folio = mysqli_fetch_assoc(mysqli_query($this->con, $query))["foliomovimiento"] + 1;

            if ($folio > 0) {
                $respuesta = array("respuesta" => "OK", "folio" => $folio);
            } else {
                $respuesta = array("respuesta" => "ERROR", "folio" => 0, "tipo" => "mensaje", "mensaje" => "No se encontró el folio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Especificacion Con Partidas Desglosadas. 
     * 
     * @access public
     * @param int $idespecificacion
     * @return boolean
     */
    public function especificacionConPartidasDesglosadas($idespecificacion)
    {
        $idespecificacion = mysqli_real_escape_string($this->con, $idespecificacion);

        try {
            $query = "
            select
                count(*) as total
            from
                trespecificacionesproductos
            where
                idespecificacion = '" . $idespecificacion . "'
                and idpedidoproducto != 0
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            $respuesta = (($total) ? true : false);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Desgloses Inventario. Recupera todos los desgloses provenientes de productos de inventario.
     * 
     * NOTA: es posible que esta funcion se pueda integrar en obtenerDesglosesUsuarioTMP nada más poniendo una condicion para recuperar todos los productos con idproducto mayor a 0 (productos de inventario)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerDesglosesInventarioTMP($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            select
                count(*) as total
            from
                trpedidoproductostmp
            where
                idusuario = '" . $idusuario . "'
                and idproducto > 0
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vrpedidoproductostmp
                where
                    idusuario = '" . $idusuario . "'
                    and idproducto > 0
                ";

                $desgloses = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "desgloses" => $desgloses);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron desgloses.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Actualizar Folio. Actualiza el folio de una sucursal
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function actualizarFolio($post)
    {
        $folio = mysqli_real_escape_string($this->con, $post["folio"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        try {
            $query = "
            update
                tsucursales
            set
                foliomovimiento = '" . $folio . "'
            where
                idalmacen = '" . $idalmacen . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "folio" => 0, "tipo" => "mensaje", "mensaje" => "No se pudo actualizar el folio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Colores Partida Temporal.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerColoresPartidaTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);

        try {
            $query = "
            select
                color
            from
                trpedidoproductostmp
            where
                idusuario = '" . $idusuario . "'
                and idcotizacionproducto = '" . $idpartida . "'
            group by
                color
            order by
                idtmp
            ";
            // idcotizacionproducto antes era idtmp 

            // $this->claseQueries->guardarQuery($query);

            $colores = mysqli_query($this->con, $query);

            if (mysqli_num_rows($colores) > 0) {
                $respuesta = array("respuesta" => "OK", "colores" => $colores);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron colores para la partida.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Desgloses. Revisa que todas las partidas desglosables hayan sido completamente desglosadas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarDesgloses($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        $correcto = true;

        try {
            $query = "
            select
                count(*) as total
            from
                trcotizacionproductostmp
            where
                idusuario = '" . $idusuario . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                // falta_desglosar es igual a la cantidad de partidas que faltan por desglosar (ya sea parcial o totalmente)
                $query = "
                select
                    sum(if(a.cantidad>ifnull(b.total_desglosado,0),1,0)) as falta_desglosar
                from
                    trcotizacionproductostmp a
                left join
                    (
                        select
                            idcotizacionproducto,
                            ifnull(sum(cantidad),0) as total_desglosado
                        from
                            trpedidoproductostmp
                        where
                            idusuario = '" . $idusuario . "'
                        group by
                            idcotizacionproducto
                    ) b
                on
                    a.idtmp = b.idcotizacionproducto
                where
                    idusuario = '" . $idusuario . "'
                    and
                    (
                        (
                            idproducto > 0
                            and idproducto in
                            (
                                select
                                    idproducto
                                from
                                    tproductoexistencias
                                where
                                    status = 1
                            )
                        )
                        or
                        (
                            idproducto = 0
                            and idcategoriaproducto in
                            (
                                select
                                    idcategoriaproducto
                                from
                                    trcategoriaproductotallas
                            )
                        )
                    )
                ";

                $falta_desglosar = mysqli_fetch_assoc(mysqli_query($this->con, $query))["falta_desglosar"];

                if ($falta_desglosar) {
                    $correcto = false;
                    $mensaje = "Hay partidas sin desglosar completamente.";
                } else {
                    $correcto = true;
                }
            } else {
                // no tienes partidas
                $correcto = false;
                $mensaje = "Debes agregar al menos una partida.";
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "titulo" => "Atención", "tipo" => "mensaje", "mensaje" => $mensaje);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Especificaciones.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarEspecificaciones($post)
    {

        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        $correcto = true;

        try {

            $query = "
            select
                count(*) as total
            from
                tespecificacionestmp
            where
                idusuario = '" . $idusuario . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No tienes especificaciones creadas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Asignado A Especificacion.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignadoAEspecificacion($post)
    {
        $idpartida = mysqli_real_escape_string($this->con, $post["idpartida"]);

        try {
            $query = "
            select
                count(*) as total
            from
                trespecificacionesproductostmp
            where
                idpartidatmp = '" . $idpartida . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
