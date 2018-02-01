<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Exception\MissingIdentityException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This handler will redirect the response if one of configured exception classes is encountered.
 */
class RedirectHandler implements HandlerInterface
{

    /**
     * Default config:
     *
     *  - `exceptions` - A list of exception classes.
     *  - `url` - Url to redirect to.
     *  - `queryParam` - Query parameter name for the target url.
     *  - `statusCode` - Redirection status code.
     *
     * @var array
     */
    protected $defaultOptions = [
        'exceptions' => [
            MissingIdentityException::class,
        ],
        'url' => '/login',
        'queryParam' => 'redirect',
        'statusCode' => 302,
    ];

    /**
     * Return a response with a location header set if an exception matches.
     *
     * {@inheritDoc}
     */
    public function handle(Exception $exception, ServerRequestInterface $request, ResponseInterface $response, array $options = [])
    {
        $options += $this->defaultOptions;

        if (!$this->checkException($exception, $options['exceptions'])) {
            throw $exception;
        }

        $url = $this->getUrl($request, $options);

        return $response
                ->withHeader('Location', $url)
                ->withStatus($options['statusCode']);
    }

    /**
     * Checks if an exception matches one of the classes.
     *
     * @param \Authorization\Exception\Exception $exception Exception instance.
     * @param array $exceptions A list of exception classes.
     * @return bool
     */
    protected function checkException(Exception $exception, array $exceptions)
    {
        foreach ($exceptions as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the url for the Location header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @param array $options Options.
     * @return string
     */
    protected function getUrl(ServerRequestInterface $request, array $options)
    {
        $url = $options['url'];
        if ($options['queryParam'] !== null) {
            $query = urlencode($options['queryParam']) . '=' . urlencode($request->getUri());
            if (strpos($url, '?') !== false) {
                $query = '&' . $query;
            } else {
                $query = '?' . $query;
            }

            $url .= $query;
        }

        return $url;
    }
}
