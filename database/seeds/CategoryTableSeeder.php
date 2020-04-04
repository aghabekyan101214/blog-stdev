<?php

use App\Model\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    const CATEGORIES = [
        "Animals",
        "Nature",
        "Cars",
        "Health",
    ];

    public function run()
    {
        foreach (self::CATEGORIES as $c){
            $category = new Category();
            $category->name = $c;
            $category->save();
        }
    }
}
