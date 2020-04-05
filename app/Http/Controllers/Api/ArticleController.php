<?php

namespace App\Http\Controllers\Api;

use App\helpers\ResponseHelper;
use App\Model\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = $request->order == "asc" ? "asc" : "desc";
        $query = strtolower($request->search);
        $sql = Article::withCount("comments")->orderBy("created_at", $order);
        if(null != $query) $sql->where("title", "LIKE", "%$query%");
        $articles = $sql->get();
        if(null != $request->slug) {
            $arr = array(
                "article" => Article::with(["category", "comments"])->where("slug", $request->slug)->first(),
            );
            return ResponseHelper::success($arr);
        }
        return ResponseHelper::success($articles);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $validator = Validator::make($data,
            [
                'title' => 'required|max:100',
                'text' => 'required',
                'category_id' => "required|integer|min:1"
            ]);
        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }

        $last = Article::orderBy("id", "desc")->first()->id ?? 0;
        $article = new Article();
        $article->title = $data["title"];
        $article->slug = $this->slugify($data["title"]) . "-" . ($last + 1);
        $article->text = $data["text"];
        $article->category_id = $data["category_id"];
        $article->user_id = Auth::user()->id;
        $article->save();
        return ResponseHelper::success(array());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $data = json_decode($request->getContent(), true);
        $validator = Validator::make($data,
            [
                'title' => 'required|max:100',
                'text' => 'required',
                'category_id' => "required|integer|min:1"
            ]);
        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }
        $article->title = $data["title"];
        $article->slug = $this->slugify($data["title"]) . "-" . $article->id;
        $article->text = $data["text"];
        $article->category_id = $data["category_id"];
        $article->save();

        return ResponseHelper::success(array());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return ResponseHelper::success(array());
    }

    private function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
