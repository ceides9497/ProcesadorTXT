<?php
  $file = file("resultado.xls");
  $file2 = implode("", $file);
  header ("Content-Disposition: attachment; filename=resultado.xls");
  header ("Content-Type: application/x-msexcel");
  echo $file2;
?>
