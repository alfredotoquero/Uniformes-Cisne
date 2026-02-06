<?php
class Clientes
{
    private $con;
    private $claseQueries;
    private $claseUsuarios;
    private $claseNotificaciones;
    private $claseLogs;

    function __construct()
    {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Notificaciones.php");
        include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Clientes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerClientes($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);
        $conrecordatorio = mysqli_real_escape_string($this->con, $post["slcConRecordatorio"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        // idusuarioasignado
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $evitar_paginacion = mysqli_real_escape_string($this->con, $post["evitar_paginacion"]);

        $pagina = (isset($post["pagina"])) ? $post["pagina"] : 1;
        $regpagina = 100;

        $where = "";
        $where .= (($busqueda != "") ? " and (nombre like '%" . $busqueda . "%' or correo like '%" . $busqueda . "%' or telefono like '%" . $busqueda . "%' or idcliente in (select idcliente from tcontactos where nombre like '%" . $busqueda . "%'))" : "");
        $where .= (($nombre != "") ? " and nombre like '%" . $nombre . "%'" : "");
        $where .= (($idciudad != "" && $idciudad != 0) ? " and idciudad = '" . $idciudad . "'" : "");
        $where .= ((!empty($idtienda)) ? " and idtienda = '" . $idtienda . "'" : "");

        switch ($conrecordatorio) {
            case 1:
                $where .= " and idcliente in (select idcliente from ttareas where status = 'A')";
                break;
            case 2:
                $where .= " and idcliente in (select idcliente from vcotizaciones where status not in ('M','C'))";
                break;
            case 3:
                $where .= " and idcliente in (select idcliente from tcotizaciones".(($fecha!="") ? " where date(fecha) >= '".$fecha."'" : "").")";
                break;
            case 4:
                $where .= " and idcliente not in (select idcliente from tcotizaciones".(($fecha!="") ? " where date(fecha) >= '".$fecha."'" : "").")";
                break;
            case 5:
                $where .= " and idcliente in (select idcliente from tpedidos".(($fecha!="") ? " where date(fecha) >= '".$fecha."'" : "").")";
                break;
            case 6:
                $where .= " and idcliente not in (select idcliente from tpedidos".(($fecha!="") ? " where date(fecha) >= '".$fecha."'" : "").")";
                break;
            case 7:
                $where .= " and idcliente in (select idcliente from tseguimiento".(($fecha!="") ? " where date(created_at) >= '".$fecha."'" : "").")";
                break;
            case 8:
                $where .= " and idcliente not in (select idcliente from tseguimiento".(($fecha!="") ? " where date(created_at) >= '".$fecha."'" : "").")";
                break;
        }

        try {
            $query = "
            select
                count(*) as total
            from
                tclientes
            where
                status = 'A'
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            $numpaginas = ceil($total / $regpagina);
            $maxpaginas = 3;

            if ($total) {
                if (isset($post["pagina"]) && (empty($evitar_paginacion) || $evitar_paginacion==0)) {
                    $query = "
                    select
                        *
                    from
                        tclientes
                    where
                        status = 'A'
                        " . $where . "
                    order by
                        nombre
                    limit
                        " . (($pagina - 1) * $regpagina) . "," . $regpagina . "
                    ";
                } else {
                    $query = "
                    select
                        *
                    from
                        tclientes
                    where
                        status = 'A'
                        " . $where . "
                    order by
                        nombre
                    ";
                }

                $clientes = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "clientes" => $clientes, "pagina" => $pagina, "numpaginas" => $numpaginas, "maxpaginas" => $maxpaginas);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron clientes registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerCliente($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        try {
            $query = "
            select
                *
            from
                tclientes
            where
                idcliente = '" . $idcliente . "'
            ";

            $this->claseQueries->guardarQuery($query);

            $cliente = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($cliente["idcliente"] > 0) {
                $respuesta = array("respuesta" => "OK", "cliente" => $cliente);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Pedidos Cliente. Recupera la colección de idpedidos asociados al cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerPedidosCliente($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $cliente = mysqli_real_escape_string($this->con, $post["cliente"]);
        $conidcliente = mysqli_real_escape_string($this->con, $post["conidcliente"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        $where = "";
        $where .= (($conidcliente == 1) ? " and idcliente = '" . $idcliente . "'" : " and cliente = '" . $cliente . "'");
        $where .= (($tipo == 1) ? " and statusproduccion != 'T'" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                vpedidos
            where
                1=1
                " . $where . "
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {

                $query = "
                select
                    idpedido
                from
                    vpedidos
                where
                    1=1
                    " . $where . "
                ";

                $pedidos = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "pedidos" => $pedidos);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron pedidos para este cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarCliente($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $correo = mysqli_real_escape_string($this->con, $post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con, $post["txtTelefono"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);

        try {
            $query = "
            insert into
                tclientes 
                (
                    nombre,
                    telefono,
                    correo,
                    idciudad
                    ".((!empty($idtienda)) ? ",idtienda" : "")."
                )
                values 
                (
                    '" . $nombre . "',
                    '" . $telefono . "',
                    '" . $correo . "',
                    '" . $idciudad . "'
                    ".((!empty($idtienda)) ? ",'" . $idtienda . "'" : "")."
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idcliente = mysqli_insert_id($this->con);

                $post["idactividad"] = 2;
                $post["comentarios"] = "Agrego el cliente " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargarabrirfancy", "titulo" => "Cliente Agregrado", "mensaje" => "Se ha agregado el cliente correctamente.", "formulario" => "formBusqueda", "idcliente" => $idcliente, "rutafancy" => "/modulos/clientes/detalle.php?idcliente=" . $idcliente);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarClienteNEW($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $correo = mysqli_real_escape_string($this->con, $post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con, $post["txtTelefono"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);
        $permitir_coincidencia = mysqli_real_escape_string($this->con, $post["permitir_coincidencia"]);

        try {
            // Se buscan coincidencias, en caso de que existan no se insertará el registro y en su lugar se propondrá al usuario que elija una de ellas
            $coincidencias = $this->buscarCoincidenciasClientes(array(
                "nombre" => $nombre,
                "correo" => $correo,
                "telefono" => $telefono
            ));

            if($coincidencias["respuesta"]=="ERROR" || $permitir_coincidencia==1){
                $query = "
                insert into
                    tclientes 
                    (
                        nombre,
                        telefono,
                        correo,
                        idciudad
                        ".((!empty($idtienda)) ? ",idtienda" : "")."
                    )
                    values 
                    (
                        '" . $nombre . "',
                        '" . $telefono . "',
                        '" . $correo . "',
                        '" . $idciudad . "'
                        ".((!empty($idtienda)) ? ",'" . $idtienda . "'" : "")."
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con, $query)) {
                    $idcliente = mysqli_insert_id($this->con);

                    $post["idactividad"] = 2;
                    $post["comentarios"] = "Agrego el cliente " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargarabrirfancy", "titulo" => "Cliente Agregrado", "mensaje" => "Se ha agregado el cliente correctamente.", "formulario" => "formBusqueda", "idcliente" => $idcliente, "rutafancy" => "/modulos/clientes/detalle.php?idcliente=" . $idcliente);
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el cliente.");
                }
            }else{
                $respuesta = array("respuesta" => "OK", "tipo" => "abrir_nuevo_fancy", "url" => "/modulos/clientes/coincidencias.php?nombre=".urlencode($nombre)."&correo=".$correo."&telefono=".$telefono."&idciudad=".$idciudad."&idtienda=".$idtienda);
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarCliente($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        // $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
        // $razonsocial = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
        $correo = mysqli_real_escape_string($this->con, $post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con, $post["txtTelefono"]);
        $idciudad = mysqli_real_escape_string($this->con, $post["slcCiudad"]);
        $idtienda = mysqli_real_escape_string($this->con, $post["slcTienda"]);

        $nombrec = $this->obtenerCliente($post)["cliente"]["nombre"];

        try {
            $query = "
            update
                tclientes
            set
                nombre = '" . $nombre . "',
                telefono = '" . $telefono . "',
                correo = '" . $correo . "',
                idciudad = '" . $idciudad . "',
                idtienda = ".((!empty($idtienda)) ? "'" . $idtienda . "'" : "NULL")."
            where
                idcliente = '" . $idcliente . "'
            ";
            // rfc = '".$rfc."',
            // razonsocial = '".$razonsocial."',

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 3;
                $post["comentarios"] = "Edito el cliente " . (($nombrec != $nombre) ? $nombrec . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Cliente Editado", "mensaje" => "Se ha editado el cliente correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Cliente.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarCliente($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        try {
            $query = "
            update
                tclientes
            set
                status = 0
            where
                idcliente = '" . $idcliente . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 4;
                $post["comentarios"] = "Elimino el cliente " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Cliente Eliminado", "mensaje" => "Se ha eliminado el cliente correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Razones Sociales.
     * 
     * @access public
     * @param int $idcliente
     * @return array
     */
    public function obtenerRazonesSociales($idcliente)
    {
        $idcliente = mysqli_real_escape_string($this->con, $idcliente);

        try {
            $query = "
            select
                *
            from
                tclienterazonessociales
            where
                idcliente = '" . $idcliente . "'
                and status = '1'
            ";

            $razones = mysqli_query($this->con, $query);

            if (mysqli_num_rows($razones)) {
                $respuesta = array("respuesta" => "OK", "razones" => $razones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron razones sociales para este cliente.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Razon Social.
     * 
     * @access public
     * @param int $idrazonsocial
     * @return array
     */
    public function obtenerRazonSocial($idrazonsocial)
    {
        $idrazonsocial = mysqli_real_escape_string($this->con, $idrazonsocial);

        try {
            $query = "
            select
                *
            from
                tclienterazonessociales
            where
                idrazonsocial = '" . $idrazonsocial . "'
            ";

            $razon = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($razon["idrazonsocial"] > 0) {
                $respuesta = array("respuesta" => "OK", "razon" => $razon);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró la razón social.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * obtener Seguimiento.
     * 
     * @access public
     * @param array $idcliente
     * @return array
     */
    public function obtenerSeguimiento($idcliente)
    {
        $idcliente = mysqli_real_escape_string($this->con, $idcliente);

        try {
            $query = "
            select
                count(*) as total
            from
                tseguimiento
            where
                idcliente = '" . $idcliente . "'
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    tseguimiento
                where
                    idcliente = '" . $idcliente . "'
                order by
                    idseguimiento desc
                ";

                $seguimiento = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "seguimiento" => $seguimiento);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró seguimiento.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tareas.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTareas($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $orden = mysqli_real_escape_string($this->con, $post["slcOrden"]);
        $status = mysqli_real_escape_string($this->con, $post["status"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);


        $orderby = "";
        $orderby .= (($orden == 1) ? " order by titulo" : (($orden == 2) ? " order by fecha" : (($orden == 3) ? " order by created_at desc" : " order by fecha")));

        $where = "";
        $where .= (($idcliente != "" && $idcliente != 0) ? " and idcliente = '" . $idcliente . "'" : "");
        $where .= (($status != "") ? " and status = '" . $status . "'" : "");
        $where .= (($idusuario != ""  && $idusuario != 0) ? " and idusuarioasignado = '" . $idusuario . "'" : "");

        try {
            $query = "
            select
                count(*) as total
            from
                ttareas
            where
                1=1
                " . $where . "
            ";

            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebasa.txt", $query);
            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    ttareas
                where
                    1=1
                    " . $where . "
                " . $orderby . "
                ";

                $tareas = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "tareas" => $tareas, "total" => $total);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron recordatorios.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Tarea.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerTarea($post)
    {
        $idseguimiento = mysqli_real_escape_string($this->con, $post["idseguimiento"]);
        $idtarea = mysqli_real_escape_string($this->con, $post["idtarea"]);

        $where = "";
        $where .= (($idseguimiento > 0) ? " and idseguimiento = '" . $idseguimiento . "'" : "");
        $where .= (($idtarea > 0) ? " and idtarea = '" . $idtarea . "'" : "");

        try {
            $query = "
            select
                *
            from
                ttareas
            where
                1=1
                " . $where . "
            ";

            $tarea = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($tarea["idtarea"] > 0) {

                $respuesta = array("respuesta" => "OK", "tarea" => $tarea);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontró el recordatorio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Razon Social.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarRazonSocial($post)
    {
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $razonsocial = mysqli_real_escape_string($this->con, $post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con, $post["txtRFC"]);
        $usocfdi = mysqli_real_escape_string($this->con, $post["slcUsoCFDI"]);
        $regimenfiscal = mysqli_real_escape_string($this->con, $post["slcRegimenFiscal"]);
        $codigopostal = mysqli_real_escape_string($this->con, $post["txtCodigoPostal"]);

        try {
            $query = "
            insert into
                tclienterazonessociales
                (
                    idcliente,
                    razon_social,
                    rfc,
                    codigo_postal,
                    usocfdi,
                    regimenfiscal
                )
            values
                (
                    '" . $idcliente . "',
                    '" . $razonsocial . "',
                    '" . $rfc . "',
                    '" . $codigopostal . "',
                    '" . $usocfdi . "',
                    '" . $regimenfiscal . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 73;
                $post["comentarios"] = "Agrego la razon social " . $razonsocial . " al cliente " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Razón Social Agregada", "mensaje" => "Se ha agregado la razón social exitosamente", "formulario" => "formRazones", "btn" => "btnAgregarR", "razon" => 1, "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar la razon social.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Razon Social.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarRazonSocial($post)
    {
        $idrazonsocial = mysqli_real_escape_string($this->con, $post["idrazonsocial"]);
        $razonsocial = mysqli_real_escape_string($this->con, $post["txtRazonSocial"]);
        $rfc = mysqli_real_escape_string($this->con, $post["txtRFC"]);
        $usocfdi = mysqli_real_escape_string($this->con, $post["slcUsoCFDI"]);
        $regimenfiscal = mysqli_real_escape_string($this->con, $post["slcRegimenFiscal"]);
        $codigopostal = mysqli_real_escape_string($this->con, $post["txtCodigoPostal"]);

        try {
            $query = "
            update
                tclienterazonessociales
            set
                razon_social = '" . $razonsocial . "',
                rfc = '" . $rfc . "',
                codigo_postal = '" . $codigopostal . "',
                usocfdi = '" . $usocfdi . "',
                regimenfiscal = '" . $regimenfiscal . "'
            where
                idrazonsocial = '" . $idrazonsocial . "'
                and status = 1
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 74;
                $post["comentarios"] = "Edito la razon social " . $razonsocial . " del cliente " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Razón Social Editada", "mensaje" => "Se ha editado la razón social exitosamente", "formulario" => "formRazones", "btn" => "btnAgregarR", "razon" => 1, "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al editar la razon social.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Razón Social.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarRazonSocial($post)
    {
        $idrazonsocial = mysqli_real_escape_string($this->con, $post["idrazonsocial"]);

        try {
            $query = "
            update
                tclienterazonessociales
            set
                status = 0
            where
                idrazonsocial = '" . $idrazonsocial . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $razonsocial = $this->obtenerRazonSocial($idrazonsocial)["razon"];
                $post["idcliente"] = $razonsocial["idcliente"];
                $razonsocial = $razonsocial["razon_social"];
                $nombre = $this->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 75;
                $post["comentarios"] = "Elimino la razon social " . $razonsocial . " del cliente " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Razón Social Eliminada", "mensaje" => "Se ha eliminado la razón social correctamente.", "formulario" => "formRazones");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar la razón social.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Seguimiento.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarSeguimiento($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $idseguimiento_padre = mysqli_real_escape_string($this->con, $post["idseguimiento_padre"]);
        $titulo = mysqli_real_escape_string($this->con, $post["txtTitulo"]);
        $comentarios = mysqli_real_escape_string($this->con, $post["txtComentarios"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        $idcontacto = mysqli_real_escape_string($this->con, $post["slcContacto"]);
        $idrazonsocial = mysqli_real_escape_string($this->con, $post["slcRazon"]);
        $idusuarioasignado = mysqli_real_escape_string($this->con, $post["slcUsuario"]);

        $idusuarioasignado = (($idusuarioasignado == 0 || $idusuarioasignado == "") ? $idusuario : $idusuarioasignado);

        $correcto = true;

        $fecha_c = date("Y-m-d H:i:s");

        $this->claseUsuarios = new Usuarios();
        $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"]["nombre"];

        try {
            $query = "
            insert into
                tseguimiento
                (
                    idusuario,
                    idcliente,
                    idseguimiento_padre,
                    idcontacto,
                    idrazonsocial,
                    titulo,
                    comentarios,
                    fechas_c,
                    usuarios_c
                )
            values
                (
                    '" . $idusuario . "',
                    '" . $idcliente . "',
                    '" . $idseguimiento_padre . "',
                    '" . $idcontacto . "',
                    '" . $idrazonsocial . "',
                    '" . $titulo . "',
                    '" . $comentarios . "',
                    '" . $fecha_c . "',
                    '" . $usuario . "'
                )
            ";
            // idusuarioasignado,
            // '".$idusuarioasignado."',

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $idseguimiento = mysqli_insert_id($this->con);

            if ($fecha != "") {
                $post["idseguimiento"] = $idseguimiento;
                if ($this->agregarTarea($post)["respuesta"] != "OK") {
                    $correcto = false;
                }
            }

            if ($correcto) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Seguimiento Agregado", "mensaje" => "Se ha agregado el seguimiento exitosamente.", "formulario" => "formSeguimiento", "btn" => "btnAgregarS", "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar el seguimiento.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Responder Seguimiento.
     * 
     * @access public
     * @return array
     */
    public function responderSeguimiento($post)
    {
        $idseguimiento = mysqli_real_escape_string($this->con, $post["idseguimiento"]);
        $comentarios = mysqli_real_escape_string($this->con, $post["txtComentarios"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idusuarioasignado = mysqli_real_escape_string($this->con, $post["slcUsuario"]);
        // $fecha = mysqli_real_escape_string($this->con,$post["txtFecha"]);

        $idusuarioasignado = (($idusuarioasignado == "" || $idusuarioasignado == 0) ? $idusuario : $idusuarioasignado);

        // $fecha = (($fecha=="") ? date("Y-m-d H:i:s") : $fecha);

        $fecha = date("Y-m-d H:i:s");

        try {
            $this->claseUsuarios = new Usuarios();
            $post["idusuario"] = $idusuarioasignado;
            $usuario = $this->claseUsuarios->obtenerUsuario($post)["usuario"]["nombre"];

            $query = "
            update
                tseguimiento
            set
                comentarios = CONCAT_WS('|', comentarios, '" . $comentarios . "'),
                fechas_c = CONCAT_WS('|', fechas_c, '" . $fecha . "'),
                usuarios_c = CONCAT_WS('|', usuarios_c, '" . $usuario . "')
            where
                idseguimiento = '" . $idseguimiento . "'
            ";

            if (mysqli_query($this->con, $query)) {
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Comentario Agregado", "mensaje" => "El comentario se ha añadido al seguimiento exitosamente.", "formulario" => "formSeguimiento", "btn" => "btnAgregarS", "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo añadir la respuesta.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Tarea.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarTarea($post)
    {
        $idseguimiento = mysqli_real_escape_string($this->con, $post["idseguimiento"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $titulo = mysqli_real_escape_string($this->con, $post["txtTitulo"]);
        $comentarios = mysqli_real_escape_string($this->con, $post["txtComentarios"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        $idusuarioasignado = mysqli_real_escape_string($this->con, $post["slcUsuario"]);

        $idusuarioasignado = (($idusuarioasignado == "" || $idusuarioasignado == 0) ? $idusuario : $idusuarioasignado);

        try {

            $query = "
            insert into
                ttareas
                (
                    idseguimiento,
                    idcliente,
                    idusuario,
                    idusuarioasignado,
                    titulo,
                    comentarios,
                    fecha
                )
            values
                (
                    '" . $idseguimiento . "',
                    '" . $idcliente . "',
                    '" . $idusuario . "',
                    '" . $idusuarioasignado . "',
                    '" . $titulo . "',
                    '" . $comentarios . "',
                    '" . $fecha . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                // $respuesta = array("respuesta"=>"OK");
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Recordatorio Agregado", "mensaje" => "Se ha agregado el recordatorio exitosamente.", "formulario" => "formTareas", "btn" => "btnAgregarT", "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar el recordatorio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Tarea.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarTarea($post)
    {
        $idtarea = mysqli_real_escape_string($this->con, $post["idtarea"]);
        $idseguimiento = mysqli_real_escape_string($this->con, $post["idseguimiento"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
        $titulo = mysqli_real_escape_string($this->con, $post["txtTitulo"]);
        $comentarios = mysqli_real_escape_string($this->con, $post["txtComentarios"]);
        $fecha = mysqli_real_escape_string($this->con, $post["txtFecha"]);
        $idusuarioasignado = mysqli_real_escape_string($this->con, $post["slcUsuario"]);

        $idusuarioasignado = (($idusuarioasignado == "" || $idusuarioasignado == 0) ? $idusuario : $idusuarioasignado);

        try {

            $query = "
            update
                ttareas
            set
                idseguimiento = '" . $idseguimiento . "',
                idcliente = '" . $idcliente . "',
                idusuario = '" . $idusuario . "',
                idusuarioasignado = '" . $idusuarioasignado . "',
                titulo = '" . $titulo . "',
                comentarios = '" . $comentarios . "',
                fecha = '" . $fecha . "'
            where
                idtarea = '" . $idtarea . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                // $respuesta = array("respuesta"=>"OK");
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar2", "titulo" => "Recordatorio Editado", "mensaje" => "Se ha editado el recordatorio exitosamente.", "formulario" => "formBusqueda", "reiniciarform" => 1);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar el recordatorio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Finalizar Tarea.
     * 
     * @access public
     * @param int $post
     * @return array
     */
    public function finalizarTarea($post)
    {
        $idtarea = mysqli_real_escape_string($this->con, $post["idtarea"]);
        $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);

        try {
            $query = "
            update
                ttareas
            set
                status = 'F'
            where
                idtarea = '" . $idtarea . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $formulario = "formBusqueda";
                if ($idcliente > 0) {
                    $formulario = "formBusqueda2";
                }
                $respuesta = array("respuesta" => "OK", "tipo" => "cargar", "formulario" => $formulario);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo finalizar el recordatorio.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Usos CFDI.
     * 
     * @access public
     * @return array
     */
    public function obtenerUsosCFDI()
    {

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
    public function obtenerRegimenesFiscales()
    {

        try {
            $query = "
            select
                *
            from
                sat_tcatregimenfiscal
            ";

            $regimenesfiscales = mysqli_query($this->con, $query);

            $respuesta = array("respuesta" => "OK", "regimenesfiscales" => $regimenesfiscales);
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Ciudades.
     * 
     * @access public
     * @return array
     */
    public function obtenerCiudades()
    {

        try {
            $query = "
            select
                count(*) as total
            from
                tciudades
            where
                status = 1
            ";

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    tciudades
                where
                    status = 1
                ";

                $ciudades = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "ciudades" => $ciudades);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se encontraron ciudades.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Notificaciones Recordatorios. Obtiene todas las notificaciones que se tendrían que registrar para los distintos usuarios que tengan tareas: pendientes, a punto de vencer y vencidas.
     * 
     * @access public
     * @return array
     */
    public function obtenerNotificacionesRecordatorios()
    {

        $fecha = date("Y-m-d");

        $this->claseNotificaciones = new Notificaciones();

        try {

            $query = "
            select
                idusuarioasignado as idusuario,
                sum(case
                    when date(fecha) > '" . $fecha . "' then 1 else 0 end
                ) as pendientes,
                sum(case
                    when date(fecha) = '" . $fecha . "' then 1 else 0 end
                ) as por_vencer,
                sum(case
                    when date(fecha) < '" . $fecha . "' then 1 else 0 end
                ) as vencidos
            from
                ttareas a
            left join
                tusuarios b
            on
                a.idusuarioasignado = b.idusuario
            where
                a.status = 'A'
                and b.status = 'A'
            group by
                idusuarioasignado
            having
                pendientes > 0
                or por_vencer > 0
                or vencidos > 0
            ";

            $usuarios = mysqli_query($this->con, $query);

            foreach ($usuarios as $usuario) {
                $post["tipousuario"] = "U";
                $post["idtiponotificacion"] = 11;
                $post["idorigen"] = 0;
                $post["idusuario"] = $usuario["idusuario"];
                if ($usuario["pendientes"]) {
                    $post["mensaje"] = "Tienes recordatorios pendientes";
                    $this->claseNotificaciones->agregarNotificaciones($post);
                }
                if ($usuario["por_vencer"]) {
                    $post["mensaje"] = "Tienes recordatorios por vencer";
                    $this->claseNotificaciones->agregarNotificaciones($post);
                }
                if ($usuario["vencidos"]) {
                    $post["mensaje"] = "Tienes recordatorios vencidos";
                    $this->claseNotificaciones->agregarNotificaciones($post);
                }
            }

            $respuesta = array("respuesta" => "OK");
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Buscar Coincidencias Clientes
     * Se utiliza para identificar coincidencias en los clientes antes de realizar una inserción de un nuevo registro.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function buscarCoincidenciasClientes($post){
        $nombre = mysqli_real_escape_string($this->con,$post["nombre"]);
        $correo = mysqli_real_escape_string($this->con,$post["correo"]);
        $telefono = mysqli_real_escape_string($this->con,$post["telefono"]);

        try{
            $query = "
            select
                a.idcliente,
                a.nombre,
                a.correo,
                a.telefono,
                b.nombre as ciudad,
                c.nombre as tienda,
                MATCH(a.nombre, a.correo) AGAINST ('+".str_replace(" "," +",$nombre)."' IN BOOLEAN MODE) AS score
            from
                tclientes a
            left join
                tciudades b
            on
                b.idciudad = a.idciudad
            left join
                ttiendas c
            on
                c.idtienda = a.idtienda
            where
                (
                    match(a.nombre, a.correo) against('+".str_replace(" "," +",$nombre)."' in boolean mode) and
                    a.status = 'A'
                    ".((!empty($correo)) ? "or a.correo like '%".$correo."%'" : "")."
                    ".((!empty($telefono)) ? "or a.telefono like '%".$telefono."%'" : "")."
                ) or
                a.idcliente in (
                    select
                        idcliente
                    from
                        tcontactos
                    where
                        match(nombre, correo) against('+".str_replace(" "," +",$nombre)."' in boolean mode) and
                        status = 'A'
                        ".((!empty($correo)) ? "or correo like '%".$correo."%'" : "")."
                        ".((!empty($telefono)) ? "or telefono like '%".$telefono."%'" : "")."
                )
            order by
                score desc";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)>0){
                $respuesta = array(
                    "respuesta" => "OK",
                    "coincidencias" => mysqli_fetch_all($result,MYSQLI_ASSOC)
                );
            }else{
                $respuesta = array(
                    "respuesta" => "ERROR",
                    "mensaje" => "No se encontraron coincidencias"
                );
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
