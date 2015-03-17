<?php

/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/09
*
*/

class Toolbox
{
  private $DB   = NULL;
  private $data = array();
  private $jqPlot_title= '';
  private $jqPlot_data = array();
  private $message = '';
  public function __construct()
  {
    $this->DB = new DB;
    self::addKeyword();
    self::saveKeyword();
    self::deleteKeyword();
    self::saveSettings();
	  self::doCommentBox();
    self::doCSVExport();
  }

  private function doCSVExport()
  {
    if( isset($_GET['action']) && $_GET['action']=='history' )
    {

      if( isset($_GET['kwID']) && $_GET['kwID']>0 )
      {

        $kwID=intval($_GET['kwID']);
        
        $fp = fopen('php://output', 'w'); 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.self::slugify( 'ranking-'.self::getKeyWordNameByID($kwID) ).'.csv"');
        header('Pragma: no-cache');    
        header('Expires: 0');

        $header = array('Datum','Position','URL');
        fputcsv($fp, $header);

        $query = "SELECT DATE_FORMAT(kwTime,'%Y-%m-%d'),kwPos,kwURL FROM seotracker_rankings WHERE kwID=$kwID ORDER BY kwTime ASC";
        $this->DB->setQuery($query);
        $result = $this->DB->getResults();

        if( !isset($result['error']) ) 
        {
          foreach($result as $row)
          {
            if( !isset($row['error']) )
            {
              if( $row['kwPos'] == 0 ) {
                $row['kwPos'] = '';
              }
              fputcsv($fp, $row);
            }
          }
        }
        
        exit;    
      }

    }
    
  }

  private function slugify($text)
  {
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text;

  }
  private function doCommentBox()
  {
	if( isset($_POST['save_comment']) )
	{
		if( $_POST['comment_kwID']>0)
		{
			$comment = $this->DB->escStr( $_POST['comment'] );
			$query = "UPDATE seotracker_keywords SET kwComment='".$comment."' WHERE kwID='".intval($_POST['comment_kwID'])."' LIMIT 1";
			$this->DB->setQueriesForCommit($query);
			$this->DB->startTransaction();
		}
	}
	
	if( isset( $_POST['delete_comment']) )
	{
			$query = "UPDATE seotracker_keywords SET kwComment='' WHERE kwID='".intval($_POST['comment_kwID'])."' LIMIT 1";
			$this->DB->setQueriesForCommit($query);
			$this->DB->startTransaction();	
	}
  }
  
  public function getKeywordList()
  {
    $html = '   <div class="editkeyword_list">
                <form action="%%URL%%?action=edit" method="POST">
                <input name="keyword"type="text">
                <input value="Add" name="add_keyword" type="submit">
                </form>'."\n";
    if( isset($_SESSION['projectID']) )
    {

      $query = "SELECT * FROM seotracker_keywords WHERE pID= '".intval($_SESSION['projectID'])."' ORDER BY kwID DESC";
      $this->DB->setQuery($query);
      $result = $this->DB->getResults();
      
      if ( !isset($result['error']) ) 
      {
        foreach( $result as $key)
        {
          $html .= '
                    <form action="%%URL%%?action=edit" method="POST">
                    <input name="kwID" value="'.$key['kwID'].'" type="hidden">
                    <input name="keyword" value="'.$key['kwText'].'" type="text">
                    <input value="Save" name="save_keyword" type="submit">
                    <input value="Delete" name="delete_keyword" type="submit">
                    </form>'."\n";      
        }
      }

    }

    $html .= '
                </div>
                  <div class="info">
                 Hier kannst du schnell und einfach neue Keywords eintragen, ändern oder komplett löschen. Größere Änderungen am Keyword werden nicht empfohlen, vor allem nicht, wenn der Rechtschreibfehler länger besteht. Beim Bearbeiten werden die Rankings für die Keyword-ID beibehalten und stimmen womöglich nicht mehr. Beim Löschen werden alle Verweise auf das Keyword, also Charts, Kommentare sowie alle Rankings aus der Datenbank entfernt.
                  </div>               
                  <div class="info">
                  <strong>Zusätzliche Informationen</strong><br/>
                  %%INFORMATION%%
                  </div>
                  <div class="info">
                  %%TEXT%%
                  </div>
                '."\n";
    return $html;
  } 

  public function getCSVText()
  {
    return 'Die Liste auf der linken Seite beinhaltet alle Keywords in der Datenbank. Genutzt werden kann sie beispielsweise, um die Keywords in Excel zu importieren oder schnell eine Trafficschätzung mit Hilfe von Google Adwords durchzuführen.';
  }
  
  public function getKeywordEditText()
  {
    return 'WICHTIG: Das Script ist dazu ausgelegt ca. 300 Keywords am Tag zu tracken. Ich empfehle jedem Nutzer bei den Standardeinstellungen zu bleiben. So wird Google den Crawler nicht blockieren und die Server-IP sperren! Wer weniger als 300 Keywords tracken will, kann die Werte entsprechend anpassen. <br />
            <strong>Ein Rechenbeispiel:</strong> Wir wollen 150 Keywords tracken.<br />
            In den Einstellungen geben wir die fünf Uhrzeiten 2,6,10,14,18 Uhr vor. Vier Stunden zwischen den Updates reichen vollkommen aus, um Google nicht misstrauisch zu machen!<br />150 Keywords durch 5 Updatezeitpunkte ergeben 30 Keywords pro Zeitpunkt.';
  }

  
  public function showSelectedProject()
  {

    $query = "SELECT * FROM seotracker_projects ORDER BY pID ASC";
    $this->DB->setQuery($query);
    $results = $this->DB->getResults();
    if( !isset($_GET['action']) )
    {
      $action = 'last5';
    } 

    switch($_GET['action'])
    {
      case 'summary':
        $action = 'summary';
      break;
      case 'edit':
        $action = 'edit';
      break;
      case 'batch':
        $action = 'batch';
      break;
      case 'csv':
        $action = 'csv';
      break;   
      case 'settings':
        $action = 'settings';
      break;   
      case 'history':
        $action = 'history';
      break;   
      case 'chart':
        $action = 'chart';
      break;   
      case 'logout':
        $action = 'logout';
      break;               
      default:
        $action = 'last5';
    }

    $action = htmlspecialchars($action);

    $html = ' <li class="dp_p">
      <form method="POST" action="%%URL%%?action='.$action.'">Projekt:<select name="project" onchange="this.form.submit()">';

    if( isset($results['error']) )
    {
      unset($_SESSION['projectID']);
      $html .= '<option selected>Bitte Projekt anlegen</option>';   
    }

    if( !isset($results['error']) ) 
    {

      if( !isset($_SESSION['projectID']) )
      {

        foreach($results as $key => $result)
        {

          if( $key == 0)
          {
            $_SESSION['projectID'] = intval($result['pID']);
            $html .= '<option value="'.$result['pID'].'" selected>'.$result['pURL'].'</option>';
          }
          else
          {
            $html .= '<option value="'.$result['pID'].'">'.$result['pURL'].'</option>';
          }
        }   
      } 
      else if ( isset($_SESSION['projectID'] ) )
      {
          foreach($results as $key => $result)
          {
            if( $result['pID'] == $_SESSION['projectID'])
            {
              $_SESSION['projectID'] = $result['pID'];
              $html .= '<option value="'.$result['pID'].'" selected>'.$result['pURL'].'</option>';
            }
            else
            {
              $html .= '<option value="'.$result['pID'].'">'.$result['pURL'].'</option>';
            }

          }  
      }
    }

    $html .= '</select></form>
    </li>';
    return $html;
  }
  public function Settings()
  {
    $html = '';


    $query = "SELECT * FROM seotracker_settings WHERE settingID=1";
    $this->DB->setQuery($query);
    $setting = $this->DB->getRow();
    $html .= ' <form method="POST"><div class="setting">
                <div class="left name">Uhrzeiten: </div>
                <div class="left"><input name="time" class="site" type="text" value="'.$setting['update_time'].'"><input name="save_settings" class="edit_save" value="Save" type="submit"></div>
                <div class="info info_right">Stunden (0-23), an denen Tracker gestartet werden soll. <strong>Nur ganze Zahlen, komma-getrennt</strong></div>
              </div></form>';
    if( !isset($_GET['pID'] ) )
    {
    $html .= '  
                <form method="POST"><div class="setting">
                <div class="left name">Projekt: </div>
                <div class="left"><input name="site" class="site" type="text" value=""><input class="edit_save" value="Add" type="submit">
                </div></form>';
    }
    else
    {
    $query = "SELECT * FROM seotracker_projects WHERE pID=".intval($_GET['pID']);
    $this->DB->setQuery($query);
    $site = $this->DB->getRow();
    $html .= '  
                <form method="POST"><div class="setting">
                <div class="left name">Projekt: </div>
                <div class="left"><input name="site_save" class="site" type="text" value="'.$site['pURL'].'"><input class="edit_save" value="Save" type="submit">
                </div></form>';
    }
    $html .= ' 
                <div class="info info_right">Die Website, für die die Rankings  getrackt werden sollen. <strong>Ohne http:// oder https://</strong><hr />
                Aktive Projekte:<br />
                <ul class="projects">
                  '.self::showActiveProjects().'
                </ul>
                </div>
              </div>';

    $html .= '';

    $hours = explode(',',$setting['update_time']);  
    $hours_c = count($hours);
    
    $query = "SELECT COUNT(*) as number FROM seotracker_keywords";
    $this->DB->setQuery($query);
    $keyword = $this->DB->getRow();
    
    $keywords = $keyword['number'];
    
    if( $keywords/$hours_c > 50)
    {
      $html .= '<div class="error">Warnung: Pro eingetragene Stunde, werden mehr als 50 Keywords aktualisiert. Das könnte dazu führen, dass Google die IP des
              Servers blockiert. Ich empfehle bei weniger als 50 Keywords zu bleiben. Dazu sollte entweder die Anzahl an Keywords verkleinert werden oder die Anzahl an
              Stunden erhöht werden. Mein Erfahrungswert liegt bei etwa 70-75 Keywords bzw. Zugriffe pro Stunde, ab dann steigt die Gefahr seitens Google blockiert zu werden.</div>';
    }

    return $html;
  }
  
  private function showActiveProjects()
  {
    $query = "SELECT * FROM seotracker_projects ORDER BY pID ASC";
    $this->DB->setQuery($query);
    $results = $this->DB->getResults();
    $html = '';

    if( !isset($results['error']) )
    {
      foreach( $results as $result)
      {
        $html .= '<li><a href="%%URL%%?action=settings&amp;pID='.$result['pID'].'"><i class="fa fa-pencil projectbutton"></i></a><a class="deleteProject" href="%%URL%%?action=settings&amp;delID='.$result['pID'].'"><i class="fa fa-trash-o projectbutton"></i></a><strong>'.$result['pURL'].'</strong></li>';
      }
    }
  
    return $html;
  }
  public function getKeywordEditInfo()
  {
    $html = '';
    if( isset($_SESSION['projectID']) )
    {
      $query = "SELECT COUNT(*) as number FROM seotracker_keywords WHERE pID=".intval($_SESSION['projectID']);
      $this->DB->setQuery($query);
      $keywordProject = $this->DB->getRow();

      $query = "SELECT * FROM seotracker_projects WHERE pID=".intval($_SESSION['projectID']);
      $this->DB->setQuery($query);
      $setting = $this->DB->getRow();
      $html .= 'Ausgewähltes Projekt: <strong class="chP">'.$setting['pURL'].'</strong> mit '.$keywordProject['number'].' Keywords<br /><hr />';

      $query = "SELECT * FROM seotracker_settings WHERE settingID=1";
      $this->DB->setQuery($query);
      $setting = $this->DB->getRow();
      $html .= 'Uhrzeiten der Aktualisierung: ';
      $hours = $setting['update_time'];
      $hours = explode(',',$hours);
      $tPh   = count($hours);
      $hours_a = array();

      foreach( $hours as $hour)
      {
        $hours_a[] = $hour.' Uhr';
      }

      $hours = implode(', ',$hours_a);
      $html .= $hours;
   
      $query = "SELECT COUNT(*) as number FROM seotracker_keywords";
      $this->DB->setQuery($query);
      $keyword = $this->DB->getRow();
      $html .= "<br />Keywords insgesamt: ".$keyword['number'];
      $kwPh  = $keyword['number']/$tPh;
      $kwCeil= ceil($kwPh);
      if($kwCeil > 50)
      {
        $kwCeil = '<strong style="color:red">'.$kwCeil.'</strong> (Wert sollte maximal 50 sein, <a style="color:#118BD9;text-decoration:none" href="%%URL%%?action=settings">siehe Einstellungen</a>)';
      }
      $html .= '<br />Keywords pro Durchlauf: '.$kwCeil;
    }
    else
    {
      $html = 'Bitte Projekt anlegen';
    }
    return $html;    
  }

  public function showCSV()
  {
    $html = '<textarea>';
    if( isset($_SESSION['projectID']) )
    {
      $query = "SELECT * FROM seotracker_keywords WHERE pID='".intval($_SESSION['projectID'])."' ORDER BY kwID DESC";
      $this->DB->setQuery($query);
      $result = $this->DB->getResults();

      if( !isset($result['error']) ) 
      {
        foreach( $result as $key)
        {
          $html .= $key['kwText']."\n";      
        }
      }
    }
    $html .= '</textarea>';

    $html .= '<div class="info">%%INFORMATION%%</div>';
    return $html;
  } 

  private function addKeyword()
  {
    if( isset($_POST['add_keyword']) && $_POST['keyword'] != '' && isset($_SESSION['projectID']) )
    {
      $kwText = strtolower(trim($_POST['keyword']));
      $kwText = str_replace('  ',' ',$kwText);        
      $kwText = $this->DB->escStr($kwText);

      $query = "INSERT INTO seotracker_keywords (kwText,pID) VALUES ('".$kwText."','".intval($_SESSION['projectID'])."')";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      self::reOrderKeywordAfterChange();
    }

    if( isset( $_POST['start_batch'] ) && $_POST['kw_batch'] != '' && isset($_SESSION['projectID']) )
    {
      $keyword_array = explode(PHP_EOL, $_POST['kw_batch']);
      $keyword_array = array_filter($keyword_array, 'trim');

      foreach($keyword_array as $keyword)
      {
        $keyword = strtolower(trim($keyword));
        $keyword = str_replace('  ',' ',$keyword);        
        $kwText = $this->DB->escStr($keyword);
        $query = "INSERT INTO seotracker_keywords (kwText,pID) VALUES ('".$kwText."','".intval($_SESSION['projectID'])."')";
        $this->DB->setQueriesForCommit($query);            

      }
      $this->DB->startTransaction();
      self::reOrderKeywordAfterChange();

    }
  }


  private function saveKeyword()
  {
    if( isset($_POST['save_keyword']) && $_POST['kwID'] > 0)
    {
      $kwID   = intval($_POST['kwID']);
      $kwText = $this->DB->escStr($_POST['keyword']);
      $query = "UPDATE seotracker_keywords SET kwText='".$kwText."' WHERE kwID='".$kwID."' AND pID='".intval($_SESSION['projectID'])."' LIMIT 1";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      self::reOrderKeywordAfterChange();
    }
  }

  private function deleteKeyword()
  {
    if( isset($_POST['delete_keyword']) )
    {
      $kwID   = intval($_POST['kwID']);
      $query = "DELETE FROM seotracker_keywords WHERE kwID='".$kwID."' AND pID='".intval($_SESSION['projectID'])."' LIMIT 1";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      self::reOrderKeywordAfterChange();
    }
  }

  private function saveSettings()
  {
    if( isset($_POST['save_settings']) )
    {
      $time   = $this->DB->escStr($_POST['time']);
      $time   = trim($time,' ');
      $time   = explode(',',$time);
      $times  = array();
      foreach( $time as $hour)
      {
        $times[$hour] = intval($hour);
      }

      array_unique($times);

      $time = implode(',',$times);
    
      $query = "UPDATE seotracker_settings SET update_time='".$time."' WHERE settingID='1' LIMIT 1";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      self::reOrderKeywordAfterChange();
    }

    if( isset($_POST['site_save'] ) )
    {
      $pURL = $this->DB->escStr($_POST['site_save']);
      $query = "UPDATE seotracker_projects SET pURL='".$pURL."' WHERE pID='".intval($_GET['pID'])."' LIMIT 1";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      header("Location: ".URL."?action=settings");
    }

    if( isset($_POST['site'] ) && filter_var('http://'.$_POST['site'],FILTER_VALIDATE_URL) )
    {
      
      $pURL = $this->DB->escStr($_POST['site']);
      $pURL = str_replace('https://','',$pURL);
      $pURL = str_replace('http://','',$pURL);

      $query = "INSERT INTO seotracker_projects (pURL) VALUES('".$pURL."')";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      header("Location: ".URL."?action=settings");
    }

    if( isset($_GET['delID']) && $_GET['delID']>0)
    {
      $query = "DELETE FROM seotracker_projects WHERE pID='".intval($_GET['delID'])."' LIMIT 1";
      $this->DB->setQueriesForCommit($query);
      $this->DB->startTransaction();
      $query = "SELECT pID FROM seotracker_projects ORDER BY pID ASC LIMIT 1";
      $this->DB->setQuery($query);
      $result = $this->DB->getRow();
      $_SESSION['projectID'] = $result['pID'];
      header("Location: ".URL."?action=settings");      
    }
  }

  private function reOrderKeywordAfterChange()
  {
	
    $query = "SELECT * FROM seotracker_settings WHERE settingID=1";
    $this->DB->setQuery($query);
    $setting = $this->DB->getRow();
    $hours = $setting['update_time'];
    $hours = explode(',',$hours);
    $tPh   = count($hours);

    $query = "SELECT COUNT(*) as number FROM seotracker_keywords";
    $this->DB->setQuery($query);
    $keyword = $this->DB->getRow();
    $kwPh  = $keyword['number']/$tPh;
    $limitPhour = ceil($kwPh);
  
    $i = 0;
    foreach($hours as $hour)
    {
      if($i < $tPh+1) 
      {
        $start = $i*$limitPhour;
        $query = "UPDATE seotracker_keywords SET kwTime=$hour WHERE kwID IN (
                   SELECT kwID FROM (
                     SELECT kwID FROM seotracker_keywords 
                     ORDER BY kwID DESC  
                     LIMIT $start, $limitPhour
                    ) tmp
                  )";
        $this->DB->setQueriesForCommit($query);
      }
      $i++;
    }
    
    $this->DB->startTransaction();
  }

  private function createDateTableHead() 
  {
    $day = 0;
    $html = '';

    while( $day < 7)
    {
      $dt = new DateTime("-$day day");
      $the_date = $dt->format('d.m.Y');
      $html .= "<th class='label' id='label'><div id='thDIV'>".$the_date."</div></th>\n";
      if ($day<6)
      {
        $html .= "<th class='label' id='label'><div id='thDIV'>&larr;</div></th>\n";
      }
      $day++;
    } 

    return $html;
  }

  private function createDataTable()
  {
    if( !isset($_SESSION['projectID']) )
    {
        return self::formatDataTable();
    }

    $keywords = "SELECT * FROM seotracker_keywords WHERE pID='".intval($_SESSION['projectID'])."' ORDER BY kwID DESC";

    $this->DB->setQuery($keywords);
    $results = $this->DB->getResults();

    
    if( !isset($results['error']) )
    {
      $this->data = array();
      foreach( $results as $result)
      {

        $kwID     = $result['kwID'];
        $kwText   = $result['kwText'];
        $kwComment= $result['kwComment']; 
        $kwTime	  = $result['kwUpdated'];
        $kwPos    = self::getPositions($kwID);
        $kwBest   = self::bestPosition($kwID);
        $this->data[$kwID] = array($kwID,$kwText,$kwComment,$kwBest,$kwPos,$kwTime);
      }
    }

    return self::formatDataTable();
  }

  private function hasComment($text)
  {
    if( $text == '')
    {
      return 'nocomment';

    }
    return '';
  }
  private function formatDataTable()
  {
    $html = '';
    
    foreach( $this->data as $kwID => $kwData)
    {
     
      $kwData[4] = self::rePlaceNULL($kwData[4]);
      
      $html .= '<tr class="subDataTable">	
      <td><span>'.$kwData[0].'</span></td>
      <td><span><a class="show_tt" title="History zum Keyword als CSV exportieren" href="%%URL%%?action=history&kwID='.$kwData[0].'"><i class="fa fa-download"></i></a></span></td>
		  <td style="font-weight:bold;" class="pagesTitle"><a class="show_tt" title="Letztes Update: '.$kwData[5].'" target="_blank" href="https://www.google.de/search?oe=utf-8&amp;pws=0&amp;complete=0&amp;hl=de&amp;num=100&amp;q='.$kwData[1].'">'.$kwData[1].'</a></td>
      <td><span><a class="commentbutton show_tt '.self::hasComment($kwData[2]).'" title="'.htmlspecialchars($kwData[2]).'" data-comment="'.htmlspecialchars($kwData[2]).'" data-kwid="'.$kwData[0].'"><i class="fa fa-comment-o"></i></a></span></td>
		  <td><span><a href="%%URL%%?action=chart&amp;kwID='.$kwData[0].'"><i class="fa fa-area-chart"></i></a></span></td>
  		<td><span style="font-weight:bold;">'.self::parseBestPos($kwData[3]).'</span></td>	
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[3],$kwData[4][0]['kwPos']).'</span></td>          
      '.self::createPosData($kwData[4][0]['kwPos'],$kwData[4][0]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][1]['kwPos'],$kwData[4][0]['kwPos']).'</span></td>      
      '.self::createPosData($kwData[4][1]['kwPos'],$kwData[4][1]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][2]['kwPos'],$kwData[4][1]['kwPos']).'</span></td>      
      '.self::createPosData($kwData[4][2]['kwPos'],$kwData[4][2]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][3]['kwPos'],$kwData[4][2]['kwPos']).'</span></td>    
      '.self::createPosData($kwData[4][3]['kwPos'],$kwData[4][3]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][4]['kwPos'],$kwData[4][3]['kwPos']).'</span></td>    
      '.self::createPosData($kwData[4][4]['kwPos'],$kwData[4][4]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][5]['kwPos'],$kwData[4][4]['kwPos']).'</span></td>    
      '.self::createPosData($kwData[4][5]['kwPos'],$kwData[4][5]['kwURL']).'
      <td><span>'.self::calcDifferenceBetweenCurrentPosAndLastPos($kwData[4][6]['kwPos'],$kwData[4][5]['kwPos']).'</span></td>    
      '.self::createPosData($kwData[4][6]['kwPos'],$kwData[4][6]['kwURL']).'
	  </tr>';

    }
    return $html;
  }
  
  private function parseBestPos($pos)
  {
    if($pos == '')
      return '<span style="display:none">101</span>';
    return $pos;
  }

  private function calcDifferenceBetweenCurrentPosAndLastPos($yesterday,$today)
  {
      $diff = '<span style="display:none">-101</span>';
      if( $today>0 && $yesterday>0)
      {
        $diff = $yesterday-$today;
        if($diff == 0)
        {
          $diff = '<span>0</span>';
        } elseif ($diff > 0){
          $diff = '<span style="color:#4cae4c">+'.$diff.'</span>';
        } elseif ($diff < 0) {
          $diff = '<span style="color:#ac2925">'.$diff.'</span>';
        }
      }
      return $diff;
  }
  private function createPosData($pos,$url) 
  {
    
    if( $pos > 0 && $url != '#')
    {
      return '<td style="font-weight:bold"><a class="show_tt" title="'.$url.'" target="_blank" href="'.$url.'">'.$pos.'</a></td>';
    } 
    else 
    {
      return '<td><span style="display:none">101</span></td>';
    }
  }

  private function rePlaceNull($kwData)
  {
    $kwDataNew = array();
    $i = 0;
    while( $i<7)
    {
      if( !isset($kwData[$i]) )
      {
      $kwDataNew[$i]['kwpID'] = '';
      $kwDataNew[$i]['kwID'] = '';
      $kwDataNew[$i]['kwTime'] = '';
      $kwDataNew[$i]['kwPos'] = '-';
      $kwDataNew[$i]['kwURL'] = '#';
      } else {
  
      $kwDataNew[$i]['kwpID'] = $kwData[$i]['kwpID'];
      $kwDataNew[$i]['kwID'] = $kwData[$i]['kwID'];
      $kwDataNew[$i]['kwTime'] = $kwData[$i]['kwTime'];

      if( $kwData[$i]['kwPos'] < 1 )
      {
        $kwDataNew[$i]['kwPos'] = '-';
      } else {
        $kwDataNew[$i]['kwPos'] = $kwData[$i]['kwPos'];
      }
      if( !isset($kwData[$i]['kwURL']) )
      {
        $kwDataNew[$i]['kwURL'] = '#';
      } else {
        $kwDataNew[$i]['kwURL'] = $kwData[$i]['kwURL'];
      }
      }
      $i++;
    }

    return $kwDataNew;
  }

  private function bestPosition($kwID)
  {
    $query = "SELECT MIN(kwPos) as kwPos FROM seotracker_rankings WHERE kwID=$kwID AND kwPos > 0";
    $this->DB->setQuery($query);
    $result = $this->DB->getRow();

    return $result['kwPos'];
  }

  private function getPositions($kwID)
  {
    $query = "SELECT * FROM seotracker_rankings WHERE kwID=$kwID ORDER BY kwpID DESC LIMIT 7";
    $this->DB->setQuery($query);
    $results = $this->DB->getResults();
    if( isset($results[0]['kwTime']) )
    {
      //faster than OOP
      $timeAdded   = strtotime($results[0]['kwTime']);
      $today_start = strtotime("00:00:00");
      if($today_start > $timeAdded)
      {
        array_unshift($results, array(
          'kwpID'=>$results[0]['kwpID'],
          'kwID'=>$results[0]['kwID'],
          'kwPos'=>null,
          'kwURL'=>null,
          'kwTime'=>null,
          )
        );
      }
    }
    
    return $results;
  }

  public function createLast7Table()
  {
    $html = '';
    $html = '
  <table id="last7table" class="tftable">
	<thead>
	<tr>
	<th class="label" id="label">
			<div id="thDIV">#</div>
		</th>
  <th class="label" id="label">
      <div id="thDIV"></div>
    </th>    
		<th class="label" id="label">
			<div id="thDIV">Keywords</div>
		</th>
		<th class="label" id="label">
			<div id="thDIV"></div>
		</th>
		<th class="label" id="label">
		<div id="thDIV"></div>
		</th>
    <th class="label" id="label">
    <div id="thDIV">Best</div>
    </th>    
    <th class="label" id="label">
    <div id="thDIV">&larr;</div>
    </th>        
      '.self::createDateTableHead().'
	</tr>
	</thead>
	<tbody>';

    $html .= self::createDataTable();
    $html .= '
          	  </tbody>
            </table>';
	$html .= self::commentForm();
    return $html;
  }
	
  private function commentForm()
  {
	$html  = '';
	
	$html .= '
	<div id="commentbox">
		<div id="commentdiv">
			<div id="closeComment"><i class="fa fa-times"></i></div>
			<h3>Kommentar speichern</h3>
			<form method="POST">
				<textarea name="comment"></textarea>
				<input type="hidden" name="comment_kwID">
				<input style="width:150px" name="save_comment" type="submit" value="Speichern"><input style="width:150px;margin-right:10px" name="delete_comment" type="submit" value="Löschen">
			</form>
		</div>
	</div>';
	
	return $html;
  }

  public function genIndexOverKeywords()
  {
	$html = '';
	if(!isset($_GET['type']) || $_GET['type'] == 'index')
	{
		$html .= self::generateIndexInfo();
    $html .= self::generateTimeSelect();    
				     self::generateIndexChartData();
		$html .= self::generatejQplotCodeForIndex();
  } else {
		$html .= self::generateCountInfo();
    $html .= self::generateTimeSelect();   
				     self::generateCountChartData();
		$html .= self::generatejQplotCodeForCount();
	}
    return $html;    
  }

  private function generateTimeURL()
  {

    switch ($_GET['action'])
    {
      case 'chart':
        $url = '%%URL%%?action=chart&kwID='.intval($_GET['kwID']);
      break;

      case 'summary':
      default:
        if( !isset($_GET['type']) )
        {
          $_GET['type'] = 'index';
        }

        switch($_GET['type'])
        {
          case 'index':
            $type = 'index';
          break;
          default:
            $type = 'kws';
        }
        $url = '%%URL%%?action=summary&type='.htmlspecialchars($type);
    }    
    return $url;
  }

  private function generateTimeSelect()
  {

    return '
    <div class="info indexmsg" style="padding-top:0;">
    Zeitraum: 
    <a class="link_chart" href="'.self::generateTimeURL().'&time=14">2 Wochen</a>
    <a class="link_chart" href="'.self::generateTimeURL().'&time=30">1 Monat</a>
    <a class="link_chart" href="'.self::generateTimeURL().'&time=60">2 Monate</a>
    <a class="link_chart" href="'.self::generateTimeURL().'&time=90">3 Monate</a> 
    <a class="link_chart" href="'.self::generateTimeURL().'&time=180">6 Monate</a>
    <a class="link_chart" href="'.self::generateTimeURL().'&time=360">1 Jahr</a>
    '.self::generatePNGURL().'
    <a id="dlpic" class="link_chart">Export als PNG</a>
    </div>
    ';
  }
  private function generatePNGURL()
  {
    if( isset($_GET['kwID']) && $_GET['kwID'] > 0)
    {
      return '<a id="dlcsv" class="link_chart" href="%%URL%%?action=history&kwID='.intval($_GET['kwID']).'">Export als CSV</a>';
    }
  }
  private function generateIndexInfo()
  {
    return '<div class="info indexmsg"><strong>Zusammenfassung aller Positionen:</strong> Für jeden Tag werden die Positionen aller Keywords zusammenaddiert und durch die Anzahl aller Keywords geteilt. Keywords, die kein Ranking haben, werden mit 0 dazu addiert. Das Diagramm ermöglicht es, einen Überblick zur Gesamtentwicklung der beobachteten Website zu bekommen.</div>';
  }

   private function generatejQplotCodeForCount()
  {
	
    return "<script>$(document).ready(function() {
	var values= [".implode(',',$this->jqPlot_data[0])."]; 	
	var values1= [".implode(',',$this->jqPlot_data[1])."]; 
	var values2= [".implode(',',$this->jqPlot_data[2])."]; 
	var values3= [".implode(',',$this->jqPlot_data[3])."]; 
   $.jqplot.config.enablePlugins = true;
	var plot3 = $.jqplot('chart3', [values,values1,values2,values3], 
    { 
		sortData:true,
      	axesDefaults: {
        	labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
tickRenderer: $.jqplot.CanvasAxisTickRenderer,
 tickOptions: {
          fontFamily: 'Arial'
	}
      	},
      	axes:{
            xaxis:{
				
				renderer:$.jqplot.DateAxisRenderer,
				rendererOptions:{ tickRenderer: $.jqplot.CanvasAxisTickRenderer },
				numberTicks: 9,
          		tickOptions:{ showMark: true,formatString: '%d.%m.%Y', showGridline: false, angle: -45 },
				autoscale:true
            },
            yaxis:{
				label: 'Anzahl Keywords',
                rendererOptions:{ drawBaseline: false,tickRenderer:$.jqplot.CanvasAxisTickRenderer },min:0,
				tickOptions:{ showMark: false,formatString: '%d' }
},  
		},
      	seriesDefaults: {
			markerOptions: { style:\"circle\",size:1,lineWidth: 2 },
          	rendererOptions: { smooth: true },
			breakOnNull: true,
      	},
     	highlighter: {
        	sizeAdjust: 3,
        	tooltipLocation: 'n',
        	tooltipAxes: 'yx',
        	useAxesFormatters: true,
			tooltipFadeSpeed:'fast'
     	},
		legend: {
			labels: ['Alle','&sum; 1-10','&sum; 11-25','&sum; 25-50'],
			renderer: $.jqplot.EnhancedLegendRenderer,
        	rendererOptions: {
            numberColumns:5
        	}, 
           	placement: 'outsideGrid',
           	location: 's',
			show: true
      	},
      	cursor:{
			show:true,
           	zoom:false,
			clickReset:true,
			showTooltip:false
      	},
	 	grid: {
			borderWidth: 0,
			background: 'transparent',
			shadow: false,
	 	}
    	}
  	);
});

