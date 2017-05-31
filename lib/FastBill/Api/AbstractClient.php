<?php

namespace FastBill\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractClient
{
    protected $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Parses und returns the response
     *
     * @param ResponseInterface $response
     * @return mixed the result parsed
     */
    public function dispatchRequest(ResponseInterface $response)
    {
        return $this->parseJSON($json = (string) $response->getBody());
    }

    public function createRequest($method, $relativeResource, $body = null)
    {
        if ($body) { // assert object/array
            if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
                $jsonString = json_encode($body, JSON_PRETTY_PRINT);
            } else {
                $jsonString = json_encode($body);
            }
        }

        $response = $this->guzzle->request($method, $this->expandurl($relativeResource), [
            'auth' => [
                $this->email,
                $this->apiKey
            ],
            'body' => $jsonString
        ]);

        return $response;
    }

    /**
     * Returns a realtive resource without some api constraints
     *
     * some apis have urls like: /api/v1/
     * this helps to expand those v1/ parameters
     * @return string
     */
    protected function expandUrl($relativeResource)
    {
        return $relativeResource;
    }

    /**
     * @return array|object
     */
    protected function parseJSON($jsonString)
    {
        $json = json_decode($jsonString);

        if ($json === NULL) {
            // workaround for JSON Syntax bug

            $patchedJSON = preg_replace("/,[\r\n]*}\s*$/", '}', $jsonString);
            $json = json_decode($patchedJSON);
        }

        if ($json === NULL) {
            throw new \RuntimeException('API does return invalid JSON: <<<JSON'."\n".$jsonString."\nJSON");
        }

        return $json;
    }
}
