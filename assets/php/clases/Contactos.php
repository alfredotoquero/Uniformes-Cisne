<?php
class Contactos{
    private $con;
    private $claseQueries;
    private $claseClientes;
    private $claseLogs;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Logs.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
        $this->claseLogs = new Logs();
    }

    /**
     * Obtener Contactos.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerContactos($post){
        // filtros
        $idcliente = mysqli_real_escape_string($this->con,$post["idcliente"]);
        $busqueda = mysqli_real_escape_string($this->con,$post["txtBusqueda"]);
        $nombre = mysqli_real_escape_string($this->con,$post["txtNombre"]);
        // $status = mysqli_real_escape_string($this->con,$post["slcStatus"]);

        $where = "";
        $where .= (($busqueda!="") ? " and (nombre like '%".$busqueda."%' or correo like '%".$busqueda."%' or telefono like '%".$busqueda."%')" : "");
        $where .= (($idcliente>0) ? " and idcliente = '".$idcliente."'" : "");
        $where .= (($nombre!="") ? " and nombre like '%".$nombre."%'" : "");
        // $where .= (($status!='') ? " and status = '".$status."'" : "");

        try{
            $query = "
            select
                *
            from
                tcontactos
            where
                status = 'A'
                ".$where."
            ";

            $contactos = mysqli_query($this->con,$query);

            if(mysqli_num_rows($contactos)>0){
                $respuesta = array("respuesta"=>"OK","contactos"=>$contactos);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se encontraron contactos registrados.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Obtener Contacto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function obtenerContacto($post){
        $idcontacto = mysqli_real_escape_string($this->con,$post["idcontacto"]);

        try{
            $query = "
            select
                *
            from
                tcontactos
            where
                idcontacto = '".$idcontacto."'
            ";
            $contacto = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if($contacto["idcontacto"]>0){
                $respuesta = array("respuesta"=>"OK","contacto"=>$contacto);
            }else{
                $respuesta = array("respuesta"=>"ERROR","mensaje"=>"No se pudo recuperar la información del contacto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Agregar Contacto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function agregarContacto($post){
        // $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
        $idcliente = mysqli_real_escape_string($this->con,$post["idcliente"]);
        $contacto = mysqli_real_escape_string($this->con,$post["txtContacto"]);
        $puesto = mysqli_real_escape_string($this->con,$post["txtPuesto"]);
        $correo = mysqli_real_escape_string($this->con,$post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con,$post["txtTelefono"]);

        $idcliente = (($idcliente==0) ? "" : $idcliente);
        
        try{
            $query = "
            insert into
                tcontactos 
                (
                    idcliente,
                    nombre,
                    puesto,
                    correo,
                    telefono
                )
            values 
                (
                    '".$idcliente."',
                    '".$contacto."',
                    '".$puesto."',
                    '".$correo."',
                    '".$telefono."'
                )
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){
                $idcontacto = mysqli_insert_id($this->con);
                
                $this->claseClientes = new Clientes();
                $nombrecliente = $this->claseClientes->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 5;
                $post["comentarios"] = "Agrego el contacto " . $contacto . " al cliente " . $nombrecliente;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>"Contacto Agregrado","mensaje"=>"Se ha agregado el contacto correctamente.","formulario" => "formContactos","idcontacto"=>$idcontacto,"reiniciarform"=>1,"btn"=>"btnAgregarC");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo agregar el contacto.");
            }
        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Editar Contacto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function editarContacto($post){
        $idcontacto = mysqli_real_escape_string($this->con,$post["idcontacto"]);
        $contacto = mysqli_real_escape_string($this->con,$post["txtContacto"]);
        $puesto = mysqli_real_escape_string($this->con,$post["txtPuesto"]);
        $correo = mysqli_real_escape_string($this->con,$post["txtCorreo"]);
        $telefono = mysqli_real_escape_string($this->con,$post["txtTelefono"]);

        $nombrec = $this->obtenerContacto($post)["contacto"]["nombre"];

        try{
            $query = "
            update
                tcontactos
            set
                correo = '".$correo."',
                nombre = '".$contacto."',
                puesto = '".$puesto."',
                telefono = '".$telefono."'
            where
                idcontacto = '".$idcontacto."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $this->claseClientes = new Clientes();
                $nombrecliente = $this->claseClientes->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 6;
                $post["comentarios"] = "Edito el contacto " . (($nombrec!=$contacto) ? $nombrec . " -> " . $contacto : $contacto) . " del cliente " . $nombrecliente;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>"Contacto Editado","mensaje"=>"Se ha editado el contacto correctamente.","formulario" => "formContactos","reiniciarform"=>1,"btn"=>"btnAgregarC");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo editar el contacto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }

    }

    /**
     * Eliminar Contacto.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function eliminarContacto($post){
        $idcontacto = mysqli_real_escape_string($this->con,$post["idcontacto"]);

        try{
            $query = "
            update
                tcontactos
            set
                status = 0
            where
                idcontacto = '".$idcontacto."'
            ";

            $this->claseQueries->guardarQuery($query);

            if(mysqli_query($this->con,$query)){

                $this->claseClientes = new Clientes();
                $contacto = $this->obtenerContacto($post)["contacto"];
                $nombre = $contacto["nombre"];
                $post["idcliente"] = $contacto["idcliente"];
                $nombrecliente = $this->claseClientes->obtenerCliente($post)["cliente"]["nombre"];
                $post["idactividad"] = 7;
                $post["comentarios"] = "Elimino el contacto " . $nombre . " del cliente " . $nombrecliente;
                $this->claseLogs->registrarActividad($post);
                $respuesta = array("respuesta"=>"OK","tipo"=>"mensajecargar2","titulo"=>"Contacto Eliminado","mensaje"=>"Se ha eliminado el contacto correctamente.","formulario" => "formContactos");
            }else{
                $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"No se pudo eliminar el contacto.");
            }

        }catch(Exception $e){
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Código ###: ".$e->getMessage());
        }finally{
            return $respuesta;
        }
    }
}
?>