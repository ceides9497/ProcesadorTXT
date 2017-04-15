<?php
  copy($_FILES['archivo']['tmp_name'],$_FILES['archivo']['name']);
  echo "Archivo guardado exitosamente";
  $nombre =  $_FILES['archivo']['name'];
  echo $nombre."<br />" ;
  echo $_FILES['archivo']['type']."<br />";
  if ($_FILES['archivo']['type']=="text/plain") {
    echo "yeah";
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
              $code = $controlCode->generate($_POST['authorizationNumber'],//Numero de autorizacion
                                             $reg[1],//Numero de factura
                                             $reg[2],//Número de Identificación Tributaria o Carnet de Identidad
                                             str_replace('/','',$reg[3]),//fecha de transaccion de la forma AAAAMMDD
                                             $reg[4],//Monto de la transacción
                                             $_POST['dosageKey']//Llave de dosificación
                      );
              fwrite($fi,$line."=>".$code.PHP_EOL);
          }
      fclose($handle);
      }else{
           throw new Exception("<b>Could not open the file!</b>");
      }
  }catch ( Exception $e ){
       echo "Error (File: ".$e->getFile().", line ".
            $e->getLine()."): ".$e->getMessage();
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
    <form action="descargar.php" method="post" enctype="multipart/form-data">
      <input type="submit" name="submit" value="Descargar">
    </form>
  </body>
</html>
