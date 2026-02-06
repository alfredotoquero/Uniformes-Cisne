<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Menu.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseMenu = new Menu();
$claseUsuarios = new Usuarios();
$claseClientes = new Clientes();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["url"] = $_GET["modulo1"];
$sololectura = $claseUsuarios->soloLectura($_POST);

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["status"] = "A";
$tareas = $claseClientes->obtenerTareas($_POST);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sistema | Uniformes Cisne</title>
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Panel de control administrativo" name="description" />
    <meta content="Distro Development Studio" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.png">

    <!-- style -->
    <!-- <link rel="stylesheet" href="/css/font-awesome/css/font-awesome.min.css" type="text/css" /> -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <!-- third party css -->
    <link href="/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/css/jquery.fancybox.min.css">

    <!-- App css -->
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

    <!-- <link href="assets/plugins/select2/dist/css/select2.css" rel="stylesheet" type="text/css">
        <link href="assets/plugins/select2/dist/css/select2-bootstrap.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
    <link href="/assets/css/select2.min.css" rel="stylesheet" type="text/css" />

    <!-- Datepicker and Wickedpicker CSS -->
    <!-- <link href="libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
        <link href="libs/wickedpicker/dist/wickedpicker.min.css" rel="stylesheet"> -->
    <!-- Datepicker and Wickedpicker CSS -->
    <link href="assets/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

    <!-- Toast -->
    <link rel="stylesheet" href="assets/css/jquery.toast.min.css">

    <!-- JQuery confirm message -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

    <!-- Style css -->
    <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />

    <style>
        /* width */
        .my-scroll {
            overflow:hidden scroll;
        }
        /* width */
        .stylescroll::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        .stylescroll::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px white;
            border-radius: 10px;
        }

        /* Handle */
        .stylescroll::-webkit-scrollbar-thumb {
            background: gray;
            border-radius: 10px;
        }

        /* Handle on hover */
        .stylescroll::-webkit-scrollbar-thumb:hover {
            background: #AAA;
        }

        .badge span {
            background-color: red;
            width: 40px;
            height: 40px;
            border-radius: 40px;
            color: white;
            font-size: 15px;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>

</head>

<body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": false}'>

    <div id="divLoader"></div>

    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        <div class="leftside-menu">

            <!-- LOGO -->
            <a href="home.php" class="logo text-center logo-light">
                <span class="logo-lg">
                    <img src="/assets/images/logo-uniformes-trazo.png" alt="" height="32">
                </span>
                <span class="logo-sm">
                    <img src="/assets/images/logo-uniformes-trazo.png" alt="" height="32">
                </span>
            </a>

            <!-- LOGO -->
            <a href="home.php" class="logo text-center logo-dark">
                <span class="logo-lg">
                    <img src="/assets/images/logo-uniformes-trazo.png" alt="" height="32">
                </span>
                <span class="logo-sm">
                    <img src="/assets/images/logo-uniformes-trazo.png" alt="" height="32">
                </span>
            </a>

            <div class="h-100" id="leftside-menu-container" data-simplebar>

                <!--- Sidemenu -->
                <ul class="side-nav">

                    <?php
                    $categorias = $claseMenu->obtenerMenu($_SESSION["usuario"]["idusuario"])["categorias"];
                    foreach ($categorias as $categoria) {
                    ?>
                        <li class="side-nav-title side-nav-item"><?= $categoria["nombre"] ?></li>
                        <?
                        foreach ($categoria["secciones"] as $seccion) {
                            // si la seccion es la de tipo de cambio, se abre en fancy (caso especial)
                            if ($seccion["url"] == "cambio") {
                        ?>
                                <li class="side-nav-item">
                                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/tipocambio.php" class="side-nav-link">
                                        <i class="<?= $seccion["icono"] ?>"></i>
                                        <span> <?= $seccion["nombre"] ?> </span>
                                    </a>
                                </li>
                            <?
                            } else {
                            ?>
                                <li class="side-nav-item<? if ($_GET["modulo1"] == "" && $seccion["url"] == "clientes") { ?> menuitem-active<? } ?>">
                                    <a href="/<?= $seccion["url"] ?>" class="side-nav-link<? if ($_GET["modulo1"] == "" && $seccion["url"] == "clientes") { ?> active<? } ?>">
                                        <i class="<?= $seccion["icono"] ?>"></i>
                                        <span> <?= $seccion["nombre"] ?> </span>
                                    </a>
                                </li>
                    <?
                            }
                        }
                    }
                    ?>

                </ul>

            </div>
            <!-- Sidebar -left -->

        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom">
                    <ul class="list-unstyled topbar-menu float-end mb-0">

                        <li class="dropdown notification-list">
                            <a class="nav-link nav-user arrow-none me-0" href="/recordatorios" role="button" id="btnRecordatorio">
                                <span class="uil uil-notes" style="font-size:25px;"></span>
                                <? if($tareas["total"]){?><div class="badge"><span><?= $tareas["total"]; ?></span></div><?} ?>
                            </a>
                        </li>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="btnNotificacion">
                                <span class="uil uil-bell" style="font-size:25px;"></span>
                                <div class="badge" id="badge_notificaciones">
                                    <span></span>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                <!-- item-->
                                <div class="my-scroll stylescroll" id="divNotificaciones" style="max-height:300px;min-width:55vh;">
                                    <div id="divNotificacionesCampana"></div>
                                </div>
                                <hr>
                                <div>
                                    <div class="dropdown-item notify-item">
                                        <span id="cant_notificaciones"></span>
                                        <a href="/notificaciones" style="float:right !important;">Ver todo <i class="mdi mdi-chevron-right me-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <span>
                                    <span class="account-user-name"><?= $_SESSION["usuario"]["nombre"] ?></span>
                                    <span class="account-position"><i class="uil-user"></i> <?= $_SESSION["usuario"]["usuario"] ?></span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                <!-- item-->
                                <a href="javascript:;" onclick="solicitudServidor('usuarios','logout','','¿Deseas cerrar la sesión?');" class="dropdown-item notify-item">
                                    <i class="mdi mdi-logout me-1"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </div>
                        </li>

                    </ul>
                    <button class="button-menu-mobile open-left">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </div>
                <!-- end Topbar -->

                <?php
                switch ($_GET["modulo1"]) {
                    case "almacenes":
                        include("modulos/almacenes.php");
                        break;
                    case "tiendas":
                        include("modulos/tiendas.php");
                        break;
                    case "colores":
                        include("modulos/colores.php");
                        break;
                    case "contactos":
                        include("modulos/contactos.php");
                        break;
                    case "cotizaciones":
                        switch ($_GET["modulo2"]) {
                            case "agregar":
                                include("modulos/cotizaciones/agregar.php");
                                break;
                            case "convertir":
                                include("modulos/cotizaciones/convertir.php");
                                break;
                            case "historial":
                                include("modulos/cotizaciones/cotizacionespadre.php");
                                break;
                            default:
                                include("modulos/cotizaciones.php");
                                break;
                        }
                        break;
                    case "cuentas":
                        include("modulos/cuentas.php");
                        break;
                    case "compras":
                        include("modulos/compras.php");
                        break;
                    case "movimientos":
                        switch ($_GET["modulo2"]) {
                            case "agregar":
                                include("modulos/movimientos/agregar.php");
                                break;

                            default:
                                include("modulos/movimientos.php");
                                break;
                        }
                        break;
                    case "pedidos":
                        switch ($_GET["modulo2"]) {
                            case "agregar":
                                include("modulos/pedidos/agregar.php");
                                break;
                            case "editar":
                                include("modulos/pedidos/editar.php");
                                break;
                            default:
                                include("modulos/pedidos.php");
                                break;
                        }
                        break;
                    case "produccion":
                        include("modulos/produccion.php");
                        break;
                    case "produccionde":
                        include("modulos/produccionde.php");
                        break;
                    case "productos":
                        switch ($_GET["modulo2"]) {
                            case "agregar":
                                include("modulos/productos/agregar.php");
                                break;
                            case "editar":
                                include("modulos/productos/editar.php");
                                break;
                            default:
                                include("modulos/productos.php");
                                break;
                        }
                        break;
                    case "proveedores":
                        include("modulos/proveedores.php");
                        break;
                    case "solicitudes":
                        switch ($_GET["modulo2"]) {
                            case 'agregar':
                                include("modulos/solicitudes/agregar.php");
                                break;

                            default:
                                include("modulos/solicitudes.php");
                                break;
                        }
                        break;
                    case "sucursales":
                        include("modulos/sucursales.php");
                        break;
                    case "tallas":
                        include("modulos/tallas.php");
                        break;
                    case "notificaciones":
                        include("modulos/notificaciones.php");
                        break;
                    case "recordatorios":
                        include("modulos/recordatorios.php");
                        break;
                    case "reportecxc":
                        include("modulos/reportes/cxc.php");
                        break;
                    case "reporteinventario":
                        include("modulos/reportes/inventario.php");
                        break;
                    case "reporteproductosvendidos":
                        include("modulos/reportes/productosvendidos.php");
                        break;
                    case "reporteventas":
                        include("modulos/reportes/ventas.php");
                        break;
                    case "reporteventaspsucursal":
                        include("modulos/reportes/ventaspsucursal.php");
                        break;
                    case "reportemovimientos":
                        include("modulos/reportes/movimientos.php");
                        break;
                    case "reportecortes":
                        include("modulos/reportes/cortes.php");
                        break;
                    case "reportereorden":
                        include("modulos/reportes/reorden.php");
                        break;
                    case "tiposproducto":
                        include("modulos/tiposproducto.php");
                        break;
                    case "usuarios":
                        include("modulos/usuarios.php");
                        break;
                    case "tarjetasregalo":
                        include("modulos/tarjetasregalo.php");
                        break;
                    case "personalizaciones":
                        include("modulos/personalizaciones.php");
                        break;
                    case "parametros":
                        include("modulos/parametros.php");
                        break;
                    case "tipostarjetaregalo":
                        include("modulos/tipostarjetaregalo.php");
                        break;
                    case "vendedores":
                        include("modulos/vendedores.php");
                        break;
                    case "emisores":
                        include("modulos/emisores.php");
                        break;
                    case "facturas":
                        include("modulos/facturas.php");
                        break;
                    case "pagos":
                        include("modulos/pagos.php");
                        break;
                    case "catalogos":
                        switch ($_GET["modulo2"]) {
                            case "valores":
                                include("modulos/catalogos/valores.php");
                                break;

                            default:
                                include("modulos/catalogos.php");
                                break;
                        }
                        break;
                    case "categorias":
                        include("modulos/categorias.php");
                        break;
                    case "clientes":
                    default:
                        include("modulos/clientes.php");
                        break;
                }
                ?>

            </div>
            <!-- content -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            2022 © Uniformes Cisne
                        </div>
                        <div class="col-md-6 text-end">
                            Versión 2.0.0
                            <!-- <div class="text-md-end footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a>
                                </div> -->
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <div class="rightbar-overlay"></div>
    <!-- /End-bar -->

    <!-- bundle -->
    <script src="/assets/js/vendor.min.js"></script>
    <script src="/assets/js/app.js"></script>

    <!-- third party js -->
    <script src="/assets/js/vendor/apexcharts.min.js"></script>
    <script src="/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- third party js ends -->

    <script src="/assets/js/sweetalert2.min.js"></script>
    <script src="/assets/js/jquery.fancybox.min.js"></script>

    <!-- Datepicker and Wickedpicker JS -->
    <!-- <script src="libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
        <script src="assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
        <script src="libs/wickedpicker/dist/wickedpicker.min.js"></script> -->
    <!-- Datepicker and Wickedpicker JS -->
    <script src="assets/js/bootstrap-datepicker.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <!-- Select2 -->
    <!-- <script src="assets/plugins/select2/dist/js/select2.min.js" type="text/javascript"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Toast -->
    <script src="assets/js/jquery.toast.min.js"></script>

    <!-- JQuery print -->
    <script src="/assets/js/jQuery.print.min.js"></script>

    <!--File Download jQuery-->
    <script src="/assets/js/jquery.fileDownload.js"></script>

    <!-- JQuery confirm message -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <!-- JQuery Mask -->
    <script src="/assets/js/jquery.mask.min.js"></script>

    <script id="funciones" data-modulo1="<? echo (isset($_GET["modulo1"])) ? $_GET["modulo1"] : ""; ?>" data-modulo2="<? echo (isset($_GET["modulo2"])) ? $_GET["modulo2"] : ""; ?>" data-modulo3="<? echo (isset($_GET["modulo3"])) ? $_GET["modulo3"] : ""; ?>" src="/assets/js/funciones.js"></script>
</body>

</html>