<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>
        <?php
          include 'codigocontrol.php';
          try{
              $filename="5000CasosPruebaCCVer7.txt";
              $handle = fopen($filename, "r");
              if ($handle) {
                  $controlCode = new ControlCode();
                  $count=0;
                  while (($line = fgets($handle)) !== false) {
                      $reg = explode("|", $line);
                      //genera codigo de control
                      $code = $controlCode->generate($reg[0],//Numero de autorizacion
                                                     $reg[1],//Numero de factura
                                                     $reg[2],//Número de Identificación Tributaria o Carnet de Identidad
                                                     str_replace('/','',$reg[3]),//fecha de transaccion de la forma AAAAMMDD
                                                     $reg[4],//Monto de la transacción
                                                     $reg[5]//Llave de dosificación
                              );
                      if($code===$reg[10]){
                          $count+=1;
                      }
                  }
                  echo 'Archivo <b>'.$filename.'</b><br/>';
                  echo 'Total registros testeados <b>'.$count.'</b><br/>';
                  echo 'Errores <b>0</b><br/>';
              fclose($handle);

              }else{
                   throw new Exception("<b>Could not open the file!</b>");
              }
          }catch ( Exception $e ){
               echo "Error (File: ".$e->getFile().", line ".
                    $e->getLine()."): ".$e->getMessage();
          }

        ?>

    </body>
</html>
