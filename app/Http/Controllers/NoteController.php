<?php

namespace App\Http\Controllers;

use App\Note;
use Illuminate\Http\Request;

use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;

class NoteController extends Controller
{
    private static $OWNERS_RIGHTS = [
        'can_modify' => true,
        'can_delete' => true,
        'can_share' => true
    ];

    private $user;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = JWTAuth::authenticate();
        $notes = $this->user->getNotes();

        $responseData = [
            'msg' => 'List of all notes.',
            'notes' => $notes
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->user = JWTAuth::authenticate();
        $this->validate($request, [
            'title' => 'required|max:120'
        ]);

        $title = $request->input('title');
        $content = $request->input('content') ?: '';

        $noteData = [
            'title' => $title,
            'content' => $content
        ];

        $note = new Note($noteData);

        if ($note->save()) {
            $note->attachUser($this->user, self::$OWNERS_RIGHTS);

            $responseData = [
                'msg' => 'Note has been created.',
                'note' => $note
            ];

            return response()->json($responseData, 201);
        }

        $responseData = [
            'msg' => 'An error occurred.'
        ];

        return response()->json($responseData, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->user = JWTAuth::authenticate();
        $note = Note::find($id);

        if (!$note) {
            $responseData = [
                'msg' => 'Can\'t find any notes with this id.'
            ];

            return response()->json($responseData, 404);
        }

        if (!$note->canUserRead($this->user->id)) {
            $responseData = [
                'msg' => 'You don\'t have permission to access this note.'
            ];

            return response()->json($responseData, 403);
        }

        $responseData = [
            'note' => $note
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->user = JWTAuth::authenticate();
        $this->validate($request, [
            'title' => 'max:120'
        ]);

        $note = Note::find($id);

        if (!$note) {
            $responseData = [
                'msg' => 'Can\'t find any notes with this id.'
            ];

            return response()->json($responseData, 404);
        }

        if (!$note->canUserModify($this->user->id)) {
            $responseData = [
                'msg' => 'You don\'t have permission to modify this note.'
            ];

            return response()->json($responseData, 403);
        }

        $note->title = $request->input('title') ?: $note->title;
        $note->content = $request->input('content') ?: $note->content;
        $note->save();

        $responseData = [
            'msg' => 'Note has been successfully updated.'
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->user = JWTAuth::authenticate();
        $note = Note::find($id);

        if (!$note) {
            $responseData = [
                'msg' => 'Can\'t find any notes with this id.'
            ];

            return response()->json($responseData, 404);
        }

        if (!$note->canUserDelete($this->user->id)) {
            $responseData = [
                'msg' => 'You don\'t have permission to modify this note.'
            ];

            return response()->json($responseData, 403);
        }

        // TODO: this must be a transaction. if delete fails, we have unreachable note (no any users to access it)
        $note->users()->detach();
        $note->delete();
        $links = [
            'create' => [
                'href' => 'api/notes',
                'method' => 'POST',
                'params' => 'title, content'
            ]
        ];

        $responseData = [
            'msg' => 'Note deleted',
            'links' => $links
        ];

        return response()->json($responseData, 200);
    }

    public function options($id) {
        $responseData = [
            // TODO: return proper options response
        ];
        return response()->json($responseData, 200);
    }
}
