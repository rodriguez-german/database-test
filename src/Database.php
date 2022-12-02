<?php

namespace PadawansTrainer\DatabaseHandler;

use PadawansTrainer\DatabaseHandler\Core\Base;

class Database extends Base
{

    public static function one( $id = 0 ){
        $consulta = "SELECT * FROM ". static::$tabla ." WHERE id='$id' ORDER BY 1 DESC";
        $resultados = self::execute( $consulta, true );

        return $resultados['rows'][0] ?? NULL;
    }
    //select( 'COUNT(*) AS cantidad, columna1 AS c1, columna2 AS c2, columnaN', [ 'where' => 'columna > 10 AND columna2 IS NOT NULL', 'order' => 'columna1 DESC' ] )
    //select( ) //select * from tabla





    public static function select( $columnas ='*', $clausulas = [ ] ){
		static::reset( );
		static::table( $clausulas['table'] ?? static::$tabla , static::$alias );

		if( isset( $clausulas['joins'] ) ):
            foreach( $clausulas['joins'] as $j ){
                $left_right = $j['type'] ?? '';
                self::$joins[ ] = " $left_right JOIN $j[table] ON $j[on] ";
                self::$join = implode( "\n ", self::$joins );
            }
        endif;

		if( isset( $clausulas['limit'] ) ):
            if( is_array( $clausulas['limit'] ) ){
                static::limit( $clausulas['limit'][1], $clausulas['limit'][0] ); 
            }else{
                static::limit( $clausulas['limit'] );
            }
        endif;

        if( isset( $clausulas['where'] ) ): static::where( $clausulas['where'] ); endif;
        if( isset( $clausulas['params'] ) ): static::bind( $clausulas['params'] ); endif;
		if( isset( $clausulas['group'] ) ): static::group( $clausulas['group'] ); endif;
		if( isset( $clausulas['having'] ) ): static::having( $clausulas['having'] ); endif;
        if( isset( $clausulas['order'] ) ): static::order( $clausulas['order'] ); endif;

        if( isset( $clausulas['get_sql'] ) ): static::get_sql( $clausulas['get_sql'] ); endif;

		return static::get( $columnas );
    }

    public static function get_paginador( ){
        $query = "SELECT COUNT(*) AS cantidad ";
        $query.= "FROM ". static::$tabla . " AS " .static::$alias;
        $query.= self::$where;
        $query.= self::$group;
        $query.= self::$having;

        $resultados = self::execute( $query , true );

        $cantidad_filas = $resultados['rows'][0]['cantidad'];
        $cantidad_links = ceil( $cantidad_filas / self::$limit );

        return [
            'pages' => $cantidad_links,
            'page' => self::$page,
            'total' => $cantidad_filas,
            'limit' => self::$limit,
            'start' => self::$offset,
            'end' => self::$offset + self::$limit
        ];
    }



}