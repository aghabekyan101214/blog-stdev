<?php

namespace App\Http\Controllers\Api;

use App\helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Comment;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function leaveComment(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $validator = Validator::make($data,
            [
                'article_id' => 'required|integer',
                'text' => 'required',
            ]);
        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }

        $comment = new Comment();
        $comment->article_id = $data["article_id"];
        $comment->comment = $data["comment"];
        $comment->save();
        return ResponseHelper::success(array());
    }
}
