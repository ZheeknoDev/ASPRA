<?php

namespace App\Core\Router;

final class Response
{
    private const STATUS_CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    ];

    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public static function instance()
    {
        return new self(new \App\Core\Router\Request);
    }

    /**
     * Response the object to be a json object
     * @param string|array|object $object
     * @param int $http_response_code - default is 200 
     */
    final public function json($object, int $http_response_code = 200)
    {
        // remove any string that could create an invalid JSON 
        ob_clean();
        // this will clean up any previously added headers, to start clean
        header_remove();
        // Set the content type to JSON and charset 
        header("Content-type: application/json; charset=utf-8");
        // Set your HTTP response code, 2xx = SUCCESS
        http_response_code($http_response_code);
        // encode your PHP Object or Array into a JSON string.
        echo json_encode($object);

        exit();
    }

    /**
     * Response the object to be a json object within response's pattern
     * @param string|array|object $response - default is null
     * @param bool $result
     * @param int $http_response_code - default is 200 
     */
    final public function json_form_response($response, bool $result = false, int $http_response_code = 200)
    {
        $object = [
            'Result' => $result,
            'StatusCode' => $http_response_code,
        ];

        if (is_string($response)) {
            $object['Message'] = $response;
        }

        if (is_array($response) or is_object($response)) {
            $object['Response'] = $response;
        }

        return $this->json($object, $http_response_code);
    }

    /**
     * Return the response message from HTTP response code.
     * @param int $http_response_code
     * @return string
     */
    final public function getResponseMessage(int $http_response_code = null): string
    {
        $code = (!empty($http_response_code)) ? $http_response_code : http_response_code();
        return (!empty(self::STATUS_CODES[$code])) ? (string) self::STATUS_CODES[$code] : null;
    }

    /**
     * Redirect the path
     * @param string $path
     */
    final public function redirect(string $path = null)
    {
        $request_uri = empty($path) ? '/' : $path;
        $redirect_to = $this->request->getPathInfo($request_uri);
        header("Location: {$redirect_to}");
        exit();
    }
}
