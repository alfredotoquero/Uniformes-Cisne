var script_tag = document.getElementById("funciones");
var modulo1 = script_tag.getAttribute("data-modulo1");
var modulo2 = script_tag.getAttribute("data-modulo2");
var modulo3 = script_tag.getAttribute("data-modulo3");
var enviandoformulario = false;
var first = 0;

$(document).ready(function () {
	// focus en cuadro de texto después de abrir select2
	$(document).on("select2:open", () => {
		document.querySelector(".select2-search__field").focus();
	});

	if (
		modulo1 == "" ||
		modulo1 == "almacenes" ||
		modulo1 == "colores" ||
		modulo1 == "contactos" ||
		modulo1 == "cotizaciones" ||
		modulo1 == "movimientos" ||
		modulo1 == "pedidos" ||
		modulo1 == "produccion" ||
		modulo1 == "produccionde" ||
		modulo1 == "productos" ||
		modulo1 == "proveedores" ||
		modulo1 == "compras" ||
		modulo1 == "reportecxc" ||
		modulo1 == "reporteinventario" ||
		modulo1 == "reporteproductosvendidos" ||
		modulo1 == "reporteventas" ||
		modulo1 == "reporteventaspsucursal" ||
		modulo1 == "reportemovimientos" ||
		modulo1 == "reportecortes" ||
		modulo1 == "reportereorden" ||
		modulo1 == "sucursales" ||
		modulo1 == "tallas" ||
		modulo1 == "tiposproducto" ||
		modulo1 == "usuarios" ||
		modulo1 == "vendedores" ||
		modulo1 == "clientes" ||
		modulo1 == "catalogos" ||
		modulo1 == "categorias" ||
		modulo1 == "tarjetasregalo" ||
		modulo1 == "tipostarjetaregalo" ||
		modulo1 == "notificaciones" ||
		modulo1 == "cuentas" ||
		modulo1 == "recordatorios" ||
		modulo1 == "parametros" ||
		modulo1 == "personalizaciones" ||
		modulo1 == "tiendas" ||
		modulo1 == "emisores" ||
		modulo1 == "facturas" ||
		modulo1 == "pagos"
	) {
		cargarDatosContenedor("formBusqueda");
	}

	if (
		(modulo1 == "cotizaciones" || modulo1 == "pedidos") &&
		modulo2 == "agregar"
	) {
		$("#txtCorreo, #txtTelefono, #slcCiudad").keypress(function () {
			$("#iconBorrar1").removeClass("fa-disabled");
			$("#btnBorrar1").prop("disabled", false);
		});

		$("#txtCorreoC, #txtTelefonoC, #txtPuesto").keypress(function () {
			$("#iconBorrar1").removeClass("fa-disabled");
			$("#btnBorrar1").prop("disabled", false);
			$("#iconBorrar2").removeClass("fa-disabled");
			$("#btnBorrar2").prop("disabled", false);
		});

		$("#chkIncluyeIva, #slcIVA, #chkSubtotalizar").change(function () {
			cargarDatosContenedor("formCotizacion");
		});

		if (modulo3 > 0) {
			cargarDatosCotizacion(modulo3);
			cargarDatosContenedor("formCotizacion");
		}
	}

	if (modulo1 == "cotizaciones" && modulo2 == "convertir") {
		$("#chkIncluyeIva, #slcIVA, #chkSubtotalizar").change(function () {
			cargarDatosContenedor("formCotizacion");
		});

		if (modulo3 > 0) {
			cargarDatosCotizacion(modulo3);
			cargarDatosContenedor("formCotizacion");
		}
	}
	if (modulo1 == "pedidos" && modulo2 == "editar") {
		$("#chkIncluyeIva, #slcIVA, #chkSubtotalizar").change(function () {
			cargarDatosContenedor("formCotizacion");
		});

		if (modulo3 > 0) {
			cargarDatosContenedor("formCotizacion");
			cargarDatosContenedor("formPedido");
			cargarDatosPedido(modulo3);
		}
	}

	if (modulo1 == "clientes") {
		$("#txtFecha").prop("disabled", true);
	}

	$(".onenter").on("keypress", function (e) {
		if (e.keyCode === 13) {
			validarFormulario("formLogin");
		}
	});

	$("#formBusqueda :input[type=text]").on("keypress", function (e) {
		if (e.keyCode === 13) {
			e.preventDefault();
			cargarDatosContenedor($(this.form).attr("name"));
		}
	});

	$("#formBusqueda select").on("change", function (e) {
		e.preventDefault();
		cargarDatosContenedor($(this.form).attr("name"));
	});

	$(".select2").select2();

	$(".select2t").select2({
		tags: true,
	});

	$(".select2AlmacenM").select2({
		placeholder: "--Seleccionar Almacenes--",
	});

	$(".select2SucursalM").select2({
		placeholder: "--Seleccionar Sucursales--",
	});

	$(".maskNumber").mask("ZZZZZZ.ZZ", {
		translation: {
			Z: {
				pattern: /[0-9]/,
				optional: true,
			},
		},
	});

	if(modulo1 == "productos" && (modulo2 == "agregar" || modulo2 == "editar")){

		$("#slcProductoServicio").select2({
			minimumInputLength: 4,
			ajax: {
				url: "/assets/php/controladores/sat.php",
				type: "POST",
				dataType: "json",
				delay: 250,
				data: function (params) {
					return {
						palabraClave: params.term,
						accion: "obtenerProductosServicios"
					};
				},
				processResults: function (data) {
					return {
						results: $.map(data.productosservicios, function(obj) {
							return {
								id: obj.idproductoservicio,
								text: obj.clave + ' - ' + obj.descripcion
							};
						})
					};
				},
				cache: true
			}
		});

		$("#slcUnidadMedida").select2({
			minimumInputLength: 1,
			ajax: {
				url: "/assets/php/controladores/sat.php",
				type: "POST",
				dataType: "json",
				delay: 250,
				data: function (params) {
					return {
						palabraClave: params.term,
						accion: "obtenerUnidadesMedida"
					};
				},
				processResults: function (data) {
					return {
						results: $.map(data.unidadesmedida, function(obj) {
							return {
								id: obj.idunidadmedida,
								text: obj.clave + ' - ' + obj.nombre
							};
						})
					};
				},
				cache: true
			}
		});

		if(modulo2 == "editar"){
			if($("#txtUnidadMedidaID").val()>0){
				var option = new Option($("#txtUnidadMedidaDescripcion").val(), $("#txtUnidadMedidaID").val(), true, true);
				$('#slcUnidadMedida').append(option).trigger('change');
			}
			if($("#txtProductoServicioID").val()>0){
				var option = new Option($("#txtProductoServicioDescripcion").val(), $("#txtProductoServicioID").val(), true, true);
				$('#slcProductoServicio').append(option).trigger('change');
			}
		}
	}

	buscarNotificaciones("campana");
	buscarNotificaciones("toast");

	setInterval(function () {
		buscarNotificaciones("campana");
		buscarNotificaciones("toast");
	}, 30000);

	$("#btnNotificacion").on("click", function () {
		conexionServidor(
			"notificaciones",
			"marcar_diez_como_leidas",
			"algo=1",
			"",
			"",
			"",
			1
		);
	});

	if ($(".date").length) {
		var d = new Date();

		var month = d.getMonth() + 1;
		var day = d.getDate();

		var output =
			d.getFullYear() +
			"-" +
			(("" + month).length < 2 ? "0" : "") +
			month +
			"-" +
			(("" + day).length < 2 ? "0" : "") +
			day;

		$(".date").datepicker({
			format: "yyyy-mm-dd",
			showOtherMonths: true,
			selectOtherMonths: true,
			autoclose: true,
			changeMonth: true,
			changeYear: true,
			orientation: "bottom",
			startDate: output,
		});
	}

	if ($("#txtFecha").length) {
		$("#txtFecha").datepicker({
			format: 'yyyy-mm-dd',   // formato exacto
			autoclose: true,
		});
	}

	if ($("#txtFechaInicial, #txtFechaFinal").length) {
		// check if element is available to bind ITS ONLY ON HOMEPAGE
		var currentDate = moment().format("YYYY-MM-DD");

		$("#txtFechaInicial, #txtFechaFinal").daterangepicker(
			{
				locale: {
					format: "YYYY-MM-DD",
				},
				opens: "left",
				alwaysShowCalendars: true,
				// "minDate": currentDate,
				// "maxDate": moment().add('months', 1),
				autoApply: true,
				autoUpdateInput: false,
				isInvalidDate: function (arg) {
					// console.log(arg);

					// Prepare the date comparision
					var thisMonth = arg._d.getMonth() + 1; // Months are 0 based
					if (thisMonth < 10) {
						thisMonth = "0" + thisMonth; // Leading 0
					}
					var thisDate = arg._d.getDate();
					if (thisDate < 10) {
						thisDate = "0" + thisDate; // Leading 0
					}
					var thisYear = arg._d.getYear() + 1900; // Years are 1900 based

					var thisCompare = thisMonth + "/" + thisDate + "/" + thisYear;
					// console.log(thisCompare);

					// if($.inArray(thisCompare,array)!=-1){
					// 		// console.log("      ^--------- DATE FOUND HERE");
					// 		return true;
					// }
				},
			},
			function (start, end, label) {
				// console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
				// Lets update the fields manually this event fires on selection of range
				var selectedStartDate = start.format("YYYY-MM-DD"); // selected start
				var selectedEndDate = end.format("YYYY-MM-DD"); // selected end

				$checkinInput = $("#txtFechaInicial");
				$checkoutInput = $("#txtFechaFinal");

				// Updating Fields with selected dates
				$checkinInput.val(selectedStartDate);
				$checkoutInput.val(selectedEndDate);

				// Setting the Selection of dates on calender on CHECKOUT FIELD (To get this it must be binded by Ids not Calss)
				var checkOutPicker = $checkoutInput.data("daterangepicker");
				checkOutPicker.setStartDate(selectedStartDate);
				checkOutPicker.setEndDate(selectedEndDate);

				// Setting the Selection of dates on calender on CHECKIN FIELD (To get this it must be binded by Ids not Calss)
				var checkInPicker = $checkinInput.data("daterangepicker");
				checkInPicker.setStartDate(selectedStartDate);
				checkInPicker.setEndDate(selectedEndDate);
			}
		);
	} // End Daterange Picker
});

function validarFormulario(formulario) {
	var error = false;
	$(".requerido", "#" + formulario).each(function () {
		var elemento = $(this).prop("tagName").toLowerCase();
		if (elemento == "input") {
			var tipo = $(this).attr("type");
			if (
				tipo == "text" &&
				($(this).val() == "" || $(this).val().trim().length == 0)
			) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (
				tipo == "password" &&
				($(this).val() == "" || $(this).val().trim().length == 0)
			) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "number" && jQuery.type($(this).val()) != "number") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "email" && !validarCorreo($(this).val())) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "file" && $(this).val() == "") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "radio" && $(this).val() == "") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			}
		} else if (elemento == "select" && $(this).val() == 0) {
			swalFocus(
				"Error",
				$(this).data("mensajeerror"),
				"error",
				$(this).attr("name")
			);
			error = true;
			return false;
		} else if (elemento == "textarea" && $(this).val() == "") {
			swalFocus(
				"Error",
				$(this).data("mensajeerror"),
				"error",
				$(this).attr("name")
			);
			error = true;
			return false;
		}
	});
	if (!error) {
		enviarFormulario(formulario);
	}
}

