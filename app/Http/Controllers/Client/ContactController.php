<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility\CoSoDaoTao;

class ContactController extends Controller
{
    //
    public function index(){
        $coSoDaoTao = CoSoDaoTao::with('tinhThanh')->get();
        return view('clients.contact.index', compact('coSoDaoTao'));
    }
}
