<?php

use App\Note;
use App\User;

class UserTest extends TestCase
{
    /** @test */
    function it_fetches_linked_notes() {
        $user = factory(User::class)->create();
        $notes = factory(Note::class, 3)->create();
        foreach ($notes as $note) {
            $note->attachUser($user);
        }

        $result = $user->getNotes();

        $this->assertEquals(3, $result->count());
    }
}