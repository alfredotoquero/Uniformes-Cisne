<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

if(isset($_SESSION["usuario"])){
    header("location: home.php");
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Sistema | Uniformes Cisne</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Sistema administrativo de Uniformes Cisne" name="description" />
        <meta content="Distro Development Studio" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.png">
        
        <!-- App css -->
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

        <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">

    </head>

    <body class="loading authentication-bg" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
        <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xxl-4 col-lg-5">
                        <div class="card">

                            <!-- Logo -->
                            <div class="card-header pt-3 pb-3 text-center bg-primary">
                                <span><img src="/assets/images/logo-uniformes-trazo.png" alt="" height="45"></span>
                                <!-- <span><img src="/assets/images/logo.png" alt="" height="18"></span> -->
                            </div>

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <h4 class="text-dark-50 text-center pb-0 fw-bold">Iniciar Sesión</h4>
                                    <p class="text-muted mb-4">Ingresa tu usuario y contraseña para acceder al sistema administrativo.</p>
                                </div>

                                <form id="formLogin" name="formLogin">
                                    <input type="hidden" name="controlador" id="controlador" value="usuarios">
                                    <input type="hidden" name="accion" id="accion" value="login">
                                    <input type="hidden" name="href" id="href" value="/home.php">
                                    <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">

                                    <div class="mb-3">
                                        <label for="txtUsuario" class="form-label">Usuario</label>
                                        <input autofocus class="form-control requerido onenter" type="text" name="txtUsuario" id="txtUsuario" placeholder="Ingresa tu usuario" data-mensajeerror="Debes indicar un usuario" data-enter="btnSesion" tabindex="1">
                                    </div>

                                    <div class="mb-3">
                                        <a href="/recuperar-password.php" class="text-muted float-end"><small>¿Olvidaste tu contraseña?</small></a>
                                        <label for="txtPassword" class="form-label">Contraseña</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" name="txtPassword" id="txtPassword" class="form-control requerido onenter" placeholder="Ingresa tu contraseña" data-mensajeerror="Debes indicar una contraseña" data-enter="btnSesion" tabindex="2">
                                            <div class="input-group-text" data-password="false">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                            <label class="form-check-label" for="checkbox-signin">Recuérdame en este equipo</label>
                                        </div>
                                    </div>

                                    <div class="mb-3 mb-0 text-center">
                                        <button class="btn btn-primary" type="button" name="btnSesion" id="btnSesion" onclick="validarFormulario('formLogin');"> Iniciar Sesión </button>
                                    </div>

                                </form>
                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

        <footer class="footer footer-alt">
            <?= date("Y"); ?> © Uniformes Cisne
        </footer>

        <!-- bundle -->
        <script src="/assets/js/vendor.min.js"></script>
        <script src="/assets/js/app.js"></script>

        <script src="/assets/js/sweetalert2.min.js"></script>
        <script id="funciones" data-modulo1="<? echo (isset($_GET["modulo1"])) ? $_GET["modulo1"] : ""; ?>" data-modulo2="<? echo (isset($_GET["modulo2"])) ? $_GET["modulo2"] : ""; ?>" data-modulo3="<? echo (isset($_GET["modulo3"])) ? $_GET["modulo3"] : ""; ?>" src="/assets/js/funciones.js"></script>
        
    </body>
</html>
