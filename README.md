# database-handler
Core de manejo de bases de datos


## INSTALACIÓN
La instalación se hace por medio de la librería composer
```bash
composer require PadawansTrainer\DatabaseHandler
```

Luego de ejecutado el `composer install`, la terminal solictará el ingreso de dos datos que serán utilizados para la manipulación de la base de datos.

1. Directorio donde se almacenará el archivo de configuración de la base de datos. Ruta relativa desde donde está ubicado el composer.json, por defecto es la carpeta padre de los `vendor`
2. Nombre del archivo que guardará los parámetros de configuración. El nombre solo acepta letras, números y guiones medio o bajos. No lleva extensión. Se almacenará en el directorio indicado en el prompt anterior, con el nombre de archivo indicado y extensión .ini

Al finalizar la instalación, se deberá editar el archivo de .ini para efectivizar la conexión a la base de datos.


## CONFIGURACIÓN
Los contenidos del archivo .ini son bastante autoexplicativos, pero a los fines documentativos se detallan a continuación

host: IP o dominio donde se encuentra el servidor MySQL
port: Puerto detrás del cual corre el servicio
user: Usuario de conexión MySQL
pass: Clave del usuario MySQL
db: Nombre de la base de datos a usar
charset: Codificación de caracteres de la base
collate: Collate dentro del charset a implementar 

## USO
En las entidades que harán uso de la clase hacer un `use`
```php
use PadawansTrainer\DatabaseHandler;
```

Las entidades deberán extender de esta clase para usar todos sus métodos.
Deberá tener especificado un atributo estático llamado `$tabla` que será la tabla (o vista) del MySQL a la que apunta este modelo.
Y un atributo estático llamado `$alias` para usarse como alias de tabla para los ruteos de sus columnas `alias_de_tabla.COLUMNA`

```php 
namespace App\Models;

class MyModel extends DatabaseHandler{
    static $tabla = 'tabla_a_consultar';
    static $alias = 'alias_de_tabla';
}
```