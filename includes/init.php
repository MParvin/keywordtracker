<?php

  if( DB_USER == '' || DB_PASS == '' || DB_NAME == '' )
  {
    die('<strong>Fehler:</strong> Datenbanknutzer, Datenbankpasswort oder Datenbankname sind leer.');
  }

  if( USER_LOGIN == 'admin' || USER_PASSWORD == 'admin')
  {
    die('<strong>Fehler:</strong> Login oder Passwort sind noch nicht ver&auml;ndert worden.');
  }

  if( substr(URL, -1) != DIRECTORY_SEPARATOR )
  {
    die('<strong>Fehler:</strong> Die URL zum Tracker muss mit '.DIRECTORY_SEPARATOR.' enden!');
  }

  if(!function_exists('classAutoLoader'))
  {
    function classAutoLoader($class)
    {
      $classFile  = 'includes'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.''.$class.'.class.php';

      if( is_file( $classFile ) ) 
      {
        if( !class_exists( $class ) ) 
        {
          include_once $classFile;
        }
      } 
      else 
      {
        die( '<strong>Fehler:</strong> Klasse "'.$class.'" konnte nicht geladen werden! Bitte Klassennamen und Pfade &uuml;berpr&uuml;fen!' );
      }
    }
  }
  
  spl_autoload_register('classAutoLoader');
?>
