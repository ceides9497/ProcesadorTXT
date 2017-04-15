<?php
  $file = file("resultado.txt");
  $file2 = implode("", $file);
  header ("Content-Disposition: attachment; filename=resultado.txt");
  header ("Content-Type: text/plain");
  echo $file2;
?>