function validarCorreo(valor) {
	if (
		/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(
			valor
		)
	) {
		return true;
	} else {
		return false;
	}
}

function enviarFormulario(formulario) {
	if (!enviandoformulario) {
		$.ajax({
			type: "POST",
			url:
				"/assets/php/controladores/" +
				$("#controlador", "#" + formulario).val() +
				".php",
			data: new FormData($("#" + formulario)[0]),
			processData: false,
			contentType: false,
			dataType: "json",
			beforeSend: function () {
				$("#divLoader").css("display", "inline");
				enviandoformulario = true;
				$("#btnValidar").prop("disabled", true);
			},
			success: function (data) {
				$("#divLoader").css("display", "none");
				enviandoformulario = false;
				if (data != "null") {
					if (data.respuesta == "OK") {
						tipo = data.tipo;
						if (tipo == "reload") {
							location.href = location.href;
						} else if (tipo == "href") {
							location.href = $("#href", "#" + formulario).val();
						} else if (tipo == "mensaje") {
							Swal.fire(data.titulo, data.mensaje, "success");
						} else if (tipo == "mensajehref") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								location.href = $("#href", "#" + formulario).val();
							});
						} else if (tipo == "mensajecerrarfancy") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								$.fancybox.close();
							});
						} else if (tipo == "mensajereload") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								location.href = location.href;
							});
						} else if (tipo == "mensajecargar") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								$.fancybox.close();
								cargarDatosContenedor(data.formulario);
								// ESTO SE PUEDE PROGRAMAR MEJOR: AGREGUÉ UN CODIGO PARA RECARGAR UN ELEMENTO ESPECIFICO (UN SELECT) QUE NO ESTÁ EN EL CONTENEDOR Y NO SE PUEDE AGREGAR FACILMENTE
								if (data.pantalla == "solicitudes") {
									recargarSelect(data.idproveedor);
								}
							});
						} else if (tipo == "mensajecargar2") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								cargarDatosContenedor(data.formulario);
								if (data.formulario == "formRecepcion") {
									cargarDatosContenedor("formBusqueda");
								}
								if (data.reiniciarform) {
									$("#" + formulario).trigger("reset");
									$("#" + formulario).toggle();
									$("#" + data.btn).show();
								}
								if (data.razon == 1) {
									$("#idrazonsocial").val("");
									$("#accion").val("agregarrazon");
								}
							});
						} else if (tipo == "cerrarfancycargar") {
							$.fancybox.close();
							cargarDatosContenedor(data.formulario);
							// SI ESTOY EN LA PANTALLA DE INDICAR ESPECIFICACIONES Y AGREGUÉ/EDITÉ UNA PARTIDA, SE REINICIAN UNA SERIE DE INPUTS
							if (data.pantalla == "cotizacion") {
								$("#slcOrigen").val(0);
								$("#slcCategoria").val(0);
								$("#slcProducto").val(0);
								$("#txtProducto").val("");
								$("#txtCantidad").val("");
								$("#txtPrecio").val("");

								mostrar2(0);
								$("#slcProducto").select2();

								// mostrar opciones de totalizacion
								$("#divTotalizacion").show();

								// cuando vienes de editar
								$("#divAgregar").show();
								$("#divEditar").hide();
								$("#slcOrigen").prop("disabled", false);
								$("#slcProducto").prop("disabled", false);
								$("#idpartida").val("");
								$("#idproducto").val("");
							}
						} else if (tipo == "mensajeventana") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								window.open(data.archivo, "_blank");
								location.href = location.href;
							});
						} else if (tipo == "mensajecargarabrirfancy") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								cargarDatosContenedor(data.formulario);
								fancy(data.rutafancy);
							});
						}else if(tipo == "abrir_nuevo_fancy"){
							$.fancybox.close();
							fancy(data.url);
						}
					} else if (data.respuesta == "ERROR") {
						Swal.fire("Error", data.mensaje, "error");
						$("#btnValidar").prop("disabled", false);
					} else if (data.respuesta == "EXCEPTION") {
						Swal.fire("Excepción", data.mensaje, "error");
						$("#btnValidar").prop("disabled", false);
					} else {
						Swal.fire(
							"Error",
							"Ocurrió un error al procesar la solicitud.",
							"error"
						);
						$("#btnValidar").prop("disabled", false);
					}
				} else {
					Swal.fire(
						"Error",
						"Ocurrió un error al procesar la solicitud (Controlador: " +
						$("#controlador", "#" + formulario).val() +
						").",
						"error"
					);
					$("#btnValidar").prop("disabled", false);
				}
			},
		});
	}
}

function solicitudServidor(
	controlador,
	accion,
	datos,
	validacion,
	tipo = "reload",
	href = "",
	delay = ""
) {
	if (validacion != "") {
		Swal.fire({
			title: "Atención",
			text: validacion,
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Aceptar",
			cancelButtonText: "Cancelar",
		}).then((result) => {
			if (result.value) {
				conexionServidor(controlador, accion, datos, tipo, href, delay);
			}
		});
	} else {
		conexionServidor(controlador, accion, datos, tipo, href, delay);
	}
}

function conexionServidor(
	controlador,
	accion,
	datos,
	tipo,
	href,
	delay,
	omitirloader = 0
) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/" + controlador + ".php",
		data: "accion=" + accion + "&" + datos,
		dataType: "json",
		beforeSend: function () {
			if (!omitirloader) {
				$("#divLoader").css("display", "inline");
			}
			enviandoformulario = true;
		},
		success: function (data) {
			if (!omitirloader) {
				$("#divLoader").css("display", "none");
			}
			enviandoformulario = false;
			if (data.respuesta == "OK") {
				tipo = data.tipo != "" && data.tipo != undefined ? data.tipo : tipo;
				if (tipo == "reload") {
					location.href = location.href;
				} else if (tipo == "href") {
					location.href = href;
				} else if (tipo == "mensaje") {
					Swal.fire(data.titulo, data.mensaje, "success");
				} else if (tipo == "mensajehref") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						location.href = href;
					});
				} else if (tipo == "mensajereload") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						location.href = location.href;
					});
				} else if (tipo == "mensajecargar") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						$.fancybox.close();
						cargarDatosContenedor(data.formulario);
						// ESTO SE PUEDE PROGRAMAR MEJOR: AGREGUÉ UN CODIGO PARA RECARGAR UN ELEMENTO ESPECIFICO (UN SELECT) QUE NO ESTÁ EN EL CONTENEDOR Y NO SE PUEDE AGREGAR FACILMENTE
						if (data.pantalla == "solicitudes") {
							recargarSelect(data.idproveedor);
						}
					});
				} else if (tipo == "mensajecargar2") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						cargarDatosContenedor(data.formulario);
					});
					// AGREGUÉ UN NUEVO TIPO, QUE NO NECESITA MOSTRAR NINGUN MENSAJE TIPO SWAL, PERO SI NECESITA RECARGAR UN CONTENEDOR (SIN CARGAR TODA LA PAGINA)
				} else if (tipo == "cargar") {
					if (delay) {
						setTimeout(function () {
							cargarDatosContenedor(data.formulario);
						}, delay);
					} else {
						cargarDatosContenedor(data.formulario);
					}
					if (data.pantalla == "notificaciones") {
						buscarNotificaciones("campana");
					}
				} else if (tipo == "fancyArchivo") {
					$.fancybox.open({
						src: data.pdf_fancy,
						type: "iframe",
						opts: {
							closeExisting: true,
						},
					});

				}
			} else if (data.respuesta == "ERROR") {
				Swal.fire("Error", data.mensaje, "error");
			} else if (data.respuesta == "EXCEPTION") {
				Swal.fire("Excepción", data.mensaje, "error");
			} else {
				Swal.fire(
					"Error",
					"Ocurrió un error al procesar la solicitud.",
					"error"
				);
			}
		},
	});
}

function cargarDatosContenedor(formulario) {
	if ($("#" + formulario).length > 0) {
		$.ajax({
			type: "POST",
			url: $("#archivo", "#" + formulario).val(),
			data: new FormData($("#" + formulario)[0]),
			processData: false,
			contentType: false,
			beforeSend: function () {
				$("#divLoader").css("display", "inline");
				enviandoformulario = true;
			},
			success: function (data) {
				$("#divLoader").css("display", "none");
				enviandoformulario = false;
				$("#" + $("#contenedor", "#" + formulario).val()).html(data);
			},
		});
	}
	console.log("cargar datos contenedor");
}

function limpiarFormulario(formulario) {
	$("#" + formulario).trigger("reset");
	$("#slcAlmacenes").val([]).change();
	if (modulo1 != "reporteinventario" && modulo1 != "reporteventaspsucursal") {
		$("#slcSucursales").val([]).change();
	}
	cargarDatosContenedor(formulario);
}

// FUNCIONES REFERENTES AL COBRO (VALIDAR, COBRAR, AGREGAR PAGO)

function finalizar(datos) {
	Swal.fire({
		title: "Finalizar pedido",
		// type: "info",
		html: "Indica el motivo:<br><textarea id='txtMotivo' class='form-control' placeholder='Motivo' rows='4'></textarea><br><input type='radio' name='rdStatus' value='E' checked> Entregado <input type='radio' name='rdStatus' value='C'> Cancelado<br><br>",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		confirmButtonText: "Aceptar",
		cancelButtonText: "Cancelar",
		preConfirm: () => {
			return validarMotivo($("#txtMotivo").val());
		},
	}).then((result) => {
		if (result.value) {
			datos["motivo"] = $("#txtMotivo").val();
			datos["status"] = $("input:radio[name=rdStatus]:checked").val();
			// si el pedido se está entregando y tiene movimientos pendientes de autorización se debe preguntar qué se quiere hacer con los mismos: cancelarlos o conservarlos
			if (datos.status == "E") {
				if (movimientosPendientes(datos.idpedido)) {
					Swal.fire({
						title: "ATENCIÓN",
						icon: "warning",
						text: "Este pedido todavía tiene movimientos pendientes de autorización. ¿Deseas conservarlos?",
						showCancelButton: true,
						showDenyButton: true,
						confirmButtonColor: "#3085d6",
						confirmButtonText: "Conservarlos",
						denyButtonText: "No Conservarlos",
						denyButtonColor: "#d33",
						cancelButtonText: "Cancelar",
					}).then((result) => {
						if (result.isConfirmed) {
							solicitarPassword("finalizar", "ajax", datos, "administrativa");
						} else if (result.isDenied) {
							datos["accionmovimientos"] = "1";
							solicitarPassword("finalizar", "ajax", datos, "administrativa");
						}
					});
				} else {
					solicitarPassword("finalizar", "ajax", datos, "administrativa");
				}
			} else {
				solicitarPassword("finalizar", "ajax", datos, "administrativa");
			}
		}
	});
}

function validarMotivo(value) {
	var correcto = true;
	if (value == "") {
		alert("Debes escribir un motivo");
		correcto = false;
	}
	return correcto;
}

function movimientosPendientes(idpedido) {
	var correcto = false;
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/pedidos.php",
		data: "accion=verificarmovimientospendientes&idpedido=" + idpedido,
		async: false,
		dataType: "json",
		success: function (data) {
			correcto = data.value;
		},
	});
	return correcto;
}

