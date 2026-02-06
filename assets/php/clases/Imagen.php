<?php
class Imagen{
    private $con;
    private $claseQueries;

    function __construct() {
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Queries.php");
        include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");
        $this->con = $con;
        $this->claseQueries = new Queries();
    }

    /**
     * Subir Imagen.
     * 
     * @access public
     * @param array $post
     * @return array
     */
    public function subirImagen($imagen,$ruta,$nombre,$tamanomax,$tabla = "",$campoid = "",$id = "",$campo = ""){
        $nombre = mysqli_real_escape_string($this->con,$nombre);
        $tabla = mysqli_real_escape_string($this->con,$tabla);
        $campoid = mysqli_real_escape_string($this->con,$campoid);
        $id = mysqli_real_escape_string($this->con,$id);
        $campo = mysqli_real_escape_string($this->con,$campo);

        try {
            
            if($imagen["tmp_name"] != NULL){

                $tamanio=getimagesize($imagen["tmp_name"]);            
                $tammax = $tamanomax;
                $img_w=$tamanio[0];
                $img_h=$tamanio[1];
                
                $tamw=$tamanio[0];
                $tamh=$tamanio[1];

                if($img_w || $img_h > $tammax){
                    if($img_w > $img_h){
                        $tamh = ($img_h * $tammax) / $img_w;
                        $tamw = $tammax;
                    }else{
                        $tamw = ($img_w * $tammax) / $img_h;
                        $tamh = $tammax;
                    }
                }
    
                // verificar tipo de la imagen
                if ($tamanio[2]==3) {
                    $original=imagecreatefrompng($imagen["tmp_name"]);
                } else if ($tamanio[2]==2) {
                    $original=imagecreatefromjpeg($imagen["tmp_name"]);
                }
                
                $newimage = imagecreatetruecolor($tamw,$tamh);
                if ($tamanio[2]==3) {
                    imagesavealpha($newimage, true);            
                    $transparencia = imagecolorallocatealpha($newimage, 0, 0, 0, 127);
                    imagefill($newimage,0,0,$transparencia);
                }

                imagecopyresampled($newimage,$original,0,0,0,0,$tamw,$tamh,$img_w,$img_h);
    
                //si existe alguna rotacion en la imagen original, se acomoda la imagen y despues se guarda (iPhone)
                $exif = exif_read_data($imagen['tmp_name']);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $newimage = imagerotate($newimage, 180, 0);
                            break;
    
                        case 6:
                            $newimage = imagerotate($newimage, -90, 0);
                            break;
    
                        case 8:
                            $newimage = imagerotate($newimage, 90, 0);
                            break;
                    }
                }
    
                if ($tamanio[2]==3) {
                    imagepng ($newimage,$_SERVER["DOCUMENT_ROOT"].$ruta.$nombre,0);
                } else if ($tamanio[2]==2) {
                    imagejpeg($newimage,$_SERVER["DOCUMENT_ROOT"].$ruta.$nombre,80);
                }
    
                if ($tabla!="") {
                    $query = "
                    update
                        ".$tabla."
                    set
                        ".$campo." = '".$nombre."'
                    where
                        ".$campoid." = '".$id."'
                    ";
                }

                $this->claseQueries->guardarQuery($query);

                mysqli_query($this->con,$query);

                $respuesta = array("respuesta"=>"OK");
            }
        } catch (Exception $e) {
            $respuesta = array("respuesta"=>"EXCEPTION","mensaje"=>"Codigo ###:" . $e->getMessage());
        }finally{
            return $respuesta;
        }
    }
}
?>