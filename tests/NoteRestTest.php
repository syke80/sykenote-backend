<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NoteTest extends TestCase
{
    const EXISTING_USER = 'user@example.com';
    const EXISTING_USER_PASSWORD = 'password';

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testLoginPage()
    {
        $expected = [];
        $postParameters = [
            'email' => self::EXISTING_USER,
            'password' => self::EXISTING_USER_PASSWORD
        ];

        $this->post('http://api.sykenote.dev/api/login', $postParameters);
        echo $this->dump();

        //$this->seeJsonEquals($expected);


        //    ->seeJsonEquals($expected);
    }
}
