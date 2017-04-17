<?php
  header ("Content-Disposition: attachment; filename=resultado.xls");
  header ("Content-Type: application/x-msexcel");
  header('Pragma: no-cache');
  readfile("resultado.xls");
?>
