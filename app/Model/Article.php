<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public function comments()
    {
        return $this->hasMany("App\Model\Comment", "article_id", "id");
    }

    public function category()
    {
        return $this->belongsTo("App\Model\Category", "category_id", "id");
    }
}
