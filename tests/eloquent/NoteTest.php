<?php

use App\Note;
use App\User;

class NoteTest extends TestCase
{
    /** @test */
    function it_fetches_linked_users() {
        $note = factory(Note::class)->create();
        $userOne = factory(User::class)->create();
        $userTwo = factory(User::class)->create();
        $note->attachUser($userOne);
        $note->attachUser($userTwo);

        $result = $note->getUsers();

        $this->assertEquals(2, $result->count());
    }

    // TODO: test other methods:
    // question: should I test set and get in one method? what should be the name of it?

}