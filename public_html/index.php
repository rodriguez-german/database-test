<?php 
require '../src/manual_autoload.php';

use PadawansTrainer\DatabaseHandler\Database;

class Prueba extends Database{
    protected static $tabla = 'usuarios';

}

/*

$rta = Prueba::select( "t.ID, EMAIL, NOMBRE, PAIS" , [
    'joins' => [
        ['table' => 'paises as p', 'on' => 'p.ID = t.FKPAIS', 'type'=>'left' ]
    ],
    'where' => 'EMAIL LIKE :nombre',
    'params' => [
        ':nombre' => '%German%'
    ],
    'order' => 'EMAIL DESC'
] );
*/
$rta = Prueba::insert( [
    'EMAIL' => 'pepe@email.com',
    'PWD' => sha1('1234'),
    'CREATED_AT' => 'NOW()',
    'NOMBRE' => NULL,
    'APELLIDO' => NULL,
   // 'FKPAIS' => 'QUERY:SELECT ID FROM PAISES WHERE PAIS="ARGENTINA" AND ESTADO=\'1\'',
    'ESTADO' => false 
] );
var_dump($rta);


