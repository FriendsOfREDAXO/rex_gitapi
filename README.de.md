# Einfacher Zugriff auf die GitHub API im REDAXO-Style (ReadOnly)

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi.png?raw=true)

## Klasse RexGitApi

Das AddOn **rex_gitapi** stellt die Klasse `RexGitApi` für den einfachen Zugriff auf die GitHub API zur Verfügung.
Im Backend können mit dem API-Tester Zugriffe auf die GitHub API getestet und der dazugehörige PHP-Code zum kopieren generiert werden.

Die Dokumentation der GitHub API findet ihr hier: https://docs.github.com/rest

Alle API-Zugriffe werden über die URL https://api.github.com/ abgesetzt.

> **Hinweis:** REDAXO-AddOns können die Klasse benutzen um z.B. Themes, Styles oder sonstige Daten direkt von GitHub upzudaten.

Die GitHub API liefert alle Ergebnisse im JSON-Format. Standardmäßig wird von der Klasse `RexGitApi` das Ergebnis als **PHP array** zurückgeliefert `get()`. Es kann aber auch das ursprüngliche JSON-Ergebnis der GitHub API abgefragt werden `get(true)`.

Generell können alle URL's der GitHub API direkt über die Klasse verarbeitet werden (öffentliche oder dafür berechtigte URL's).

> **Hinweis:** Ohne Authentifizierung sind nur 60 Zugriffe pro Stunde auf die GitHub Api möglich. Mit einem Token können pro Stunde 5.000 Zugriffe auf die API erfolgen. Siehe [Authentifizierung (Token)](#token)

Die folgende URL liefert z.B. wieviele Zugriffe maximal ausgeführt werden können, wieviele bereits durchgeführt wurden und wie viele Zugriffe auf die API noch verbleiben.

`https://api.github.com/rate_limit`

Als URL-Parameter der Klasse kann entweder die vollständige URL `https://api.github.com/rate_limit` oder auch nur `rate_limit` übergeben werden. Protokoll und Domain (`https://api.github.com/`) können weggelassen werden.

Der Token kann bei instanzierung mit `new`, mit der `factory()`-Methode oder per `setToken()` übergeben werden.
Wird kein Token übergeben und es existiert ein Token in den AddOn-Einstellungen wird dieser verwendet.

Per Default werden die GitHub-Ergebnise gecached. Die Cache-Lifetime kann in den AddOn-Einstellungen geändert werden.
Mit der Methode `setCache(false)` kann das Caching abgeschaltet werden. Bei der AddOn-Einstellung `Niemals einen Cache anlegen` wird kein Cache erstellt!

**Beispiel:**

```php
// GitHub rate limit ermitteln
$gitapi = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory(<TOKEN>);
// oder $gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi(<TOKEN>);
$gitapi->setDebug(false);
$gitapi->setCache(false);
$gitapi->setUrl('https://api.github.com/rate_limit');
$gitapi->execute();
$gitresult = $gitapi->get();
dump($gitresult);

// Result als PHP array
$gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->execute('https://api.github.com/rate_limit')->get();
dump($gitresult);

// Result im JSON Format
$jsonresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->execute('rate_limit')->get(true);
dump($jsonresult);

// Spezial-Methode
$gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getLimits();
dump($gitresult);

$jsonresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getLimits(true);
dump($jsonresult);
```

### Methoden: Parameter

| Methode | Beschreibung | Parameter | Return |
| --- | --- | --- | --- |
| setToken() | Setzen des GitHub API Tokens (Personal access token) | string $token | RexGitApi |
| getToken() | Get des GitHub API Tokens (Personal access token) | keine | string |
| setUrl() | Setzen der GitHub API Url die verarbeitet werden soll | string $url | RexGitApi |
| getUrl() | Get der der GitHub API Url | keine | string |
| setOrg() | Setzen der Organisation | string $org | RexGitApi |
| getOrg() | Get der Organisation | keine | string |
| setUser() | Setzen des GitHub Users | string $user | RexGitApi |
| getUser() | Get des GitHub Users | keine | string |
| setRepo() | Setzen des Github Repos | string $repo | RexGitApi |
| getRepo() | Get des Github Repos | keine | string |
| setDebug() | Setzen des Debug-Modus<br>true = Es werden Debug-Ausgaben mit der dump()-Methode ausgegeben | bool $debug | RexGitApi |
| getDebug() | Get des Debug-Modus | keine | bool |
| setCache() | Setzen des Cache-Modus<br>**Hinweis:** Wenn in den AddOn-Einstellungen `Niemals einen Cache anlegen` ausgewählt wurde, hat das Setzen keine Auswirkung! | bool $cache | RexGitApi |
| getCache() | Get des Cache-Modus | keine | bool |

### Methoden: Verarbeitung

| Methode | Beschreibung | Parameter | Return |
| --- | --- | --- | --- |
| factory() |  |
| execute(string $url) |  |
| get(bool $json = false) |  |
| hasError():bool |  |
| getMessage():string |  |
| getCachePath() |  |
| getCacheFiles() |  |
| deleteCache() |  |

### Spezial-Methoden

| Methode | Beschreibung | Parameter | Return |
| --- | --- | --- | --- |
| urlToFileName(string $url) |  |
| fileNameToUrl(string $filename) |  |
| getLimits(bool $json = false) |  |
| getOrgInfo(string $org, bool $json = false) |  |
| getOrgRepoList(string $org, bool $json = false) |  |
| getUserInfo(string $user, bool $json = false) |  |
| getUserRepoList(string $user) |  |
| getRepoInfo(string $user, string $repo, bool $json = false) |  |
| getPagedContent(string $url, bool $json = false) |  |

### Beispiele

Der bevorzugte Weg RexGitApi zu benutzen ist eine neue Instanz zu erzeugen (wg. Error-Handling).

`<TOKEN>` ist optional. Wenn kein Token angegeben wird wird der Token aus den Addon-Einstellungen verwendet.

#### Abfragen von Fehlern

Um Fehler bei dem GitHub-Zugriff abzufangen sollte mit `new` eine Instanz von `RexGitAPi` erstellt werden.

`$gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi();`

Mit der Metode `hasError()` kann ein eventuell auftretender Fehler abgefragt werden.
Die Fehlermeldung kann mit der Methode `getMessage()` ausgegeben werden.

Beispiel:

```php
$gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi();
$gitapi->setDebug(false);
$gitresult = $gitapi->execute('orgs/friendsofredaxo/repos?page=99');
if ($gitapi->hasError()) {
     echo rex_view::error(
        $gitapi->getMessage()
        . '<br>Requested URL: ' . $gitapi->getUrl()
        . '<br>GitHub-Token: ' . $gitapi->getToken()
    );
} else {
    $gitresult = $gitapi->get();
    dump($gitresult);
}
```

Repo-liste sortiert
https://api.github.com/orgs/FriendsOfREDAXO/repos?sort=name&direction=asc

## Debug-Ausgabe

Über `setDebug(true)` kann die Debug-Ausgabe der Klasse angefordert werden. Das kann manchmal recht hilfreich sein.
Es werden 2 Blöcke mit der REDAXO `dump()` Anweisung ausgegeben. Arrays können hier durch Klick auf den Pfeil aufgeklappt werden.

**Block 1: Parameter** (DEBUG PARAMETERS)

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_debug1.png?raw=true)

