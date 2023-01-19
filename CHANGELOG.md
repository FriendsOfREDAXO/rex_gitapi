# rex_gitapi - Changelog

## Version 0.0.1 - xx.01.2023

Einfacher Zugriff auf die GitHub API im REDAXO-Style (ReadOnly) by @aeberhard

Erste Version mit einigen Spezial-Methoden zur Vereinfachung der API Zugriffe.

### Features

* Klasse `RexGitApi` für Zugriffe auf die GitHub API
* Lesezugriff auf **ALLE** GitHub API Seiten (öffentliche oder dafür berechtigte URL's)
* Zugriff mit Token möglich (Personal access token), dadurch sind 5.000 Zugriffe pro Stunde möglich (Ohne Token nur 60 Zugriffe)
* Caching der GitHub API Ergebnisse
* API-Tester zum testen der API Zugriffe mit dump-Ausgabe

### TODO

* Weitere Features sind geplant, siehe https://github.com/FriendsOfREDAXO/rex_gitapi/issues/1
* API-Tester erweitern
* API-Tester generiert den dazugehörigen PHP-Code
* Button zum kopieren des generierten PHP-Codes