// tipopassword: tipo de contraseña ('' => gerencial, 'administrativa' => administrativa)
// datos (idpedido,motivo,status,accionmovimientos,idcorte)
// accionmovimientos indica si los movimientos pendientes relacionados al pedido se conservan o cancelan
function solicitarPassword(accion, tipo, datos, tipopassword = "", ruta = "") {
	var datosstr = jsonToStr(datos);
	Swal.fire({
		title: "Introduce la contraseña " + tipopassword,
		input: "password",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		confirmButtonText: "Aceptar",
		cancelButtonText: "Cancelar",
		// closeOnConfirm: true,
		width: "800px",
		allowOutsideClick: false,
		preConfirm: (login) => {
			return validarPassword(login, tipopassword);
		},
	}).then((result) => {
		if (result.value) {
			if (tipo == "ajax") {
				$.ajax({
					type: "POST",
					url: "/assets/php/controladores/pedidos.php",
					data: datosstr + "&accion=" + accion,
					dataType: "json",
					success: function (data) {
						if (data.respuesta == "OK") {
							cargarDatosContenedor(data.formulario);
						}
					},
				});
			} else if (tipo == "fancy") {
				fancy(ruta + "?" + datosstr);
			}
		}
	});
}

function validarPassword(password, tipo) {
	var correcto = false;
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/usuarios.php",
		data: "accion=validarpassword&password=" + password + "&tipo=" + tipo,
		dataType: "json",
		async: false,
		success: function (data) {
			if (data.respuesta == "OK") {
				correcto = true;
			} else if (data.respuesta == "ERROR") {
				alert(data.mensaje);
			}
		},
	});
	return correcto;
}

function cambiarAccionFormulario(formulario, accion) {
	$("#accion", "#" + formulario).val(accion);
	console.log("Nueva accion formulario: " + accion);
}

function cambiarAccionFormulario3(formulario, accion) {
	$("#accion", "#" + formulario).val(accion);
	console.log("Nueva accion formulario: " + accion);
	validarFormulario(formulario);
}

function cambiarChecks(check, chkClass) {
	if ($(check).is(":checked")) {
		$("." + chkClass).prop("checked", true);
	} else {
		$("." + chkClass).prop("checked", false);
	}
}

function formatMoney(n, c, d, t) {
	var c = isNaN((c = Math.abs(c))) ? 2 : c,
		d = d == undefined ? "." : d,
		t = t == undefined ? "," : t,
		s = n < 0 ? "-" : "",
		i = String(parseInt((n = Math.abs(Number(n) || 0).toFixed(c)))),
		j = (j = i.length) > 3 ? j % 3 : 0;

	return (
		s +
		(j ? i.substr(0, j) + t : "") +
		i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
		(c
			? d +
			Math.abs(n - i)
				.toFixed(c)
				.slice(2)
			: "")
	);
}

function descargarReporte(formulario, ruta, archivo, chkClass) {
	var idproductos = "";
	$("." + chkClass + ":checked").each(function () {
		idproductos += $(this).val() + ",";
	});
	idproductos = idproductos.substr(0, idproductos.length - 1);
	var datos = $("#" + formulario).serialize();
	window.open(
		ruta + archivo + ".php?" + datos + "&idproductos=" + idproductos,
		"_blank"
	);
}

function cargarPDF(formulario, ruta, archivo, almenosuno = 0, chkClass = "") {
	var correcto = false;
	if (almenosuno) {
		if ($("." + chkClass + ":checked").length > 0) {
			correcto = true;
		} else {
			Swal.fire(
				"¡Atencion!",
				"Debes seleccionar al menos un registro.",
				"error"
			);
		}
	} else {
		correcto = true;
	}

	if (correcto) {
		var datos = $("#" + formulario).serialize();
		window.open(ruta + archivo + ".php?" + datos, "_blank");
	}
}

function descargarCompraExcel(idcompra) {
	$.ajax({
		type: "POST",
		data: "idcompra=" + idcompra,
		url: "/modulos/compras/descargarcompras.php",
		success: function (data) {
			$.fileDownload("/archivos/compras/" + data + ".xls")
				.done(function () {
					alert("ATENCION: Se ha descargado el archivo correctamente.");
					$.ajax({
						type: "POST",
						url: "/modulos/compras/eliminarArchivo.php",
						data: "nombre=" + data + ".xls",
					});
				})
				.fail(function () {
					alert("ERROR: No se pudo descargar el archivo!");
					$.ajax({
						type: "POST",
						url: "/modulos/compras/eliminarArchivo.php",
						data: "nombre=" + data + ".xls",
					});
				});
		},
	});
}

// función especial para movimientos. Genera un pdf basado en la opcion seleccionada (desglosado o agrupado). Se pueden seleccionar múltiples movimientos
function generarPDFMovimientos() {
	var idsmovimientos = "";
	$(".chkMovimiento:checked").each(function () {
		idsmovimientos += $(this).val() + ",";
	});
	idsmovimientos = idsmovimientos.substr(0, idsmovimientos.length - 1);

	$.confirm({
		columnClass: "medium",
		title: "¿Deseas mostrar los movimientos agrupados o desglosados?",
		content: "Selecciona el tipo en el que quieres que se genere el PDF",
		buttons: {
			cancelar: {
				text: "Cancelar",
				action: function () { },
			},
			agrupado: {
				text: "Agrupados",
				btnClass: "btn-blue",
				keys: ["enter", "shift"],
				action: function () {
					window.open(
						"/modulos/movimientos/packinglist.php?tipo=agrupados&idsmovimientos=" +
						idsmovimientos,
						"_blank"
					);
				},
			},
			desglosados: {
				text: "Desglosados",
				btnClass: "btn-blue",
				keys: ["enter", "shift"],
				action: function () {
					window.open(
						"/modulos/movimientos/packinglist.php?tipo=desglosados&idsmovimientos=" +
						idsmovimientos,
						"_blank"
					);
				},
			},
		},
	});
}

// pone el div invisible/visible dependiendo del valor escogido
function cambiarDiv(val, div) {
	if (val == 1) {
		$("#" + div + " :input").prop("disabled", false);
		$("#" + div + " :input").show();
	} else if (val == 2) {
		$("#" + div + " :input").prop("disabled", true);
		$("#" + div + " :input").hide();
	}
	console.log("val: " + val + " div: " + div);
}

// pone el div invisible/visible dependiendo del valor escogido
function cambiarDiv2(val, div) {
	if (val == 1) {
		$("#" + div + " :input").prop("disabled", false);
		$("#" + div).show();
	} else if (val == 2) {
		$("#" + div + " :input").prop("disabled", true);
		$("#" + div).hide();
	}
	console.log("val: " + val + " div: " + div);
}

function buscarNotificaciones(tipo) {
	$.ajax({
		type: "POST",
		url: "/modulos/notificaciones/buscarnotificaciones.php",
		dataType: "json",
		data: "tipo=" + tipo,
		success: function (data) {
			if (data.respuesta == "OK") {
				if (tipo == "toast") {
					for (var i = 0; i < data.notificaciones.length; i++) {
						$.toast(data.notificaciones[i].mensaje);
						console.log(
							"mensaje notificacion: " + data.notificaciones[i].mensaje
						);
					}
				} else if (tipo == "campana") {
					$("#divNotificaciones").removeClass("my-scroll");
					if (data.notificaciones.length > 0) {
						$("#divNotificacionesCampana").html("");
						for (var i = 0; i < data.notificaciones.length; i++) {
							$("#divNotificacionesCampana").append(
								'\
							<div class="dropdown-item notify-item">\
								<div>\
									<a href="javascript:;" >\
										<i class="mdi mdi-chevron-up me-1"></i><span>' +
								data.notificaciones[i].titulo +
								"</span>\
									</a>\
									<div> <span>" +
								data.notificaciones[i].notificacion +
								"</span></div>\
								</div>\
								<div><span>" +
								data.notificaciones[i].tiempo_transcurrido +
								"</span></div>\
							</div>"
							);
						}
						if (data.notificaciones.length > 3) {
							$("#divNotificaciones").addClass("my-scroll");
						}
					} else {
						$("#divNotificacionesCampana").html(
							'\
						<div class="dropdown-item notify-item">\
							<div>\
								<a href="javascript:;" >\
									<span></span>\
								</a>\
								<div> <span></span></div>\
							</div>\
							<div><span></span></div>\
						</div>\
						'
						);
					}

					$("#cant_notificaciones").html(
						data.cant_notificaciones + " Notificaciones"
					);

					if (data.cant_notificaciones_no_leidas > 0) {
						$("#badge_notificaciones").html(
							"<span style='font-size:15px;'>" +
							data.cant_notificaciones_no_leidas +
							"</span>"
						);
						$("#badge_notificaciones").addClass("badge");
					} else {
						$("#badge_notificaciones").html("");
						$("#badge_notificaciones").removeClass("badge");
					}
				}
			}
		},
	});
	return 0;
}

function marcarAtendida(
	btn,
	todas = 0,
	controlador,
	accion,
	datos,
	validacion,
	tipo,
	href,
	delay
) {
	if (todas) {
		$(".btnNotificacion").html('<i class="fas fa-circle-notch fa-spin"></i>');
		$(".btnNotificacion").attr("disabled", true);
	} else {
		$(btn).html('<i class="fas fa-circle-notch fa-spin"></i>');
		$(btn).attr("disabled", true);
	}

	solicitudServidor(controlador, accion, datos, validacion, tipo, href, delay);
}

function seleccionarAlmacen(idalmacen, formulario) {
	$("#" + formulario + " input[name=idalmacen]").val(idalmacen);
	cargarDatosContenedor(formulario);
}

function calcularPorcentaje() {
	var totalsolicitado = 0;
	$(".txtCantidad").each(function () {
		totalsolicitado += Number($(this).val());
	});
	totalsolicitado += Number($("#totalsolicitado").val());
	var totalrequerido = Number($("#totalrequerido").val());
	var porcentaje = (totalsolicitado / totalrequerido) * 100;
	$("#spPorcentaje").html(porcentaje.toFixed(2) + "%");
}

function cancelarCompra() {
	Swal.fire({
		title: "Atención",
		text: "¿Está seguro de cancelar la compra?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Aceptar",
		cancelButtonText: "Cancelar",
	}).then((result) => {
		if (result.value) {
			asignarProductos(0);
		}
	});
}

function asignarProductos(tipo) {
	var asignar = true;
	var vacio = true;
	$(".txtCantidad").each(function () {
		if (Number($(this).val()) > Number($(this).data("maximo"))) {
			asignar = false;
		}
		if (Number($(this).val()) > 0) {
			vacio = false;
		}
	});
	if ((asignar && !vacio) || tipo == 0 || tipo == 3) {
		$("#tipo", "#formRecibir").val(tipo);
		$("#accion").val("recibirproducto");
		validarFormulario("formRecibir");
	} else {
		Swal.fire(
			"ATENCION",
			"Para poder continuar, debes asignar al menos un producto y las cantidades no deben superar el restante.",
			"error"
		);
	}
}

function mostrarSolicitudes(idproveedor, formulario) {
	console.log("mostrar solicitudes.");
	if (idproveedor == 0) {
		$("#btnGenerar").prop("disabled", true);
		$("#btnAsignar").prop("disabled", false);
	} else if (idproveedor > 0) {
		$("#btnGenerar").prop("disabled", false);
		$("#btnAsignar").prop("disabled", true);
	}
}

function guardarValor(val) {
	console.log("idsolicitudcompra: " + val);

	var valores = 0;

	valores = $("input[name='chkSolicitudes[]']:checked")
		.map(function () {
			return $(this).val();
		})
		.get();

	console.log("valores: " + valores);

	$("#solicitudes").val(valores);
}

