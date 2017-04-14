<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Conseguir Pruebas</title>
</head>
<body>
<h1>Calcular</h1>
<form action="procesar.php" method="post" enctype="multipart/form-data">
    <label for="">Número de autorización:</label>
    <input type="text" name="authorizationNumber" value="" required=""><br><br>
    <label for="">Llave de dosificación:</label>
    <input type="text" name="dosageKey" value="" size="65" required=""><br><br>
    <label for="archivo">Seleccionar archivo a subir:</label>
    <input type="file" name="archivo" id="archivo" required=""><br><br>
    <input type="submit" value="Upload" name="submit">
</form>

</body>
</html>
