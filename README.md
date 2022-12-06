# GoogleDrive Api Wrapper


### usage
at first you need to create oauth client on google cloud platform. so go to the your google console [dashboard](https://console.cloud.google.com/apis/dashboard) and create a new client.
then go to Drive.php file and fill below fields.
```php
class Drive {

    public static $clientId='YOUR_CLIENT_ID';
    public static $clientSecret='YOUR_CLIENT_SECRET';
    public static $redirectUri='YOUR_REDIRECT_URI'; //you should set callback.php file in your redirect_uri: http://domain.com/callback.php

```

after done this settings now you can call other methods.
### Authentication
to work on google APIs you need to authentication.
so call getCode method.
#### getCode
```php
    require 'Server.php';
    require 'Drive.php';

    //Drive::getCode(); return get code url

    echo '<a href="'.Drive::getCode().'">Go Auth</a>';

```
<br/>

#### load list drive

```php
    require 'Server.php';
    require 'Drive.php';
    //simpleUpload
   echo Drive::loadListTeamdriveId();

```
