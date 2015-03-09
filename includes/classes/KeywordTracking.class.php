<?php

/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/09
*
*/

class KeywordTracking 
{
  private $Template = '';
  private $Tool = NULL;

  public function start()
  {
    session_start();
    User::Login();
    
    $this->Template = new TemplateEngine;
    
    if( !User::LoggedIn() )
    {
      $this->Template->setVar('%%PAGETITLE%%','Kein Zugriff - Keyword-Tracker');  
      $this->Template->setVar('%%NAVIGATION%%','');
      $this->Template->showLoginForm();
      $this->Template->setVar('%%PROJECTS%%',''); 
    } 

    if( User::LoggedIn() )
    {
      User::setProject();
      self::showRequestedContent();
    } 

    $this->Template->displayTemplate();  
  }

  private function EditKeywords()
  {
    $this->Template->setVar('%%PAGETITLE%%','Keywords hinzufügen/bearbeiten - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->getKeywordList()); 
    $this->Template->setVar('%%TEXT%%',$this->Tool->getKeywordEditText());
    $this->Template->setVar('%%INFORMATION%%',$this->Tool->getKeywordEditInfo());
  }

  private function CSVKeywords()
  {
    $this->Template->setVar('%%PAGETITLE%%','Keywords als CSV - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->showCSV());  
    $this->Template->setVar('%%INFORMATION%%',$this->Tool->getCSVText());
  }

  private function showSettings()
  {
    $this->Template->setVar('%%PAGETITLE%%','Einstellungen - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->Settings());  

  }

  private function last7()
  {
    $this->Template->setVar('%%PAGETITLE%%','Vergangene Tage - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->createLast7Table());  
  } 

  private function genSummary()
  {
    $this->Template->setVar('%%PAGETITLE%%','Keyword-Index - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->genIndexOverKeywords());  
  } 

  private function genHistory()
  {
    $this->Template->setVar('%%PAGETITLE%%','Keyword-History - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->genHistoryForm());  
  }

  private function genChart()
  {
    $this->Template->setVar('%%PAGETITLE%%','Entwicklung für Keyword - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->genKeyWordChart());  
  }

  private function batchKeywords()
  {
    $this->Template->setVar('%%PAGETITLE%%','Batch-Eintragen von Keywords - Keyword-Tracker'); 
    $this->Template->setVar('%%NAVIGATION%%',''); 
    $this->Template->setVar('%%CONTENT%%',$this->Tool->batchKeywordForm());  
    $this->Template->setVar('%%TEXT%%',$this->Tool->getKeywordEditText());
    $this->Template->setVar('%%INFORMATION%%',$this->Tool->getKeywordEditInfo());
  }

  private function showRequestedContent()
  {
    $this->Tool = new Toolbox;
    $this->Template->setVar('%%PROJECTS%%',$this->Tool->showSelectedProject()); 

    if( !isset($_GET['action']) )
    {
      $_GET['action'] = 'last5';
    }

    switch ($_GET['action'])
    {
      case 'last5':
        self::last7();
        break;
      case 'edit':
        self::EditKeywords();
        break;
      case 'batch':
        self::batchKeywords();
        break;        
      case 'csv':
        self::CSVKeywords();
        break;
      case 'settings':
        self::showSettings();
        break;
      case 'summary':
        self::genSummary();
        break;
      case 'chart':
        self::genChart();
        break; 
      case 'history':
        self::genHistory();
        break;                  
      case 'logout':
        User::Logout();
        break;
      default:
        self::last7();
    }
  }

}

?>
