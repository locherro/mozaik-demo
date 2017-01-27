<?php
/**
 * Created by PhpStorm.
 * User: ma_stefan_berberich
 * Date: 27.01.17
 * Time: 09:55
 */

$a = array(
    array('day' => 1, 'umsatz' => 1800),
    array('day' => 2, 'umsatz' => 3000),

);



$b = fopen('test.csv', 'a+');
fwrite($b, date('d.m.Y H:i:s') . "\n" );
fclose($b);

header('Content-Type: application/json');

echo json_encode($a);
?>
