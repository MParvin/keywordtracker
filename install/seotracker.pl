
#!/usr/bin/perl

use DBI;
use LWP::Simple;
use LWP::UserAgent;
use URI::Escape;
use autodie;

$user = '';
$pw = '';
$database = '';
$host = 'localhost'; 

$port = '3306';
$platform = 'mysql';

$dsn = "dbi:$platform:$database:$host:$port";

$connect = DBI->connect($dsn, $user, $pw) or die "Verbindung fehlgeschlagen: $DBI::errstr\n";
$connect->do("SET NAMES 'utf8'");


$query = "SELECT seotracker_keywords.kwID, seotracker_keywords.kwText,seotracker_projects.pURL FROM seotracker_keywords,seotracker_projects WHERE seotracker_keywords.kwTime='".get_time('hour')."' AND seotracker_keywords.pID=seotracker_projects.pID ORDER BY seotracker_keywords.kwID ASC";
$query_handle = $connect->prepare($query);
$query_handle->execute();
$query_handle->bind_columns(undef, \$kwID, \$kwtext, \$pURL);


while($query_handle->fetch()) 
{
 #print "$kwText ($kwID) -> $pURL\n";
 my $ua = LWP::UserAgent->new(agent => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0", cookie_jar => {});
 $ua->default_header('Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8','Accept-Language' => "de,en-US;q=0.7,en;q=0.3",'Cache-Control' => 'max-age=0');

 my $req = HTTP::Request->new(GET => 'https://www.google.de/search?oe=utf-8&pws=0&complete=0&hl=de&num=100&q='.uri_escape($kwtext));
 my $res = $ua->request($req);
 if ($res->is_success) {
 my @con = $res->content =~ /<(div|li) class=\"g\"(.+?)<\/(div|li)>/gi;
 my $i = 1;
 my $found = 0;

 foreach $line (@con) 
 {
  if (index($line,$pURL) != -1) 
  {
   $connect ||= DBI->connect($dsn, $user, $pw) or die "Verbindung fehlgeschlagen: $DBI::errstr\n";

   my @all = $line=~ /<(h3|span) class=\"(r|_Tyb)\"><a(.*)href=\"(.*)\" onmousedown=\"return rwt\(this,'','','','(\d{1,3})','(.*)',event\)\">(.*)<\/a><\/(h3|span)>/gi;
   $found = 1;
   $url_1 = $connect->quote($all[3]);
   $posi  = int($all[4]);
   
   if( $posi != 0 ) {
    $query1 = "INSERT INTO seotracker_rankings (kwID,kwPos,kwURL) VALUES ($kwID,$posi,$url_1)";
    $query_handle1 = $connect->prepare($query1);
    $query_handle1->execute() || dberror ("Fehler im 1. Insert: $DBI::errstr");
    $query_handle1->finish();
    $query1 = "UPDATE seotracker_keywords SET kwUpdated='".get_time('iso')."' WHERE kwID=$kwID LIMIT 1";
    $query_handle1 = $connect->prepare($query1);
    $query_handle1->execute() || dberror ("Fehler im 1. Update: $DBI::errstr");
    $query_handle1->finish();
   }
   #print "Gefunden fur $pURL: $kwtext ($kwID) auf Position $posi ($url_1) - ". get_time('iso'). "\n";
   last;
  } 
  $i++;
 }

 if ($found eq 0) 
 {
  $connect ||= DBI->connect($dsn, $user, $pw) or die "Verbindung fehlgeschlagen: $DBI::errstr\n";
  $query1 = "INSERT INTO seotracker_rankings (kwID,kwPos,kwURL) VALUES ($kwID,NULL,NULL)";
  $query_handle1 = $connect->prepare($query1);
  $query_handle1->execute() || dberror ("Fehler in 2. Insert: $DBI::errstr");
  $query_handle1->finish();
  $query1 = "UPDATE seotracker_keywords SET kwUpdated='".get_time('iso')."' WHERE kwID=$kwID LIMIT 1";
  $query_handle1 = $connect->prepare($query1);
  $query_handle1->execute() || dberror ("Fehler in 2. Update: $DBI::errstr");
  $query_handle1->finish();
  #print "Nicht gefunden fur $pURL: $kwtext ($kwID) - ". get_time('iso'). "\n";
 }

 } else {
  print $res->status_line . "\n";
 
 }

  custom_sleep(10,30);

}

$query_handle->finish();
$connect->disconnect();
exit 0;

sub custom_sleep
{
  die "custom_sleep() -> Zu viele Parameter\n" unless @_ <= 2;
  die "custom_sleep() -> Zu wenig Parameter\n" unless @_ >= 2;
  my $range = $_[0];
  my $time_to_add = $_[1];
  my $random_number = int(rand($range))+$time_to_add;
  sleep($random_number);
}

sub get_time
{

 my ($Sekunden, $Minuten, $Stunden, $Monatstag, $Monat, $Jahr, $Wochentag, $Jahrestag, $Sommerzeit) = localtime(time);
 $Monat+=1;
 $Jahrestag+=1;
 $Monat = $Monat < 10 ? $Monat = "0".$Monat : $Monat;
 $Monatstag = $Monatstag < 10 ? $Monatstag = "0".$Monatstag : $Monatstag;
 $Minuten = $Minuten < 10 ? $Minuten = "0".$Minuten : $Minuten;
 $Sekunden = $Sekunden < 10 ? $Sekunden = "0".$Sekunden : $Sekunden;
 $Jahr+=1900;

 if($_[0] eq 'hour')
 {
  return $Stunden;
 }

 if($_[0] eq 'iso')
 {
  return $Jahr."-".$Monat."-".$Monatstag." ".$Stunden.":".$Minuten.":".$Sekunden;
 }

 if($_[0] ne 'iso' || $_[0] ne 'hour')
 {
  die "get_Time() -> Unbekannte oder keine Parameter\n";
 }

}
