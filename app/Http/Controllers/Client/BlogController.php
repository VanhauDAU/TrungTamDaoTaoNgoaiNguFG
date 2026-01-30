<?php

namespace App\Http\Controllers\Client;

use App\Models\Content\BaiViet;
use Illuminate\Http\Request;
use App\Models\Content\DanhMucBaiViet;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    //
    public function index(Request $request){
        $query = BaiViet::where('trangThai', 1)->latest();
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('danhMucs', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug); 
            });
        }

        $blogs = $query->with(['danhMucs', 'tags'])->paginate(9)->withQueryString(); 

        $categories = DanhMucBaiViet::all();

        return view('clients.blog.index', compact('blogs', 'categories'));
    }
}
