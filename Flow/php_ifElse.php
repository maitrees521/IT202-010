<?php
echo "<br>\nPHP [ifElse] Exercise [1] <br>\n";
$a = 50;
$b = 10;
if($a > $b) {
  echo "Hello World";
}

echo "<br>\nPHP [ifElse] Exercise [2] <br>\n";
$a = 50;
$b = 10;
if ($a != $b) {
  echo "Hello World";
}

echo "<br>\nPHP [ifElse] Exercise [3] <br>\n";
$a = 50;
$b = 10;
if ($a == $b) {
  echo "Yes";
} 
else {
  echo "No";
}

echo "<br>\nPHP [ifElse] Exercise [4] <br>\n";
$a = 50;
$b = 10;
if ($a == $b) {
  echo "1";
} elseif ($a > $b) {
  echo "2";
} else {
  echo "3";
}
?>