<?php

namespace Tomb1n0\GenericApiClient\Http;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class FakeResponse
{
    /**
     * The body of the response
     *
     * @var array<int|string,mixed>|string|null
     */
    protected array|string|null $body = null;

    /**
     * The Status of the Response
     *
     * @var integer
     */
    protected int $status;

    /**
     * The headers of the Response
     *
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * The factory to use to
     *
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * Create a new FakeResponse
     *
     * @param array<int|string,mixed>|string|null $body
     * @param integer $status
     * @param array<string, string> $headers
     */
    public function __construct(array|string|null $body = null, int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
        $this->responseFactory = new HttpFactory();

        if (is_array($this->body)) {
            $this->body = json_encode($body);
        }
    }

    public function toPsr7Response(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->status);
        $response = $response->withBody(Utils::streamFor($this->body));

        foreach ($this->headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}
