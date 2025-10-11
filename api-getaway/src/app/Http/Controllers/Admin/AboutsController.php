<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutsController extends Controller
{
    //
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/abouts", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.abouts.index', $response->json());
        }
        return redirect()->back()->with('message', 'Web service error');

    }
    public function edit(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/abouts/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.abouts.edit', $response->json());
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function update(Request $request)
    {
        //
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/abouts/update", $request,['about_image']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.abouts.index')->with('success', 'abouts updated successfully');
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
