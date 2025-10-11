<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        return view('admin.contacts.index');
    }
    public function create(Request $request)
    {
        return view('admin.contacts.create');
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequestMedias("POST", $this->url, "admin/contacts/store", $request, ['image']);
        if ($response->status() == 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.contacts.index')->with('success', 'contacts added successfully');
        }


        return redirect()->back()->with('message', 'Web service error');
    }
    public function edit($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/contacts/{$id}/edit", $request, ['logo']);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.contacts.edit', $response->json());
        }

        return redirect()->back()->with('message', 'Web service error');
    }
    public function update($id, Request $request)
    {
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/contacts/{$id}", $request, ['image']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.contacts.index')->with('success', 'contacts updated successfully');
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function destroy($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/contacts/{$id}/delete", $request);
        Log::info("data", ['data' => $response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'contacts deleted successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/contacts/data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promocode service error'], 500);
    }
    public function changeStatus($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/contacts/{$id}/status", $request);
        Log::info("data1", ['data' => $response->json()]);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'contacts status updated successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