// tres funciones de la pantalla de solicitudes que necesitan enviar un conjunto de datos de tamaño indefinido (la cantidad de solicitudes que seleccione el usuario)

function eliminarSolicitudes(formulario) {
	var datos = $("#" + formulario).serialize();

	if ($(".chkSolicitud:checked").length > 0) {
		solicitudServidor(
			"solicitudes",
			"eliminarsolicitudes",
			datos,
			"¿Estás seguro de que deseas eliminar las solicitudes seleccionadas?",
			""
		);
	} else {
		Swal.fire("¡Atencion!", "Debes seleccionar al menos un registro.", "error");
	}
}

function generarOrdenCompra(formulario) {
	var datos = $("#" + formulario).serialize();

	solicitudServidor(
		"solicitudes",
		"generarordencompra",
		datos,
		"¿Estás seguro de que deseas generar la orden de compra con las solicitudes seleccionadas?",
		""
	);
}

function asignarProveedor(formulario) {
	var datos = $("#" + formulario).serialize();
	var proveedores = "";

	$.ajax({
		type: "POST",
		url: "/modulos/solicitudes/buscarproveedores.php",
		dataType: "json",
		async: false,
		success: function (data) {
			if (data.respuesta == "OK") {
				for (var i = 0; i < data.proveedores.length; i++) {
					proveedores +=
						'<option value="' +
						data.proveedores[i].idproveedor +
						'">' +
						data.proveedores[i].nombre +
						"</option>";
				}
			}
		},
	});

	Swal.fire({
		title: "Indicar Proveedor",
		html:
			'<div style="display: block; text-align: center;margin-top:35px;margin-bottom:35px;">' +
			'<select id="slcProveedorAsignar" name="slcProveedorAsignar">' +
			'<option value="0">--Selecciona un Proveedor--</option>' +
			proveedores +
			"</select></div>",
		confirmButtonColor: "#3085d6",
		confirmButtonText: "Aceptar",
		closeOnConfirm: true,
		preConfirm: () => {
			return validarProveedor($("#slcProveedorAsignar").val());
		},
	}).then((result) => {
		if (result.value) {
			solicitudServidor(
				"solicitudes",
				"asignarproveedor",
				datos + "&idproveedorasignar=" + $("#slcProveedorAsignar").val(),
				"",
				"mensajehref",
				"/solicitudes/" + $("#slcProveedorAsignar").val()
			);
		}
	});
}

function validarProveedor(val) {
	var correcto = true;
	if (val == 0) {
		alert("Debes seleccionar un proveedor.");
		correcto = false;
	}
	return correcto;
}

function recargarSelect(idproveedor) {
	$.ajax({
		type: "POST",
		url: "/modulos/solicitudes/selectproveedor.php",
		data: "miidproveedor=" + idproveedor,
		success: function (data) {
			$("#slcProveedor").html(data);
		},
	});
	console.log("select recargado");
}

// forma generalizada de hacer focus en un input usando sweetalert
function swalFocus(titulo, mensaje, tipo, input) {
	Swal.fire({
		title: titulo,
		text: mensaje,
		icon: tipo,
		didClose: () => {
			$("#" + input).focus();
		},
	});
}

// FUNCIONES MOVIMIENTOS INICIO
function agregarProductoMovimiento() {
	// NOTA: cuando hay al menos un producto agregado, las opciones superiores (tipo movimiento, almacen y almacen secundario) deben quedar fijas
	// NOTA: cuando se hace una salida o un traspaso, debe haber suficiente producto en el almacen (en el caso del traspaso, el almacen origen) para que se pueda agregar
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
	if ($("#slcTipoMovimiento").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes seleccionar un tipo de movimiento.",
			"error",
			"slcTipoMovimiento"
		);
	} else if ($("#slcAlmacen").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes seleccionar un almacen.",
			"error",
			"slcAlmacen"
		);
	} else if (
		$("#divAlmacenSec").is(":visible") &&
		$("#slcAlmacenS").val() == 0
	) {
		swalFocus(
			"ATENCION",
			"Debes seleccionar un almacen secundario.",
			"error",
			"slcAlmacenS"
		);
	} else if ($("#txtCantidad").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes introducir una cantidad.",
			"error",
			"txtCantidad"
		);
	} else if ($("#slcProducto").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes seleccionar un producto.",
			"error",
			"slcProducto"
		);
	} else {
		fancy(
			"/modulos/movimientos/cargarcolortalla.php?idproducto=" +
			$("#idproducto").val() +
			"&idalmacen=" +
			$("#almacen").val() +
			"&tipomovimiento=" +
			$("#tipomovimiento").val() +
			"&accion=agregarpartida&idpartida=" +
			$("#idmovimientopartida").val()
		);
	}
}

function asignarIdProductoMovimiento(idproducto) {
	$("#idproducto").val(idproducto);
}

function cargarColorTallaLibreMovimiento(idcategoria, tipo) {
	$("#idproducto").val("");
	$.ajax({
		type: "POST",
		url: "/modulos/movimientos/cargarcolortalla.php",
		data: "idcategoria=" + idcategoria + "&idpartida=" + $("#idpartida").val(),
		async: tipo,
		success: function (data) {
			$("#divColorTalla").html(data);
		},
	});
}

function cargarDatosMovimiento(idpartida, idproducto) {
	fancy(
		"/modulos/movimientos/cargarcolortalla.php?idproducto=" +
		idproducto +
		"&idalmacen=" +
		$("#almacen").val() +
		"&tipomovimiento=" +
		$("#tipomovimiento").val() +
		"&accion=editarpartida&idpartida=" +
		idpartida
	);
}

function eliminarProductoMovimiento(idpartida) {
	// si se elimina el producto que se habia seleccionado para editar, se deben reiniciar los datos superiores
	if ($("#idpartida").val() == idpartida) {
		$("#slcProducto").prop("disabled", false);
		$("#divAgregar").show();
	}
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/movimientos.php",
		data: "idpartida=" + idpartida + "&accion=eliminarpartida",
		success: function (data) {
			recargarListaProductosM();
		},
	});
}

function validarM() {
	var correcto = false;
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
	if (total == 0) {
		Swal.fire("ATENCION", "Debes introducir al menos una cantidad", "warning");
	} else {
		// entrada
		if ($("#tipomovimiento").val() == "1") {
			correcto = true;
			// traspaso o salida
		} else {
			// validar que las cantidades introducidas no son mayores a las existencias correspondientes
			$.ajax({
				type: "POST",
				url: "/assets/php/controladores/movimientos.php",
				data:
					$("#formColorTalla").serialize() +
					"&accion=validarexistenciasalmacen",
				async: false,
				dataType: "json",
				success: function (data) {
					if (data.respuesta == "OK") {
						correcto = true;
					} else {
						Swal.fire(data.titulo, data.mensaje, "error");
					}
				},
			});
			console.log("Entra validar existencias");
		}

		if (correcto) {
			$("#formColorTalla #accion").prop("disabled", false);
			agregarM();
		}
	}
}

function agregarM() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/movimientos.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		data: $("#formColorTalla").serialize(),
		dataType: "json",
		success: function (data) {
			if (data.aumentaidpartida) {
				$("#idmovimientopartida").val(
					Number($("#idmovimientopartida").val()) + 1
				);
			}
			// mostrar las tallas y colores del producto seleccionado
			recargarListaProductosM();
		},
	});
}

function recargarListaProductosM() {
	$.ajax({
		type: "POST",
		url: "/modulos/movimientos/listaproductos.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		success: function (data) {
			// mostrar las tallas y colores del producto seleccionado
			$("#listaProductos").html(data);
			limpiarInputs();
			$.fancybox.close();
		},
	});
}

function limpiarInputs() {
	$("#divColorTalla").html("");
	$("#slcProducto").val(0);
	$("#idproducto").val("");
	$("#slcProducto").select2();
}

function isNumber(evt) {
	evt = evt ? evt : window.event;
	var charCode = evt.which ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		return false;
	}
	return true;
}

function tipoMovimiento(value) {
	if (value == "3") {
		$("#divAlmacenSec").show();
	} else {
		$("#divAlmacenSec").hide();
	}

	$("#tipomovimiento").val($("#slcTipoMovimiento").val());
}

function cargarAlmacenes(idalmacen) {
	if ($("#divAlmacenSec").is(":visible")) {
		$.ajax({
			type: "POST",
			url: "/modulos/movimientos/cargaralmacenes.php",
			data: "idalmacen=" + idalmacen,
			success: function (data) {
				datos = data.split("|");
				if (datos[1] == "OK") {
					$("#divAlmacenSec").html(datos[0]);
				}
			},
		});
	}
	$("#almacen").val(idalmacen);
	$("#almacens").val();
}

function validarFormMovimiento() {
	if ($("#slcTipoMovimiento").val() == "2" && $("#txtNotas").val() == "") {
		Swal.fire(
			"ATENCION",
			'Debes especificar el motivo de la salida en "Notas"',
			"error"
		);
	} else {
		// debe haber al menos un producto agregado
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/movimientos.php",
			data:
				"accion=validarmovimiento&tipomovimiento=" +
				$("#tipomovimiento").val() +
				"&idalmacen=" +
				$("#almacen").val(),
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					fancy("/modulos/movimientos/verificarproductos.php");
				} else if (data.respuesta == "ERROR") {
					Swal.fire(data.titulo, data.mensaje, "error");
				}
			},
		});
	}
}

function guardarMovimiento() {
	if (confirm("ATENCIÓN: ¿Deseas continuar?")) {
		$("#formMovimiento #accion").val("agregar");
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/movimientos.php",
			data: $("#formMovimiento").serialize(),
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					console.log("Ir a pantalla movimientos.");
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						location.href = $("#href", "#formMovimiento").val();
					});
				} else if (data.respuesta == "ERROR") {
					Swal.fire(data.titulo, data.mensaje, "error");
				}
			},
		});
	}
}

function recibirProductos() {
	// validar que al menos un input tenga una cantidad escrita
	var almenosuno = false;
	$("input[name*='txtCantidad[]']").each(function (e) {
		if (Number($(this).val()) > 0) {
			almenosuno = true;
		}
	});
	// calcular si se esta recibiendo menos producto de al menos una partida
	var menosproducto = false;
	// validar que ninguna cantidad escrita exceda la de la partida respectiva
	var cantidadesvalidas = true;
	var idmovimientoinventario = 0;
	$("input[name*='cantidadProducto[]']").each(function (e) {
		idmovimientoinventario = $(this).prop("id").split("-")[1];
		if (
			Number($("#txtCantidad-" + idmovimientoinventario).val()) >
			Number($(this).val())
		) {
			cantidadesvalidas = false;
		}
		if (
			Number($("#txtCantidad-" + idmovimientoinventario).val()) <
			Number($(this).val())
		) {
			menosproducto = true;
		}
	});

	if (!almenosuno) {
		alert("ATENCION: Debes ingresar al menos una cantidad");
	} else if (!cantidadesvalidas) {
		alert(
			"ATENCION: Alguna cantidad no es valida o se excede de la cantidad a recibir"
		);
	} else if (menosproducto) {
		if (
			confirm("ATENCION: Se esta recibiendo menos producto. ¿Deseas continuar?")
		) {
			$("#accion").val("recibir");
			enviarFormulario("formRecepcion");
		}
	} else {
		$("#accion").val("recibir");
		enviarFormulario("formRecepcion");
	}
}

