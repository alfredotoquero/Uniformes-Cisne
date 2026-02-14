<?php
class Pedidos
{
    private $con;
    private $claseQueries;
    private $claseCotizaciones;
    private $claseMovimientos;
    private $claseUsuarios;
    private $claseProductos;
    private $claseAlmacenes;
    private $claseSucursales;
    private $claseClientes;
    private $claseContactos;
    private $claseNotificaciones;
    private $claseCorreos;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Cotizaciones.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Movimientos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Contactos.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Notificaciones.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Correos.php");
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
     * Obtener Pedidos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPedidos($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $fechainicial = mysqli_real_escape_string($this->con, $post["txtFechaInicial"]);
        $fechafinal = mysqli_real_escape_string($this->con, $post["txtFechaFinal"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);
        $status = mysqli_real_escape_string($this->con, $post["slcStatus"]);
        $tipo = mysqli_real_escape_string($this->con, $post["slcTipo"]);

        $tipopedido = mysqli_real_escape_string($this->con, $post["tipopedido"]);

        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        $pagina = (isset($post["pagina"])) ? $post["pagina"] : 1;
        $regpagina = 100;

        $where = "";
        $where .= (($busqueda != "") ? " and (cliente like '%" . $busqueda . "%' or idpedido like '%" . $busqueda . "%' or contacto like '%" . $busqueda . "%' or usuario like '%" . $busqueda . "%')" : "");
        $where .= (($fechainicial != "") ? " and date(fecha) >= '" . $fechainicial . "'" : "");
        $where .= (($fechafinal != "") ? " and date(fecha) <= '" . $fechafinal . "'" : "");
        $where .= (($status != "" && $status != 0) ? " and status = '" . $status . "'" : "");
        $where .= (($idsucursal != "") ? " and idsucursal = '" . $idsucursal . "'" : "");
        $where .= (($tipopedido == "activos") ? " and (((statusproduccion='S' or statusproduccion='' or statusproduccion='P' or (status!='E' and status!='C' and statuspago=1)) and status!='C' and status!='E') or (status='A' and statusproduccion='T'))" : "");
        $where .= (($tipopedido == "finalizados") ? (($tipo == 1) ? " and (status='E' or status='C') " : (($tipo == "T") ? " and status = 'E'" : " and status = '" . $tipo . "'")) : "");
        $where .= (($idcliente > 0) ? " and idcliente = '" . $idcliente . "'" : "");
        $where .= ((!empty($idtienda)) ? " and idcliente in (select idcliente from tclientes where idtienda = '" . $idtienda . "')" : "");

        try {
            $query = "
            select
                *
            from
                vpedidos
            where
                1=1
                " . $where . "
            order by
                idpedido desc
            limit 
                " . (($pagina - 1) * $regpagina) . "," . $regpagina . "
            ";

            $pedidos = mysqli_query($this->con, $query);

            $query = "
            select
                count(*) as total
            from
                vpedidos
            where
                1=1
                " . $where . "
            ";
            $numpaginas = ceil(mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"] / $regpagina);
            $maxpaginas = 3;

            if (mysqli_num_rows($pedidos) > 0) {
                $respuesta = array("respuesta" => "OK", "pedidos" => $pedidos, "pagina" => $pagina, "numpaginas" => $numpaginas, "maxpaginas" => $maxpaginas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron pedidos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPedido($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                *
            from
                vpedidos
            where
                idpedido = '" . $idpedido . "'
            ";
            $pedido = mysqli_fetch_assoc(mysqli_query($this->con, $query));
            $this->claseQueries->guardarQuery($query);
            if ($pedido["idpedido"] > 0) {
                $respuesta = array("respuesta" => "OK", "pedido" => $pedido);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del pedido.");
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
     * Agregar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarPedido($post)
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
        $subtotal = mysqli_real_escape_string($this->con, $post["subtotal"]);
        $iva = mysqli_real_escape_string($this->con, $post["iva"]);
        $total = mysqli_real_escape_string($this->con, $post["total"]);
        $pendiente = (($total == 0) ? '1' : '0');
        $linea1 = mysqli_real_escape_string($this->con, $post["txtLinea1"]);
        $linea2 = mysqli_real_escape_string($this->con, $post["txtLinea2"]);
        $linea3 = mysqli_real_escape_string($this->con, $post["txtLinea3"]);
        $linea4 = mysqli_real_escape_string($this->con, $post["txtLinea4"]);
        $linea5 = mysqli_real_escape_string($this->con, $post["txtLinea5"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);
        $puesto = mysqli_real_escape_string($this->con, $post["txtPuesto"]);

        $incluyeiva = (($incluyeiva == "") ? "0" : $incluyeiva);

        $correcto = true;

        $this->claseUsuarios = new Usuarios();
        $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"];

        $this->claseCotizaciones = new Cotizaciones();
        $this->claseSucursales = new Sucursales();

        try {
            // obtenemos el idalmacen de la sucursal
            $post["idsucursal"] = $idsucursal;
            $idalmacensucursal = $this->claseSucursales->obtenerSucursal($post)["sucursal"]["idalmacen_produccion"];

            $campos = "";
            $valores = "";
            // al agregar cliente, si se selecciona el checkbox de "no registrar al cliente", se guardan los datos en el pedido (tpedidos) en lugar de la tabla de clientes
            if ($noguardarcliente != "" && $noguardarcliente > 0) {
                $campos = "cliente,correocliente,telefonocliente,contacto,correocontacto,telefonocontacto,";
                $valores = "'" . $cliente . "','" . $correo . "','" . $telefono . "','" . $contacto . "','" . $correocontacto . "','" . $telefonocontacto . "',";
            } else {
                $this->claseContactos = new Contactos();
                $arraycontacto = array("idcliente" => $idcliente, "txtContacto" => $contacto, "txtCorreo" => $correocontacto, "txtTelefono" => $telefonocontacto, "idusuario" => $idusuario, "txtPuesto" => $puesto);
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
                        $respuesta = array("respuesta" => "OK", "tipo" => "abrir_nuevo_fancy", "url" => "/modulos/pedidos/coincidencias.php?idusuario=".$idusuario."&idsucursal=".$idsucursal."&noguardarcliente=".$noguardarcliente."&idcliente=".$idcliente."&cliente=".urlencode($cliente)."&correo=".urlencode($correo)."&telefono=".urlencode($telefono)."&idcontacto=".$idcontacto."&contacto=".urlencode($contacto)."&correocontacto=".urlencode($correocontacto)."&telefonocontacto=".urlencode($telefonocontacto)."&opcionvigencia=".$opcionvigencia."&incluyeiva=".$incluyeiva."&tasaiva=".$tasaiva."&subtotalizar=".$subtotalizar."&subtotal=".$subtotal."&iva=".$iva."&total=".$total."&idcotizacionpadre=".$idcotizacionpadre."&linea1=".urlencode($linea1)."&linea2=".urlencode($linea2)."&linea3=".urlencode($linea3)."&linea4=".urlencode($linea4)."&linea5=".urlencode($linea5)."&idciudad=".$idciudad."&idtienda=".$idtienda."&puesto=".urlencode($puesto));
                        return;
                    }
                } else {
                    if ($idcontacto == 0 && $contacto != "0" && $contacto != "") {
                        $idcontacto = $this->claseContactos->agregarContacto($arraycontacto)["idcontacto"];
                    }
                }
            }

            $query = "
            insert into
                tpedidos
                (
                    idusuario,
                    idsucursal,
                    " . $campos . "
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
                    pendiente
                )
            values
                (
                    '" . $idusuario . "',
                    '" . $idsucursal . "',
                    " . $valores . "
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
                    '" . $pendiente . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idpedido = mysqli_insert_id($this->con);

                // dos arreglos (uno temporal y uno permantente) de las partidas
                $idcotizacionproductostmp = array();
                $idcotizacionproductos = array();
                $partidas = $this->claseCotizaciones->obtenerPartidasTMP($idusuario);
                foreach ($partidas["partidas"] as $partida) {
                    $query = "
                    insert into
                        trcotizacionproductos
                        (
                            idpedido,
                            idproducto,
                            producto,
                            idcategoriaproducto,
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
                            '" . $idpedido . "',
                            '" . $partida["idproducto"] . "',
                            '" . $partida["producto"] . "',
                            '" . $partida["idcategoriaproducto"] . "',
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

                    mysqli_query($this->con, $query);

                    $idcotizacionproducto = mysqli_insert_id($this->con);

                    // se insertan el permanente y el temporal en los arreglos
                    array_push($idcotizacionproductostmp, $partida["idtmp"]);
                    array_push($idcotizacionproductos, $idcotizacionproducto);
                }

                $partidas = $this->claseCotizaciones->obtenerPartidasTMP($idusuario, 1);
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

                $idpedidoproductostmp = array();
                $idpedidoproductos = array();
                $desgloses = $this->claseCotizaciones->obtenerDesglosesUsuarioTMP($post);
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

                // NOTA1: especificaciones
                $notificarbordado = false;
                $notificarserigrafia = false;
                $especificaciones = $this->claseCotizaciones->obtenerEspecificacionesTMP($post);
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
                    $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $especificacion["idtmp"] . "/";
                    $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificaciones/" . $idespecificacion . "/";
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
                    $partidas = $this->claseCotizaciones->obtenerPartidasEspecificacionTMP($post);
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
                    if (!$this->claseCotizaciones->especificacionConPartidasDesglosadas($idespecificacion)) {
                        // si además la especificacion no requiere produccion, automáticamente debe terminarse (no aparecerá en producción ni siquiera al activar el pedido)
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

                        $totalesp = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                        if ($totalesp) {
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


                // NOTA2: tsolicitudes
                $arrayMovimientos = array();
                $generarsolicitudcompra = false;
                foreach ($desgloses["desgloses"] as $desglose) {
                    $this->claseProductos = new Productos();
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

                        // $this->claseSucursales = new Sucursales();
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
                    $folio1 = $this->claseCotizaciones->obtenerFolio($idalmacen)["folio"];
                    $folio2 = $this->claseCotizaciones->obtenerFolio($idalmacensucursal)["folio"];

                    if (count($movimientos) > 0) {
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
                                '0'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (mysqli_query($this->con, $query)) {

                            $idmovimientoinventario = mysqli_insert_id($this->con);

                            // actualizar folios
                            $post["folio"] = $folio1;
                            $post["idalmacen"] = $idalmacen;
                            $this->claseCotizaciones->actualizarFolio($post);
                            $post["folio"] = $folio2;
                            $post["idalmacen"] = $idalmacensucursal;
                            $this->claseCotizaciones->actualizarFolio($post);

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
                            }
                        }
                    }
                }

                // si es un pedido interno ($total == 0), se puede activar automaticamente
                if ($total == 0) {
                    $post["idpedido"] = $idpedido;
                    $this->activarPedido($post);
                }

                $post["idtiponotificacion"] = 1;
                $post["mensaje"] = "El pedido #" . $idpedido . " ha sido creado por " . $usuario["nombre"];
                $post["idorigen"] = $idpedido;
                // Esta notificacion sería para todos aquellos usuarios que tienen acceso a la pantalla de produccion
                $query = "
                insert into
                    tnotificaciones
                    (
                        tipousuario,
                        idusuario,
                        idtiponotificacion,
                        mensaje,
                        idorigen
                    )
                select
                    'U',
                    idusuario,
                    1,
                    '" . $mensaje . "',
                    '" . $idpedido . "'
                from
                    tusuarios
                where
                    idusuario in
                    (
                        select
                            idusuario
                        from
                            trusuariosecciones
                        where
                            idseccion in
                            (
                                select
                                    idseccion
                                from
                                    tsecciones
                                where
                                    produccion = 1
                            )
                    )
                    and idusuario != '" . $usuario["idusuario"] . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

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
                    $post["tipousuario"] = 4;
                    $post["mensaje"] = "Se ha solicitado producto para el pedido #" . $idpedido;
                    if (!$this->claseNotificaciones->agregarNotificacionesADSB($post)) {
                        $correcto = false;
                    }
                }

                if ($correcto) {
                    $this->claseCotizaciones->eliminarPartidasTMP($idusuario);
                    $this->claseCotizaciones->eliminarDesglosesTMP($idusuario);
                    $this->claseCotizaciones->eliminarEspecificacionesTMP($idusuario);

                    $post["idactividad"] = 44;
                    $post["comentarios"] = "Agrego el pedido #" . $idpedido;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Pedido Agregrado", "mensaje" => "Se ha agregado el pedido correctamente.");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar el pedido.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código #AgregarPedido: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarPedido($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idcotizacion = mysqli_real_escape_string($this->con, $post["idcotizacion"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);
        // $noguardarcliente = mysqli_real_escape_string($this->con,$post["chkNoGuardarCliente"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        // $cliente = mysqli_real_escape_string($this->con,$post["txtCliente"]);
        // $correo = mysqli_real_escape_string($this->con,$post["txtCorreo"]);
        // $telefono = mysqli_real_escape_string($this->con,$post["txtTelefono"]);
        $idcontacto = mysqli_real_escape_string($this->con, $post["idcontacto"]);
        // $contacto = mysqli_real_escape_string($this->con,$post["txtContacto"]);
        // $correocontacto = mysqli_real_escape_string($this->con,$post["txtCorreoC"]);
        // $telefonocontacto = mysqli_real_escape_string($this->con,$post["txtTelefonoC"]);
        $fecha = date("Y-m-d H:i:s");
        $opcionvigencia = mysqli_real_escape_string($this->con, $post["slcVigencia"]);
        $vigencia = date("Y-m-d H:i:s", strtotime("+" . $opcionvigencia . " days", strtotime(date("Y-m-d"))));
        $incluyeiva = mysqli_real_escape_string($this->con, $post["chkIncluyeIva"]);
        $tasaiva = mysqli_real_escape_string($this->con, $post["slcIVA"]);
        $subtotal = mysqli_real_escape_string($this->con, $post["subtotal"]);
        $iva = mysqli_real_escape_string($this->con, $post["iva"]);
        $total = mysqli_real_escape_string($this->con, $post["total"]);
        $pendiente = (($total == 0) ? '1' : '0');
        $linea1 = mysqli_real_escape_string($this->con, $post["txtLinea1"]);
        $linea2 = mysqli_real_escape_string($this->con, $post["txtLinea2"]);
        $linea3 = mysqli_real_escape_string($this->con, $post["txtLinea3"]);
        $linea4 = mysqli_real_escape_string($this->con, $post["txtLinea4"]);
        $linea5 = mysqli_real_escape_string($this->con, $post["txtLinea5"]);

        $correcto = true;

        $this->claseCotizaciones = new Cotizaciones();
        $this->claseSucursales = new Sucursales();

        try {

            $post["idsucursal"] = $idsucursal;
            $idalmacensucursal = $this->claseSucursales->obtenerSucursal($post)["sucursal"]["idalmacen_produccion"];

            $this->eliminarDatosPedido($idpedido);

            $query = "
            update
                tpedidos
            set
                idsucursal = '" . $idsucursal . "',
                incluyeiva = '" . $incluyeiva . "',
                tasaiva = '" . $tasaiva . "',
                subtotal = '" . $subtotal . "',
                iva = '" . $iva . "',
                total = '" . $total . "',
                fechavigencia = '" . $vigencia . "',
                idvigencia = '" . $opcionvigencia . "',
                linea1 = '" . $linea1 . "',
                linea2 = '" . $linea2 . "',
                linea3 = '" . $linea3 . "',
                linea4 = '" . $linea4 . "',
                linea5 = '" . $linea5 . "',
                pendiente = '" . $pendiente . "'
            where
                idpedido = '" . $idpedido . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                // dos arreglos (uno temporal y uno permantente) de las partidas
                $idcotizacionproductostmp = array();
                $idcotizacionproductos = array();
                $partidas = $this->claseCotizaciones->obtenerPartidasTMP($idusuario);
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

                    // se recupera el id permanente
                    $idcotizacionproducto = mysqli_insert_id($this->con);

                    // se insertan el permanente y el temporal en los arreglos
                    array_push($idcotizacionproductostmp, $partida["idtmp"]);
                    array_push($idcotizacionproductos, $idcotizacionproducto);
                }

                $partidas = $this->claseCotizaciones->obtenerPartidasTMP($idusuario, 1);
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

                // dos arreglos (uno temporal y otro permanente) de los desgloses
                $idpedidoproductostmp = array();
                $idpedidoproductos = array();
                $desgloses = $this->claseCotizaciones->obtenerDesglosesUsuarioTMP($post);
                foreach ($desgloses["desgloses"] as $desglose) {
                    // recupera el indice del arreglo temporal del elemento de la iteracion correspondiente
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

                // NOTA1: especificaciones
                $especificaciones = $this->claseCotizaciones->obtenerEspecificacionesTMP($post);
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
                            " . (($especificacion["rproduccion"] == 0) ? ",2" : "") . "
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }

                    $idespecificacion = mysqli_insert_id($this->con);

                    // ya que se haya creado un idespecificacion permanente, se creará la carpeta respectiva en la que irá la imagen que antes estaba en la zona temporal, y se colocará ahí
                    $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $especificacion["idtmp"] . "/";
                    $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificaciones/" . $idespecificacion . "/";
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
                    $partidas = $this->claseCotizaciones->obtenerPartidasEspecificacionTMP($post);
                    foreach ($partidas["partidas"] as $partida) {
                        // recupera el indice del arreglo temporal del elemento de la iteracion correspondiente
                        $indice = array_search($partida["idpedidoproductotmp"], $idpedidoproductostmp);
                        $indice2 = array_search($partida["idpartidatmp"], $idcotizacionproductostmp);

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
                    if (!$this->claseCotizaciones->especificacionConPartidasDesglosadas($idespecificacion)) {
                        // si además la especificacion no requiere produccion, automáticamente debe terminarse (no aparecerá en producción ni siquiera al activar el pedido)
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

                // NOTA2: tsolicitudes
                $arrayMovimientos = array();
                $generarsolicitudcompra = false;
                foreach ($desgloses["desgloses"] as $desglose) {
                    $this->claseProductos = new Productos();
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
                    $folio1 = $this->claseCotizaciones->obtenerFolio($idalmacen)["folio"];
                    $folio2 = $this->claseCotizaciones->obtenerFolio($idalmacensucursal)["folio"];

                    if (count($movimientos) > 0) {
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
                                '0'
                            )
                        ";

                        $this->claseQueries->guardarQuery($query);

                        if (mysqli_query($this->con, $query)) {

                            $idmovimientoinventario = mysqli_insert_id($this->con);

                            // actualizar folios
                            $post["folio"] = $folio1;
                            $post["idalmacen"] = $idalmacen;
                            $this->claseCotizaciones->actualizarFolio($post);
                            $post["folio"] = $folio2;
                            $post["idalmacen"] = $idalmacensucursal;
                            $this->claseCotizaciones->actualizarFolio($post);

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
                            }
                        }
                    }
                }

                if ($correcto) {
                    $this->claseCotizaciones->eliminarPartidasTMP($idusuario);
                    $this->claseCotizaciones->eliminarDesglosesTMP($idusuario);
                    $this->claseCotizaciones->eliminarEspecificacionesTMP($idusuario);

                    $post["idactividad"] = 45;
                    $post["comentarios"] = "Edito el pedido #" . $idpedido;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Pedido Editado", "mensaje" => "Se ha editado el pedido correctamente.");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al editar el pedido.");
                }
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Desglose. Recupera el desglose de un pedido (vespecificacionesproductos)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerDesglose($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        $where = (($tipo == 1) ? " and cantidad_surtida > 0 " : "");

        try {
            $query = "
            select
                count(*) as total
            from
                vespecificacionesproductos
            where
                idespecificacion in
                (
                    select
                        idespecificacion
                    from
                        tespecificaciones
                    where
                        idpedido = '" . $idpedido . "'
                ) 
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];


            if ($total) {

                $query = "
                select
                    *
                from
                    vespecificacionesproductos
                where
                    idespecificacion in
                    (
                        select
                            idespecificacion
                        from
                            tespecificaciones
                        where
                            idpedido = '" . $idpedido . "'
                    )
                    " . $where . "
                ";

                $desgloses = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "desgloses" => $desgloses);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron datos de desglose en el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Sin Desglose. Recupera todas las partidas del pedido que no tienen desglose
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasSinDesglose($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                *
            from
                vrpedidoproductos
            where
                idpedido = '" . $idpedido . "'
                and idpedidoproducto not in
                (
                    select
                        idpedidoproducto
                    from
                        trespecificacionesproductos
                    where
                        idpedido = '" . $idpedido . "'
                )
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            

            if ($total) {

                $query = "
                select
                    *
                from
                    vrpedidoproductos
                where
                    idpedido = '" . $idpedido . "'
                    and idpedidoproducto not in
                    (
                        select
                            idpedidoproducto
                        from
                            trespecificacionesproductos
                        where
                            idpedido = '" . $idpedido . "'
                    )
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron datos de desglose en el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Pagos. Recupera los pagos del pedido (ttickets)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPagos($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vtickets
            where
                idpedido = '" . $idpedido . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vtickets
                where
                    idpedido = '" . $idpedido . "'
                ";

                $pagos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "pagos" => $pagos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron pagos para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Cotizacion. (vrcotizacionproductos)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasCotizacion($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vrcotizacionproductos
            where
                idpedido = '" . $idpedido . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    c.*,
                    group_concat(d.color,' : ',d.tallas separator ';') as desgloses
                from
                    vrcotizacionproductos c
                left join
                    (
                        select
                            idcotizacionproducto,
                            idproducto,
                            producto,
                            idcolor,
                            color,
                            group_concat(talla,' - ',cantidad order by idcolor,posicion separator ' / ') as tallas
                        from
                            vrpedidoproductos
                        where
                            idpedido = '" . $idpedido . "'
                        group by
                            idcotizacionproducto,idproducto,producto,idcolor,color
                    ) as d
                on
                    c.idcotizacionproducto = d.idcotizacionproducto
                    and c.idproducto = d.idproducto
                    and c.producto = d.producto
                where
                    idpedido = '" . $idpedido . "'
                group by
                    c.idcotizacionproducto,c.idproducto,c.producto
                order by
                    c.idcotizacionproducto
                ";
                // idcolor,idtalla,idcotizacionproducto

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Partidas Cotizacion. (vrcotizacionproductos)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPartidasCotizacion1($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                count(*) as total
            from
                vrcotizacionproductos
            where
                idpedido = '" . $idpedido . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    *
                from
                    vrcotizacionproductos c
                left join
                    (
                        select
                            idcotizacionproducto,
                            idproducto,
                            producto,
                            color,
                            group_concat(talla,',',cantidad separator ';') as desgloses
                        from
                            vrpedidoproductos
                        where
                            idpedido = '" . $idpedido . "'
                    ) as d
                on
                    c.idproducto = d.idproducto
                where
                    idpedido = '" . $idpedido . "'
                group by
                    c.idproducto
                order by
                    c.idcotizacionproducto
                ";
                // idcolor,idtalla,idcotizacionproducto

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Especificaciones. Recupera las especificaciones junto con el desglose de las mismas
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificaciones($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $pendiente = mysqli_real_escape_string($this->con, $post["pendiente"]);

        $where = "";
        $where .= (($pendiente == "") ? " and e.pendiente = 1" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                vespecificaciones e
            where
                idpedido = '" . $idpedido . "'
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    e.*,
                    a.desgloses,
                    b.partidas
                from
                    vespecificaciones e
                left join
                    (
                        select
                            idespecificacion,
                            group_concat(' ',cantidad,' ', producto,' | Talla: ', talla,', Color: ', color order by idcolor,color,posicion separator '\n') as desgloses
                        from
                            vespecificacionesproductos
                        where
                            idpedido = '" . $idpedido . "'
                        group by
                            idespecificacion
                    ) as a
                on
                    e.idespecificacion = a.idespecificacion
                left join
                    (
                        select
                            a.idpedido,
                            b.idespecificacion,
                            group_concat(' ', a.cantidad,' ',a.producto separator '\n') as partidas
                        from
                            vrcotizacionproductos a
                        left join
                            vespecificacionesproductos b
                        on
                            a.idcotizacionproducto = b.idcotizacionproducto
                        where
                            a.idpedido = '" . $idpedido . "'
                            and a.idproducto = 0
                            and b.idpedidoproducto = 0
                        group by
                            a.idpedido,b.idespecificacion
                    ) b
                on
                    e.idpedido = b.idpedido
                    and e.idespecificacion = b.idespecificacion
                where
                    e.idpedido = '" . $idpedido . "'
                    " . $where . "
                order by
                    e.fechaentrega, e.idespecificacion
                ";

                file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebqqqPPP.txt", $query);
                $especificaciones = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "especificaciones" => $especificaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron especificaciones para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Solicitudes. Recupera las solicitudes asociadas al pedido
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSolicitudes($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        $where = "";
        $where .= (($idpedido != "") ? " and s.idpedido = '" . $idpedido . "'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vsolicitudescompra s
            where
                1=1
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vsolicitudescompra s
                left join
                    (
                        select
                            idsolicitudcompra,
                            idcompraproducto,
                            cantidad_recibida
                        from
                            trcompraproductos
                        group by
                            idsolicitudcompra
                    ) as c
                on
                    s.idsolicitudcompra = c.idsolicitudcompra
                where
                    1=1
                    " . $where . "
                order by
                    idproducto,producto,idcolor,color,talla_posicion,fecha desc
                ";
                // order by
                //     idproducto,color,talla_posicion,fecha desc

                $solicitudes = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "solicitudes" => $solicitudes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron solicitudes registradas.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Productos Asignados. Recupera los productos asignados al pedido desde producción
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerProductosAsignados($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                a.cantidad,
                c.producto,
                d.nombre as usuario,
                e.nombre as almacen,
                a.registro as fecha
            from
                tasignacionproductospedido a
            left join
                trespecificacionesproductos b
            on
                b.idespecificacionproducto = a.idespecificacionproducto
            left join
                vrpedidoproductos c
            on
                c.idpedidoproducto = b.idpedidoproducto
            left join
                tusuarios d
            on
                d.idusuario = a.idusuario
            left join
                talmacenes e
            on
                e.idalmacen = a.idalmacen
            where
                a.idpedido = ".$idpedido."
            order by
                a.registro";

            $productos = mysqli_query($this->con, $query);

            if(mysqli_num_rows($productos)>0){
                $productos = mysqli_fetch_all($productos,MYSQLI_ASSOC);
                $respuesta = array("respuesta" => "OK", "productos" => $productos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron productos asignados al pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Movimientos. Recupera los movimientos asociados al pedido especificado
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerMovimientos($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        $where = "";
        $where .= (($idpedido != "") ? " and idpedido = '" . $idpedido . "'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vmovimientoproductos
            where
                1=1
                " . $where . "
                and pendiente = 1
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    vmovimientoproductos
                where
                    1=1
                    " . $where . "
                    and pendiente = 1
                ";
                // order by
                //     idproducto,color,talla_posicion,fecha desc

                $movimientos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "movimientos" => $movimientos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron movimientos registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Activar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function activarPedido($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        $correcto = true;

        try {
            $query = "
            update
                tpedidos
            set
                pendiente = 1
            where
                idpedido = '" . $idpedido . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            /** 
             * revisar qué movimientos del pedido se van a autorizar. Estos movimientos son aquellos que cumplen con lo siguiente:
             * 
             * -que el movimiento sea de traspaso (creo que siempre se cumple)
             * -que su status de autorizacion esté "en espera" de respuesta (solo se pueden autorizar al activar el pedido, no antes)
             * -que el usuario que lo active tenga asignado el almacen origen (¿o es el usuario que agregó el pedido?)
             * 
             */
            $this->claseUsuarios = new Usuarios();
            $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"];

            $this->claseMovimientos = new Movimientos();
            $post["almacenes"] = $usuario["almacenes"];
            $movimientos = $this->claseMovimientos->obtenerMovimientos($post);
            if ($movimientos["respuesta"] == "OK") {
                foreach ($movimientos["movimientos"] as $movimiento) {
                    if ($movimiento["autorizacion"] == 0 && $movimiento["idtipomovimiento"] == 3) {
                        $post["autorizacion"] = 2;
                        $post["idmovimientoinventario"] = $movimiento["idmovimientoinventario"];
                        $this->claseMovimientos->actualizarAutorizacionMovimiento($post);
                    }
                }
            }

            if ($correcto) {

                $post["idactividad"] = 46;
                $post["comentarios"] = "Activo el pedido #" . $idpedido;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Pedido Activado", "mensaje" => "Se ha activado el pedido correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al activar el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Entregar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function entregarPedido($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $desgloses = $post["chkDesglose"];
        $productos = $post["chkProducto"];

        $correcto = true;

        $fecha = date("Y-m-d H:i:s");

        try {

            foreach ($desgloses as $idespecificacionproducto) {
                $idespecificacionproducto = mysqli_real_escape_string($this->con, $idespecificacionproducto);

                $query = "
                update
                    trespecificacionesproductos
                set
                    status_entrega = 1,
                    status = 2,
                    fecha_inicio_produccion = '" . $fecha . "',
                    fecha_fin_produccion = '" . $fecha . "'
                where
                    idespecificacionproducto = '" . $idespecificacionproducto . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            foreach ($productos as $idpedidoproducto) {
                $idpedidoproducto = mysqli_real_escape_string($this->con, $idpedidoproducto);

                // NOTA: cuando se entregue producto sin haber terminado todas las especificaciones, ¿qué pasará con los registros de tespecificaciones restantes?
                $query = "
                update
                    trpedidoproductos
                set
                    status = 'E'
                where
                    idpedidoproducto = '" . $idpedidoproducto . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            $query = "
            select
                count(*) as total
            from
                trespecificacionesproductos
            where
                idespecificacion in
                (
                    select
                        idespecificacion
                    from
                        tespecificaciones
                    where
                        idpedido = '" . $idpedido . "'
                )
                and status_entrega = 0
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total == 0) {
                $query = "
                update
                    tpedidos
                set
                    statusproduccion = 'T'
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                select
                    count(*) as total
                from
                    trpedidoproductos
                where
                    idpedido = '" . $idpedido . "'
                    and idpedidoproducto not in
                    (
                        select
                            idpedidoproducto
                        from
                            vespecificacionesproductos
                        where
                            idpedido = '" . $idpedido . "'
                    )
                    and status = ''
                ";

                $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

                if ($total == 0) {
                    $query = "
                    update
                        tpedidos
                    set
                        status = 'E',
                        fechaactualizacion = '" . $fecha . "',
                        idusuarioentrega = '" . $idusuario . "'
                    where
                        idpedido = '" . $idpedido . "'
                    ";

                    $this->claseQueries->guardarQuery($query);

                    if (!mysqli_query($this->con, $query)) {
                        $correcto = false;
                    }
                }
            }

            if ($correcto) {

                $post["idactividad"] = 47;
                $post["comentarios"] = "Entrego el pedido #" . $idpedido;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Pedido Entregado", "mensaje" => "Se ha entregado el pedido correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al entregar el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Finalizar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function finalizarPedido($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $status = mysqli_real_escape_string($this->con, $post["status"]);
        $motivo = mysqli_real_escape_string($this->con, $post["motivo"]);
        $accionmovimientos = mysqli_real_escape_string($this->con, $post["accionmovimientos"]);

        $fecha = date("Y-m-d H:i:s");

        $correcto = true;

        try {
            $query = "
            update
                tpedidos
            set
                status = '" . $status . "',
                statusproduccion = 'T',
                motivofinalizacion = '" . $motivo . "',
                idusuariofinalizacion = '" . $idusuario . "',
                fechaactualizacion = '" . $fecha . "'
            where
                idpedido = '" . $idpedido . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $query = "
            update
                tsolicitudescompra
            set
                status = 2
            where
                idpedido = '" . $idpedido . "'
                and idcompra = 0
                and status = 0
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $query = "
            update
                tespecificaciones
            set
                status = 2
            where
                idpedido = '" . $idpedido . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            // actualizar también los movimientos relacionados
            // si se está cancelando el pedido y los movimientos están pendientes de autorización, se rechazan
            // si ya están autorizados, pero falta recibir/cancelar los productos
            if ($status == "C" || ($status == "E" && $accionmovimientos == "1")) {
                $query = "
                update
                    tmovimientosinventario
                set
                    autorizacion = 1
                where
                    idpedido = '" . $idpedido . "'
                    and autorizacion = 0
                ";

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Pedido Finalizado", "mensaje" => "Se ha finalizado el pedido correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al activar el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * ALFREDO PREGUNTA | EN EL SISTEMA ANTERIOR, AL CANCELAR UN PEDIDO, TODAS LAS SOLICITUDES DE COMPRA RELACIONADAS QUE TUVIERAN STATUS = 0 (TENGO QUE VER QUÉ SIGNIFICA EL CERO EN ESTOS CASOS) ERAN ELIMINADAS. NO SÉ SI QUIERES QUE ESTE SIGA SIENDO EL CASO O EN LUGAR DE ESO, ACTUALIZAR SU STATUS PARA CONSIDERARLAS COMO "CANCELADAS", PERO QUE PUEDAN SEGUIR VIENDOSE EN LA BASE DE DATOS
     * Eliminar Pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function cancelarPedido($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            update
                tpedidos
            set
                status = 'C'
            where
                idpedido = '" . $idpedido . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 48;
                $post["comentarios"] = "Cancelo el pedido #" . $idpedido;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Pedido Eliminado", "mensaje" => "Se ha eliminado el pedido correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Devuelve un listado de especificaciones de un pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificacionesPedidoBK($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $pendiente = mysqli_real_escape_string($this->con, $post["pendiente"]);
        $status = mysqli_real_escape_string($this->con, $post["status"]);
        $sinproduccionterminada = mysqli_real_escape_string($this->con, $post["sinproduccionterminada"]);


        $where = "";
        $wherestatus = "";
        $where .= (($pendiente == 1) ? " and pendiente = 1" : "");
        $where .= (($sinproduccionterminada == 1) ? " and statusproduccion < 2" : "");
        // NOTA: ¿por qué teniamos fija la condición de que el status no fuera 2?
        $wherestatus .= (($status == "") ? " and status != 2" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vespecificaciones
            where
                idpedido in
                (
                    select
                        idpedido
                    from
                        tpedidos
                    where
                        idpedido = '" . $idpedido . "' 
                        " . $where . "
                )
            ";
            $this->claseQueries->guardarQuery($query);
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    e.*,
                    a.desgloses,
                    b.partidas
                from
                    vespecificaciones e
                left join
                    (
                        select
                            idespecificacion,
                            group_concat(' ',cantidad,' ',producto,' | Talla: ',talla,', Color: ',color order by idcolor,color,posicion separator '\n') as desgloses
                        from
                            vespecificacionesproductos
                        where
                            idpedido = '" . $idpedido . "'
                        group by
                            idespecificacion
                    ) a
                on
                    e.idespecificacion = a.idespecificacion
                left join
                    (
                        select
                            a.idpedido,
                            b.idespecificacion,
                            group_concat(' ', a.cantidad,' ',a.producto separator '\n') as partidas
                        from
                            vrcotizacionproductos a
                        left join
                            vespecificacionesproductos b
                        on
                            a.idcotizacionproducto = b.idcotizacionproducto
                        where
                            a.idpedido = '" . $idpedido . "'
                            and a.idproducto = 0
                            and b.idpedidoproducto = 0
                        group by
                            a.idpedido,b.idespecificacion
                    ) b
                on
                    e.idpedido = b.idpedido
                    and e.idespecificacion = b.idespecificacion
                where
                    1=1
                    " . $wherestatus . "
                    and e.idpedido in
                    (
                        select
                            idpedido
                        from
                            tpedidos
                        where
                            idpedido = '" . $idpedido . "'
                            " . $where . "
                    )
                order by
                    e.fechaentrega, e.idespecificacion
                ";

                $especificaciones = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "especificaciones" => $especificaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron especificaciones para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Devuelve un listado de especificaciones de un pedido.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificacionesPedido($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $pendiente = mysqli_real_escape_string($this->con, $post["pendiente"]);
        $status = mysqli_real_escape_string($this->con, $post["status"]);
        $sinproduccionterminada = mysqli_real_escape_string($this->con, $post["sinproduccionterminada"]);


        $where = "";
        $wherestatus = "";
        $where .= (($pendiente == 1) ? " and pendiente = 1" : "");
        $where .= (($sinproduccionterminada == 1) ? " and statusproduccion < 2" : "");
        // NOTA: ¿por qué teniamos fija la condición de que el status no fuera 2?
        $wherestatus .= (($status == "") ? " and status != 2" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                vespecificaciones
            where
                idpedido in
                (
                    select
                        idpedido
                    from
                        tpedidos
                    where
                        idpedido = '" . $idpedido . "' 
                        " . $where . "
                )
            ";
            $this->claseQueries->guardarQuery($query);
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    e.*,
                    b.partidas
                from
                    vespecificaciones e
                left join
                    (
                        select
                            a.idpedido,
                            b.idespecificacion,
                            group_concat(' ', a.cantidad,' ',a.producto separator '\n') as partidas
                        from
                            vrcotizacionproductos a
                        left join
                            vespecificacionesproductos b
                        on
                            a.idcotizacionproducto = b.idcotizacionproducto
                        where
                            a.idpedido = '" . $idpedido . "'
                            and a.idproducto = 0
                            and b.idpedidoproducto = 0
                        group by
                            a.idpedido,b.idespecificacion
                    ) b
                on
                    e.idpedido = b.idpedido
                    and e.idespecificacion = b.idespecificacion
                where
                    1=1
                    " . $wherestatus . "
                    and e.idpedido in
                    (
                        select
                            idpedido
                        from
                            tpedidos
                        where
                            idpedido = '" . $idpedido . "'
                            " . $where . "
                    )
                order by
                    e.fechaentrega, e.idespecificacion
                ";

                $especificaciones = mysqli_query($this->con, $query);

                $arrayEspecificaciones = array();
                while($r = mysqli_fetch_assoc($especificaciones)){
                    $query = "
                    select
                        *,
                        sum(cantidad) as cantidad_real
                    from
                        vespecificacionesproductos
                    where
                        idespecificacion = ".$r["idespecificacion"]." and
                        idpedidoproducto > 0
                    group by
                        producto,
                        idcolor,
                        color,
                        talla,
                        idespecificacion
                    order by
                        producto,
                        idcolor,
                        color,
                        posicion";
                    // $query = "
                    // select
                    //     *,
                    //     sum(cantidad) as cantidad_real
                    // from
                    //     vespecificacionesproductos
                    // where
                    //     idespecificacion = ".$r["idespecificacion"]." and
                    //     idpedidoproducto > 0
                    // order by
                    //     producto,
                    //     idcolor,
                    //     color,
                    //     posicion";
                    $result = mysqli_query($this->con,$query);

                    $producto = "";
                    $color = "";
                    while($tmp = mysqli_fetch_assoc($result)){
                        if($producto!=$tmp["producto"]){
                            if($producto!=""){
                                $r["desgloses"] .= "\n";
                            }
                            $producto = $tmp["producto"];
                            $r["desgloses"] .= $producto."\n";
                        }
                        $r["desgloses"] .= "    ".$tmp["cantidad_real"]."-".$tmp["talla"].", ".$tmp["color"]."\n";
                        // if($color!=$tmp["color"]){
                        //     $color = $tmp["color"];
                        //     $r["desgloses"] .= "    ".$tmp["color"].": ".$tmp["cantidad"]."-".$tmp["talla"];
                        // }else{
                        //     $r["desgloses"] .= " / ".$tmp["cantidad"]."-".$tmp["talla"];
                        // }
                    }

                    $arrayEspecificaciones[] = $r;
                }

                $respuesta = array("respuesta" => "OK", "especificaciones" => $arrayEspecificaciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron especificaciones para este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Devuelve un listado de especificaciones de un pedido sin validar si esta pendiente o su estatus.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerEspecificacionesPedidoSinValidacion($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idespecificacion = mysqli_real_escape_string($this->con, $post["idespecificacion"]);
        $where = "";

        try {
            $query = "SELECT * from vespecificacionesproductos v WHERE idpedido =".$idpedido." AND idespecificacion =".$idespecificacion."";
            $especificaciones = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "especificaciones" => $especificaciones);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Datos Pedido. Borra los datos (de las tablas permanentes, no temporales) de desglose, especificacion y partida del pedido.
     * 
     * @access private
     * @param array $post
     * @return array
     */
    private function eliminarDatosPedido($idpedido)
    {
        $idpedido = mysqli_real_escape_string($this->con, $idpedido);

        $correcto = true;

        try {

            if ($idpedido != 0 && $idpedido != "") {
                $query = "
                delete from
                    trcotizacionproductos
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    trpedidoproductos
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    tsolicitudescompra
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    tespecificaciones
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    trespecificacionesproductos
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    tmovimientoinventarioproductos
                where
                    idmovimientoinventario in
                    (
                        select
                            idmovimientoinventario
                        from
                            tmovimientosinventario
                        where
                            idpedido = '" . $idpedido . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $query = "
                delete from
                    tmovimientosinventario
                where
                    idpedido = '" . $idpedido . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            } else {
                $correcto = false;
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Datos Pedido. Reinserta toda la informacion del pedido (partidas, desgloses y especificaciones)
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarDatosPedidoTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        $correcto = true;

        $this->claseCotizaciones = new Cotizaciones();

        try {

            // dos arreglos (uno temporal y otro permanente) de los desgloses
            $idpedidoproductostmp = array();
            $idpedidoproductos = array();
            // dos arreglos (uno temporal y otro permanente) de las partidas
            $idcotizacionproductostmp = array();
            $idcotizacionproductos = array();
            // pasar la informacion de las partidas de la tabla permanente a la temporal (trcotizacionproductos, trpedidoproductos, tespecificaciones, trespecificacionesproductos)
            $partidas = $this->obtenerPartidas($idpedido);
            foreach ($partidas["partidas"] as $partida) {

                $post["idproducto"] = $partida["idproducto"];
                $post["producto"] = $partida["producto"];
                $post["idcategoriaproducto"] = $partida["idcategoriaproducto"];
                $post["cantidad"] = $partida["cantidad"];
                $post["precio"] = $partida["precio"];

                $post["txtSerigrafia1"] = $partida["serigrafia1"];
                $post["txtSerigrafia2"] = $partida["serigrafia2"];
                $post["txtSerigrafia3"] = $partida["serigrafia3"];
                $post["chkPersonalizadoNumero"] = $partida["personalizadonumero"];
                $post["chkPersonalizadoNombre"] = $partida["personalizadonombre"];
                $post["chkBordado1"] = $partida["bordado1"];
                $post["chkBordado2"] = $partida["bordado2"];
                $post["chkBordado3"] = $partida["bordado3"];
                $post["chkBordado4"] = $partida["bordado4"];
                $post["chkBordadoEspecial"] = $partida["bordadoespecial"];
                $post["chkLinea1"] = $partida["personalizado1linea"];
                $post["chkLinea2"] = $partida["personalizado2lineas"];
                $post["chkLinea3"] = $partida["personalizado3lineas"];
                $post["chkSXL"] = $partida["sxl"];
                $post["chk2XL"] = $partida["2xl"];
                $post["chk3XL"] = $partida["3xl"];
                $post["txtObservaciones"] = $partida["observaciones"];

                $idcotizacionproductotmp = $this->claseCotizaciones->agregarPartidaTMP($post)["idcotizacionproductotmp"];

                array_push($idcotizacionproductos, $partida["idcotizacionproducto"]);
                array_push($idcotizacionproductostmp, $idcotizacionproductotmp);

                $cantidad = 0;
                $query = "
                select
                    *
                from
                    trpedidoproductos
                where
                    idcotizacionproducto = '" . $partida["idcotizacionproducto"] . "'
                ";

                $desgloses = mysqli_query($this->con, $query);
                foreach ($desgloses as $desglose) {
                    $cantidad += $desglose["cantidad"];
                    if ($cantidad > $partida["cantidad"]) {
                        break;
                    }
                    $query = "
                    insert into
                        trpedidoproductostmp
                        (
                            idusuario,
                            idcotizacionproducto,
                            idproducto,
                            producto,
                            idcategoriaproducto,
                            idtalla,
                            talla,
                            idcolor,
                            color,
                            cantidad,
                            precio
                        )
                    values
                        (
                            '" . $idusuario . "',
                            '" . $idcotizacionproductotmp . "',
                            '" . $desglose["idproducto"] . "',
                            '" . $desglose["producto"] . "',
                            '" . $desglose["idcategoriaproducto"] . "',
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

                    $idpedidoproductotmp = mysqli_insert_id($this->con);

                    array_push($idpedidoproductos, $desglose["idpedidoproducto"]);
                    array_push($idpedidoproductostmp, $idpedidoproductotmp);
                }
            }

            // se tienen que conseguir los id's permanentes de las especificaciones que fueron insertadas en este pedido
            $query = "
            select
                *
            from
                tespecificaciones
            where
                idpedido = '" . $idpedido . "'
            ";
            $especificaciones = mysqli_query($this->con, $query);

            foreach ($especificaciones as $especificacion) {

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
                        rproduccion,
                        imagen1,
                        imagen2,
                        imagen3,
                        imagen4,
                        imagen5
                    )
                values
                    (
                        '" . $idusuario . "',
                        '" . $especificacion["nombrediseno"] . "',
                        '" . $especificacion["fechaentrega"] . "',
                        '" . $especificacion["serigrafia"] . "',
                        '" . $especificacion["digital"] . "',
                        '" . $especificacion["bordado"] . "',
                        '" . $especificacion["especificaciones"] . "',
                        '" . $especificacion["rproduccion"] . "',
                        '" . $especificacion["imagen1"] . "',
                        '" . $especificacion["imagen2"] . "',
                        '" . $especificacion["imagen3"] . "',
                        '" . $especificacion["imagen4"] . "',
                        '" . $especificacion["imagen5"] . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }

                $idespecificaciontmp = mysqli_insert_id($this->con);

                // copiar las imagenes permanentes y pasarlas a la carpeta temporal, para que los cambios no se efectuen hasta que le des guardar
                $origen = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/";
                $destino = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/especificacionestmp/" . $idespecificaciontmp . "/";
                mkdir($destino, 0777, true);
                copy($origen . $especificacion["imagen1"], $destino . $especificacion["imagen1"]);
                copy($origen . $especificacion["imagen2"], $destino . $especificacion["imagen2"]);
                copy($origen . $especificacion["imagen3"], $destino . $especificacion["imagen3"]);
                copy($origen . $especificacion["imagen4"], $destino . $especificacion["imagen4"]);
                copy($origen . $especificacion["imagen5"], $destino . $especificacion["imagen5"]);


                // obtener las partidas
                $query = "
                select
                    *
                from
                    trcotizacionproductos
                where
                    idcotizacionproducto in
                    (
                        select
                            idcotizacionproducto
                        from
                            trespecificacionesproductos
                        where
                            idespecificacion = '" . $especificacion["idespecificacion"] . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                $partidas = mysqli_query($this->con, $query);

                foreach ($partidas as $partida) {
                    $indice2 = array_search($partida["idcotizacionproducto"], $idcotizacionproductos);
                    // obtener los idpedidoproductos asociados con el idespecificacion actual y pasarlos a la tabla temporal (trespecificacionesproductostmp)
                    $query = "
                    select
                        *
                    from
                        trpedidoproductos
                    where
                        idcotizacionproducto = '" . $partida["idcotizacionproducto"] . "'
                        and idpedidoproducto in
                        (
                            select
                                idpedidoproducto
                            from
                                trespecificacionesproductos
                            where
                                idespecificacion = '" . $especificacion["idespecificacion"] . "'
                        )
                    ";

                    $this->claseQueries->guardarQuery($query);

                    $desgloses = mysqli_query($this->con, $query);
                    if (mysqli_num_rows($desgloses) > 0) {
                        foreach ($desgloses as $desglose) {
                            $indice = array_search($desglose["idpedidoproducto"], $idpedidoproductos);
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
                                    '" . $idespecificaciontmp . "',
                                    '" . $idpedidoproductostmp[$indice] . "',
                                    '" . $idcotizacionproductostmp[$indice2] . "'
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
                                '" . $idespecificaciontmp . "',
                                '" . $idcotizacionproductostmp[$indice2] . "'
                            )
                        ";
                        $this->claseQueries->guardarQuery($query);

                        if (!mysqli_query($this->con, $query)) {
                            $correcto = false;
                        }
                    }
                }
            }

            if ($correcto) {

                $respuesta = array("respuesta" => "OK");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron cargar los datos del pedido.");
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
     * @param int $idpedido
     * @return array
     */
    public function obtenerPartidas($idpedido)
    {
        $idpedido = mysqli_real_escape_string($this->con, $idpedido);

        try {
            $query = "
            select
                count(*) as total
            from
                trcotizacionproductos
            where
                idpedido = '" . $idpedido . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    trcotizacionproductos
                where
                    idpedido = '" . $idpedido . "'
                ";

                $partidas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "partidas" => $partidas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron partidas en este pedido.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Reiniciar Precio Productos Temporales.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function reiniciarPrecioProductosTMP($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            update
                trcotizacionproductostmp
            set
                precio = 0
            where
                idusuario = '" . $idusuario . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "cargar", "formulario" => "formCotizacion");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar los productos.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Al Menos Un Movimiento Autorizado
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function alMenosUnMovimientoAutorizado($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                count(*) as total
            from
                tmovimientosinventario
            where
                autorizacion != 0
                and idpedido != 0
                and idpedido in
                (
                    select
                        idpedido
                    from
                        tpedidos
                    where
                        idpedido = '" . $idpedido . "'
                )
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = array("respuesta" => true);
            } else {
                $respuesta = array("respuesta" => false);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
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

        $this->claseCotizaciones = new Cotizaciones();

        try {

            $respuesta = $this->claseCotizaciones->validarDesgloses($post);
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

        $this->claseCotizaciones = new Cotizaciones();

        try {

            $respuesta = $this->claseCotizaciones->validarEspecificaciones($post);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Verificar Movimientos Pendientes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function verificarMovimientosPendientes($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);

        try {
            $query = "
            select
                count(*) as total
            from
                tmovimientosinventario
            where
                idpedido = '" . $idpedido . "'
                and autorizacion = 0
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $respuesta = array("value" => 1);
            } else {
                $respuesta = array("value" => 0);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    function editarSucursal($post)
    {
        $idpedido = mysqli_real_escape_string($this->con, $post["idpedido"]);
        $idsucursal = mysqli_real_escape_string($this->con, $post["slcSucursal"]);

        try {
            $query = "
            update 
                tpedidos
            set 
                idsucursal='" . $idsucursal . "'
            where 
                idpedido='" . $idpedido . "'";
            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajehref", "titulo" => "Pedido Editado", "mensaje" => "Se ha editado la sulcursal correctamente.");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al actualizar la sucursal.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    public function facturarPedido($post){
        try{
            $idpedido = mysqli_real_escape_string($this->con,$post["idpedido"]);
            $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
            $idcliente = mysqli_real_escape_string($this->con,$post["idcliente"]);
            $idrazonsocial = mysqli_real_escape_string($this->con,$post["slcRazonSocial"]);
            $razonsocial = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
            $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
            $codigo_postal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
            $idregimenfiscal = mysqli_real_escape_string($this->con,$post["slcRegimenFiscal"]);
            $idusocfdi = mysqli_real_escape_string($this->con,$post["slcUsoCFDI"]);
            $idemisor = mysqli_real_escape_string($this->con,$post["slcEmisor"]);
            $comentarios = mysqli_real_escape_string($this->con,$post["txtComentarios"]);
            $idmetodopago = mysqli_real_escape_string($this->con,$post["slcMetodoPago"]);
            $idformapago = ($idmetodopago==1) ? 21 : mysqli_real_escape_string($this->con,$post["slcFormaPago"]);
            $correo = mysqli_real_escape_string($this->con,$post["txtCorreo"]);

            if($idrazonsocial=="" || $idrazonsocial==0){
                $query = "
                select
                    usocfdi
                from
                    sat_tcatusoscfdi
                where
                    idusocfdi = '".$idusocfdi."'";
                $usocfdi = mysqli_fetch_assoc(mysqli_query($this->con,$query))["usocfdi"];

                $query = "
                select
                    regimenfiscal
                from
                    sat_tcatregimenfiscal
                where
                    idregimenfiscal = '".$idregimenfiscal."'";
                $regimenfiscal = mysqli_fetch_assoc(mysqli_query($this->con,$query))["regimenfiscal"];
            }

            if($idrazonsocial==""){
                $query = "
                insert
                into
                    tclienterazonessociales
                (
                    idcliente,
                    razon_social,
                    rfc,
                    idusocfdi,
                    usocfdi,
                    idregimenfiscal,
                    regimenfiscal,
                    codigo_postal
                ) values (
                    '".$idcliente."',
                    '".$razonsocial."',
                    '".$rfc."',
                    '".$idusocfdi."',
                    '".$usocfdi."',
                    '".$idregimenfiscal."',
                    '".$regimenfiscal."',
                    '".$codigo_postal."'
                )";
                mysqli_query($this->con,$query);
                $idrazonsocial = mysqli_insert_id($this->con);
            }

            if($idrazonsocial>0){
                $query = "
                select
                    *
                from
                    tclienterazonessociales
                where
                    idrazonsocial = '".$idrazonsocial."'";
                $tmp = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            }

            if($idcliente==0){
                $tmp["rfc"] = $rfc;
                $tmp["razon_social"] = $razonsocial;
                $tmp["usocfdi"] = $usocfdi;
                $tmp["regimenfiscal"] = $regimenfiscal;
                $tmp["codigo_postal"] = $codigo_postal;
            }

            $receptor = array(
                "Rfc" => $tmp["rfc"],
                "Nombre" => $tmp["razon_social"],
                "UsoCFDI" => $tmp["usocfdi"],
                "DomicilioFiscalReceptor" => $tmp["codigo_postal"],
                "RegimenFiscalReceptor" => $tmp["regimenfiscal"]
            );

            // Obtener serie y folio
            $query = "
            select
                a.rfc,
                a.razon_social,
                a.codigo_postal,
                b.regimenfiscal,
                a.serie,
                a.folio
            from
                temisores a
            left join
                sat_tcatregimenfiscal b
            on
                b.idregimenfiscal = a.idregimenfiscal
            where
                a.idemisor = '".$idemisor."'";
            $tmp = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $serie = $tmp["serie"];
            $folio = $tmp["folio"];

            $emisor = array(
                "Rfc" => $tmp["rfc"],
                "Nombre" => $tmp["razon_social"],
                "RegimenFiscal" => $tmp["regimenfiscal"],
                "LugarExpedicion" => $tmp["codigo_postal"]
            );

            // Obtener numero de certificado, certificado y archivo keypem
            $ruta = $_SERVER["DOCUMENT_ROOT"] . "/emisores/" . str_replace("&", "_", $tmp['rfc']);
            $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/"."certificado.cer");
            $certificado = $this->obtenerContenidoCertificado($ruta."/sat/"."certificado.cer");
            $archivo_keypem = file_get_contents($ruta."/sat/"."llave.key.pem");

            $pedido = $this->obtenerPedido(array("idpedido" => $idpedido))["pedido"];

            // Conceptos
            $query = "
            select
                cantidad,
                producto,
                precio,
                cve_unidad_medida,
                cve_producto_servicio
            from
                vrpedidoproductos
            where
                idpedido = '".$idpedido."'";
            $result = mysqli_query($this->con,$query);

            $subtotal = 0;
            $conceptos_factura = array();
            while($tmp = mysqli_fetch_assoc($result)){
                $cadena_utf8 = mb_convert_encoding($tmp["producto"], 'UTF-8', 'auto');

                $cadena_sin_acentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cadena_utf8);
                
                $valor_unitario = ($pedido["incluyeiva"]==1) ? ($tmp["precio"]/(1+($pedido["tasaiva"]/100))) : $tmp["precio"];

                $conceptos_factura[] = [
                    "Cantidad" => $tmp["cantidad"],
                    "Descripcion" => $cadena_sin_acentos,
                    "ValorUnitario" => sprintf("%.6f", $valor_unitario),
                    "Importe" =>  sprintf("%.6f", $valor_unitario*$tmp["cantidad"]),
                    "ClaveUnidad" => $tmp["cve_unidad_medida"],
                    "ClaveProdServ" => $tmp["cve_producto_servicio"],
                    "ObjetoImp" => '02'
                ];

                $subtotal += $valor_unitario*$tmp["cantidad"];
            }

            //Metodo y forma de pago
            $query = "
            select
                metodopago
            from
                sat_tcatmetodospago
            where
                idmetodopago = '".$idmetodopago."'";
            $metodopago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["metodopago"];

            $query = "
            select
                formapago
            from
                sat_tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $formapago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["formapago"];

            //Declaramos el logo en base64, en caso de que no exista uno para el emisor entonces tomamos el logo de la app
            $logo = $_SERVER["DOCUMENT_ROOT"]."/imagenes/tiendas/".$pedido["idtienda"]."_logo.png";
            $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/assets/images/logo-uniformes-trazo.png")));

            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "Version" => "4.0",
                "pruebas" => 1,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "colortxt" => "000000",
                "logo" => $logo,
                "tipoComprobante" => "I",
                "serie" => $serie,
                "folio" => $folio,
                "emisor" => $emisor,
                "receptor" => $receptor,
                "conceptos" => $conceptos_factura,
                "subtotal" => $subtotal,
                "iva_trasladado" => $pedido["tasaiva"],
                "metodopago" => $metodopago,
                "formapago" => $formapago,
                "moneda" => "MXN",
                "tipo_cambio" => "1",
                "carta_porte" => false,
                "comentarios" => $comentarios
            );

            $url = "https://api.xptk.app/timbrador/index.php";

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($datos),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: CH60NP5HQZYUPZEQ'
                ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);

            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/facturarPedido.txt",print_r($datos,true)."\n\n".print_r($response,true));

            if ($response["response"] == true) {
                $query = "
                insert
                into
                    tfacturas
                (
                    idusuario,
                    idcliente,
                    idrazonsocial,
                    idemisor,
                    idmetodopago,
                    idformapago,
                    serie,
                    folio,
                    subtotal,
                    iva,
                    total,
                    uuid,
                    timbrado
                ) values (
                    '".$idusuario."',
                    '".$idcliente."',
                    '".$idrazonsocial."',
                    '".$idemisor."',
                    '".$idmetodopago."',
                    '".$idformapago."',
                    '".$serie."',
                    '".$folio."',
                    '".$response["subtotal"]."',
                    '".($response["total"]-$response["subtotal"])."',
                    '".$response["total"]."',
                    '".$response["uuid"]."',
                    '".$response["fechaTimbrado"]."'
                )";
                mysqli_query($this->con,$query);

                $idfactura = mysqli_insert_id($this->con);

                file_put_contents($ruta."/facturas/".$response["uuid"].".xml",base64_decode($response["xml"]));
                file_put_contents($ruta."/facturas/".$response["uuid"].".pdf",base64_decode($response["pdf"]));

                $query = "
                update
                    temisores
                set
                    folio = folio + 1
                where
                    idemisor = '".$idemisor."'";
                mysqli_query($this->con,$query);

                $query = "
                update
                    tpedidos
                set
                    idfactura = '".$idfactura."'
                where
                    idpedido = '".$idpedido."'";
                mysqli_query($this->con,$query);

                //Se envia la factura por correo
                $folio = $serie."-".$folio;
                $fecha = date("Y-m-d");
                $total = $response["total"];

                include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/envioFactura.php");
                include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/base.php");

                $this->claseCorreos = new Correos();

                $respuesta = $this->claseCorreos->enviarCorreo(array(
                    "idtienda" => $pedido["idtienda"],
                    "asunto" => "Envío de factura",
                    "mensaje" => $cuerpo,
                    "correos" => array(
                        $correo
                    ),
                    "adjuntos" => array(
                        array(
                            "nombre" => $response["uuid"].".xml",
                            "archivo" => $response["xml"]
                        ),
                        array(
                            "nombre" => $response["uuid"].".pdf",
                            "archivo" => $response["pdf"]
                        )
                    )
                ));

                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo" => "mensajecargar",
                    "titulo" => "Factura generada",
                    "mensaje" => "Se ha generado la factura correctamente".(($respuesta["result"]=="success") ? " y se ha enviado por correo" : " pero no se pudo enviar por correo (".$respuesta["mensaje"].")"),
                    "formulario" => "formBusqueda"
                );
            }else{
                throw new Exception($response["mensaje"]);
            }

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => "Código 111 ".$e->getMessage()
            );
        }catch(Throwable $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => "Código 111 ".$e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * getNumCer
     * Obtener el numero de certificado de un archivo .cer
     * @param  string Path del archivo .cer
     * @return string Numero de certificado
     */
    public function obtenerNumeroCertificado($certificado)
    {
        $numero = FALSE;
        //si funciona retorna un array como: Array ( [0] => "serial=323030303130303030303032303030303032393"
        //local
        //exec(".\openssl\openssl.exe x509 -inform DER -in $certificado -serial 2>&1", $datacer);
        //web
        exec("openssl x509 -inform DER -in $certificado -serial", $datacer);
        //Reemplazamos el texto que no nos interesa(str_replace) y convertimos el string a array(str_split)
        $serialnumbers = str_split(str_replace("serial=", "", $datacer[0]));
        //Para despues obtener los numeros en posiciones impares
        for ($i = 0; $i < count($serialnumbers); $i++) {
            if ($i % 2 != 0) {
                $numero .= $serialnumbers[$i];
            }
        }
        return $numero;
    }

    /**
     * getCer
     * Obtener el contenido del certificado
     * @param  string $pathcer Path de certificado
     * @return cadena          Retorna el contenido del certificado
     */
    public function obtenerContenidoCertificado($certificado)
    {
        //locla
        //exec(".\openssl\openssl.exe x509 -inform DER -in $certificado",$cer); //Local
        //web
        exec("openssl x509 -inform DER -in $certificado", $cer);  //VPS
        array_pop($cer);                                                    //elimino el ultimo elemento
        array_shift($cer);                                                  //y el primero
        $contenido = implode($cer);                                         //despues convierto a string
        return $contenido;
    }
}
?>