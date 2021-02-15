<?php
echo "<br>\nPHP [Switch] Exercise [1] <br>\n";
$color = "green";
switch ($color) {
  case "red":
    echo "Hello";
    break;
  case "green":
    echo "Welcome";
    break;
}

echo "<br>\nPHP [Switch] Exercise [2] <br>\n";
$color1 = "red";
switch ($color1) {
  case "red":
    echo "Hello";
    break;
  case "green":
    echo "Welcome";
    break;
  default:
    echo "Neither";
}

?>