function cancelarRecepcion() {
	Swal.fire({
		title: "Cancelar Recepcion",
		type: "info",
		html: "Indica el motivo:<br><textarea id='txtMotivo' class='form-control' placeholder='Motivo' rows='4'></textarea>",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		confirmButtonText: "Aceptar",
		cancelButtonText: "Cancelar",
		preConfirm: () => {
			return validarMotivo($("#txtMotivo").val());
		},
	}).then((result) => {
		if (result.value) {
			$("#formRecepcion #accion").val("cancelar");
			$("#formRecepcion #motivo").val($("#txtMotivo").val());
			enviarFormulario("formRecepcion");
		}
	});
}

function realizarAutorizacion(accion) {
	$("#formRecepcion #accion").val(accion);
	if (accion == "autorizar") {
		if (confirm("ATENCION: Estás seguro de que deseas autorizar?")) {
			$("#formRecepcion #autorizacion").val(2);
			enviarFormulario("formRecepcion");
		}
	} else {
		if (confirm("ATENCION: Estás seguro de que deseas no autorizar?")) {
			$("#formRecepcion #autorizacion").val(1);
			enviarFormulario("formRecepcion");
		}
	}
}
// FUNCIONES MOVIMIENTOS FIN

// FUNCIONES SOLICITUDES INICIO
function validarSolicitudes() {
	if ($("#txtPedido").val() != "" && Number($("#txtPedido").val()) == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar un número de pedido.",
			"error",
			"txtPedido"
		);
	} else {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/solicitudes.php",
			data: "accion=validarsolicitudes&pedido=" + $("#txtPedido").val(),
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					$("#accion").val("generarsolicitudes");
					console.log("submit solicitudes");
					generarSolicitudes();
				} else if (data.respuesta == "ERROR") {
					Swal.fire("Atención", data.mensaje, "error");
				}
			},
		});
	}
}

function asignarIdProductoSolicitud(idproducto) {
	$("#idproducto").val(idproducto);
}

function generarSolicitudes() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/solicitudes.php",
		data: $("#formSolicitud").serialize(),
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
					location.href = $("#href", "#formSolicitud").val();
				});
			} else if (data.respuesta == "ERROR") {
				Swal.fire(data.titulo, data.mensaje, "error");
			}
		},
	});
}

// funcion para la pantalla de solicitudes. cuando se escoge el origen del movimiento, se tienen que mostrar algunas cosas y reiniciar otras
function mostrar(value, value2) {
	if (value == 1) {
		$("#textProducto").hide();
		$("#selectProducto").show();
		// ocultar select de categoria
		$("#divCategoria").hide();
	} else {
		$("#textProducto").show();
		$("#selectProducto").hide();
		// mostrar select de categoria
		$("#divCategoria").show();
	}

	if (value2 == 1) {
		$("#slcCategoria").val(0);
		$("#slcProducto").val(0);
		$("#slcProducto").select2();
		$("#divColorTalla").html("");
	}
}

function cargarColorTallaProductoSolicitud(idproducto, tipo, idpartida) {
	$("#idproducto").val(idproducto);
	$.ajax({
		type: "POST",
		url: "/modulos/solicitudes/cargarcolortalla.php",
		data: "idproducto=" + idproducto + "&idpartida=" + idpartida,
		async: tipo,
		success: function (data) {
			// mostrar las tallas y colores del producto seleccionado
			$("#divColorTalla").html(data);
		},
	});
}

// NOTA: Hay que considerar si esta funcion que carga colores y tallas puede generalizarse, ya que se usaría en solicitudes y pedidos (al menos)
function cargarColorTallaLibreSolicitud(idcategoria, tipo) {
	$("#idproducto").val("");
	$.ajax({
		type: "POST",
		url: "/modulos/solicitudes/cargarcolortalla.php",
		data: "idcategoria=" + idcategoria + "&idpartida=" + $("#idpartida").val(),
		async: tipo,
		success: function (data) {
			// mostrar las tallas y colores del producto seleccionado
			$("#divColorTalla").html(data);
		},
	});
}

function agregarProductoSolicitud() {
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
	if ($("#slcOrigen").val() == "0") {
		swalFocus(
			"ATENCION",
			"ATENCION: Debes indicar el origen.",
			"error",
			"slcOrigen"
		);
	} else if ($("#slcOrigen").val() == "2" && $("#slcCategoria").val() == "0") {
		swalFocus(
			"ATENCION",
			"ATENCION: Debes indicar un producto.",
			"error",
			"slcProducto"
		);
	} else if (
		$("#slcProducto").val() == "0" &&
		$("#slcProducto").is(":visible")
	) {
		swalFocus(
			"ATENCION",
			"ATENCION: Debes indicar un producto.",
			"error",
			"slcProducto"
		);
	} else if (
		$("#txtProducto").val() == "" &&
		$("#txtProducto").is(":visible")
	) {
		swalFocus(
			"ATENCION",
			"ATENCION: Debes indicar un producto.",
			"error",
			"txtProducto"
		);
	} else {
		fancy(
			"/modulos/solicitudes/cargarcolortalla.php?slcCategoria=" +
			$("#slcCategoria").val() +
			"&idproducto=" +
			$("#idproducto").val() +
			"&txtProducto=" +
			encodeURIComponent($("#txtProducto").val()) +
			"&accion=agregarpartida&idsolicitudproducto=" +
			$("#idsolicitudproducto").val()
		);
	}
}

function eliminarProductoSolicitud(idpartida) {
	if ($("#idpartida").val() == idpartida) {
		$("#slcProducto").prop("disabled", false);
		$("#slcCategoria").prop("disabled", false);
		$("#txtProducto").val("");
		$("#divAgregar").show();
		$("#divEditar").hide();
	}
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/solicitudes.php",
		data: "idpartida=" + idpartida + "&accion=eliminarpartida",
		success: function (data) {
			recargarListaProductosS();
		},
	});
}

function cargarDatosSolicitud(idpartida, idproducto) {
	fancy(
		"/modulos/solicitudes/cargarcolortalla.php?slcCategoria=" +
		$("#slcCategoria").val() +
		"&idproducto=" +
		idproducto +
		"&accion=editarpartida&idpartida=" +
		idpartida +
		"&idsolicitudproducto=" +
		idpartida
	);
}

function validarS() {
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
	if (total == 0) {
		Swal.fire("ATENCION", "Debes introducir al menos una cantidad", "warning");
	} else {
		$("#formColorTalla #accion").prop("disabled", false);
		agregarS();
	}
}

function agregarS() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/solicitudes.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		data: $("#formColorTalla").serialize(),
		dataType: "json",
		success: function (data) {
			if (data.aumentaidpartida) {
				$("#idsolicitudproducto").val(
					Number($("#idsolicitudproducto").val()) + 1
				);
			}
			// mostrar las tallas y colores del producto seleccionado
			recargarListaProductosS();
		},
	});
}

function recargarListaProductosS() {
	$.ajax({
		type: "POST",
		url: "/modulos/solicitudes/listaproductos.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		success: function (data) {
			// mostrar las tallas y colores del producto seleccionado
			$("#listaProductos").html(data);
			limpiarInputs();
			$.fancybox.close();
		},
	});
}
// FUNCIONES SOLICITUDES FIN

// FUNCIONES COTIZACIONES INICIO
function borrarDatosCliente() {
	$("#txtCorreo").prop("readonly", false);
	$("#txtTelefono").prop("readonly", false);
	$("#slcCliente").val(0).trigger("change");
	$("#txtCorreo").val("");
	$("#txtTelefono").val("");
	$("#slcCiudad").val(0);
	$("#slcCiudad").prop("disabled", false);
	$("#idcliente").val(0);
	$("#iconBorrar1").addClass("fa-disabled");
	$("#btnBorrar1").prop("disabled", true);
	$("#divGuardarCliente").show();
	$("#slcContacto")
		.find("option")
		.remove()
		.end()
		.append('<option value="0">--Seleccionar--</option>')
		.val("0");
	borrarDatosContacto();
}

function borrarDatosContacto() {
	$("#txtCorreoC").prop("readonly", false);
	$("#txtTelefonoC").prop("readonly", false);
	$("#slcContacto").val(0).trigger("change");
	$("#txtCorreoC").val("");
	$("#txtTelefonoC").val("");
	$("#txtPuesto").val("");
	$("#idcontacto").val(0);
	$("#iconBorrar2").addClass("fa-disabled");
	$("#btnBorrar2").prop("disabled", true);
}

function seleccionarCliente(select) {
	if (esClienteNuevo(select.value)) {
		console.log("Nuevo");
		$("#idcliente").val(0);
		$("#txtNombre").val("");
		$("#txtNombre").prop("readonly", false);
		$("#txtTelefono").val("");
		$("#txtTelefono").prop("readonly", false);
		$("#txtCorreo").val("");
		$("#txtCorreo").prop("readonly", false);
		$("#slcCiudad").val(0);
		$("#slcCiudad").prop("disabled", false);
		$("#slcTienda").val(0);
		$("#slcTienda").prop("disabled", false);
		$("#slcContacto")
			.find("option")
			.remove()
			.end()
			.append('<option value="0">--Seleccionar--</option>')
			.val("0");
		$("#divGuardarCliente").show();
	} else {
		console.log("Existente");
		// se llenan los datos del cliente
		$("#idcliente").val($(select).val());
		$("#txtCorreo").val($(select).find(":selected").data("correo"));
		$("#txtCorreo").attr("readonly", "true");
		$("#txtTelefono").val($(select).find(":selected").data("telefono"));
		$("#txtTelefono").attr("readonly", "true");
		$("#slcCiudad").val($(select).find(":selected").data("idciudad"));
		$("#slcCiudad").prop("disabled", true);
		$("#slcTienda").val($(select).find(":selected").data("idtienda"));
		$("#slcTienda").prop("disabled", true);
		$("#iconBorrar1").removeClass("fa-disabled");
		$("#btnBorrar1").prop("disabled", false);

		// se deben borrar los datos de contacto
		cargarListaContactos($(select).val());

		if ($("#idcontacto").val() != "0" && first == 0) {
			$("#slcContacto").val($("#idcontacto").val()).trigger("change");
			first = 1;
		} else {
			$("#slcContacto").val(0).trigger("change");
		}

		// se quita el checkbox de no guardar datos del cliente, ya que es un cliente registrado
		$("#chkNoGuardarCliente").prop("checked", false);
		$("#divGuardarCliente").hide();
	}

	$("#txtCorreoC").val("");
	$("#txtCorreoC").prop("readonly", false);
	$("#txtTelefonoC").val("");
	$("#txtTelefonoC").prop("readonly", false);
	$("#txtPuesto").val("");
	$("#txtPuesto").prop("readonly", false);
	$("#iconBorrar2").addClass("fa-disabled");
	$("#btnBorrar2").prop("disabled", true);
}

function esClienteNuevo(idcliente) {
	var correcto = true;
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/clientes.php",
		data: "accion=buscarcliente&idcliente=" + idcliente,
		dataType: "json",
		async: false,
		success: function (data) {
			if (data.respuesta == "OK") {
				correcto = false;
			}
		},
	});
	return correcto;
}

function cargarListaContactos(idcliente) {
	$.ajax({
		type: "POST",
		url: "/modulos/cotizaciones/listacontactos.php",
		data: "idcliente=" + idcliente,
		async: false,
		success: function (data) {
			$("#divContacto").html(data);
			if (modulo2 == "convertir") {
				$("#slcContacto").prop("disabled", true);
			}
		},
	});
}

