<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Contactos.php");

$claseContactos = new Contactos();

$contactos = $claseContactos->obtenerContactos($_POST);
?>
<select name="slcContacto" id="slcContacto" class="form-control select2t" onchange="seleccionarContacto(this);">
    <option value="0">--Seleccionar--</option>
<?
foreach($contactos["contactos"] as $contacto){
    ?>
    <option value="<?= $contacto["idcontacto"] ?>" data-telefono="<?= $contacto["telefono"] ?>" data-correo="<?= $contacto["correo"] ?>" data-puesto="<?= $contacto["puesto"] ?>"><?= $contacto["nombre"] ?></option>
    <?
}
?>
</select>

<script>
    $(document).ready(function (e){
        $("#slcContacto").select2({
            tags: true
        });
    });
</script>