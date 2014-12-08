<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DataCollector;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\DebugSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Csa Guzzle Collector
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleCollector extends DataCollector
{
    private $history;

    /**
     * Constructor
     *
     * @param DebugSubscriber $history the request history subscriber
     */
    public function __construct(DebugSubscriber $history)
    {
        $this->history = $history;
        $this->data = [];
    }

    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        foreach ($this->history as $transaction) {
            $request = $transaction['request'];
            $response = $transaction['response'];

            $req = [
                'request' => [
                    'method'  => $request->getMethod(),
                    'version' => $request->getProtocolVersion(),
                    'url'     => (string) $request->getUrl(),
                    'query'   => $request->getQuery() ? $request->getQuery()->toArray() : array(),
                    'headers' => $request->getHeaders(),
                    'body'    => (string) $request->getBody(),
                ],
                'duration' => $transaction['duration']
            ];

            if ($response) {
                $req['response'] = [
                    'statusCode'   => $response->getStatusCode(),
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'url'          => $response->getEffectiveUrl(),
                    'headers'      => $response->getHeaders(),
                    'body'         => (string) $response->getBody(),
                ];
            }

            $data[] = $req;
        }

        $this->data = $data;
    }

    public function getCalls()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'guzzle';
    }
}