function opcionesFiltroClientes(opcion) {
	if (opcion >= 3) {
		$("#txtFecha").prop("disabled", false);
	} else {
		$("#txtFecha").val("");
		$("#txtFecha").prop("disabled", true);
	}
}

function buscarCliente() {
	$.ajax({
		type: "POST",
		url: "/modulos/cotizaciones/listaclientes.php",
		data: $("#formBusqueda").serialize(),
		success: function (data) {
			$("#listaClientes").html(data);
		},
	});
}

function borrarCliente() {
	$("#txtNombre").val("");
	buscarCliente();
}

function buscarContactos() {
	if ($("#idcliente").val() == 0) {
		Swal.fire("ATENCION", "Debes seleccionar un cliente existente.", "error");
	} else {
		fancy(
			"/modulos/cotizaciones/buscarcontacto.php?idcliente=" +
			$("#idcliente").val()
		);
	}
}

function seleccionarContacto(select) {
	if (esContactoNuevo(select.value)) {
		$("#idcontacto").val(0);
		$("#txtCorreoC").val("");
		$("#txtCorreoC").attr("readonly", false);
		$("#txtTelefonoC").val("");
		$("#txtTelefonoC").attr("readonly", false);
		$("#txtPuesto").val("");
		$("#txtPuesto").attr("readonly", false);
	} else {
		$("#idcontacto").val(select.value);
		$("#txtCorreoC").val($(select).find(":selected").data("correo"));
		$("#txtCorreoC").attr("readonly", true);
		$("#txtTelefonoC").val($(select).find(":selected").data("telefono"));
		$("#txtTelefonoC").attr("readonly", true);
		$("#txtPuesto").val($(select).find(":selected").data("puesto"));
		$("#txtPuesto").attr("readonly", true);
	}
	$("#iconBorrar2").removeClass("fa-disabled");
	$("#btnBorrar2").prop("disabled", false);
}

function esContactoNuevo(idcontacto) {
	var correcto = true;
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/contactos.php",
		data: "accion=buscarcontacto&idcontacto=" + idcontacto,
		dataType: "json",
		async: false,
		success: function (data) {
			if (data.respuesta == "OK") {
				correcto = false;
			}
		},
	});
	return correcto;
}

function mostrar2(value) {
	if (value == 2) {
		$("#textProducto").show();
		$("#selectProducto").hide();
		$("#selectTipoProducto").show();
		$("#slcCategoria").prop("disabled", false);
		// Reducir tamaño del precio unitario para que el botón no salte de línea
		$("#divPrecio").removeClass("col-md-2").addClass("col-md-1");
	} else {
		$("#selectProducto").show();
		$("#textProducto").hide();
		$("#selectTipoProducto").hide();
		$("#slcCategoria").prop("disabled", true);
		// Restaurar tamaño original del precio unitario
		$("#divPrecio").removeClass("col-md-1").addClass("col-md-2");
	}
}

function agregarProductoCotizacion() {
	if (Number($("#txtCantidad").val()) == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar una Cantidad.",
			"error",
			"txtCantidad"
		);
	} else if ($("#slcOrigen").val() == 0) {
		swalFocus("ATENCION", "Debes indicar un Origen.", "error", "slcOrigen");
	} else if ($("#slcOrigen").val() == 1 && $("#slcProducto").val() == 0) {
		swalFocus("ATENCION", "Debes indicar un Producto.", "error", "slcProducto");
	} else if ($("#slcOrigen").val() == 2 && $("#slcCategoria").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar una Categoria.",
			"error",
			"slcCategoria"
		);
	} else if ($("#slcOrigen").val() == 2 && $("#slcTipoProducto").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar un Tipo de Producto.",
			"error",
			"slcTipoProducto"
		);
	} else if ($("#slcOrigen").val() == 2 && $("#txtProducto").val() == "") {
		swalFocus("ATENCION", "Debes indicar un Producto.", "error", "txtProducto");
	} else if (
		!$("#txtPrecio").prop("disabled") &&
		Number($("#txtPrecio").val()) == 0
	) {
		swalFocus("ATENCION", "Debes indicar un Precio.", "error", "txtPrecio");
	} else {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data:
				"accion=validarnombreunico&nombreproducto=" +
				($("#txtProducto").is(":visible") ? $("#txtProducto").val() : ""),
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					fancy(
						"/modulos/cotizaciones/indicarespecificaciones.php?accion=agregarpartida&idproducto=" +
						$("#slcProducto").val() +
						($("#txtProducto").val() != ""
							? "&producto=" + encodeURIComponent($("#txtProducto").val())
							: "") +
						"&idcategoriaproducto=" +
						$("#slcCategoria").val() +
						"&idtipoproducto=" +
						$("#slcTipoProducto").val() +
						"&cantidad=" +
						$("#txtCantidad").val() +
						"&precio=" +
						$("#txtPrecio").val()
					);
				} else {
					Swal.fire(data.titulo, data.mensaje, "error");
				}
			},
		});
	}
}

function editarProductoCotizacion() {
	if (Number($("#txtCantidad").val()) == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar una Cantidad.",
			"error",
			"txtCantidad"
		);
	} else if ($("#slcOrigen").val() == 0) {
		swalFocus("ATENCION", "Debes indicar un Origen.", "error", "slcOrigen");
	} else if ($("#slcOrigen").val() == 1 && $("#slcProducto").val() == 0) {
		swalFocus("ATENCION", "Debes indicar un Producto.", "error", "slcProducto");
	} else if ($("#slcOrigen").val() == 2 && $("#slcCategoria").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar una Categoria.",
			"error",
			"slcCategoria"
		);
	} else if ($("#slcOrigen").val() == 2 && $("#slcTipoProducto").val() == 0) {
		swalFocus(
			"ATENCION",
			"Debes indicar un Tipo de Producto.",
			"error",
			"slcTipoProducto"
		);
	} else if ($("#slcOrigen").val() == 2 && $("#txtProducto").val() == "") {
		swalFocus("ATENCION", "Debes indicar un Producto.", "error", "txtProducto");
	} else if (
		!$("#txtPrecio").prop("disabled") &&
		Number($("#txtPrecio").val()) == 0
	) {
		swalFocus("ATENCION", "Debes indicar un Precio.", "error", "txtPrecio");
	} else {
		// conseguir las especificaciones que se pusieron en esta partida
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data:
				"accion=recuperarespecificaciones&idpartida=" + $("#idpartida").val(),
			dataType: "json",
			success: function (data) {
				fancy(
					"/modulos/cotizaciones/indicarespecificaciones.php?accion=editarpartida&idpartida=" +
					data.idpartida +
					"&idproducto=" +
					$("#idproducto").val() +
					"&producto=" +
					encodeURIComponent($("#txtProducto").val()) +
					"&idcategoriaproducto=" +
					$("#slcCategoria").val() +
					"&idtipoproducto=" +
					($("#slcTipoProducto").val() ?? "0") +
					"&cantidad=" +
					$("#txtCantidad").val() +
					"&precio=" +
					$("#txtPrecio").val() +
					"&serigrafia1=" +
					data.serigrafia1 +
					"&serigrafia2=" +
					data.serigrafia2 +
					"&serigrafia3=" +
					data.serigrafia3 +
					"&personalizadonumero=" +
					data.personalizadonumero +
					"&personalizadonombre=" +
					data.personalizadonombre +
					"&bordado1=" +
					data.bordado1 +
					"&bordado2=" +
					data.bordado2 +
					"&bordado3=" +
					data.bordado3 +
					"&bordado4=" +
					data.bordado4 +
					"&bordadoespecial=" +
					data.bordadoespecial +
					"&personalizado1linea=" +
					data.personalizado1linea +
					"&personalizado2lineas=" +
					data.personalizado2lineas +
					"&personalizado3lineas=" +
					data.personalizado3lineas +
					"&sxl=" +
					data.sxl +
					"&xl2=" +
					data.xl2 +
					"&xl3=" +
					data.xl3 +
					"&observaciones=" +
					encodeURIComponent(data.observaciones)
				);
			},
		});
	}
}

function cargarDatosProducto(idpartida) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		data: "accion=cargardatospartida&idpartida=" + idpartida,
		dataType: "json",
		success: function (data) {
			var idproducto = data.idproducto == 0 ? "" : data.idproducto;
			var origen = data.idproducto == 0 ? "2" : "1";
			$("#idproducto").val(idproducto);
			$("#idpartida").val(data.idtmp);
			$("#txtCantidad").val(data.cantidad);
			$("#slcOrigen").val(origen);
			$("#slcOrigen").prop("disabled", true);
			$("#slcProducto").val(data.idproducto);
			$("#slcProducto").prop("disabled", true);
			$("#txtProducto").val(data.producto);
			$("#txtPrecio").val(data.precio);
			$("#slcCategoria").val(data.idcategoriaproducto);
			$("#slcTipoProducto").val(data.idtipoproducto ?? "0");

			$("#slcProducto").select2();
			$("#slcProducto").val(data.idproducto);
			$("#slcTipoProducto").select2();
			mostrar2($("#slcOrigen").val());
			$("#txtCantidad").focus();

			// ocultar boton agregar y mostrar boton editar
			$("#divAgregar").hide();
			$("#divEditar").show();
		},
	});
}

function eliminarProductoCotizacion(idpartida) {
	// si se elimina el mismo producto que estaba por editarse, las opciones deben editarse
	if (confirm("ATENCION: Deseas eliminar la partida?")) {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data: "accion=eliminarpartida&idpartida=" + idpartida,
			dataType: "json",
			success: function (data) {
				cargarDatosContenedor(data.formulario);
				// si la partida que se esta eliminando es la misma que la seleccionada en la edicion, se reinician los inputs
				if (idpartida == $("#idpartida").val()) {
					$("#slcOrigen").val(0);
					$("#slcCategoria").val(0);
					$("#slcOrigen").prop("disabled", false);
					$("#slcProducto").val(0);
					$("#slcProducto").prop("disabled", false);
					$("#txtProducto").val("");
					$("#txtCantidad").val("");
					$("#txtPrecio").val("");
					$("#idpartida").val("");
					$("#idproducto").val("");

					mostrar2($("#slcOrigen").val());
					$("#divAgregar").show();
					$("#divEditar").hide();
				}
			},
		});
	}
}

function validarFormCotizacion() {
	if (Number($("#total").val()) == 0) {
		swalFocus(
			"ATENCION",
			"Debes agregar productos a la cotización.",
			"error",
			"txtCantidad"
		);
	} else if ($("#slcSucursal").val() == "") {
		swalFocus(
			"ATENCION",
			"Debes seleccionar una sucursal.",
			"error",
			"slcSucursal"
		);
	} else if ($("#slcVigencia").val() == "0") {
		swalFocus(
			"ATENCION",
			"Debes seleccionar una vigencia.",
			"error",
			"slcVigencia"
		);
	} else if (!$("#txtCorreo").is("[readonly]")) {
		if ($("#slcCliente").val() == "0") {
			swalFocus("ATENCION", "Debes indicar un cliente.", "error", "slcCliente");
		} else if (
			$("#txtTelefono").val() == "" &&
			$("#txtTelefonoC").val() == ""
		) {
			swalFocus(
				"ATENCION",
				"Debes indicar al menos un teléfono.",
				"error",
				"txtTelefono"
			);
		} else if (
			correoRepetido($("#txtCorreo").val()) &&
			$("#txtCorreo").val() != ""
		) {
			if (
				confirm(
					"Se ha detectado un cliente con datos similares, ¿desea continuar?"
				)
			) {
				enviarFormulario("formCotizacion");
			}
		} else {
			enviarFormulario("formCotizacion");
		}
	} else {
		enviarFormulario("formCotizacion");
	}
}

