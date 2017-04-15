<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Conseguir Pruebas</title>
</head>
<body>
<h1>Calcular</h1>
<form action="procesar.php" method="post" enctype="multipart/form-data" id="form">
    <label for="">Número de autorización:</label>
    <input type="text" name="authorizationNumber" value="" required=""><br><br>
    <label for="">Llave de dosificación:</label>
    <input type="text" name="dosageKey" value="" size="65" required=""><br><br>
    <label for="archivo">Seleccionar archivo a subir:</label>
    <input type="file" name="archivo" id="archivo" accept="text/plain"><br><br>
    <input type="submit" value="Subir archivo .txt" name="submit" required="" onclick="return comprueba_extension(this.form, this.form.archivo.value)">
</form>
<script type="text/javascript">
    function comprueba_extension(formulario, archivo){
        extension = (archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
        if(extension == ".txt")
            return true;
        alert("Debe insertar un archivo .txt obligatoriamente, Gracias.");
        return false;
    }
</script>
</body>
</html>
