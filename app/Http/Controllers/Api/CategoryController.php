<?php

namespace App\Http\Controllers\Api;

use App\helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $arr = array(
            "categories" => Category::all()
        );
        return ResponseHelper::success($arr);
    }
}