function correoRepetido(correo) {
	var repetido = false;
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		data: "accion=validarcorreorepetido&correo=" + correo,
		async: false,
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "REPETIDO") {
				repetido = true;
			}
		},
	});
	return repetido;
}

function cargarDatosCotizacion(idcotizacion) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		data: "accion=cargardatoscotizacion&idcotizacion=" + idcotizacion,
		async: false,
		dataType: "json",
		success: function (data) {
			$("#idcliente").val(data.idcliente);
			$("#idcontacto").val(data.idcontacto);
			$("#slcCliente").val(data.idcliente).trigger("change");
			$("#txtCorreo").val(data.correocliente);
			$("#txtCorreo").attr("readonly", "true");
			$("#txtTelefono").val(data.telefonocliente);
			$("#txtTelefono").attr("readonly", "true");
			$("#slcCiudad").val(data.idciudad);
			$("#slcCiudad").prop("disabled", true);
			$("#iconBorrar1").removeClass("fa-disabled");
			$("#btnBorrar1").prop("disabled", false);
			$("#txtCorreoC").val(data.correocontacto);
			$("#txtCorreoC").attr("readonly", "true");
			$("#txtTelefonoC").val(data.telefonocontacto);
			$("#txtTelefonoC").attr("readonly", "true");
			$("#txtPuesto").val(data.puesto);
			$("#txtPuesto").attr("readonly", "true");
			$("#iconBorrar2").removeClass("fa-disabled");
			$("#btnBorrar2").prop("disabled", false);
			$("#chkNoGuardarCliente").prop("checked", false);
			$("#divGuardarCliente").hide();

			data.incluyeiva == 1
				? $("#chkIncluyeIva").prop("checked", true)
				: $("#chkIncluyeIva").prop("checked", false);
			data.subtotalizar == 1
				? $("#chkSubtotalizar").prop("checked", true)
				: $("#chkSubtotalizar").prop("checked", false);
			$("#slcIVA").val(data.tasaiva);
			$("#slcSucursal").val(data.idsucursal);
			$("#slcVigencia").val(data.idvigencia);
			$("#txtLinea1").val(data.linea1);
			$("#txtLinea2").val(data.linea2);
			$("#txtLinea3").val(data.linea3);
			$("#txtLinea4").val(data.linea4);
			$("#txtLinea5").val(data.linea5);
			$("#divTotalizacion").show();
		},
	});
}

function validarC(idpartida) {
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
	console.log("idpartida: " + $("#idpartida").val() + " total: " + total);
	console.log("idpartida: " + idpartida + " total: " + total);
	if (total == 0) {
		Swal.fire("ATENCION", "debes introducir al menos una cantidad", "warning");
	} else {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data:
				"accion=validarcantidades&idpartida=" +
				idpartida +
				"&cantidad=" +
				total,
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					agregarC();
				} else {
					Swal.fire(data.titulo, data.mensaje, "error");
					console.log(data.dato);
				}
			},
		});
	}
}

function agregarC() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		data: $("#formColorTalla").serialize(),
		success: function (data) {
			recargarListaProductosC();
		},
	});
}

function recargarListaProductosC() {
	$.ajax({
		type: "POST",
		url: "/modulos/cotizaciones/listaproductosconvertir.php",
		// agregar el form completo para enviar todos los inputs de la tabla
		data: $("#formCotizacion").serialize(),
		success: function (data) {
			// mostrar las tallas y colores del producto seleccionado
			$("#listaProductos").html(data);
			$.fancybox.close();
		},
	});
}

// se van a insertar imagenes
function agregarEspecificacion() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		data: new FormData($("#formEspecificacion")[0]),
		processData: false,
		contentType: false,
		dataType: "json",
		success: function (data) {
			var img1 = $("input[name=img1]").val().split("\\").pop();
			var img2 = $("input[name=img2]").val().split("\\").pop();
			var img3 = $("input[name=img3]").val().split("\\").pop();
			var img4 = $("input[name=img4]").val().split("\\").pop();
			var img5 = $("input[name=img5]").val().split("\\").pop();

			var chk1 = $("#chkSerigrafia").prop("checked")
				? $("#chkSerigrafia").val()
				: "";
			var chk2 = $("#chkBordado").prop("checked") ? $("#chkBordado").val() : "";
			var chk3 = $("#chkDigital").prop("checked") ? $("#chkDigital").val() : "";

			var rd1 = $("input[name=rdRequiereProduccion]").prop("checked")
				? $("input[name=rdRequiereProduccion]:checked").val()
				: "";
			var rd2 = $("input[name=rdRequiereDiseno]").prop("checked")
				? $("input[name=rdRequiereDiseno]:checked").val()
				: "";
			var rd3 = $("input[name=rdRequiereAprobacionDiseno1]").prop("checked")
				? $("#rdRequiereAprobacionDiseno").val()
				: "";

			var datos =
				"txtNombre=" +
				encodeURIComponent($("#txtNombre").val()) +
				"&txtFecha=" +
				$("#txtFecha").val() +
				"&chkSerigrafia=" +
				chk1 +
				"&chkBordado=" +
				chk2 +
				"&chkDigital=" +
				chk3 +
				"&txtEspecificaciones=" +
				encodeURIComponent($("#txtEspecificaciones").val()) +
				"&rdRequiereProduccion=" +
				rd1 +
				"&rdRequiereDiseno=" +
				rd2 +
				"&rdRequiereAprobacionDiseno=" +
				rd3 +
				"&imagen1=" +
				img1 +
				"&imagen2=" +
				img2 +
				"&imagen3=" +
				img3 +
				"&imagen4=" +
				img4 +
				"&imagen5=" +
				img5;

			console.log(
				"idespecificacion=" + $("#idespecificacion").val() + "&" + datos
			);

			if ($("#idespecificacion", "#formEspecificacion").val() > 0) {
				fancy(
					"/modulos/cotizaciones/reasignardesglose.php?idespecificacion=" +
					$("#idespecificacion").val() +
					"&" +
					datos
				);
			} else {
				fancy("/modulos/cotizaciones/asignardesglose.php?" + datos);
			}
		},
	});
}

function actualizarRequiereAprobacion(val) {
	if (val == 0) {
		$(".rdAP").prop("disabled", true);
		$(".rdAP").prop("checked", false);
		$("#rdRequiereAprobacionDiseno").val(val);
	} else {
		$(".rdAP").prop("disabled", false);
		$("#rdRequiereAprobacionDiseno").val(
			$("#rdRequiereAprobacionDiseno1").val()
		);
	}
}

function actualizarValor(val) {
	$("#rdRequiereAprobacionDiseno").val(val);
}

function asignarDesglose() {
	if (Number($("input[type=checkbox]:checked", "#formDesglose").length) == 0) {
		Swal.fire("ATENCION", "Debes indicar al menos una partida.", "error");
	} else {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data: $("#formDesglose").serialize(),
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					console.log("desgloses asignados");
					$.fancybox.close();
					mostrarEspecificaciones();
					// NOTA: tambien recargar la lista de partidas para que se pueda determinar si sigue apareciendo o no el boton de desglosar
					recargarListaProductosC();
				} else {
					Swal.fire(data.titulo, data.mensaje, "error");
				}
			},
		});
	}
}

function mostrarEspecificaciones() {
	$.ajax({
		type: "POST",
		url: "/modulos/cotizaciones/listaespecificaciones.php",
		success: function (data) {
			$("#listaEspecificaciones").html(data);
		},
	});
}

function reasignarDesgloses(idespecificacion) {
	fancy(
		"/modulos/cotizaciones/reasignardesglose.php?idespecificacion=" +
		idespecificacion
	);
}

function eliminarEspecificacion(idespecificacion) {
	if (confirm("ATENCION: Deseas eliminar la especificacion?")) {
		$.ajax({
			type: "POST",
			url: "/assets/php/controladores/cotizaciones.php",
			data:
				"accion=eliminarespecificacion&idespecificacion=" + idespecificacion,
			dataType: "json",
			success: function (data) {
				if (data.respuesta == "OK") {
					mostrarEspecificaciones();
					recargarListaProductosC();
				}
			},
		});
	}
}

function validarFormCotizacionConvertir() {
	// NOTA: ¿se valida que todas las partidas hayan sido desglosadas completamente?
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/cotizaciones.php",
		data: "accion=validardesgloses",
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				// se valida que haya al menos una especificacion agregada y que todos los desgloses estén asignados
				$.ajax({
					type: "POST",
					url: "/assets/php/controladores/cotizaciones.php",
					data: "accion=validarespecificaciones",
					dataType: "json",
					success: function (data) {
						if (data.respuesta == "OK") {
							indicarMovimientosInventario();
						} else {
							Swal.fire({
								title: "Atención",
								text: "No tienes especificaciones creadas. ¿Deseas continuar?",
								icon: "warning",
								showCancelButton: true,
								confirmButtonColor: "#3085d6",
								cancelButtonColor: "#d33",
								confirmButtonText: "Sí",
								cancelButtonText: "No",
							}).then((result) => {
								if (result.value) {
									indicarMovimientosInventario();
								}
							});
						}
					},
				});
			} else {
				Swal.fire(data.titulo, data.mensaje, "error");
			}
		},
	});
}

function indicarMovimientosInventario() {
	fancy(
		"/modulos/cotizaciones/indicarmovimientosinventario.php?idsucursal=" +
		$("#slcSucursal").val()
	);
}
// FUNCIONES COTIZACIONES FIN

// FUNCIONES PEDIDOS INICIO
function validarFormPedido() {
	// NOTA: ¿se valida que todas las partidas desglosables hayan sido desglosadas completamente?
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/pedidos.php",
		data: "accion=validardesgloses",
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				// se valida que haya al menos una especificacion agregada y que todos los desgloses estén asignados
				$.ajax({
					type: "POST",
					url: "/assets/php/controladores/pedidos.php",
					data: "accion=validarespecificaciones",
					dataType: "json",
					success: function (data) {
						if (data.respuesta == "OK") {
							indicarMovimientosInventario();
						} else {
							Swal.fire({
								title: "Atención",
								text: "No tienes especificaciones creadas. ¿Deseas continuar?",
								icon: "warning",
								showCancelButton: true,
								confirmButtonColor: "#3085d6",
								cancelButtonColor: "#d33",
								confirmButtonText: "Sí",
								cancelButtonText: "No",
							}).then((result) => {
								if (result.value) {
									indicarMovimientosInventario();
								}
							});
						}
					},
				});
			} else {
				Swal.fire(data.titulo, data.mensaje, "error");
			}
		},
	});
}

// NOTA: al seleccionar el pedido interno, los productos agregados deben dejar de considerar precio
function validarPedidoInterno() {
	if ($("#chkPedidoInterno").prop("checked")) {
		$("#slcIva").val("8");
		$("#slcIva").prop("disabled", true);
		$("#chkIncluyeIVA").prop("checked", false);
		$("#chkIncluyeIVA").prop("disabled", true);
		$("#txtPrecio").val("");
		$("#txtPrecio").prop("disabled", true);
		$("#productos").val();
		actualizarPrecioProductos();
		// precios reiniciados
	} else {
		$("#slcIva").prop("disabled", false);
		$("#chkIncluyeIVA").prop("disabled", false);
		$("#txtPrecio").prop("disabled", false);
		$("#productos").val();
	}
}

