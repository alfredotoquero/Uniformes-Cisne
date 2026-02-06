<?
if($_SESSION["usuario"]!=NULL){
	//sino, calculamos el tiempo transcurrido
	$fechaGuardada = $_SESSION["fecha"];
	$ahora = date("Y-n-j H:i:s");
	$tiempo_transcurrido = (strtotime($ahora)-strtotime($fechaGuardada));
	//comparamos el tiempo transcurrido
	if($tiempo_transcurrido >= 7200) {
		//si pasaron 10 minutos o más
		unset($_SESSION["usuario"]);
		unset($_SESSION["fecha"]);
		session_destroy(); // destruyo la sesión
		header("location: index.php");
	}else {
		$_SESSION["fecha"] = $ahora;
	}
}else{
	header("location: index.php");
}
?>