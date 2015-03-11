<h1>Installationsanweisungen</h1>

1. Vor dem hochladen müssen die folgenden Dateien bearbeitet werden:
– includes/config.php
– install/seotracker.pl

In beiden Dateien müssen die Zugangsdaten zur mySQL-DB eingetragen werden. Außerdem kleinere Anpassungen, wie etwa die URL zum Tracker.

2. Dateien ins Wunschverzeichnis hochladen
3. Im Ordner install/ befindet sich die full.sql – diese Datei importiert man in die eigene Datenbank
4. Die seotracker.pl sollte umbenannt oder verschoben werden, damit sie nicht im Standardverzeichnis install/ zu finden ist. Wenn das vergessen wird, wird sich jeder diese Datei mit den Zugangsdaten herunterladen können, wenn er die URL zum Tracker kennt. Legt euch ein weiteres Verzeichnis an und packt sie dort hin!
5. Cronjob anlegen

Der Punkt ist wichtig. Es muss ein Cronjob angelegt werden, der JEDE STUNDE ausgeführt wird, im einfachsten Fall schaut das in etwa so aus:
0 * * * * perl /absoluter/pfad/zur/perldatei/seotracker.pl > /eventuell/eine/log/datei.log 2&>1

Hier wird zu jeder vollen Stunde das Script ausgeführt. Die absoluten Pfade müssen natürlich angepasst werden. Was auch sicher gestellt werden muss:
– Ausführbarkeit der Datei für den Nutzer, der den Cronjob ausführt
– Generelle Verfügbarkeit der oben genannten PERL-Pakete
– Eingetragen werden muss der absolute Pfad, nicht die URL zur Datei oder relative Pfade – damit wird der Cronjob-Dienst wenig anfangen können

Hinweise: Das Script ist für Leute gedacht, die einen eigenen Server betreiben und dort SSH-Zugriff haben. Bei einigen Webhostern lassen sich auch PERL-Dateien ausführen. Wer einen managed Server mietet,, der muss ggf. mit dem Betreiber sprechen damit die Pakete nachinstalliert werden. Wer Lust hat, kann das PERL-Script auch mit PHP und CURL nachbilden. Dann wird das Programm auch auf jedem Webhoster laufen, der normale PHP Cronjobs erlaubt!
