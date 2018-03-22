<?php
namespace Csrf\Exception;

use \Exception as Exception;
/**
 * Csrf Invalid token exception
 * @autho: Julfiker <mail.julfiker@gmail.com>
 */
class CsrfInvalidException extends Exception {
    protected $massage = "Invalid token";
}