</script>
<div style=\"clear:both\"></div>
<div id=\"chart3\" style=\"z-index:0;margin:20px 0px;width:97%;height:450px;\"></div>
<div style=\"clear:both\"></div>";
	
  }
  
  private function generatejQplotCodeForIndex()
  {
    return "<script>$(document).ready(function() {
	var values= [".implode(',',$this->jqPlot_data)."]; 	
   $.jqplot.config.enablePlugins = true;
	var plot3 = $.jqplot('chart3', [values], 
    { 
		sortData:true,
      	axesDefaults: {
        	labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
tickRenderer: $.jqplot.CanvasAxisTickRenderer,
 tickOptions: {
          fontFamily: 'Arial'
	}
      	},
      	axes:{
            xaxis:{
				
				renderer:$.jqplot.DateAxisRenderer,
				rendererOptions:{ tickRenderer: $.jqplot.CanvasAxisTickRenderer },
				numberTicks: 9,
          		tickOptions:{ showMark: true,formatString: '%d.%m.%Y', showGridline: false, angle: -45 },
				autoscale:true
            },
            yaxis:{
				label: 'Position',
                rendererOptions:{ drawBaseline: false,tickRenderer:$.jqplot.CanvasAxisTickRenderer },min:100,max:0,
				tickOptions:{ showMark: false,formatString: '%d' }
},  
		},
      	seriesDefaults: {
          	rendererOptions: { smooth: true },
			breakOnNull: true,
      	},
      	series:[ { 
            markerOptions: { style:\"circle\",size:1,lineWidth: 2 },
			lineWidth: 2
         	}, ],
     	highlighter: {
        	sizeAdjust: 3,
        	tooltipLocation: 'n',
        	tooltipAxes: 'yx',
        	useAxesFormatters: true,
			tooltipFadeSpeed:'fast'
     	},
      	cursor:{
			show:true,
           	zoom:false,
			clickReset:true,
			showTooltip:false
      	},
	 	grid: {
			borderWidth: 0,
			background: 'transparent',
			shadow: false,
	 	}
    	}
  	);
});

