<?php 
/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/09
*
*/
class DB 
{

  private $queries    = array();
	public $connection 	= FALSE;
	public $queryString = '';
	public $result		= NULL;

	public function __construct() 
	{  

    $this->connection = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME,DB_POST);

		if (mysqli_connect_errno()) 
		{
	    die("Connection failed: ".mysqli_connect_errno()." : ". mysqli_connect_error());
		}

    $this->connection->set_charset("utf8");


	}

  public function deleteOldQueries()
  {
    $this->queries    = array();
  }

  public function setQueriesForCommit( $sqlQuery )
  {
    if($sqlQuery != '')
    {
      $this->queries[]  = $sqlQuery;
    }
  }

  public function startTransaction()
  {
	if(@function_exists(mysqli_begin_transaction))
	{
		$error_occured  = 0;
		$this->connection->begin_transaction();

		foreach( $this->queries as $query )
		{
		   $status = $this->connection->query($query);
		   if( (int) $status == 0)
		   {
			$error_occured = 1;
			break;
		   }
		}
		
		if( $error_occured == 1)
		{
		   $this->connection->rollback();
		} 
		else 
		{
		   $this->connection->commit();
		} 
	} 
	else
	{
		foreach( $this->queries as $query )
		{
		   $status = $this->connection->query($query);
		}	
	}
	
    self::deleteOldQueries();
	
  }

  public function showQueriesInQueue()
  {
    if( !empty($this->queries) )
    {
      foreach( $this->queries as $query )
      {
        echo $query."<br />";
      }
    }
    else
    {
      echo 'Warteschlange ist leer!';
    }
  }

	public function getRow($type='ASSOC') 
	{
		if ($this->queryString != '' && $this->queryString != NULL) 
		{
			$ressource = $this->connection->query($this->queryString) or die('Error detected: <strong>'.$this->connection->error.'</strong> -> DIE()');
			if ($ressource)
			{
				if($type == 'NUMERIC') 
				{	
					$this->result = mysqli_fetch_array($ressource,MYSQLI_NUM);
				} 
				else
				{
					$this->result = mysqli_fetch_array($ressource,MYSQLI_ASSOC);
				}
			
				if( $this->result != NULL) 
				{
					return $this->result;
				} 
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			die('Query wurde nicht gesetzt, zuvor setQuery() ausf&uuml;hren');
		} 
	}

	public function getResults($type='ASSOC')
	{
		if ($this->queryString != '' && $this->queryString != NULL) 
		{
			$ressource = $this->connection->query($this->queryString) or die('Error detected: <strong>'.$this->connection->error.'</strong> -> DIE()');
			if ($ressource)
			{
				$temp = array();
				if($type == 'NUMERIC') 
				{	
					while($row = mysqli_fetch_array($ressource,MYSQLI_NUM))
					{
						$temp[] = $row;
					}		
					$this->result = $temp;
				} 
				else
				{
					while($row = mysqli_fetch_array($ressource,MYSQLI_ASSOC))
					{
						$temp[] = $row;
					}		
					$this->result = $temp;
				}
			
				if( $this->result != NULL) 
				{
					return $this->result;
				} 
				else
				{
					return array('error'=>'Keine Ergebnisse vorhanden');
				}
			}
		}
		else
		{
			die('Query wurde nicht gesetzt, zuvor setQuery() ausf&uuml;hren');
		} 
	}
	
	public function getResultCount($sql_query)
  {
    if($this->connection != NULL)
    {
      return $this->connection->query($sql_query)->num_rows;
    }
  }

	public function setQuery($queryString) 
	{
		if ($queryString != '' && $queryString !== NULL) 
		{
			$this->queryString = $queryString;
		} 
    else 
    {
			die('QueryString ist leer, obwohl es aktiv gesetzt wird.');
		}
	}


  public function escStr($string)
  {
    return $this->connection->real_escape_string($string);
  }

	public function sendQuery($queryString)
  {
    $this->connection->query($queryString) or die('Error detected: '.$this->connection->error.' -> DIE()');
  }


}
?>
