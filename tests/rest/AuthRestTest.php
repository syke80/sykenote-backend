<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
include 'PrivateEndpointRestTest.php';

class AuthRestTest extends PrivateEndpointRestTest
{
    use DatabaseTransactions;

    const USER_CREATED_MESSAGE = 'User has been created.';
    const EMAIL_ALREADY_REGISTERED_RESPONSE = [ "email" => ["The email has already been taken."]];
    const LOGGED_IN_MESSAGE = 'Successfully logged in.';
    const INVALID_CREDENTIALS_MESSAGE = 'Invalid credentials.';
    const INVALID_POSTFIX = 'XY';
    const FIRST_INDEX = 1;


    /** @test */
    public function itStoresUserData()
    {
        $user = factory(User::class)->make();
        $credentials = [
            'email' => $user['email'],
            'password' => $user['password']
        ];
        $expectedStoredData = [
            'email' => $user['email'],
        ];
        $expectedResponse = [
            'msg' => self::USER_CREATED_MESSAGE,
            'email' => $user['email']
        ];

        $this->post('http://api.sykenote.dev/api/user', $credentials, $this->getHeaders())
             ->seeInDatabase('users', $expectedStoredData)
             ->assertResponseStatus(201)
             ->seeJsonContains($expectedResponse);
    }

    /** @test */
    public function itReturnsErrorIfUserAlreadyExistsAndTryToStoreIt()
    {
        $user = factory(User::class)->create();
        $credentials = [
            'email' => $user['email'],
            'password' => $user['password']
        ];

        $this->post('http://api.sykenote.dev/api/user', $credentials, $this->getHeaders())
            ->assertResponseStatus(422)
            ->seeJsonContains(self::EMAIL_ALREADY_REGISTERED_RESPONSE);
    }
    /** @test */
    public function itShouldGetTokenIfCredentialsAreCorrect() {
        $user = factory(User::class)->create(['password' => bcrypt('foo')]);
        $credentials = [
            'email' => $user['email'],
            'password' => 'foo'
        ];
        $expectedResponse = [
            'msg' => self::LOGGED_IN_MESSAGE
        ];
        $expectedResponseStructure = [
            'msg',
            'token'
        ];

        $this->post('http://api.sykenote.dev/api/login', $credentials, $this->getHeaders())
            ->assertResponseStatus(200)
            ->seeJsonContains($expectedResponse)
            ->seeJsonStructure($expectedResponseStructure);
    }

    /** @test */
    public function itShouldReturnWithErrorIfCredentialsAreNotCorrect() {
        $user = factory(User::class)->create();
        $credentials = [
            'email' => $user['email'].self::INVALID_POSTFIX,
            'password' => $user['password']
        ];
        $expectedResponse = [
            'msg' => self::INVALID_CREDENTIALS_MESSAGE
        ];

        $this->post('http://api.sykenote.dev/api/login', $credentials, $this->getHeaders())
            ->assertResponseStatus(401)
            ->seeJsonContains($expectedResponse);
    }

    // TODO: login get should return with user details object (email)
}
