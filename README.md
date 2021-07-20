# CandyWrappers

A collection of PHP wrapper classes to make your life sweeter

## Usage
Each class takes two parameters to contruct: the host (string) and a resource (object) containing authentication information. Resources include the following properties:

* API_Connector
  * $type ('basic', 'token', or 'oauth' - **client credentials flow is experimental**)
  * $user ($type = 'basic')
  * $pass ($type = 'basic')
  * $token ($type = 'token')
  * $token_endpoint ($type = 'oauth')
  * $client_id ($type = 'oauth')
  * $client_secret ($type = 'oauth')
* Database_Connector
  * $type (**required**; 'mysql', 'sqlsrv', or 'odbc')
  * $user (**required**)
  * $pass (**required**)
  * $dbname
  * $port
* LDAP_Connector
  * $base_dn (**required**)
  * $user (**required**)
  * $pass (**required**)

## Examples
### API (cURL)
```php
use CandyWrappers\API_Connector;

# set authorization credentials (supports HTTP Basic or Bearer Token)
$api_authorization = (object)[
    'type' => 'basic',
    'user' => 'me',
    'pass' => '********'
];

# instantiates with a cURL handle with API base URL and credential headers
$api = new API_Connector('https://my.api.com', $api_authorization);

# sets $result to the content returned by https://my.api.com/items?format=json
# note: JSON is automatically decoded to a PHP object
$result = $api->get('/items', ['format' => 'json']);
```
### Database (PDO)
```php
use CandyWrappers\Database_Connector;

# set database information
$db_resource = (object)[
    'type' => 'mysql',
    'user' => 'me_again',
    'pass' => '********'
];

# instantiates a PDO connected to localhost with the supplied credentials
$database = new Database_Connector('localhost', $db_resource);

# sets $result to the results of the query
# note: query is prepared and executed with arguments after query string, in order
# note: Database_Connector::query() returns an array of objects
$result = $database->query('SELECT * FROM my_table WHERE id = ?', 10);
```

## License
[MIT](https://choosealicense.com/licenses/mit/)