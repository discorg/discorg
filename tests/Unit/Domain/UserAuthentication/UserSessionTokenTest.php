<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\UserAuthentication;

use App\Domain\UserAuthentication\UserSessionToken;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use function strlen;

final class UserSessionTokenTest extends TestCase
{
    public function testGeneratedTokensAreDifferent() : void
    {
        $token1 = UserSessionToken::generate();
        $token2 = UserSessionToken::generate();

        Assert::assertFalse($token1->equals($token2));
    }

    public function testGeneratedTokenIsComplexEnough() : void
    {
        $token = UserSessionToken::generate();

        Assert::assertTrue(strlen($token->toString()) > 20);
    }

    public function testFromStoredValue() : void
    {
        $token = UserSessionToken::fromStoredValue('test');

        Assert::assertSame('test', $token->toString());
    }
}
