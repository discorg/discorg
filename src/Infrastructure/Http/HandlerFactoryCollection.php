<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Assert\Assertion;
use Closure;
use Psr\Http\Server\RequestHandlerInterface;
use function array_key_exists;

class HandlerFactoryCollection
{
    /** @var RequestHandlerInterface[] */
    private $handlerFactoriesByActionIdentifier = [];

    /**
     * @param RequestHandlerInterface[] $handlerFactoriesByActionIdentifier
     */
    private function __construct(array $handlerFactoriesByActionIdentifier)
    {
        $this->handlerFactoriesByActionIdentifier = $handlerFactoriesByActionIdentifier;
    }

    /**
     * @param RequestHandlerInterface[] $handlerFactoriesByActionIdentifier
     */
    public static function fromArray(array $handlerFactoriesByActionIdentifier) : self
    {
        Assertion::allIsInstanceOf($handlerFactoriesByActionIdentifier, Closure::class);

        return new self($handlerFactoriesByActionIdentifier);
    }

    /**
     * @throws HandlerNotFound
     */
    public function getFactory(HttpActionIdentifier $actionIdentifier) : Closure
    {
        $actionAsString = $actionIdentifier->toString();

        if (! array_key_exists($actionAsString, $this->handlerFactoriesByActionIdentifier)) {
            throw HandlerNotFound::fromActionIdentifier($actionIdentifier);
        }

        return $this->handlerFactoriesByActionIdentifier[$actionAsString];
    }
}
