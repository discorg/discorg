<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Authentication;

use App\Infrastructure\Http\Authentication\CannotParseAuthentication;
use App\Infrastructure\Http\Authentication\TokenAuthentication;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use function sprintf;

final class TokenAuthenticationTest extends TestCase
{
    public function testSuccess() : void
    {
        $token = 'some-pseudo-uuid';

        $request = $this->createRequest([
            'Authorization' => sprintf('Bearer %s', $token),
        ]);

        $authentication = TokenAuthentication::fromRequestHeader($request);

        Assert::assertSame($token, $authentication->token());
    }

    public function testFailWithHeaderNotFound() : void
    {
        $request = $this->createRequest([]);

        $this->expectException(CannotParseAuthentication::class);
        TokenAuthentication::fromRequestHeader($request);
    }

    public function testFailWithInvalidCredentialsString() : void
    {
        $request = $this->createRequest([
            'Authorization' => sprintf('Bearer '),
        ]);

        $this->expectException(CannotParseAuthentication::class);
        TokenAuthentication::fromRequestHeader($request);
    }

    /**
     * @param string[] $headers
     */
    private function createRequest(array $headers) : ServerRequest
    {
        return new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user/me/session'),
            $headers,
        );
    }
}
