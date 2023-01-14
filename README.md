# Easy access to the GitHub API in REDAXO style (ReadOnly)

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi.png?raw=true)

## Class RexGitApi

The **rex_gitapi** AddOn provides the `RexGitApi` class for easy access to the GitHub API.
In the backend, access to the GitHub API can be tested with the API tester and the associated PHP code can be generated for copying.

The GitHub API documentation can be found here: https://docs.github.com/rest

All API accesses are made using the URL https://api.github.com/.

> **Notice:** REDAXO-AddOns can use the class to update e.g. themes, styles or other data directly from GitHub.

The GitHub API returns all results in JSON format. By default, the class `RexGitApi` returns the result as a **PHP array** `get()`. However, the original JSON result of the GitHub API can also be queried `get(true)`.

In general, all URLs of the GitHub API can be processed directly via the class (public or authorized URL's).

> **Notice:** Without authentication, only 60 accesses per hour to the GitHub Api are possible. One token can access the API 5,000 times per hour. See [Authentication (token)](#token)

The following URL provides e.g. how many accesses can be carried out at most, how many have already been carried out and how many accesses to the API still remain.

`https://api.github.com/rate_limit`

Either the full URL `https://api.github.com/rate_limit` or just `rate_limit` can be passed as the URL parameter of the class. Protocol and domain (`https://api.github.com/`) can be omitted.

The token can be passed when instantiating with `new`, with the `factory()` method or with `setToken()`.
If no token is passed and there is a token in the AddOn settings, this will be used.

GitHub results are cached by default. The cache lifetime can be changed in the AddOn settings.
The caching can be switched off with the method `setCache(false)`. With the AddOn setting `Never create a cache` no cache will be created!

**Example:**

```php
// Determine GitHub rate limit
$gitapi = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory();
// or $gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi();
$gitapi->setDebug(false);
$gitapi->setCache(false);
$gitapi->setUrl('https://api.github.com/rate_limit');
$gitapi->execute();
$gitresult = $gitapi->get();
dump($gitresult);

// Result PHP array
$gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->execute('https://api.github.com/rate_limit')->get();
dump($gitresult);

// Result JSON Format
$jsonresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->execute('rate_limit')->get(true);
dump($jsonresult);

// special method
$gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getLimits();
dump($gitresult);

$jsonresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getLimits(true);
dump($jsonresult);
```

### Methods: parameter

| Method | Description | Parameters | Return |
| --- | --- | --- | --- |
| setToken() | Setting the GitHub API token (Personal access token) | string $token | RexGitApi |
| getToken() | Get the GitHub API token (Personal access token) | none | string |
| setUrl() | Set the GitHub API Url to process | string $url | RexGitApi |
| getUrl() | Get the GitHub API Url | none | string |
| setOrg() | Set the organization | string $org | RexGitApi |
| getOrg() | Get the organization | keine | string |
| setUser() | Set the GitHub user | string $user | RexGitApi |
| getUser() | Get the GitHub user | none | string |
| setRepo() | Set the GitHub repo | string $repo | RexGitApi |
| getRepo() | Get the GitHub repo | none | string |
| setDebug() | Set debug mode<br>true = Debug output is done using the dump() method | bool $debug | RexGitApi |
| getDebug() | Get debug mode | none | bool |
| setCache() | Set cache mode<br>**Notice:** If `Never create a cache` was selected in the addon settings, setting it has no effect! | bool $cache | RexGitApi |
| getCache() | Get cache mode | none | bool |

### Methods: processing

| Method | Description |
| --- | --- |
| factory() |  |
| execute(string $url) |  |
| get(bool $json = false) |  |
| hasError():bool |  |
| getMessage():string |  |
| getCachePath() |  |
| getCacheFiles() |  |
| deleteCache() |  |

### Special methods

| Method | Description |
| --- | --- |
| urlToFileName(string $url) |  |
| fileNameToUrl(string $filename) |  |
| getLimits(bool $json = false) |  |
| getOrgInfo(string $org, bool $json = false) |  |
| getOrgRepoList(string $org, bool $json = false) |  |
| getUserInfo(string $user, bool $json = false) |  |
| getUserRepoList(string $user) |  |
| getRepoInfo(string $user, string $repo, bool $json = false) |  |
| getPagedContent(string $url, bool $json = false) |  |

### Examples

The preferred way to use RexGitApi is to create a new instance (due to error handling)

<TOKEN> is optional. If no token is specified, the token from the addon settings is used.

#### Querying Errors

To catch errors when accessing GitHub, an instance of `RexGitAPI` should be created with `new`.

`$gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi();`

With the method `hasError()` a possibly occurring error can be queried.
The error message can be output using the `getMessage()` method.

Example:

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

## Debug output

The debug output of the class can be requested via `setDebug(true)`. That can sometimes be quite helpful.
2 blocks are dumped with the REDAXO `dump()` instruction. Arrays can be expanded here by clicking on the arrow.


**Block 1: Parameters**

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_debug1.png?raw=true)

Description

0. Info Debug output of RexGitApi class
1. Requested GitHub Api URL
2. CURLOPT_USERAGENT
3. CURLOPT_HTTPHEADER (including the token)

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_debug2.png?raw=true)

**Block 2: Results**

Description

0. Info Debug output of RexGitApi class
1. JSON result of the API call
2. PHP array result of the API call

## API tester

... TODO

## Authentication (Token)

Without authentication, only 60 accesses per hour to the GitHub API are possible. With token you can access the API 5,000 times per hour.

To create an **access token** (personal access token), you should log into GitHub and then go to the following URL:

`https://github.com/settings/apps`

Then click **Personal access tokens** (1) on the page and then **Tokens (classic)** (2) (see screenshot).

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token1.png?raw=true)

On the next page, click on the button **Generate new token** (1) and then select **Generate new token (classic)** (2) (see screenshot).

> **Notice:** Then the Github password must be entered again for confirmation!

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token2.png?raw=true)

On the next page, enter a **label** (1), select the **token expiration** (2), and select **repo** (3) from the options.

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token3.png?raw=true)

For access to user data, additionally select the option **user** (4).

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token4.png?raw=true)

The generated token is then output on the following screen, which should definitely be copied and saved.

> **Notice:** Be sure to copy and save the token! The token can no longer be displayed!

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token5.png?raw=true)

Ideally the token is stored in the settings for `rex_gitapi`.

![Screenshot](https://github.com/FriendsOfREDAXO/rex_gitapi/blob/assets/rex_gitapi_token6.png?raw=true)

> **Notice:** Tokens can be deleted and re-created at any time. A new token in the settings of the AddOn `rex_gitapi` is taken into account immediately.

## Any questions or suggestions? Found a bug?

Then it goes on here ...

* On GitHub: [https://github.com/FriendsOfREDAXO/rex_gitapi](https://github.com/FriendsOfREDAXO/rex_gitapi)

* In the Slack channel: [https://friendsofredaxo.slack.com/](https://friendsofredaxo.slack.com/)

## Credits ##

* GitHub API https://docs.github.com/rest
* [Friends Of REDAXO](https://github.com/FriendsOfREDAXO) Gemeinsame REDAXO-Entwicklung!
* Andreas Eberhard @aeberhard, http://aesoft.de

> Photo by Christina Morillo from Pexels: https://www.pexels.com/photo/eyeglasses-in-front-of-laptop-computer-1181253/
