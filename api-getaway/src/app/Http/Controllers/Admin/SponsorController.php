<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        return view('admin.sponsor.index');
    }
    public function create(Request $request)
    {
        return view('admin.sponsor.create');
    }
    public function store(Request $request)
    {

        $response = $this->forwardRequestMedias("POST", $this->url, "admin/sponsors/store", $request, ['logo']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor added successfully');
        }


        return redirect()->back()->with('message', 'Web service error');
    }
    public function edit($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/sponsors/{$id}/edit", $request, ['logo']);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            // dd($response->json());
            return view('admin.sponsor.edit', $response->json());
        }

        return redirect()->back()->with('message', 'Web service error');
    }
    public function update($id, Request $request)
    {
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/sponsors/{$id}", $request,['logo']);
        // dd($response->json());
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor updated successfully');
        }


        return redirect()->back()->with('message', 'Web service error');
    }
    public function destroy($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/sponsors/{$id}/delete", $request);
        Log::info($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'Sponsor deleted successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/sponsors/data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promocode service error'], 500);
    }
    public function changeStatus($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/sponsors/{$id}/status", $request);
        Log::info($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'Sponsor status updated successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
