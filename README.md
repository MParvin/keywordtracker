# keywordtracker

1. Dateien hochladen
2. config.php in /includes/ anpassen (URL, DB-Zugriff, Login)
3. full.sql in die eigene Datenbank importieren
4. seotracker.pl anpassen (DB-Zugriff)
5. Cronjob für seotracker.pl anlegen - wichtig: Jede Stunde EINMAL
z.B.: 0 * * * * perl /script/seotracker.pl 2>&1
