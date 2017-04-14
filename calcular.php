<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Codigo de control</title>
  </head>
  <body>
    <h1>Codigo de control</h1>
    <form class="" action="calcular.php" method="post" name="calc">
      <label for="">Número de autorización:</label>
      <input type="text" name="authorizationNumber" value=""><br><br>
      <label for="">Número de factura:</label>
      <input type="text" name="invoiceNumber" value=""><br><br>
      <label for="">Fecha de transacción: </label>
      <input type="texto" name="dateOfTransaction" value="" placeholder="Año/Mes/Día"><br><br>
      <label for="">NIT/CI :</label>
      <input type="text" name="nitci" value=""><br><br>
      <label for="">Monto de la transacción: </label>
      <input type="text" name="transactionAmount" value=""><br><br>
      <label for="">Llave de dosificación:</label>
      <input type="text" name="dosageKey" value="" size="65"><br><br>
      <input type="submit" name="submit" value="Obtener codigo de control"> <br><br>
    </form>

    <?php
    if(isset($_POST['submit'])){
      include 'codigocontrol.php';
      $controlCode = new ControlCode();
      if( empty($_POST['authorizationNumber']) || empty($_POST['invoiceNumber']) || empty($_POST['dateOfTransaction']) ||
              empty($_POST['transactionAmount']) || empty($_POST['dosageKey']) || (!strlen($_POST['nitci'])>0 )  ){
          echo "<b>Todos los campos son obligatorios</b>";
      }else{
        $resultado = $controlCode->generate($_POST['authorizationNumber'],//Numero de autorizacion
                                                      $_POST['invoiceNumber'],//Numero de factura
                                                      $_POST['nitci'],//Número de Identificación Tributaria o Carnet de Identidad
                                                      str_replace('/','',$_POST['dateOfTransaction']),//fecha de transaccion de la forma AAAAMMDD
                                                      $_POST['transactionAmount'],//Monto de la transacción
                                                      $_POST['dosageKey']//Llave de dosificación
                                                    );
        echo "Su codigo de control es: ".$resultado;
      }
    }


     ?>
  </body>
</html>
