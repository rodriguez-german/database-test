<?php 
require '../src/Core.php';
require '../src/Database.php';
require '../src/ScriptHandler.php';

use PadawansTrainer\DatabaseHandler\Database;

class Prueba extends Database{
    protected static $tabla = 'usuarios';

}

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
var_dump($rta);