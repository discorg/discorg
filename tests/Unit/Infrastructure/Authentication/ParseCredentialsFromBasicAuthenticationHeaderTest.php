<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Authentication;

use App\Domain\UserAuthentication\UserCredentials;
use App\Infrastructure\Http\Authentication\CannotParseAuthentication;
use App\Infrastructure\Http\Authentication\ParseCredentialsFromBasicAuthenticationHeader;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use function base64_encode;
use function sprintf;

final class ParseCredentialsFromBasicAuthenticationHeaderTest extends TestCase
{
    public function testSuccess() : void
    {
        $username = 'username';
        $password = 'secret';

        $request = $this->createRequest([
            'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $username, $password))),
        ]);

        $credentials = (new ParseCredentialsFromBasicAuthenticationHeader())->__invoke($request);

        Assert::assertEquals(
            UserCredentials::fromStrings($username, $password),
            $credentials,
        );
    }

    public function testFailWithHeaderNotFound() : void
    {
        $request = $this->createRequest([]);

        $this->expectException(CannotParseAuthentication::class);
        (new ParseCredentialsFromBasicAuthenticationHeader())->__invoke($request);
    }

    public function testFailWithIncorrectlyEncodedCredentials() : void
    {
        $encodedCredentials = base64_encode(sprintf('%s:%s', 'username', 'password'));
        $corruptedEncodedCredentials = sprintf('xxx%s', $encodedCredentials);

        $request = $this->createRequest([
            'Authorization' => sprintf('Basic %s', $corruptedEncodedCredentials),
        ]);

        $this->expectException(CannotParseAuthentication::class);
        (new ParseCredentialsFromBasicAuthenticationHeader())->__invoke($request);
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
        (new ParseCredentialsFromBasicAuthenticationHeader())->__invoke($request);
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
