<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
       public function index(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/settings", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.settings.index', $response->json());
        }

        return redirect()->back()->with('message', 'Web service error');
    }
    public function edit(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/settings/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.settings.edit', $response->json());
        }

        return redirect()->back()->with('message', 'Web service error');
    }
    public function update(Request $request)
    {
        // dd($request->all());
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/settings/update", $request,['navbar_logo', 'footer_logo']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully');
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
