<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;
use Exception;

class PostController extends Controller
{
    public $successStatus = 200;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = Post::all();

        return sendResponse(PostResource::collection($posts), 'Posts retrieved successfully.');
        // return response()->json( PostResource::collection($posts), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
        
    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'title'       => 'required|min:10',
            'description' => 'required|min:40'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);
        /* if ($validator->fails()) {
            return response()->json( ['Validation Error' => $validator->errors()], 422);            
        } */
        
        try {
            
            $post    = Post::create([
                'title'       => $request->title,
                'description' => $request->description,
                'created_by' => Auth::user()->id
            ]);

            $success = new PostResource($post);
            $message = 'Yay! A post has been successfully created.';
        } catch (Exception $e) {
            $success = [];
            $message = 'Oops! Unable to create a new post.';
        }

        return sendResponse($success, $message);
        // return response()->json(['success'=>$success, 'message'=>$message], $this->successStatus);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = Post::find($id);

        if (is_null($post)) return sendError('Post not found.');

        return sendResponse(new PostResource($post), 'Post retrieved successfully.');
        // return response()->json([ 'post' => new PostResource($post), 'message'=>'Post retrieved successfully.'], $this->successStatus );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Post    $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Post $post)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'title'       => 'required|min:10',
            'description' => 'required|min:40'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);
        /* if ($validator->fails()) {
            return response()->json( ['Validation Error' => $validator->errors()], 422);            
        } */

        try {
            $post->title       = $request->title;
            $post->description = $request->description;
            $post->save();

            $success = new PostResource($post);
            $message = 'Yay! Post has been successfully updated.';
        } catch (Exception $e) {
            $success = [];
            $message = 'Oops, Failed to update the post.';
        }

        return sendResponse($success, $message);
        // return response()->json(['success'=>$success, 'message'=>$message] , $this->successStatus);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();
            // return sendResponse([], 'The post has been successfully deleted.');
            return response()->json([], 'The post has been successfully deleted.');
        } catch (Exception $e) {
            return response()->json( ['error'=>'Oops! Unable to delete post.'], 404);
        }
    }
}