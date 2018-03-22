<?php
namespace Julfiker\Service;

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Session\Session as Session;

/**
 * A service to check and generate unique csrf token on each rendering page.
 * @author Julfiker <mail.julfiker@gmail.com>
 */
class CsrfManager
{
    /** Constant */
    const SLAT = "uglymindcannotbehuman";
    const SESSION_STORE_TOKEN_NAME = 'csrf_token';
    const SESSION_STORE_TOKEN_EXPIREDAT = 'EXPIRED_AT';
    const TOKEN_FIELD_NAME = "_token";

    /** @var  string */
    private $CSRFToken;

    /** @var  */
    private $CSRFTokenExpireTime;

    /** @var int  */
    private $tokenValidInMinutes = 30; //30 minutes

    /** @var \Symfony\Component\HttpFoundation\Request  */
    private $request;

    /** @var \Symfony\Component\HttpFoundation\Session\Session  */
    private $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->request = Request::createFromGlobals();
        $this->session = new Session();
    }

    /**
     * Check token is expired already
     * @return bool
     */
    private function _isTokenExpired() {
        return time() > $this->session->get(self::SESSION_STORE_TOKEN_EXPIREDAT);
    }

    /**
     * Check token is not expired yet
     * @return bool
     */
    private function _isNotExpiredAt() {
        return !$this->_isTokenExpired();
    }

    /**
     * Unset storage
     */
    public function unsetToken() {
        $this->session->remove(self::SESSION_STORE_TOKEN_EXPIREDAT);
        $this->session->remove(self::SESSION_STORE_TOKEN_NAME);
        $this->session->clear();
    }

    /**
     * Checking token based on post|pull|delete request
     * @return boolean
     * @throws \Exception
     */
    public function checkToken() {
        $pass = false;
        $method = $this->request->getRealMethod();
        if (in_array($method, ['PUT','POST','DELETE'])) {
            $csrfToken = $this->getRequest()->get($this->getTokenFieldName());
            if ($csrfToken && $this->getStorageToken() == $csrfToken) {
                $pass = true;
            }
        }
        $this->unsetToken();
        return $pass;
    }

    /**
     * Generate CSRF token to handle request
     *
     * @return string
     */
    public function generateToken() {
        $sesId = session_id();
        if (!$sesId)
           $sesId = session_id("UN_AUTHORIZED");

        $this->CSRFToken = md5($sesId+self::SLAT+time());
        $minutes = $this->tokenValidInMinutes;
        $this->CSRFTokenExpireTime = strtotime("+$minutes minutes");

        //Store into the session
        $this->session->set(self::SESSION_STORE_TOKEN_EXPIREDAT, $this->CSRFTokenExpireTime);
        $this->session->set(self::SESSION_STORE_TOKEN_NAME, $this->CSRFToken);
        $this->session->save();
    }

    /**
     * Get already storage token
     *
     * @return string
     */
    public function getStorageToken() {
        if ($this->session->get(self::SESSION_STORE_TOKEN_NAME) && $this->_isNotExpiredAt())
            return $this->session->get(self::SESSION_STORE_TOKEN_NAME);

        return false;
    }

    /**
     * Refresh new token
     * @return void
     */
    public function refreshToken() {
        $this->generateToken();
    }

    /**
     * Get CSRF token
     * @return string
     */
    public function getCSRFToken() {
        if (!$this->session->has(self::SESSION_STORE_TOKEN_NAME) || $this->_isTokenExpired())
        $this->refreshToken();

       return $this->session->get(self::SESSION_STORE_TOKEN_NAME);
    }

    /**
     * Token field name
     */
    public function getTokenFieldName() {
        return self::TOKEN_FIELD_NAME;
    }
    /**
     * Get request object
     *
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @return Session
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * Checking token is invalid or not
     * @return bool
     */
    public function isValidToken() {
        return $this->checkToken();
    }

    /**
     * Set time expire token
     * @param int $minutes
     * @return void
     */
    public function setExpiredAt($minutes) {
        $this->tokenValidInMinutes = $minutes;
    }
}
