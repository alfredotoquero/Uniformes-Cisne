<?php
class Almacenes
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
     * Obtener Almacenes.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerAlmacenes($post)
    {
        // filtros
        $busqueda = mysqli_real_escape_string($this->con, $post["txtBusqueda"]);
        $tipoalmacen = mysqli_real_escape_string($this->con, $post["tipoalmacen"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        $where = "";
        $where .= (($busqueda != "") ? " and nombre like '%" . $busqueda . "%'" : "");

        $where .= (($tipoalmacen == '1') ? " and idalmacen not in (select idalmacen from tsucursales where status = 'A')" : "");
        // tipoalmacen = 2 es para obtener todos excepto un almacen
        $where .= (($tipoalmacen == '2') ? " and idalmacen != '" . $idalmacen . "'" : "");

        try {
            $query = "
            select
                *
            from
                talmacenes
            where
                status = 1
                " . $where . "
            ";

            
            // $this->claseQueries->guardarQuery($query);

            $almacenes = mysqli_query($this->con, $query);

            if (mysqli_num_rows($almacenes) > 0) {
                $respuesta = array("respuesta" => "OK", "almacenes" => $almacenes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron almacenes registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Almacenes de Reorden.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerAlmacenesReorden()
    {
        // filtros
        try {
            $query = "
            select
                *
            from
                talmacenes
            where
                status = 1 and
                reorden = 1
            ";

            $almacenes = mysqli_query($this->con, $query);

            if (mysqli_num_rows($almacenes) > 0) {
                $respuesta = array("respuesta" => "OK", "almacenes" => $almacenes);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se encontraron almacenes de reorden registrados.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Obtener Almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerAlmacen($post)
    {
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        try {
            $query = "
            select
                *
            from
                talmacenes
            where
                idalmacen = '" . $idalmacen . "'
            ";
            $almacen = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            if ($almacen["idalmacen"] > 0) {
                $respuesta = array("respuesta" => "OK", "almacen" => $almacen);
            } else {
                $respuesta = array("respuesta" => "ERROR", "mensaje" => "No se pudo recuperar la información del almacen.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Agregar Almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarAlmacen($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $reorden = mysqli_real_escape_string($this->con, $post["slcReorden"]);

        try {
            $query = "
            insert into
                talmacenes
                (
                    nombre,
                    reorden
                )
            values
                (
                    '" . $nombre . "',
                    '" . $reorden . "'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $idalmacen = mysqli_insert_id($this->con);

                // despues de agregar un almacen, se debe agregar a la tabla tproductoexistencias (idproducto,idcolor,idtalla,idalmacen)
                $query = "
                insert into
                    tproductoexistencias
                    (
                        idproducto,
                        idtalla,
                        idcolor,
                        idalmacen
                    )
                select
                    idproducto,
                    idtalla,
                    idcolor,
                    '" . $idalmacen . "'
                from
                    tproductoexistencias
                where
                    idalmacen = 1
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con, $query)) {
                    $post["idactividad"] = 20;
                    $post["comentarios"] = "Agrego el almacen " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Almacen Agregrado", "mensaje" => "Se ha agregado el almacen correctamente.", "formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al agregar el almacen (existencias)");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo agregar el almacen.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Editar Almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarAlmacen($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);
        $nombre = mysqli_real_escape_string($this->con, $post["txtNombre"]);
        $reorden = mysqli_real_escape_string($this->con, $post["slcReorden"]);

        $nombrea = $this->obtenerAlmacen($post)["almacen"]["nombre"];

        try {
            $query = "
            update
                talmacenes
            set
                nombre = '" . $nombre . "',
                reorden = '" . $reorden . "'
            where
                idalmacen = '" . $idalmacen . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                $post["idactividad"] = 21;
                $post["comentarios"] = "Edito el almacen " . (($nombrea != $nombre) ? $nombrea . " -> " . $nombre : $nombre);
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Almacen Editado", "mensaje" => "Se ha editado el almacen correctamente.", "formulario" => "formBusqueda");
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo editar el almacen.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    /**
     * Eliminar Almacen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarAlmacen($post)
    {
        $idusuario = mysqli_real_escape_string($this->con, $post["idusuario"]);
        $idalmacen = mysqli_real_escape_string($this->con, $post["idalmacen"]);

        try {
            $query = "
            update
                talmacenes
            set
                status = 0
            where
                idalmacen = '" . $idalmacen . "'
            ";

            $this->claseQueries->guardarQuery($query);

            if (mysqli_query($this->con, $query)) {
                // al quitar el almacen, se deben "eliminar" también sus registros en la tabla tproductoexistencias (para poder contabilizar bien las existencias). esto se hara poniendo status = 0 ("eliminado") a los registros correspondientes
                $query = "
                update
                    tproductoexistencias
                set
                    status = 0
                where
                    idalmacen = '" . $idalmacen . "'
                ";

                $this->claseQueries->guardarQuery($query);

                if (mysqli_query($this->con, $query)) {
                    $nombre = $this->obtenerAlmacen($post)["almacen"]["nombre"];
                    $post["idactividad"] = 22;
                    $post["comentarios"] = "Elimino el almacen " . $nombre;
                    $this->claseLogs->registrarActividad($post);
                    $respuesta = array("respuesta" => "OK", "tipo" => "mensajecargar", "titulo" => "Almacen Eliminado", "mensaje" => "Se ha eliminado el almacen correctamente.", "formulario" => "formBusqueda");
                } else {
                    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Ocurrió un error al eliminar el almacen (existencias)");
                }
            } else {
                $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "No se pudo eliminar el almacen.");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta" => "EXCEPTION", "mensaje" => "Código ###: " . $e->getMessage());
        } finally {
            return $respuesta;
        }
    }

    // select a.idactividad,b.actividad,count(*),row_number() over (order by count(*) desc) as orden from tlog a left join tcatactividadeslog b on a.idactividad = b.idactividad group by a.idactividad order by count(*) desc
    /*
    "posibilidad de agregar imágenes a especificaciones de pedido Posterior al cierre del pedido"
    "recibos de dinero aplicables a pedido"
        "estoy tratando de editar una cantidad en cotizaciones pero no procesa la edicion" (video relacionado)
    "pueden revisar el pedido 4554 por favor en ambos sistemas."
    "-ocupamos poder eliminar y fusionar clientes en sistema"
        "Se siguen duplicando los movimientos? Este es del 9 de Julio, si es así es Urgente atender este tema por que los inventarios se alteran, por el momento daré una entrada a este producto para rectificar el inventario"
    "Alfredo, me puedes apoyar con esto, no salen especificaciones"
        "siguen si poder hacerse movimientos"
        "Sigue sin arrojarme el calendario en especificaciones"
        "Me permitió desglosar mas piezas de las que son en la partida."
    tambien permite convertir a pedido sin asignar todas las partidas a especificaciones

    "Alfredo en ese modelo tengo una línea sin color pero con existencia"
        select idcolor from tproductoexistencias where idcolor not in (select idcolor from tcatcolores) and idcolor != 0 group by idcolor (112,276,303,453)
    "En esa cuadrícula en la parte de arriba está arrojando el total de piezas por talla, por que arroja en esta cuadrícula el total y en otras no"
        porque tiene un idtalla = 0
    "Me dicen que se quedo en punto de venta la prenda marcada por un periodo largo de tiempo y se cobro solo…. Pero aparte de eso es que. No cuadran las cuentas"


    // -"Productos - Busqueda: Realizar búsqueda tipo "google"." ¿A qué te refieres?

    // Este tiene que ver con el buscador de productos, si ellos quieren filtrar todos los productos uabc actualmente lo pueden hacer, pero si quieren todas las sudaderas uabc no pueden, ya que si escriben esas 2 cosas en el buscador puede que no encuentre todos los resultados porque funciona con un like

    // No sé si te acuerdas pero hace algunos años trabajamos un proyecto que se llamaba “busca tu ropa” (algo así) donde buscaba por palabras y había como un ranking dependiendo la similitud que tenía la búsqueda con los resultados

    Falta:
        -Validar cliente (datos no vacíos) al agregar pedido
        -Ajustar el PDF de producción del pedido
        -Agregar idespecificación a la orden de compra (no solo idpedido)
        -Marcar automaticamente como completada una especificacion si esta no tiene productos desglosados y tampoco requiere diseño ni produccion
        -Que los movimientos no salgan en la lista si el pedido no está activado
        -Entrando en editar pedido, si se seleccionó "Pedido interno" cuando se agregó, no se mantiene seleccionado
        -Al editar el pedido, se deben actualizar todas las cosas pertinentes (movimientos, solicitudes y status de especificaciones en producción diferían)
        -Modificar un poco el formato de las partidas en informacion.php y pedido.php en pedidos
        -Autorizar los movimientos de un pedido al activar (no antes)
        -Si un pedido es interno ($total == 0), activar automáticamente
        -Al editar un pedido interno, que esté preseleccionada la opcion de "pedido interno"
        -Si un pedido interno se activa automaticamente y autoriza movimientos, no se debe poder editar
        -En compras, la lista se muestra con duplicados

    */
}
