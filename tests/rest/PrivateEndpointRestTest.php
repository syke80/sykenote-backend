<?php
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class PrivateEndpointRestTest extends TestCase
{
    use DatabaseTransactions;

    const TOKEN_EXPIRED_MESSAGE = 'token_expired';
    const TOKEN_INVALID_MESSAGE = 'token_invalid';
    const TOKEN_MISSING_MESSAGE = 'token_not_provided';
    const INVALID_TOKEN_CONTENT = 'foo';
    const USER_PASSWORD = 'foo';

    // Needs to be overwritten
    protected $endpointUrl = null;

    protected $testUser = null;
    protected $otherUser = null;

    protected function createTestUser()
    {
        $this->testUser = factory(User::class)->create(['password' => bcrypt(self::USER_PASSWORD)]);
    }

    protected function createOtherUser()
    {
        $this->otherUser = factory(User::class)->create(['password' => bcrypt(self::USER_PASSWORD)]);
    }

    protected function getTestUser()
    {
        if (is_null($this->testUser)) {
            $this->createTestUser();
        }

        return $this->testUser;
    }

    protected function getOtherUser()
    {
        if (is_null($this->otherUser)) {
            $this->createOtherUser();
        }

        return $this->otherUser;
    }

    protected function getHeadersWithValidUserToken()
    {
        $token = JWTAuth::fromUser($this->getTestUser());
        JWTAuth::setToken($token);
        $extraHeaders = [ 'Authorization' => 'Bearer '.$token];
        return $this->getHeaders($extraHeaders);
    }

    protected function getHeadersWithInvalidToken()
    {
        $token = JWTAuth::fromUser($this->getTestUser());
        $token = substr($token, 0, -3).'xxx';
        JWTAuth::setToken($token);
        $extraHeaders = [ 'Authorization' => 'Bearer '.$token];
        return $this->getHeaders($extraHeaders);
    }

    protected function getHeadersWithExpiredToken()
    {
        // BUG: it's not expired, it's invalid
        $token = JWTAuth::fromUser($this->getTestUser());
        JWTAuth::invalidateToken($token);
        JWTAuth::setToken($token);
        $extraHeaders = [ 'Authorization' => 'Bearer '.$token];
        return $this->getHeaders($extraHeaders);
    }

    protected function getHeaders($extraHeaders = [])
    {
        $headers = ['Accept' => 'application/json'] + $extraHeaders;

        return $headers;
    }

    public function itReturnsWithErrorIfTokenIsExpired($httpMethod)
    {
        $this->call($httpMethod, $this->endpointUrl, $this->getHeadersWithInvalidToken());
        $this->assertResponseStatus(400)
            ->seeJson(['error' => self::TOKEN_EXPIRED_MESSAGE]);
    }

    public function itReturnsWithErrorIfTokenIsInvalid($httpMethod)
    {
        $this->call($httpMethod, $this->endpointUrl, $this->getHeadersWithInvalidToken());
        $this->assertResponseStatus(400)
            ->seeJson(['error' => self::TOKEN_INVALID_MESSAGE]);
    }

    protected function itReturnsErrorIfTokenIsMissing($httpMethod)
    {
        $this->call($httpMethod, $this->endpointUrl, $this->getHeaders());
        $this->assertResponseStatus(400)
             ->seeJson(['error' => self::TOKEN_MISSING_MESSAGE]);
    }

    /** @test */
    public function itIsAccessibleWithoutTokenWithOptionsMethod()
    {
        $this->call('OPTIONS', $this->endpointUrl, $this->getHeaders());
        $this->assertResponseStatus(200);
    }
}