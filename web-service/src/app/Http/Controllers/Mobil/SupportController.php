<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(){
        $query = Contact::query()
            ->orderBy('position')->whereIn('type',['phone','telegram','email'])
            ->orderByDesc('id')->get();
        return $this->successResponse($query, "success");
    }
}
