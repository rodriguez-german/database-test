<?php

namespace PadawansTrainer\DatabaseHandler\Core;

use \PDO;
use \PDOException;
use PadawansTrainer\DatabaseHandler\Core\ScriptHandler;

class Base
{
    protected static $tabla = 'dual'; //esto va a definir MODELO POR MODELO a qué tabla se le va a pedir cada consulta del CRUD
    protected static $alias = 't'; //t de tabla, es el alias del modelo que esté usando

    private static $cnx;
    protected static $join = '';
    protected static $where = '';
    protected static $group = '';
    protected static $having = '';
    protected static $order = ''; //por defecto no tengo order by
    protected static $offset = 0;
    protected static $limit = 10;

    protected static $page = 1;
    protected static $joins = [ ];
    protected static $bindedParams = [ ];
	protected static $getsql = false;
    protected static $empty = false; //false: empty values will be NULL | true: empty values will be String ''


    /*
    * Get Configuration parameter
    */
    private static function getConfigurationParameter( $params, $param, $default = NULL )
    {
        return isset($params[$param]) && !empty($params[$param]) ? $params[$param]: $default; 
    }


    /*
    * Get SQL Connection
    */
    private static function getConnection( )
    {
        if( ! self::$cnx ){
            $params = ScriptHandler::getConfiguration( );

            $host = self::getConfigurationParameter( $params, 'host', 'localhost' );
            $port = self::getConfigurationParameter( $params, 'port', '3306' );
            $user = self::getConfigurationParameter( $params, 'user', 'root' );
            $pass = self::getConfigurationParameter( $params, 'pass', '' );
            $db = self::getConfigurationParameter( $params, 'db' );
            $charset = self::getConfigurationParameter( $params, 'charset', 'utf8mb4' );
    
            self::$cnx = new PDO( "mysql:host=$host;dbname=$db;port=$port;charset=$charset", $user, $pass );
            self::$cnx->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            self::$cnx->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
        }
    }


    /*
    * Select, insert, update, delete
    */
    protected static function get( $columnas = '*' ){
		$query = "SELECT $columnas FROM " . static::$tabla . " AS " . self::$alias;
		$query.= self::$join;
		$query.= self::$where;
		$query.= self::$group;
		$query.= self::$having;
		$query.= self::$order;
		if( self::$limit > 0 ) $query.= " LIMIT " . self::$offset. ", ".self::$limit;

		return self::execute( $query , true );
    }

    public static function insert( $params ){
        $columns = self::array_to_set( $params );
        $query = "INSERT INTO " . static::$tabla;
		$query.= " SET $columns";

        return self::execute( $query );
    }

    public static function update( ){}
    public static function delete( ){}

    /*
    * Query Handler methods
    */

	protected static function add_table( $table, $on, $left_right = '' ){
		self::$joins[ ] = " $left_right JOIN $table ON $on ";
		self::$join = implode( "\n ", self::$joins );
	}

	protected static function where( $condition ){
		self::$where = " WHERE $condition ";
	}

	protected static function group( $column ){
		self::$group = " GROUP BY $column ";
	}

	protected static function having( $condition ){
		self::$having = " HAVING $condition ";
	}

	protected static function order( $columns ){
		self::$order = " ORDER BY $columns ";
	}

	protected static function limit( $cantidad, $inicio = 0 ){
		self::$offset = $inicio;
		self::$limit = $cantidad;
	}

	protected static function table( $new_table, $table_alias = NULL ){
		static::$tabla = $new_table;
		self::$alias = $table_alias ?? $new_table;
	}

	protected static function reset( ){
		self::limit( static::$limit );
		self::$join = self::$where = self::$group = self::$having = self::$order = '';
		self::$joins = self::$bindedParams = [ ];
		self::$getsql = false;
	}

    protected static function bind( $params ){
        self::$bindedParams = $params;
    }

	protected static function get_sql( ){
		static::$getsql = true;
	}

	protected static function empty( ){
		static::$empty = true;
	}

    /*
    * Execute SQL String to Database 
    */
	protected static function raw( $query ){
		return self::execute( $query, true );
	}

    protected static function execute( $query_sql, $iterar = false ){
        self::getConnection( );

        /*
echo $query_sql;
var_dump(self::$bindedParams);
        $stm = self::$cnx->prepare( $query_sql );
        foreach( self::$bindedParams as $param => $value ){
            var_dump($param, $value );
            $stm->bindParam( $param, $value );
        }

        try{
            $stm->execute( );
        }catch( PDOException $error ){
            return [
                'status' => 0,
                'error' => $error->getMessage( ),
                'query' => $query_sql,
                'qtty' => 0,
                'rows' => [ ],
                'id' => 0
            ];
        }

        $respuesta = [
            'status' => 1,
            'qtty' => $stm->rowCount()
        ];

        if( $lastId = self::$cnx->lastInsertId( ) ){
            $respuesta['id'] = $lastId;
        }


        if( $iterar ):
            $filas = [ ];
            while( $r = $stm->fetch( PDO::FETCH_ASSOC ) ){
                $filas[] = $r;
            }
            $respuesta['rows'] = $filas;
        endif;

        return $respuesta;
    }


    private static function getPrimitiveValue( $val ){
        if( $val === false ) $val = 0;
        if( $val === true ) $val = 1;
        if( ! static::$empty && empty( trim( $val ) ) && $val !== 0 && $val !== '0' ) $val = NULL;
        return $val;
    }

    private static function array_to_set( $array, $values = [ ], $bindedParams = [ ] ){ 
		foreach( $array as $f => $v ){ 
            $param = strtolower($f);
            $v = self::getPrimitiveValue($v);
			if( preg_match( '/^now(\(\s?\))?$/i' , $v ) )  $values[ ] = "$f=NOW( )";
			elseif( preg_match( '/^date\(\s?\)$/i', $v  ) ) $values[ ] = "$f=DATE( NOW( ) )";
			elseif( preg_match( '/^time\(\s?\)$/i', $v  ) ) $values[ ] = "$f=TIME( NOW( ) )";
            /*elseif( preg_match( '/^query\:/i' , $v ) ){
                $subconsulta = str_ireplace( "query:", "", $v );
                if( preg_match_all( "/=(\d+|'\d+'|\".+?\"|'.+?')/", $subconsulta, $matches ) ){
                    foreach($matches[0] as $index => $reemplazar){
                        $param = "sub_".uniqid( );
                        $subconsulta = str_replace($reemplazar, "=:$param", $subconsulta);
                        $valor = preg_replace("/[\"'=]/", "", $matches[1][$index] );
                        $bindedParams[":$param"] = $valor;
                    }
                }
                $values[ ] = "$f=($subconsulta)";
            }
            */
            else{
                $values[ ] = "$f=:$param";
                $bindedParams[":$param"] = $v;
            }
		}

        self::bind( $bindedParams );
		return implode( ', ' , $values );
	}
}