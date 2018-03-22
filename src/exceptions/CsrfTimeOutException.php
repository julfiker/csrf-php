<?php
namespace Csrf\Exception;

use \Exception as Exception;

/**
 * Csrf token timeout exception
 * @autho: Julfiker <mail.julfiker@gmail.com>
 */
class CsrfTimeOutException extends Exception {protected $massage = "Token already expired! please try again.";}