</script>
<div style=\"clear:both\"></div>
<div id=\"chart3\" style=\"z-index:0;margin:20px 0px;width:97%;height:450px;\"></div>
<div style=\"clear:both\"></div>";
	
  }
  
  private function chooseTimeEnd()
  {
    if(!isset( $_GET['time'] ))
    {
      return 30;
    }
    else
    {
      return intval($_GET['time']);
    }  

  }
  private function generateIndexChartData()
  {

  if( isset($_SESSION['projectID']) )
  {
    $start 	= 0;
	  $end 	= self::chooseTimeEnd();

	  while($start < $end)
	  {
		  $dt = new DateTime("-$start days");
		  $dt1 = $dt->format('Y-m-d');
		  $query = "SELECT DISTINCT seotracker_rankings.kwID, seotracker_rankings.kwPos as pos FROM `seotracker_rankings`,seotracker_keywords WHERE seotracker_rankings.kwID=seotracker_keywords.kwID AND seotracker_keywords.pID=".intval($_SESSION['projectID'])." AND seotracker_rankings.kwTime LIKE '".$dt1."%'";
		  $this->DB->setQuery($query);
		  $results = $this->DB->getResults();
		  $count = count($results);

		  if( $count > 0 && !isset( $results['error'] ) ) 
		  {
       
			  $vals = 0;
			  foreach( $results as $key => $row ) 
			  {
          if( !isset($row['error']) && intval($row['pos']) > 0 ) 
          {
				    $vals = $vals+intval($row['pos']);
          } else {
            $count = $count-1;
          }
			  }
        if( $count <= 0 )
        {
          $count = 1;
        }

			  $index_rank = $vals/$count;
			  $wert = round($index_rank,2);
        if ( $wert == 0)
        {
          $wert = 'null';
        }

			  $this->jqPlot_data[] = '["'.$dt1.'",'.$wert.']'; 	
		  } 
		  else 
		  {
			  $this->jqPlot_data[] = '["'.$dt1.'",null]'; 	
		  }
		  $start++;
	

	  }
  } else {
    $this->jqPlot_data[] = '[null,null]'; 	
  }
	
  }

  private function generateCountInfo()
  {
    return '<div class="info indexmsg"><strong>Täglich verarbeitete Keywords:</strong> Das folgende Diagramm zeigt für jeden Tag an, wie viele Keywords bereits verarbeitet wurden. So lässt sich für jeden Tag erkenne, ob der PERL-Tracker im Hintergrund ggf. Probleme gemacht hat, Google den Server geblockt hat oder sonstiges.. Außerdem wird angezeigt, wie viele Keywords in den Top 10, zwischen 11 und 25 sowie darüber in den Google-Suchergebnissen zu finden sind.</div>';
  }

  
  private function generateCountChartData()
  {	
  if( isset($_SESSION['projectID']) )
  {
    $start 	= 0;
	  $end 	= self::chooseTimeEnd();;
	
	
	  while($start < $end)
	  {
		  $dt = new DateTime("-$start days");
		  $dt1 = $dt->format('Y-m-d');

		  $query = "SELECT DISTINCT seotracker_rankings.kwID as kws FROM `seotracker_rankings`, seotracker_keywords WHERE seotracker_rankings.kwTime LIKE '".$dt1."%' AND seotracker_keywords.kwID = seotracker_rankings.kwID AND seotracker_keywords.pID=".intval($_SESSION['projectID']);
		  //echo $query;
		  $count = $this->DB->getResultCount($query);

		  if( $count > 0 ) 
		  {
			  $this->jqPlot_data['0'][] = '["'.$dt1.'",'.round($count,2).']'; 			
		  } 
		  else 
		  {
			  $this->jqPlot_data['0'][] = '["'.$dt1.'",null]'; 	
		  }
		  ##################

		  $query = "SELECT DISTINCT seotracker_rankings.kwID as kws FROM `seotracker_rankings`, seotracker_keywords WHERE (seotracker_rankings.kwPos BETWEEN 1 AND 10) AND seotracker_rankings.kwTime LIKE '".$dt1."%' AND seotracker_keywords.kwID = seotracker_rankings.kwID AND seotracker_keywords.pID=".intval($_SESSION['projectID']);
		  $count = $this->DB->getResultCount($query);

		  if( $count > 0 ) 
		  {
			  $this->jqPlot_data['1'][] = '["'.$dt1.'",'.round($count,2).']'; 			
		  } 
		  else 
		  {
			  $this->jqPlot_data['1'][] = '["'.$dt1.'",null]'; 	
		  }
		  ##################	

		  $query = "SELECT DISTINCT seotracker_rankings.kwID as kws FROM `seotracker_rankings`, seotracker_keywords WHERE (seotracker_rankings.kwPos BETWEEN 11 AND 25) AND seotracker_rankings.kwTime LIKE '".$dt1."%' AND seotracker_keywords.kwID = seotracker_rankings.kwID AND seotracker_keywords.pID=".intval($_SESSION['projectID']);
		  $count = $this->DB->getResultCount($query);

		  if( $count > 0 ) 
		  {
			  $this->jqPlot_data['2'][] = '["'.$dt1.'",'.round($count,2).']'; 			
		  } 
		  else 
		  {
			  $this->jqPlot_data['2'][] = '["'.$dt1.'",null]'; 	
		  }
		  ##################	

		  $query = "SELECT DISTINCT seotracker_rankings.kwID as kws FROM `seotracker_rankings`, seotracker_keywords WHERE (seotracker_rankings.kwPos BETWEEN 25 AND 50) AND seotracker_rankings.kwTime LIKE '".$dt1."%' AND seotracker_keywords.kwID = seotracker_rankings.kwID AND seotracker_keywords.pID=".intval($_SESSION['projectID']);
		  $count = $this->DB->getResultCount($query);

		  if( $count > 0 ) 
		  {
			  $this->jqPlot_data['3'][] = '["'.$dt1.'",'.round($count,2).']'; 			
		  } 
		  else 
		  {
			  $this->jqPlot_data['3'][] = '["'.$dt1.'",null]'; 	
		  }
		  ##################			
	
	  $start++;
	  }	
	} else {
    $this->jqPlot_data['0'][] = '[null,null]'; 	
    $this->jqPlot_data['1'][] = '[null,null]'; 	
    $this->jqPlot_data['2'][] = '[null,null]'; 	
    $this->jqPlot_data['3'][] = '[null,null]'; 	
  }
	
  }
	
  private function getKeyWordNameByID( $id )
  {
    $query = "SELECT kwText FROM seotracker_keywords WHERE kwID=".intval($id)." LIMIT 1";
    $this->DB->setQuery($query);
    $result = $this->DB->getRow();  
    return $result['kwText'];
  }

  public function genKeyWordChart()
  {
    if( isset($_GET['kwID']) && $_GET['kwID']>0 ) 
    {
      $end = self::chooseTimeEnd();
      $kwID = intval( $_GET['kwID'] );
      $query = "SELECT kwPos,kwTime FROM seotracker_rankings WHERE kwID=".$kwID." ORDER BY kwpID DESC LIMIT $end";
      $this->DB->setQuery($query);
      $results = $this->DB->getResults(); 
      $values = array();

      if( !isset($results['error']) )
      {
        foreach($results as $result)
        {
          if( $result['kwPos'] == 0 ) 
          {
            $result['kwPos'] = 'null';
          }
          $values[] = '["'.$result['kwTime'].'",'.$result['kwPos'].']';
        }

      } 
      else
      {
        $dt = new DateTime;
        $values = array('["'.$dt->format('Y-m-d').'",null]');
      }

      $this->jqPlot_data = $values;
      $html = '<div class="info indexmsg"><strong>Entwicklung für "'.self::getKeyWordNameByID( $kwID ).'"</strong> Du siehst an der Stelle die Entwicklung für das gewählte Keyword in den vergangenen '.self::chooseTimeEnd().' Tagen. Tage an denen das Keyword nicht in den SERPs zu finden war, erzeugen eine leere Stelle im Graphen.</div>';
      $html .= self::generateTimeSelect();   
      return $html.self::generatejQplotCodeForIndex();
    }

    return '<div style="margin-bottom: 25px !important;" class="info indexmsg"><strong>FEHLER</strong> Hier scheint es ein kleines Problem mit der GET-Parameter zu geben. Ich empfehle nicht in der URL herumzuspielen!</div>';

  }

  public function batchKeywordForm()
  {
    $html = '<div class="editkeyword_list">
                <form action="%%URL%%?action=batch" method="POST">';

    $html .='<textarea style="width:100%;height:450px" name="kw_batch"></textarea>
            <input name="start_batch" style="width:200px;float:right;margin-bottom:20px" type="submit" value="Vorgang starten">';                  
    $html .= '  
                </form>
                </div>
                  <div class="info">
                 Hier kannst du schnell und einfach neue Keywords als Batchvorgang hinzufügen. Pro Zeile bitte nur ein Keyword eintragen.
                  </div>               
                  <div class="info">
                  <strong>Zusätzliche Informationen</strong><br/>
                  %%INFORMATION%%
                  </div>
                  <div class="info">
                  %%TEXT%%
                  </div>
                '."\n";
    return $html;
  }

  public function genHistoryForm()
  {

    $html = '<div class="info indexmsg">Hier kannst du die gesamte History des gewählten Projektes herunterladen. Dabei wird eine CSV-Datei erzeugt, in der du alle Rankings aller deiner Keywords findest. Im CSV-Head findest du das Keyword, die erste Spalte liefert das Datum. Unter dem Keyword findest du dann die Positionen zum jeweiligen Tag. Je nach Größe der Datenbank bzw. Anzahl an Keywords und aufgezeichneter Daten, kann der Prozess sehr lange dauern. Bei falsch eingestelltem Webserver, könnte er abgebrochen werden. Viele Webserver brechen Scripte beispielsweise nach 30 Sekunden ab. </div>';
    $html .= '<div class="info indexmsg" style="margin-bottom:15px !important;"><a class="link_chart" href="%%URL%%?action=history&p=start">Prozess starten</a></div>';
    return $html;
  }
 
}
?>
