<?php

/**
 * @category Class
 * @package  App/Core/Router
 * @author   Marry Go Round <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core\Router;

final class Request
{
    public function __construct()
    {
        $this->setRequestVariable();
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * Return parameters of body to be the object
     * For method: GET, POST, PUT, PATH, DELETE
     * @return object
     */
    final public function body(): object
    {
        return (json_decode(file_get_contents('php://input')));
    }

    /**
     * Return a full path of the request
     * @return string
     */
    final public function getPathInfo(string $request_uri = null): string
    {
        return (string) implode('', [
            ($this->isSecure()) ? 'https://' : 'http://',
            $this->httpHost,
            (!empty($request_uri)) ? $request_uri : $this->requestUri
        ]);
    }

    /**
     * Return all parameter of the request to be objects
     * For method: GET, POST
     * @return object
     */
    final public function params(): object
    {
        # when current method is GET
        if ($this->requestMethod == 'GET') {
            $requestParameters = $_GET;
            $inputType = INPUT_GET;
        }

        # when current method is POST
        if ($this->requestMethod == 'POST') {
            $requestParameters = $_POST;
            $inputType = INPUT_POST;
        }

        if (!empty($requestParameters) && !empty($inputType)) {
            $parameters = [];
            foreach ($requestParameters as $name => $value) {
                $parameters[$name] = (filter_input($inputType, $name, FILTER_SANITIZE_SPECIAL_CHARS));
            }
            # return parameter as objects
            return (!empty($parameters)) ? (object) $parameters : null;
        }
        return null;
    }

    final public function hasAuthorized(string $authorization)
    {
        if (!empty($this->httpAuthorization)) {
            $explode_authorization = explode(' ', $this->httpAuthorization);
            if (strtolower($explode_authorization[0]) == $authorization && !empty($explode_authorization[1])) {
                return $explode_authorization[1];
            }
        }
        return null;
    }

    /**
     * Check if the request has the content-type in header  or not ?
     * @param string $contentType
     * @return bool
     */
    private function hasContentType(string $contentType): bool
    {
        if (!empty($this->contentType) && !empty($this->httpContentType)) {
            return (strtolower($this->contentType) == $contentType && strtolower($this->httpContentType) == $contentType);
        }
        return false;
    }

    /**
     * Check if the request is an AJAX request
     * @return bool
     */
    final public function isAjax(): bool
    {
        if (!empty($this->httpXRequestedWith)) {
            return ('xmlhttprequest' == strtolower($this->httpXRequestedWith) ?? '');
        }
        return false;
    }

    /**
     * Check if the request is an JSON request
     * @return bool
     */
    final public function isJson(): bool
    {
        return $this->hasContentType('application/json');
    }

    /**
     * Check if the request is an JSON request
     * @return bool
     */
    final public function isXml(): bool
    {
        return $this->hasContentType('application/xml');
    }

    /**
     * Validate the HTTP protocol is secure or not ?
     * @return bool
     */
    final public function isSecure(): bool
    {
        if (!empty($this->https) && $this->https === 'on') {
            return true;
        }
        if (!empty($this->serverPort) && $this->serverPort === 443) {
            return true;
        }
        if (!empty($this->httpXForwardedSsl) && $this->httpXForwardedSsl === 'on') {
            return true;
        }
        if (!empty($this->httpXForwardedProto) && $this->httpXForwardedProto === 'https') {
            return true;
        }
        return false;
    }

    /**
     * Validate the request are from API , Ajax and JSON or not ?
     * @return bool
     */
    final public function requestApi(): bool
    {
        if ($this->isJson()) {
            return true;
        }
        if ($this->isXml()) {
            return true;
        }
        if ($this->isAjax()) {
            return true;
        }
        return false;
    }

    /**
     * Set new variable of the request 
     * @return void
     */
    private function setRequestVariable(): void
    {
        /**
         * Closure : reformat string to camel case 
         * @param string $string
         * @return string
         */
        $strCamelCase = function (string $string) {
            $string = strtolower($string);
            preg_match_all('/_[a-z]/', $string, $matches);
            foreach ($matches[0] as $match) {
                $str_replace = str_replace('_', '', strtoupper($match));
                $string = str_replace($match, $str_replace, $string);
            }
            return $string;
        };

        foreach ($_SERVER as $key => $value) {
            $this->{$strCamelCase($key)} = $value;
        }
    }
}
