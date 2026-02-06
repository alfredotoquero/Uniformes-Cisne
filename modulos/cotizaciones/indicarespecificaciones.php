<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Indicar Especificaciones</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="cotizaciones">
        <!-- accion = agregarpartida o editarpartida -->
        <input type="hidden" name="accion" id="accion" value="<?= $_GET["accion"] ?>">
        <input type="hidden" name="idpartida" id="idpartida" value="<?= $_GET["idpartida"] ?>">
        <!-- datos de la pantalla anterior -->
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
        <input type="hidden" name="producto" id="producto" value="<?= $_GET["producto"] ?>">
        <input type="hidden" name="idcategoriaproducto" id="idcategoriaproducto" value="<?= $_GET["idcategoriaproducto"] ?>">
        <input type="hidden" name="idtipoproducto" id="idtipoproducto" value="<?= $_GET["idtipoproducto"] ?? "0" ?>">
        <input type="hidden" name="cantidad" id="cantidad" value="<?= $_GET["cantidad"] ?>">
        <input type="hidden" name="precio" id="precio" value="<?= $_GET["precio"] ?>">
        <div class="row">
            <div class="col-sm-6 text-left">
                <div class="row mb-2">
                    <div class="col-sm-12 text-left">Serigrafía: <input type="text" id="txtSerigrafia1" name="txtSerigrafia1" style="width:30px;" value="<? if(isset($_GET["serigrafia1"])){ echo $_GET["serigrafia1"]; } ?>"> / <input type="text" id="txtSerigrafia2" name="txtSerigrafia2" style="width:30px;" value="<? if(isset($_GET["serigrafia2"])){ echo $_GET["serigrafia2"]; } ?>"> / <input type="text" id="txtSerigrafia3" name="txtSerigrafia3" style="width:30px;" value="<? if(isset($_GET["serigrafia3"])){ echo $_GET["serigrafia3"]; } ?>"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkPersonalizadoNumero" name="chkPersonalizadoNumero" value="1" <? if(isset($_GET["personalizadonumero"]) && $_GET["personalizadonumero"]==1){ ?> checked <? } ?>>&nbsp;Personalizado Número</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkPersonalizadoNombre" name="chkPersonalizadoNombre" value="1" <? if(isset($_GET["personalizadonombre"]) && $_GET["personalizadonombre"]==1){ ?> checked <? } ?>>&nbsp;Personalizado Nombre</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left">Tallas</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkSXL" name="chkSXL" value="1" <? if(isset($_GET["sxl"]) && $_GET["sxl"]==1){ ?> checked <? } ?>>&nbsp;S-XL</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chk2XL" name="chk2XL" value="1" <? if(isset($_GET["xl2"]) && $_GET["xl2"]==1){ ?> checked <? } ?>>&nbsp;2XL</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chk3XL" name="chk3XL" value="1" <? if(isset($_GET["xl3"]) && $_GET["xl3"]==1){ ?> checked <? } ?>>&nbsp;3XL</div>
                </div>
            </div>
            <div class="col-sm-6 text-left">
                <div class="row">
                    <div class="col-sm-12 text-left">Bordado</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkBordado1" name="chkBordado1" value="1" <? if(isset($_GET["bordado1"]) && $_GET["bordado1"]==1){ ?> checked <? } ?>>&nbsp;1 Bordado</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkBordado2" name="chkBordado2" value="1" <? if(isset($_GET["bordado2"]) && $_GET["bordado2"]==1){ ?> checked <? } ?>>&nbsp;2 Bordados</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkBordado3" name="chkBordado3" value="1" <? if(isset($_GET["bordado3"]) && $_GET["bordado3"]==1){ ?> checked <? } ?>>&nbsp;3 Bordados</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkBordado4" name="chkBordado4" value="1" <? if(isset($_GET["bordado4"]) && $_GET["bordado4"]==1){ ?> checked <? } ?>>&nbsp;4 Bordados</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkBordadoEspecial" name="chkBordadoEspecial" value="1" <? if(isset($_GET["bordadoespecial"]) && $_GET["bordadoespecial"]==1){ ?> checked <? } ?>>&nbsp;Bordado Especial</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left">Personalizado</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkLinea1" name="chkLinea1" value="1" <? if(isset($_GET["personalizado1linea"]) && $_GET["personalizado1linea"]==1){ ?> checked <? } ?>>&nbsp;1 Linea</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkLinea2" name="chkLinea2" value="1" <? if(isset($_GET["personalizado2lineas"]) && $_GET["personalizado2lineas"]==1){ ?> checked <? } ?>>&nbsp;2 Lineas</div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-left"><input type="checkbox" id="chkLinea3" name="chkLinea3" value="1" <? if(isset($_GET["personalizado3lineas"]) && $_GET["personalizado3lineas"]==1){ ?> checked <? } ?>>&nbsp;3 Lineas</div>
                </div>
            </div>
        </div>
        <div class="row mt-2 mb-2">
            <div class="col-sm-12">
                <textarea id="txtObservaciones" name="txtObservaciones" rows="5" class="form-control" placeholder="Observaciones"><? if(isset($_GET["observaciones"])){ echo $_GET["observaciones"]; } ?></textarea>
            </div>
        </div>
        <center><button type="button" id="btnValidar" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button></center>
    </form>
</div>