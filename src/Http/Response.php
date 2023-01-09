<?php

namespace Tomb1n0\GenericApiClient\Http;

use Illuminate\Support\Arr;
use Tomb1n0\GenericApiClient\Options;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tomb1n0\GenericApiClient\Contracts\PaginationHandlerContract;

class Response
{
    protected Client $client;
    protected RequestInterface $request;
    protected ResponseInterface $response;
    protected string $contents;
    protected ?PaginationHandlerContract $paginationHandler = null;
    protected $decoded;

    public function __construct(
        Client $client,
        RequestInterface $request,
        ResponseInterface $response,
        PaginationHandlerContract $paginationHandler,
    ) {
        $this->client = $client;
        $this->request = $request;
        $this->response = $response;
        $this->paginationHandler = $paginationHandler;

        $this->contents = $this->response->getBody()->getContents();
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function json(?string $key = null, $default = null): mixed
    {
        if (!$this->decoded) {
            $this->decoded = json_decode($this->contents, true);
        }

        if (is_null($key)) {
            return $this->decoded;
        }

        return Arr::get($this->decoded, $key, $default);
    }

    public function hasNextPage(): bool
    {
        if (isset($this->paginationHandler)) {
            return $this->paginationHandler->hasNextPage($this);
        }

        return false;
    }

    public function getNextPage(): ?Response
    {
        if (isset($this->paginationHandler) && $this->hasNextPage()) {
            $request = $this->paginationHandler->getNextPage($this);

            return $this->client->send($request);
        }

        return null;
    }

    public function forEachPage(callable $callback): void
    {
        if (!isset($this->paginationHandler)) {
            return;
        }

        $response = $this;

        // Call for this first page
        $callback($response);

        // Call for every subsequent page if there is one
        while ($response->hasNextPage()) {
            $response = $response->getNextPage();

            $callback($response);
        }
    }
}
