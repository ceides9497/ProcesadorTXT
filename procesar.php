<?php
  ini_set('max_input_time', 120);
  ini_set('post_max_size', "50M");
  ini_set('max_execution_time', 300);
	include("excelwriter.inc.php");
  include 'codigocontrol.php';
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

  try{
      $filename="mi_fichero.txt";
      $handle = fopen($filename, "r");
      if ($handle) {
          $controlCode = new ControlCode();
          $line = fgets($handle);
          $reg = explode("|", $line);
          $resultado = count($reg);
          if ($resultado == 12) {
            paraLibros($handle,$controlCode);
          }else {
            paraNormal($handle,$controlCode);
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
    echo "Error (File: ".$e->getFile().", line ".
                    $e->getLine()."): ".$e->getMessage();
  }

  function paraNormal($handle,$controlCode){
    $fi = fopen("resultado.txt","w+") or die ("Problemas al recuperar el archivo");
    $excel=new ExcelWriter("resultado.xls");
    if($excel==false)
      echo $excel->error;
    $myArr=array("X","Num","Fecha","Numero Factura","Numero de Autorizacion","V","NIT","Nombre","Monto 1","Monto 2","Monto 3","Monto 4",
                  "Monto 5","Descuento","Monto de la transaccion","Impuesto","Codigo de control","Codigo de control VERIFICADO");
    $excel->writeLine($myArr);
    while (($line = fgets($handle)) !== false) {
        $reg = explode("|", $line);
        //genera codigo de control
        $fecha = str_replace('/', '-',$reg[2]);
        $fecha = date("Y-m-d",strtotime($fecha));
        if(!(($reg[5]=="V" || $reg[5]=="v") && $reg[14] > 0)){
          fwrite($fi,$line." => Sin Codigo de control".PHP_EOL);
          $resultado = count($reg);
          if ($resultado == 16) {
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],$reg[12],$reg[13],$reg[14],$reg[15],"Sin Codigo","Sin Codigo de control");
            $excel->writeLine($myArr);
          }
          if ($resultado == 17){
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],$reg[12],$reg[13],$reg[14],$reg[15],$reg[16],"Sin Codigo de control");
            $excel->writeLine($myArr);
          }
        }else {
          $code = $controlCode->generate($_POST['authorizationNumber'],//Numero de autorizacion
                                          $reg[3],//Numero de factura
                                          $reg[6],//Número de Identificación Tributaria o Carnet de Identidad
                                          str_replace('-','',$fecha),//fecha de transaccion de la forma AAAAMMDD
                                          $reg[14],//Monto de la transacción
                                         $_POST['dosageKey']//Llave de dosificación
                  );
          fwrite($fi,$line." =>".$code.PHP_EOL);
          $resultado = count($reg);
          if ($resultado == 16) {
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],$reg[12],$reg[13],$reg[14],$reg[15],"Sin Codigo",$code);
            $excel->writeLine($myArr);
          }
          if ($resultado == 17){
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],$reg[12],$reg[13],$reg[14],$reg[15],$reg[16],$code);
            $excel->writeLine($myArr);
          }
        }
    }
    $excel->close();
    fclose($fi);
  }

  function paraLibros($handle,$controlCode){
    $fi = fopen("resultado.txt","w+") or die ("Problemas al recuperar el archivo");
    $excel=new ExcelWriter("resultado.xls");
    if($excel==false)
      echo $excel->error;
    $myArr=array("NIT","Nombre; Pais; Placa","Numero Factura","Numero de Autorizacion","Fecha","Monto","Monto 2",
                  "Descuento","Monto de la transaccion","Impuesto","Producto","Codigo de control","Codigo de control VERIFICADO");
    $excel->writeLine($myArr);
    while (($line = fgets($handle)) !== false) {
        $reg = explode("|", $line);
        //genera codigo de control
        $fecha = str_replace('/', '-',$reg[4]);
        $fecha = date("Y-m-d",strtotime($fecha));
        if(!($reg[8] > 0)){
          fwrite($fi,$line." => Sin Codigo de control".PHP_EOL);
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],"Sin Codigo de control");
            $excel->writeLine($myArr);
        }else {
          $code = $controlCode->generate($_POST['authorizationNumber'],//Numero de autorizacion
                                          $reg[2],//Numero de factura
                                          $reg[0],//Número de Identificación Tributaria o Carnet de Identidad
                                          str_replace('-','',$fecha),//fecha de transaccion de la forma AAAAMMDD
                                          $reg[8],//Monto de la transacción
                                         $_POST['dosageKey']//Llave de dosificación
                  );
          fwrite($fi,$line." =>".$code.PHP_EOL);
            $myArr=array($reg[0],$reg[1],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6],$reg[7],
                          $reg[8],$reg[9],$reg[10],$reg[11],$code);
            $excel->writeLine($myArr);
        }
    }
    $excel->close();
    fclose($fi);
  }
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
          <input type="submit" name="submit" value="Descargar en .txt">
        </form>
        <br>
        <form action="descargarExcel.php" method="post" enctype="multipart/form-data">
          <input type="submit" name="submit" value="Descargar en .xls">
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
