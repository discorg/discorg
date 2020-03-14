<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Authentication;

use App\Infrastructure\Http\Authentication\BasicAuthentication;
use App\Infrastructure\Http\Authentication\CannotParseAuthentication;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use function base64_encode;
use function sprintf;

final class BasicAuthenticationTest extends TestCase
{
    public function testSuccess() : void
    {
        $username = 'username';
        $password = 'secret';

        $request = $this->createRequest([
            'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $username, $password))),
        ]);

        $authentication = BasicAuthentication::fromRequestHeader($request);

        Assert::assertSame($username, $authentication->username());
        Assert::assertSame($password, $authentication->password());
    }

    public function testFailWithHeaderNotFound() : void
    {
        $request = $this->createRequest([]);

        $this->expectException(CannotParseAuthentication::class);
        BasicAuthentication::fromRequestHeader($request);
    }

    public function testFailWithIncorrectlyEncodedCredentials() : void
    {
        $encodedCredentials = base64_encode(sprintf('%s:%s', 'username', 'password'));
        $corruptedEncodedCredentials = sprintf('xxx%s', $encodedCredentials);

        $request = $this->createRequest([
            'Authorization' => sprintf('Basic %s', $corruptedEncodedCredentials),
        ]);

        $this->expectException(CannotParseAuthentication::class);
        BasicAuthentication::fromRequestHeader($request);
    }

    /**
     * @dataProvider dataProviderForTestFailWithInvalidCredentialsString
     */
    public function testFailWithInvalidCredentialsString(string $credentials) : void
    {
        $request = $this->createRequest([
            'Authorization' => sprintf('Basic %s', base64_encode($credentials)),
        ]);

        $this->expectException(CannotParseAuthentication::class);
        BasicAuthentication::fromRequestHeader($request);
    }

    /**
     * @return mixed[]
     */
    public function dataProviderForTestFailWithInvalidCredentialsString() : array
    {
        return [
            ['username'],
            ['username:'],
            [':password'],
            ['username:password:foo'],
        ];
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
