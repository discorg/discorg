<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Assert\Assertion;
use Closure;
use function array_key_exists;

final class HandlerFactoryCollection
{
    /** @var Closure[] */
    private $handlerFactoriesByActionIdentifier = [];

    /**
     * @param Closure[] $handlerFactoriesByActionIdentifier
     */
    private function __construct(array $handlerFactoriesByActionIdentifier)
    {
        $this->handlerFactoriesByActionIdentifier = $handlerFactoriesByActionIdentifier;
    }

    /**
     * @param Closure[] $handlerFactoriesByActionIdentifier
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
