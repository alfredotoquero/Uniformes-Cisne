<?
$cuerpo = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>'.$titulo.'</title>
  <style>
    body {
      background-color: #f5f5f5;
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      padding: 25px;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .header {
      text-align: center;
      padding-bottom: 15px;
      border-bottom: 1px solid #e0e0e0;
    }
    .logo {
      max-width: 200px;
      margin-bottom: 10px;
    }
    .content {
      margin-top: 20px;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }
    .btn {
      display: inline-block;
      background: #007bff;
      color: #fff !important;
      padding: 12px 20px;
      margin-top: 15px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }
    .footer {
      text-align: center;
      margin-top: 25px;
      font-size: 12px;
      color: #999;
    }
    .info-box {
      background: #f0f8ff;
      padding: 15px;
      border-radius: 5px;
      border-left: 4px solid #007bff;
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="header">
      <img class="logo" src="'.$logo.'" alt="Logo">
      <h2>'.$titulo.'</h2>
    </div>

    <div class="content">'.$contenido.'</div>

    <div class="footer">
      © '.date("Y").' — Este correo fue generado automáticamente.
    </div>

  </div>
</body>
</html>';
?>