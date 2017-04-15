<?php
  $error = null;
  //error_reporting(0);
  copy($_FILES['archivo']['tmp_name'],$_FILES['archivo']['name']);
  //echo "Archivo guardado exitosamente";
  $nombre =  $_FILES['archivo']['name'];
  //echo $nombre."<br />" ;
  //echo $_FILES['archivo']['type']."<br />";
  if ($_FILES['archivo']['type']=="text/plain") {
    //echo "yeah";
    rename($nombre, "mi_fichero.txt");
  }

  $fi = fopen("resultado.txt","w+") or die ("Problemas al recuperar el archivo");
  include 'codigocontrol.php';
  try{
      $filename="mi_fichero.txt";
      $handle = fopen($filename, "r");
      if ($handle) {
          $controlCode = new ControlCode();
          $count=0;
          while (($line = fgets($handle)) !== false) {
              $reg = explode("|", $line);
              //genera codigo de control
              $fecha = str_replace('/', '-',$reg[2]);
              $fecha = date("Y-m-d",strtotime($fecha));
              $code = $controlCode->generate($_POST['authorizationNumber'],//Numero de autorizacion
                                              $reg[3],//Numero de factura
                                              $reg[6],//Número de Identificación Tributaria o Carnet de Identidad
                                              str_replace('-','',$fecha),//fecha de transaccion de la forma AAAAMMDD
                                              $reg[14],//Monto de la transacción
                                             $_POST['dosageKey']//Llave de dosificación
                      );
              fwrite($fi,$line."=>".$code.PHP_EOL);
          }
      fclose($handle);
      }else{
           throw new Exception("<b>Could not open the file!</b>");
      }
  }catch ( Exception $e ){
    $error = true;
    echo "<center>
    <h1>Error!!!</h1>
    <b>Asegurese de haber ingresado los datos correctos</b><br /><br />
    </center>";
  }
  fclose($fi);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Resultado</title>
  </head>
  <body>
    <?php if (!$error): ?>
      <center>
        <h1>Exito!!!</h1>
        <b>Ahora puede descargar el archivo.</b><br><br>
        <form action="descargar.php" method="post" enctype="multipart/form-data">
          <input type="submit" name="submit" value="Descargar">
        </form>
        <br>
        <button type="button" name="button"><a href="main.php" style="color:black;  text-decoration:none;">Volver</a></button>
      </center>
    <?php else: ?>
      <center>
        <button type="button" name="button"><a href="main.php" style="color:black;  text-decoration:none;">Volver</a></button>
      </center>
    <?php endif; ?>
  </body>
</html>
