<?php

/*
*
* Author: Damian Schwyrz
* URL: http://damianschwyrz.de
* More: http://blog.damianschwyrz.de/seo-keyword-monitor-and-tracker/
* Last update: 2015/03/11
*
*/

  include_once 'includes/config.php';
  include_once 'includes/init.php';

  $KWT = new KeywordTracking;
  $KWT->start();
  
?>
