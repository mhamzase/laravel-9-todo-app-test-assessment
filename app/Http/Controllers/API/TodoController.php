<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TodoResourse;
use App\Models\Todo;
use App\Http\Controllers\API\ResponseController as ResponseController;

class TodoController extends ResponseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = TodoResourse::collection(Todo::where('user_id', auth()->user()->id)->paginate(10));

        return $this->sendResponse($todos, 'Todos retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'completed' => 'required|boolean',
        ]);

        $todo = auth()->user()->todos()->create($data);

        return $this->sendResponse(new TodoResourse($todo), 'Todos created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $todo = auth()->user()->todos()->find($id);

        if ($todo) {
            return $this->sendResponse(new TodoResourse($todo), 'Todo retrieved successfully.');
        } else {
            return $this->sendError('Todo not found.', 404);
        }
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
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'completed' => 'required|boolean',
        ]);

        $todo = auth()->user()->todos()->find($id);

        if ($todo) {
            $todo->update($data);
            return $this->sendResponse(new TodoResourse($todo), 'Todo updated successfully.');
        } else {
            return $this->sendError('Todo not found.', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $todo = auth()->user()->todos()->find($id);

        if ($todo) {
            $todo->delete();
            return $this->sendResponse([], 'Todo deleted successfully.');
        } else {
            return $this->sendError('Todo not found.',  404);
        }
    }

    /**
     * Get todos by search query
     *
     * @return \Illuminate\Http\Response
     */

    public function search(Request $request)
    {
        $query = $request->get('query');

        $todos = auth()->user()->todos()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->paginate(10);

        return $this->sendResponse(TodoResourse::collection($todos));
    }
}
