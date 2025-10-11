<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    //
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/downloads", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.downloads.index', $response->json());
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function edit(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/downloads/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.downloads.edit', $response->json());
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function update(Request $request)
    {
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/downloads/update", $request, ['image']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.downloads.index')->with('success', 'downloads updated successfully');
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
