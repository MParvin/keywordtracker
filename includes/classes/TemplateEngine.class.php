<?php

/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/09
*
*/

class TemplateEngine
{ 
  public  $html         = '';

  public function __construct()
  {
    self::generateHeader();
    self::generateNavigation();
    self::generateContent();
    self::generateFooter();
  }

  public function setVar($location,$content)
  {
    $this->html = str_replace($location,$content,$this->html);
  }

  public function showLoginForm()
  {
    $this->html = str_replace('%%CONTENT%%',file_get_contents( TEMPLATE_DIR.DIRECTORY_SEPARATOR.'login.tpl' ),$this->html);
  }

  private function generateHeader()
  {
    $this->html = file_get_contents( TEMPLATE_DIR.DIRECTORY_SEPARATOR.'header.tpl' );
  }

  private function generateNavigation()
  {
    $this->html .= file_get_contents( TEMPLATE_DIR.DIRECTORY_SEPARATOR.'navigation.tpl' );
  }

  private function generateContent()
  {
    $this->html .= file_get_contents( TEMPLATE_DIR.DIRECTORY_SEPARATOR.'content.tpl' );
  }

  private function generateFooter()
  {
    $this->html .= file_get_contents( TEMPLATE_DIR.DIRECTORY_SEPARATOR.'footer.tpl' );
  }

  public function displayTemplate()
  {
    self::setVar('%%URL%%',URL);
    echo $this->html;
  }
  
}


?>
