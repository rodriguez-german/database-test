<?php
namespace PadawansTrainer\DatabaseHandler;

use Composer\Script\Event;

class ScriptHandler
{
    private static $iniFilename = 'padawans-trainer-db.ini';

    public static function buildConfiguration(Event $event): void
    {
        $directoryInput = self::handleDirectoryInput( $event );
        $directoryOutput = self::createDirectoryFromInput( $directoryInput, $event );
        $realPath = str_replace( ["/", "\\"], DIRECTORY_SEPARATOR, $directoryOutput );

        self::saveIniFiles( $realPath );

        $event->getIO()->write('<warning>Please, before using PadawansTrainer\DatabaseHandler, you must edit parameters in '.$realPath.DIRECTORY_SEPARATOR.self::$iniFilename.'</warning>');
    }


    public static function updateConfiguration( )
    {
        console.log('updating');
    }


    private static function handleDirectoryInput( Event $event ): String
    {
        $directoryInput = '';
        do{
            $loop = false;
            $directoryInput = $event->getIO()->ask('Please, type the relative route from composer.json location, where the PadawansTrainer\DatabaseHandler configuration file will be created (Default `vendor` parent folder): ');
            $directoryInput = trim( $directoryInput );

            $response = self::validateDirectoryInput( $directoryInput );
            if( ! $response ){ $event->getIO()->write('<error>Directory structure cannot contain `../` paths</error>' ); $loop = true; }

            $response = self::validateDirectoryChars( $directoryInput );
            if( ! $response ){ $event->getIO()->write('<error>Directory structure cannot contain any of these characters `#<$+%>!`&*\'|{?"=}:@`</error>' ); $loop = true; }

            $response = self::validateDirectoryDots( $directoryInput );
            if( ! $response ){ $event->getIO()->write('<error>Directory name cannot end with dots</error>' ); $loop = true; }
        }while( $loop );

        return $directoryInput;
    }


    private static function validateDirectoryInput( String $directory ): Bool
    {
        $response = preg_match( "/(\B\.\.\/)/", $directory );
        return !$response;
    }


    private static function validateDirectoryChars( String $directory ): Bool
    {
        $response = preg_match( "/[#<$\+%>\!`&\*'|\{\?\"=\}:@]/", $directory );
        return !$response;
    }


    private static function validateDirectoryDots( String $directory ): Bool
    {
        $response = preg_match( "/\.\//", $directory );
        if( $response ) return false;

        $response = preg_match( "/\.\/?$/", $directory );
        return !$response;
    }


    private static function createDirectoryFromInput( String $directoryInput, Event $e ): String
    {
        $vendorDir = $e->getComposer()->getConfig()->get('vendor-dir');
        $root = dirname($vendorDir);
        $root = str_replace( ["/", "\\"], DIRECTORY_SEPARATOR, $root );

        $dirPath = str_replace( DIRECTORY_SEPARATOR, "/", $directoryInput );

        $partes = explode( "/", $dirPath );
        if(count($partes) == 0 || (count($partes) == 1 && empty(trim($partes[0]))) ) return $root;
        $currentDirectory = $root;
        foreach( $partes as $subDir ){
            $currentDirectory.= DIRECTORY_SEPARATOR.$subDir ;
            if(!is_dir($currentDirectory)) mkdir( $currentDirectory, '0775' );
        }
        return $currentDirectory.DIRECTORY_SEPARATOR;
    }


    private static function saveIniFiles(String $path): void
    {
        $currentDirectory = __DIR__.DIRECTORY_SEPARATOR;
        @file_put_contents( $currentDirectory.'route.ini', "path=$path" );
        @copy( $currentDirectory.'config.ini', $path. DIRECTORY_SEPARATOR. self::$iniFilename );
    }


    public static function getConfiguration( ): Array
    {
        $ini = @parse_ini_file( __DIR__ . DIRECTORY_SEPARATOR . 'route.ini' );
        $path = $ini['path'] ?? NULL;
        $content = @parse_ini_file($path.DIRECTORY_SEPARATOR.self::$iniFilename, true);

        return $content ?? [ ];
    }
}