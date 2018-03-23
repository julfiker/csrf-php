# csrf-php
Generating csrf token and checking in POST|PULL|DELETE method action. Its independent service for php application. You can integrated it php any kind application.

## Installation
``` sh
composer require julfiker/csrf-php
```
Just run composer require command with package name. This assumes you have composer installed and available in your path as composer. Instruction to have composer https://getcomposer.org/doc/00-intro.md. 

## How to use in plain php application
```php
<?php 
require_once __DIR__."/vendor/autoload.php";
use Julfiker\Manager\CsrfManager as Csrf;
$csrf = new Csrf();
$csrf->setExpiredAt(10); //10 minutes; But default it has 30 minutes
$token = $csrf->getCSRFToken();
$tokenFieldName = $csrf->getTokenFieldName();
```
```html
<!-- view html template page -->
<form action="post.php" method="post">
    <label>Subscribes email</label>
    <input type="text" name="email" />
    <input type="hidden" value="<?php echo $token?>" name="<?php echo $tokenFieldName?>" />
    <button type="submit">Submit</button>
</form>

```
Checking token in post action
```php
<?php 
require_once __DIR__."/vendor/autoload.php";
use Julfiker\Service\CsrfManager as Csrf;


$csrf = new Csrf();
if (!$csrf->isValidToken()) { //Is not valid token
    echo "Invalid token!";
    exit;
}
echo "Token was valid and saving the information";
```

## How to use in zendframework 1.*
Work in progress
