# csrf-php
Generating csrf token and checking in POST|PULL|DELETE method action. Its independent service for php application. You can integrated it php any kind application.

## Installation
``` sh
composer require julfiker/csrf-php
```
Just run composer require command with package name. This assumes you have composer installed and available in your path as composer. Instruction to have composer https://getcomposer.org/doc/00-intro.md. 

## How to use in plain php application
```php
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
### In a multiple way you can integrate the csrf token validation for crontroller action  
Option 1: You can use customer action helper to check csrf token from controller action specifically  
Option 2: Plugin to check csrf on each post action method in general.  
*Example Action helper*  
```php
/**
 * Action helper checking csrf from action, it can be used in controller action like
 *
 * $this->_helper->csrf->validateToken()->ifInvalid()->gotoReferer();
 * OR
 * $this->_helper->csrf->validateToken()->ifInvalid()->gotoUrl('url_str');
 * OR
 * $csrf = $this->_helper->csrf->validateToken();
 * if ($csrf->isInvalidToken())
 *  $csrf->gotoUrl('url_string');
 *
 * @author: Julfiker <mail.julfiker@gmail.com>
 */
class ProjectNameSpace_Zend_Controller_Action_Helper_Csrf extends  Zend_Controller_Action_Helper_Redirector
{
    /** @var \Julfiker\Service\CsrfManager  */
    protected $csrfManager;

    /** @var bool  */
    protected $isValidToken = false;

    /** @var \Zend_Controller_Action_Helper_FlashMessenger  */
    protected $flashMessenger;

    public function __construct() {
        //Dependency injecting
        $this->csrfManager = new \Julfiker\Service\CsrfManager();
        $this->flashMessenger =  \Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }

    /**
     * Checking csrf token valid or not
     * @return $this
     */
    public function validateToken() {
        $this->isValidToken = $this->getCsrfManager()->isValidToken();
        return $this;
    }

    /**
     * @return $this
     */
    public function ifInvalid() {
        return $this;
    }

    /**
     * Redirecting to referer url
     */
    public function goToReferer() {
         if ($this->isInvalidToken()) {
             $this->flashMessenger->addMessage(array('error' => "Invalid token!"));
             return $this->gotoUrl($_SERVER['HTTP_REFERER']);
         }

        return $this->isValidToken;
    }

    /**
     * Redirecting to specific url
     * @param string $url
     * @param array $options
     * @return redirect|bool
     */
    public function gotoUrl($url, array $options = array()) {
        if ($this->isInvalidToken()) {
            return parent::gotoUrl($url, $options);
        }
        return $this->isValidToken;
    }

    /**
     * Get Csrf manager instance
     */
    public function getCsrfManager() {
        return $this->csrfManager;
    }

    /**
     * @return bool
     */
    public function isValidToken() {
        return $this->isValidToken;
    }

    /**
     * @return bool
     */
    public function isInvalidToken() {
        return !$this->isValidToken;
    }
}
```
### How to use action helper in controller  
Controller action example to use action helper  
```php
//Checking csrf protection
$this->_helper->csrf->validateToken()
    ->ifInvalid()
    ->gotoReferer();
//Or
$csrf = $this->_helper->csrf->validateToken(); 
if ($csrf->isInvalidToken())
$csrf->gotoUrl(‘url_string’);
```
However, without action helper you can use directly service to check csrf token like following  
```php
$csrf = new \Julfiker\Service\CsrfManager();
if (!$csrf->isValidToken()) {
    echo "Invalid token!";
    exit;
}
```
Another way to check token in general for all action.  
You need to create a controller plugin   
#### Example plugin code*
```php
/**
 * Class ProjectNameSpace_Zend_Controller_Plugin_Csrf
 */
class ProjectNameSpace_Zend_Controller_Plugin_Csrf extends Zend_Controller_Plugin_Abstract
{
    /**
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
       if ($request->isPost() || $request->isPut() || $request->isDelete()) {
         $csrf = new \Julfiker\Service\CsrfManager();
            if (!$csrf->isValidToken()) {
                //Redirect logic
                //Set flash error message here
                if ($referer = $request->getHeader('referer')) {                    
                    $this->_response->setRedirect($referer); 
                }
                else {
                    $this->_response->setRedirect("/"); 
                }
            }
       }
    }
}
```
Note: You have register plugin into application.ini. Or through front controller.   


### To render html token with hidden input element on each form
I recommend to use view helper to do that.  
#### Example view helper
```php
/**
 * Csrf token view helper used to render token
 *
 * @author: Julfiker <mail.julfiker@gmail.com>
 */
class ProjectNameSpace_Zend_View_Helper_CsrfToken extends Zend_View_Helper_Abstract
{
    /** @var  \Julfiker\Service\CsrfManager */
    private $csrfManager;

    /**
     * View to helper to render csrf token
     */
    public function csrfToken() {
        $this->csrfManager = new \Julfiker\Service\CsrfManager();
        //$this->csrfManager->setExpiredAt(30); //Set expired at, Default 30 MINUTES
        return $this;
    }

    /**
     * Render token field in html format
     * in the template or view page
     * @return string as html
     */
    public function render() {
       return "<input type='hidden' name='".$this->getCsrfManager()->getTokenFieldName()."' value='".$this->getCsrfManager()->getCSRFToken()."' />";
    }

    /**
     * @return \managers\CSRFManager
     */
    public function getCsrfManager() {
        return $this->csrfManager;
    }

    /**
     * Get token element for the form object, get specific element object with token value
     * @return \Zend_Form_Element_Hidden;
     */
    public function getElement() {
        $token = new Zend_Form_Element_Hidden($this->getCsrfManager()->getTokenFieldName());
        $token->setValue($this->csrfManager->getCSRFToken());
        return $token;
    }
}
```
### How to render token in html view by example view helper code   

If you used raw html form, then you can use following code to render token hidden filed  
```html
<?php echo $this->csrfToken()->render(); ?>
```  
If you used zend form to render form, then you can use following example code to add token into the form  
```php
$csrfToken = $this->getView()->csrfToken()->getElement(); 
$this->addElement($csrfToken);
```



