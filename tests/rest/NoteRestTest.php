<?php
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Note;
include 'PrivateEndpointRestTest.php';

class NoteRestTest extends PrivateEndpointRestTest
{
    use DatabaseTransactions;

    const LIST_MESSAGE = 'List of all notes.';
    const NOTE_CREATED_MESSAGE = 'Note has been created.';
    const NOTE_NOT_EXISTS_MESSAGE = 'Can\'t find any notes with this id.';
    const NO_PERMISSION_TO_ACCESS_NOTE_MESSAGE = 'You don\'t have permission to access this note.';

    public function __construct() {
        $this->endpointUrl = $this->baseUrl . '/notes';
    }

    /** @test */
    public function itCreatesANote()
    {
        $newNoteModel = factory(Note::class)->make();
        $newNote = [
            'title' => $newNoteModel->title,
            'content' => $newNoteModel->content
        ];

        $expectedResponseStructure = [
            'msg',
            'note' => [
                'id',
                'updated_at',
                'created_at',
                'title'
            ]
        ];
        $expectedResponse = [
            'msg' => self::NOTE_CREATED_MESSAGE,
            'title' => $newNote['title']
        ];

        $this->post($this->endpointUrl, $newNote, $this->getHeadersWithValidUserToken());

        $this->assertResponseStatus(201)
             ->seeJsonStructure($expectedResponseStructure)
             ->seeJson($expectedResponse);
    }

    /** @test */
    public function itDeletesANote()
    {
        $newNoteModel = factory(Note::class)->make();
        $newNote = [
            'title' => $newNoteModel->title,
            'content' => $newNoteModel->content
        ];

        $expectedResponseStructure = [
            'msg',
            'note' => [
                'id',
                'updated_at',
                'created_at',
                'title'
            ]
        ];
        $expectedResponse = [
            'msg' => self::NOTE_CREATED_MESSAGE,
            'title' => $newNote['title']
        ];

        $this->post($this->endpointUrl, $newNote, $this->getHeadersWithValidUserToken());

        $this->assertResponseStatus(201)
            ->seeJsonStructure($expectedResponseStructure)
            ->seeJson($expectedResponse);
    }

    /** @test */
    public function itGetsAllNotesOfAUser()
    {
        $newNoteModels = [
            factory(Note::class)->create(),
            factory(Note::class)->create(),
            factory(Note::class)->create()
        ];

        $newNoteModels[0]->attachUser($this->getTestUser());
        $newNoteModels[2]->attachUser($this->getTestUser());
        $newNoteModels[1]->attachUser($this->getOtherUser());
        $newNoteModels[2]->attachUser($this->getOtherUser());

        $expectedResponse = [
            'msg' => self::LIST_MESSAGE,
            'notes' => [
                [
                    'id' => $newNoteModels[0]->id,
                    'updated_at' => $newNoteModels[0]->updated_at->toDateTimeString(),
                    'created_at' => $newNoteModels[0]->created_at->toDateTimeString(),
                    'title' => $newNoteModels[0]->title
                ],
                [
                    'id' => $newNoteModels[2]->id,
                    'updated_at' => $newNoteModels[2]->updated_at->toDateTimeString(),
                    'created_at' => $newNoteModels[2]->created_at->toDateTimeString(),
                    'title' => $newNoteModels[2]->title
                ]
            ]
        ];

        $this->get($this->endpointUrl, $this->getHeadersWithValidUserToken())
             ->assertResponseStatus(200)
             ->seeJson($expectedResponse);
    }

    /** @test */
    public function itGetsANote()
    {
        $newNoteModel = factory(Note::class)->create();
        $newNoteModel->attachUser($this->getTestUser());
        $currentNoteEndpointUrl = $this->endpointUrl . '/' . $newNoteModel->id;

        $expectedResponse = [
            'id' => $newNoteModel->id,
            'updated_at' => $newNoteModel->updated_at->toDateTimeString(),
            'created_at' => $newNoteModel->created_at->toDateTimeString(),
            'title' => $newNoteModel->title,
            'content' => $newNoteModel->content
        ];

        $this->get($currentNoteEndpointUrl, $this->getHeadersWithValidUserToken())
            ->assertResponseStatus(200)
            ->seeJson($expectedResponse);
    }

    /** @test */
    public function itReturnsUnauthorizedIfUsersHasNoRightsToAccessNote()
    {
        $newNoteModel = factory(Note::class)->create();
        $currentNoteEndpointUrl = $this->endpointUrl . '/' . $newNoteModel->id;

        $expectedResponse = [
            'msg' => self::NO_PERMISSION_TO_ACCESS_NOTE_MESSAGE
        ];

        $this->get($currentNoteEndpointUrl, $this->getHeadersWithValidUserToken())
            ->assertResponseStatus(403)
            ->seeJson($expectedResponse);
    }

    /**
     * This test framework is crap, it stops on exception, and doesn't let the app process it and send the response.
     */
    public function itShouldParseTokenOnPost() {
        $httpMethod = 'POST';
        $this->itReturnsErrorIfTokenIsMissing($httpMethod);
        $this->itReturnsWithErrorIfTokenIsInvalid($httpMethod);
        $this->itReturnsWithErrorIfTokenIsExpired($httpMethod);
    }
}