function cargarDatosPedido(idpedido) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/pedidos.php",
		data: "accion=cargardatospedido&idpedido=" + idpedido,
		async: false,
		dataType: "json",
		success: function (data) {
			$("#idcliente").val(data.idcliente);
			$("#idcontacto").val(data.idcontacto);

			data.incluyeiva == 1
				? $("#chkIncluyeIva").prop("checked", true)
				: $("#chkIncluyeIva").prop("checked", false);
			data.total == 0
				? $("#chkPedidoInterno").prop("checked", true)
				: $("#chkPedidoInterno").prop("checked", false);
			$("#slcIVA").val(data.tasaiva);
			$("#slcSucursal").val(data.idsucursal);
			$("#slcVigencia").val(data.idvigencia);
			$("#txtLinea1").val(data.linea1);
			$("#txtLinea2").val(data.linea2);
			$("#txtLinea3").val(data.linea3);
			$("#txtLinea4").val(data.linea4);
			$("#txtLinea5").val(data.linea5);
		},
	});
}

function actualizarPrecioProductos() {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/pedidos.php",
		data: "accion=reiniciarprecioproductos",
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				recargarListaProductosC();
			} else {
				console.log("Error actualizacion precios.");
			}
		},
	});
}
// FUNCIONES PEDIDOS FIN

function fancy(source) {
	$.fancybox.open({
		src: source,
		type: "ajax",
		opts: {
			closeExisting: true,
		},
	});
}

function cambiarPagina(pagina) {
	$("#pagina", "#formBusqueda").val(pagina);
	cargarDatosContenedor("formBusqueda");
}

function cargarDatosPestana(val, val2 = 0) {
	const archivos = [
		"general",
		"contactos",
		"razones",
		"cotizaciones",
		"pedidos",
		"seguimiento",
		"tareas",
		"editar",
		"asignaralmacenes",
		"asignarcoloresytallas",
		"asignarcatalogos",
		"variantesprecio",
	];
	const rutas = ["clientes/detalle/", "productos/masopciones/"];
	const datos = [
		"idcliente=" + $("#formDetalle #idcliente").val(),
		"idproducto=" + $("#idproducto").val(),
	];
	console.log(
		"idcliente: " +
		$("#formDetalle #idcliente").val() +
		" archivo: " +
		archivos[val]
	);
	$(".nav-link").removeClass("active");
	$("#" + archivos[val]).toggleClass("active");
	$.ajax({
		type: "POST",
		url: "/modulos/" + rutas[val2] + archivos[val] + ".php",
		data: datos[val2],
		success: function (data) {
			$("#divArchivo").html(data);
		},
	});
}

function cargarRazonSocial(idrazonsocial) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/clientes.php",
		data: "accion=cargarrazonsocial&idrazonsocial=" + idrazonsocial,
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				$("#formRazones").show();
				$("#txtRazonSocial").val(data.razon.razon_social);
				$("#txtRFC").val(data.razon.rfc);
				$("#slcUsoCFDI").val(data.razon.usocfdi);
				$("#slcRegimenFiscal").val(data.razon.regimenfiscal);
				$("#txtCodigoPostal").val(data.razon.codigo_postal);
				$("#accion").val("editarrazon");
				$("#idrazonsocial").val(idrazonsocial);
				$("#btnAgregarR").hide();
			} else {
				Swal.fire(
					"ERROR",
					"ocurrió un error al recuperar la razon social.",
					"error"
				);
			}
		},
	});
}

function cargarContacto(idcontacto) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/contactos.php",
		data: "accion=cargar&idcontacto=" + idcontacto,
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				$("#formContactos").show();
				$("#txtContacto").val(data.contacto.nombre);
				$("#txtPuesto").val(data.contacto.puesto);
				$("#txtCorreo").val(data.contacto.correo);
				$("#txtTelefono").val(data.contacto.telefono);
				$("#accion").val("editar");
				$("#idcontacto").val(idcontacto);
				$("#btnAgregarC").hide();
			} else {
				Swal.fire(
					"ERROR",
					"ocurrió un error al recuperar el contacto.",
					"error"
				);
			}
		},
	});
}

function cancelarFormulario(formulario, btn) {
	$("#" + formulario).trigger("reset");
	$("#" + formulario).toggle();
	$("#" + btn).show();
}

function toggleDiv(div, btn) {
	$("#" + div).toggle();
	$("#" + btn).toggle();
}

function mostrarFormularioRespuestaSeguimiento(div, btn, idseguimiento) {
	$("#" + div).show();
	$("#" + btn).hide();
	$("#idseguimiento").val(idseguimiento);
}

function cargarSeguimiento(idseguimiento) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/clientes.php",
		data: "accion=cargarseguimiento&idseguimiento=" + idseguimiento,
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				console.log("cargarseguimiento");
				$("#formSeguimiento").show();
				$("#txtTitulo").val(data.razon.razon_social);
				$("#txtComentarios").val(data.razon.rfc);
				$("#txtFecha").val(data.razon.usocfdi);
				$("#slcContacto").val(data.razon.regimenfiscal);
				$("#slcRazon").val(data.razon.codigo_postal);
				$("#accion").val("agregarcomentarioseguimiento");
				$("#idseguimiento").val(idseguimiento);
			} else {
				Swal.fire(
					"ERROR",
					"ocurrió un error al recuperar el seguimiento.",
					"error"
				);
			}
		},
	});
}

function cargarTarea(idtarea) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/clientes.php",
		data: "accion=cargartarea&idtarea=" + idtarea,
		dataType: "json",
		success: function (data) {
			if (data.respuesta == "OK") {
				console.log("cargarseguimiento");
				$("#formTareas2").show();
				$("#txtTitulo").val(data.tarea.titulo);
				$("#txtComentarios").val(data.tarea.comentarios);
				$("#txtFecha").val(data.tarea.fecha.substring(0, 10));
				$("#idcliente").val(data.tarea.idcliente);
				$("#idtarea").val(idtarea);
			} else {
				Swal.fire(
					"ERROR",
					"ocurrió un error al recuperar el recordatorio.",
					"error"
				);
			}
		},
	});
}

function mostrarInfoDiv(id, div) {
	$("#" + div + id).toggle();
}

// FUNCIONES ADICIONALES PRODUCTOS INICIO
function agregarColor(idcolor) {
	console.log("idcolor: " + idcolor);

	var value = idcolor;
	var datos = idcolor.split("-");

	$("#slcColorP option[value='" + idcolor + "']").attr("disabled", "disabled");

	var idcolor = datos[0];
	var nombre = datos[1];

	var rowCount = $("#tablaColores tr").length;

	console.log("valor seleccionado: " + value);

	// agregar el elemento a la tabla
	$("#tablaColores > tbody:last-child").append(
		'\
	<tr id="color' +
		rowCount +
		'">\
		<input type="hidden" name="idcolores[]" value="' +
		idcolor +
		'">\
		<td class="align-middle text-right">' +
		nombre +
		'</td>\
		<td align="right">\
			<a href="javascript:;" onclick="eliminarColor(' +
		rowCount +
		",'" +
		value +
		'\');" ><i class="uil uil-times"></i></a>\
		</td>\
	</tr>\
	'
	);

	$("#slcColorP").val($("#slcColorP option:first").val());
	$("#slcColorP").select2();

	$("#tablaColores thead").show();
}

function agregarTalla(idtalla) {
	console.log("idtalla: " + idtalla);

	var value = idtalla;
	var datos = idtalla.split("-");

	$("#slcTallaP option[value='" + idtalla + "']").attr("disabled", "disabled");

	var idtalla = datos[0];
	var nombre = datos[1];

	var rowCount = $("#tablaTallas tr").length;

	console.log("valor seleccionado: " + value);

	// agregar el elemento a la tabla
	$("#tablaTallas > tbody:last-child").append(
		'\
	<tr id="talla' +
		rowCount +
		'">\
		<input type="hidden" name="idtallas[]" value="' +
		idtalla +
		'">\
		<td class="align-middle text-right">' +
		nombre +
		'</td>\
		<td align="right">\
			<a href="javascript:;" onclick="eliminarTalla(' +
		rowCount +
		",'" +
		value +
		'\');" ><i class="uil uil-times"></i></a>\
		</td>\
	</tr>\
	'
	);

	$("#slcTallaP").val($("#slcTallaP option:first").val());
	$("#slcTallaP").select2();

	$("#tablaTallas thead").show();
}

function eliminarColor(idcolor, value) {
	console.log("idcolor eliminado: " + idcolor);

	$("#color" + idcolor).remove();

	$("#slcColorP option[value='" + value + "']").removeAttr("disabled");
	$("#slcColorP").select2();

	if ($("#tablaColores tr").length == 1) {
		$("#tablaColores thead").hide();
	}
}

function eliminarTalla(idtalla, value) {
	console.log("idtalla eliminada: " + idtalla);
	console.log("value: " + value);

	$("#talla" + idtalla).remove();

	$("#slcTallaP option[value='" + value + "']").removeAttr("disabled");
	$("#slcTallaP").select2();

	if ($("#tablaTallas tr").length == 1) {
		$("#tablaTallas thead").hide();
	}
}

function precargarDatosSAT(){
	var option;

	$('#slcUnidadMedida').empty();
	$('#slcProductoServicio').empty();

	if($("#slcTipoProducto option:selected").data("idunidadmedida")>0){
		option = new Option($("#slcTipoProducto option:selected").data("unidadmedida"), $("#slcTipoProducto option:selected").data("idunidadmedida"), true, true);
		$('#slcUnidadMedida').append(option).trigger('change');
	}else{
		option = new Option("--Selecciona la unidad de medida--", 0, true, true);
		$('#slcUnidadMedida').append(option).trigger('change');
	}

	if($("#slcTipoProducto option:selected").data("idproductoservicio")>0){
		option = new Option($("#slcTipoProducto option:selected").data("productoservicio"), $("#slcTipoProducto option:selected").data("idproductoservicio"), true, true);
		$('#slcProductoServicio').append(option).trigger('change');
	}else{
		option = new Option("--Selecciona el producto/servicio--", 0, true, true);
		$('#slcProductoServicio').append(option).trigger('change');
	}
}
// FUNCIONES ADICIONALES PRODUCTOS FIN

// FUNCIONES ADICIONALES PRODUCCION INICIO
function asignarProductosProduccion() {
	var asignar = false;
	$(".txtCantidad").each(function () {
		if (
			Number($(this).val()) > 0 &&
			Number($(this).val()) <= Number($(this).data("maximo"))
		) {
			asignar = true;
		}
	});
	if (asignar) {
		$("#btnAsignar").prop("disabled", true);
		validarFormulario("formAsignar");
	} else {
		alert(
			"ATENCION: Debes asignar al menos un producto para continuar y las cantidades no deben superar el restante."
		);
	}
}
// FUNCIONES ADICIONALES PRODUCCION FIN

function cambiarAccionFormulario2(formulario, accion) {
	if ($(".chkProducto:checkbox:checked").length) {
		$("#accion", "#" + formulario).val(accion);
		validarFormulario(formulario);
	}
}

function jsonToStr(json) {
	var str = "";
	for (var key in json) {
		if (str != "") {
			str += "&";
		}
		str += key + "=" + encodeURIComponent(json[key]);
	}
	return str;
}
