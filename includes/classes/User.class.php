<?php

/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/09
*
*/

class User
{
  static function setProject()
  {
    if ( isset($_POST['project']) && intval($_POST['project'])>0 ) 
    {
      $_SESSION['projectID'] = intval($_POST['project']);
    }
  }

  static function Login()
  {
    if ( isset($_POST['do']) ) 
    {
      if ($_POST['login'] == USER_LOGIN && USER_PASSWORD == $_POST['password'] ) 
      {
        $hash = md5(sha1($_POST['password']));
        $_SESSION['logged_in'] = $hash;
        setcookie('logged_in',$hash);
        header('Location: '.URL);
        exit;
      } 
    }
  }

  static function LoggedIn()
  {
    if( !isset($_COOKIE['logged_in']) || $_COOKIE['logged_in'] != md5(sha1(USER_PASSWORD)) )
    {
      return false;
    }

    if( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != md5(sha1(USER_PASSWORD)) )
    {
      return false;
    }    

    return true;
  }

  static function Logout()
  {
      setcookie('logged_in','');
      session_destroy();
      header('Location: '.URL);
      exit;
  }  

}
?>
