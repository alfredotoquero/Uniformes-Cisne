<?
function fecha_formateada($fecha){
	$fecha = explode(" ",$fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];
	
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia."/";

	switch($mes){
		case "01": $fecha.= "Ene"; break;
		case "02": $fecha.= "Feb"; break;
		case "03": $fecha.= "Mar"; break;
		case "04": $fecha.= "Abr"; break;
		case "05": $fecha.= "May"; break;
		case "06": $fecha.= "Jun"; break;
		case "07": $fecha.= "Jul"; break;
		case "08": $fecha.= "Ago"; break;
		case "09": $fecha.= "Sep"; break;
		case "10": $fecha.= "Oct"; break;
		case "11": $fecha.= "Nov"; break;
		case "12": $fecha.= "Dic"; break;
	}

	$fecha .= "/".$ano;
	
	if($hora!=""){
		$fecha .= "<br>".date("h:i a",strtotime($hora));
	}
	
	return $fecha;
}

function fecha_formateada_largo($fecha){
	$fecha = explode(" ",$fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];
	
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia." de ";

	switch($mes){
		case "01": $fecha.= "Enero"; break;
		case "02": $fecha.= "Febrero"; break;
		case "03": $fecha.= "Marzo"; break;
		case "04": $fecha.= "Abril"; break;
		case "05": $fecha.= "Mayo"; break;
		case "06": $fecha.= "Junio"; break;
		case "07": $fecha.= "Julio"; break;
		case "08": $fecha.= "Agosto"; break;
		case "09": $fecha.= "Septiembre"; break;
		case "10": $fecha.= "Octubre"; break;
		case "11": $fecha.= "Noviembre"; break;
		case "12": $fecha.= "Diciembre"; break;
	}

	$fecha .= " de ".$ano;
	
	if($hora!=""){
		$fecha .= " ".date("h:i a",strtotime($hora));
	}
	
	return $fecha;
}
?>