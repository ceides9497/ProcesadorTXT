<?php
  copy($_FILES['archivo']['tmp_name'],$_FILES['archivo']['name']);
  echo "Archivo guardado exitosamente";
  $nombre =  $_FILES['archivo']['name'];
  echo $nombre."<br />" ;
  echo $_FILES['archivo']['type']."<br />";
  if ($_FILES['archivo']['type']=="text/plain") {
    echo "yeah";
  }else {
    echo "nope";
    echo "<script language="javascript">window.location='main.php';</script>";
  }
  rename($nombre, "mi_fichero.txt");
?>
