<?php
class Usuarios
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
     * Inicio de sesión.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function iniciarSesion($post)
    {
        $usuario = mysqli_real_escape_string($this->con, $post["txtUsuario"]);
        $password = mysqli_real_escape_string($this->con, $post["txtPassword"]);

        try {
            $query = "
            select
                *
            from
                tusuarios
            where
                usuario = '" . $usuario . "'
                and password = '" . $password . "'
                and status = 'A'
            order by
                nombre
            ";
            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($usuario["idusuario"] > 0) {
                if ($usuario["status"] == "A") {

                    $post["idactividad"] = 1;
                    $post["idusuario"] = $usuario["idusuario"];
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "usuario" => $usuario, "tipo" => "href");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Los datos ingresados son incorrectos.");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Los datos ingresados son incorrectos.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código 100: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Validar Password.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function validarPassword($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        // $password = mysqli_real_escape_string($this->con,$post["txtPassword"]);
        $password = mysqli_real_escape_string($this->con, $post["password"]);
        $tipo = mysqli_real_escape_string($this->con, $post["tipo"]);

        $where = "";
        $where .= (($tipo == "administrativa") ? " and permisoadministrador = 1 " : "");

        try {
            $query = "
            select
                *
            from
                tusuarios
            where
                idusuario = '" . $idusuario . "'
                and password = '" . $password . "'
                and status = 'A'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($usuario["idusuario"] > 0) {
                $respuesta = array("respuesta" => "OK");
                // $respuesta = true;
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Los datos ingresados son incorrectos.");
                // $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código 100: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Usuarios.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerUsuarios($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);

        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");

        try {

            $query = "
            select
                count(*) as total
            from
                tusuarios
            where
                status = 'A'
                " . $where . "
            ";

            // $this->claseQueries->guardarQuery($query);

            $total = mysqli_fetch_assoc(mysqli_query($this->con, $query))["total"];

            if ($total) {
                $query = "
                select
                    *
                from
                    tusuarios
                where
                    status = 'A'
                    " . $where . "
                ";

                // $this->claseQueries->guardarQuery($query);

                $usuarios = mysqli_query($this->con, $query);

                $respuesta = array("respuesta" => "OK", "usuarios" => $usuarios);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron usuarios registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Usuario.
     *
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerUsuario($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            select
                *
            from
                tusuarios
            where
                idusuario = '" . $idusuario . "'
            ";
            $usuario = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($usuario["idusuario"] > 0) {
                $query = "
                select 
                    * 
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
                            idusuario = '" . $usuario["idusuario"] . "' and 
                            sololectura=0
                    )";
                $usuario["permisos_produccion"] = (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) ? true : false;

                $query = "
                select 
                    * 
                from 
                    tusuarios 
                where 
                    idusuario='" . $usuario["idusuario"] . "' and 
                    (
                        tipousuario like '%S%' or 
                        tipousuario like '%B%'
                    )";
                $usuario["permiso_produccion"] = (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) ? true : false;

                $query = "
                select 
                    * 
                from 
                    tusuarios 
                where 
                    idusuario='" . $usuario["idusuario"] . "' and 
                    tipousuario like '%D%'";
                $usuario["permiso_diseno"] = (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) ? true : false;

                $query = "
                select 
                    * 
                from 
                    tusuarios 
                where 
                    idusuario='" . $usuario["idusuario"] . "' and 
                    tipousuario like '%A%'";
                $usuario["permiso_almacen"] = (mysqli_num_rows(mysqli_query($this->con, $query)) > 0) ? true : false;

                $respuesta = array("respuesta" => "OK", "usuario" => $usuario);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del usuario.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Agregar Usuario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarUsuario($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $usuario = mysqli_real_escape_string($this->con, $post["txtUsuario"]);
        $contrasena = mysqli_real_escape_string($this->con, $post["txtPassword"]);
        $permisoadmin = mysqli_real_escape_string($this->con, $post["chkPermisoAdministrador"]);
        $formato = mysqli_real_escape_string($this->con, $post["slcFormato"]);

        try {
            $query = "
            insert into
                tusuarios
                (
                    nombre,
                    usuario,
                    password,
                    permisoadministrador,
                    formato
                )
            values
                (
                    '" . $nombre . "',
                    '" . $usuario . "',
                    '" . $contrasena . "',
                    '" . $permisoadmin . "',
                    '" . $formato . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 8;
                $post["idusuario"] = $post["miidusuario"];
                $post["comentarios"] = "Agrego al usuario " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Usuario Agregado", "mensaje" => "El usuario se ha agregado exitosamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el usuario");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Editar Usuario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarUsuario($post)
    {
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $usuario = mysqli_real_escape_string($this->con, $post["txtUsuario"]);
        $contrasena = mysqli_real_escape_string($this->con, $post["txtPassword"]);
        $permisoadmin = mysqli_real_escape_string($this->con, $post["chkPermisoAdministrador"]);
        $formato = mysqli_real_escape_string($this->con, $post["slcFormato"]);
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        $nombreu = $this->obtenerUsuario($post)["usuario"]["nombre"];

        try {
            $query = "
            update
                tusuarios
            set
                nombre = '" . $nombre . "',
                usuario = '" . $usuario . "',
                password = '" . $contrasena . "',
                permisoadministrador = '" . $permisoadmin . "',
                formato = '" . $formato . "'
            where
                idusuario = '" . $idusuario . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $post["idactividad"] = 9;
                $post["idusuario"] = $post["miidusuario"];
                $post["comentarios"] = "Edito al usuario " . (($nombreu != $nombre) ? $nombreu . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Usuario Editado", "mensaje" => "El usuario se ha editado exitosamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el usuario");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Deshabilitar Usuario.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function deshabilitarUsuario($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);

        try {
            $query = "
            update
                tusuarios
            set
                status = 'I'
            where
                idusuario = '" . $idusuario . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerUsuario($post)["usuario"]["nombre"];
                $post["idactividad"] = 10;
                $post["idusuario"] = $post["miidusuario"];
                $post["comentarios"] = "Elimino al usuario " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Usuario Eliminado", "mensaje" => "El usuario se ha eliminado exitosamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el usuario");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Asignar Almacenes. Asigna almacenes a un usuario
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarAlmacenes($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $almacenes = $post["chkAlmacenes"];
        // $almacenes = mysqli_real_escape_string($this->con,$post["chkAlmacenes"]);

        try {

            $stralmacenes = "";
            foreach ($almacenes as $idalmacen) {
                $idalmacen = mysqli_real_escape_string($this->con, $idalmacen);
                $stralmacenes .= $idalmacen . ",";
            }
            $stralmacenes = substr($stralmacenes, 0, strlen($stralmacenes) - 1);

            $query = "
            update
                tusuarios
            set
                almacenes = '" . $stralmacenes . "'
            where
                idusuario = '" . $idusuario . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {

                $nombre = $this->obtenerUsuario($post)["usuario"]["nombre"];
                $post["idactividad"] = 81;
                $post["idusuario"] = $post["miidusuario"];
                $post["comentarios"] = "Asigno los almacenes del usuario " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Almacenes Asignados", "mensaje" => "Los almacenes se han asignado correctamente", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los almacenes");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Obtener Secciones. Obtiene todas las secciones del sistema
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerSecciones($post)
    {

        try {

            $query = "
            select
                *
            from
                tsecciones
            ";

            $secciones = mysqli_query($this->con, $query);

            $arraySecciones = array();
            while ($seccion = mysqli_fetch_assoc($secciones)) {
                $arraySecciones[] = $seccion;
            }
            
            if (count($arraySecciones) > 0) {
                $respuesta = array("respuesta" => "OK", "secciones" => $arraySecciones);
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron recuperar las secciones");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Tiene Permiso. Revisa si el usuario tiene permiso para esta seccion
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function tienePermiso($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idseccion = mysqli_real_escape_string($this->con, $post["idseccion"]);
        $sololectura = mysqli_real_escape_string($this->con, $post["sololectura"]);



        $where = "";
        $where .= (($sololectura != "" && $sololectura == 1) ? " and sololectura = '" . $sololectura . "'" : "");

        try {

            $query = "
            select
                *
            from
                trusuariosecciones
            where
                idusuario = '" . $idusuario . "'
                and idseccion = '" . $idseccion . "'
                " . $where . "
            ";

            $seccion = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($seccion["idseccion"]) {
                $respuesta = array("respuesta" => "OK", "solo_lectura" => $seccion["sololectura"]);
            } else {
                $respuesta = array("respuesta" => "ERROR");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Asignar Permisos. Asigna los permisos de acceso a un usuario
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function asignarPermisos($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        // $permisos = mysqli_real_escape_string($this->con,$post["chkPermisos"]);
        // $lecturas = mysqli_real_escape_string($this->con,$post["chkLecturas"]);
        $permisos = $post["chkPermisos"];
        $lecturas = $post["chkLecturas"];

        $correcto = true;

        try {

            $query = "
            update
                tusuarios
            set
                tipousuario = '" . $_POST["chkAlmacen"] . $_POST["chkDiseno"] . $_POST["chkSerigrafia"] . $_POST["chkBordado"] . "'
            where
                idusuario = '" . $idusuario . "'
            ";

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $query = "
            delete from
                trusuariosecciones
            where
                idusuario = '" . $idusuario . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (!mysqli_query($this->con, $query)) {
                $correcto = false;
            }

            $correcto = true;
            foreach ($permisos as $id => $permiso) {
                $query = "
                insert into
                    trusuariosecciones
                    (
                        idusuario,
                        idseccion,
                        sololectura
                    )
                values
                    (
                        '" . $idusuario . "',
                        '" . $permiso . "',
                        '" . $lecturas[$id] . "'
                    )
                ";

                $this->claseQueries->guardarQuery($query);

                if (!mysqli_query($this->con, $query)) {
                    $correcto = false;
                }
            }

            // $query = "
            // update
            //     tusuarios
            // set
            //     tipousuario = '".$_POST["chkAlmacen"].$_POST["chkDiseno"].$_POST["chkSerigrafia"].$_POST["chkBordado"]."'
            // where
            //     idusuario = '".$idusuario."'
            // ";

            if ($correcto) {

                $nombre = $this->obtenerUsuario($post)["usuario"]["nombre"];
                $post["idactividad"] = 82;
                $post["idusuario"] = $post["miidusuario"];
                $post["comentarios"] = "Cambio los permisos del usuario " . $nombre;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Permisos Asignados", "mensaje" => "Los permisos se han asignado correctamente al usuario", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudieron asignar los permisos");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /** 
     * Solo Lectura. Recupera el valor de "solo lectura" del usuario
     * 
     * @access public
     * @param array $post
     * @return boolean
     */
    public function soloLectura($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $url = mysqli_real_escape_string($this->con, $post["url"]);

        try {
            $query = "
            select
                *
            from
                trusuariosecciones
            where
                idusuario = '" . $idusuario . "'
                and idseccion in
                (
                    select
                        idseccion
                    from
                        tsecciones
                    where
                        url = '" . $url . "'
                )
                and sololectura = '1'
            ";

            $sololectura = mysqli_query($this->con, $query);

            if (mysqli_num_rows($sololectura)) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Es Administrador. Revisa si tiene permiso administrador
     * 
     * @access public
     * @param array $idusuario
     * @return boolean
     */
    public function esAdministrador($idusuario)
    {
        $idusuario = mysqli_real_escape_string($this->con, $idusuario);

        try {
            $query = "
            select
                *
            from
                tusuarios
            where
                idusuario = '" . $idusuario . "'
                and permisoadministrador = 1
            ";

            if (mysqli_num_rows(mysqli_query($this->con, $query))) {
                $respuesta = true;
            } else {
                $respuesta = false;
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Codigo ###:" . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }
}