Beschreibung:

0. Info Debug-Ausgabe der RexGitApi-Klasse (DEBUG PARAMETERS)
1. angeforderte GitHub Api URL
2. CURLOPT_USERAGENT
3. CURLOPT_HTTPHEADER (Accept-Header, API-Version und Token)

**Block 2: Ergebnisse** (DEBUG RESPONSE)

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_debug2.png?raw=true)

Beschreibung

0. Info Debug-Ausgabe der RexGitApi-Klasse (DEBUG RESPONSE)
1. JSON-Result des API-Aufrufs
2. PHP-Array-Result des API-Aufrufs

> **Hinweis:** Sollte der GitHub-API-Zugriff reinen Text zurückliefern wird im PHP-Array `rexgitjson` mit dem Text gesetzt!

## Klasse RexGitApiCache

## Klasse RexGitApiCurl

## Klasse RexGitApiUrl

## Klasse RexGitApiUtil

## API-Tester

... TODO

<a name="token"></a>

## Authentifizierung (Token)

Ohne Authentifzizierung sind nur 60 Zugriffe pro Stunde auf die GitHub Api möglich. Mit einem Token können pro Stunde 5.000 Zugriffe auf die API erfolgen.

Zur Erstellung eines **Zugriff-Tokens** (Personal access token) solltest Du dich bei GitHub anmelden und dann folgende URL aufrufen:

`https://github.com/settings/apps`

Auf der Seite dann **Personal access tokens** (1) und danach **Tokens (classic)** (2) anklicken (siehe Screenshot).

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token1.png?raw=true)

Auf der nächsten Seite dann den Button **Generate new token** (1) anklicken und danach **Generate new token (classic)** (2) auswählen (siehe Screenshot).

> **Hinweis:** Danach muss noch einmal das Github-Passwort zur Bestätigung eingegeben werden!

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token2.png?raw=true)

Auf der nächsten Seite eine **Bezeichnung** (1) eingeben, ein **Ablaufdatum** (2) des Tokens auswählen und bei den Optionen **repo** (3) auswählen.

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token3.png?raw=true)

Für Zugriff auf Benutzerdaten noch zusätzlich die Opiton **user** (4) auswählen.

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token4.png?raw=true)

Auf dem folgenden Screen wird dann der generierte Token ausgegeben der unbedingt kopiert und gespeichert werden sollte.

> **Hinweis:** Token unbedingt kopieren und speichern! Der Token kann nicht mehr angezeigt werden!

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token5.png?raw=true)

Idealerweise wird der Token in den Einstellungen für `rex_gitapi` gespeichert.

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token6.png?raw=true)

> **Hinweis:** Tokens können jederzeit gelöscht und neu angelegt werden. Ein neuer Token in den Einstellungen vom AddOn `rex_gitapi` wird sofort berücksichtigt.

## Noch Fragen oder Anregungen? Einen Bug gefunden?

Dann geht es hier weiter ...

* Auf Github: [https://github.com/FriendsOfREDAXO/rex_gitapi](https://github.com/FriendsOfREDAXO/rex_gitapi)

* im Slack-Channel: [https://friendsofredaxo.slack.com/](https://friendsofredaxo.slack.com/)

## Credits ##

* GitHub API https://docs.github.com/rest
* [Friends Of REDAXO](https://github.com/FriendsOfREDAXO) Gemeinsame REDAXO-Entwicklung!
* Andreas Eberhard @aeberhard, http://aesoft.de

> Photo by Christina Morillo from Pexels: https://www.pexels.com/photo/eyeglasses-in-front-of-laptop-computer-1181253/
