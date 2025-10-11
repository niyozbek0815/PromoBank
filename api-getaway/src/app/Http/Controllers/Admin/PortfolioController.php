<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PortfolioController extends Controller
{

    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        return view('admin.portfolio.index');
    }
    public function create(Request $request)
    {
        return view('admin.portfolio.create');
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequestMedias("POST", $this->url, "admin/portfolio/store", $request, ['image']);
        // dd($response->json());
        if ($response->status() == 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.portfolio.index')->with('success', 'portfolio added successfully');
        }


        return redirect()->back()->with('message', 'Web service error');
    }
    public function edit($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/portfolio/{$id}/edit", $request, ['logo']);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.portfolio.edit', $response->json());
        }

        return redirect()->back()->with('message', 'Web service error');
    }
    public function update($id, Request $request)
    {
        $response = $this->forwardRequestMedias("PUT", $this->url, "admin/portfolio/{$id}", $request, ['image']);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->route('admin.portfolio.index')->with('success', 'portfolio updated successfully');
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function destroy($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/portfolio/{$id}/delete", $request);
        Log::info("data", ['data' => $response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'portfolio deleted successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "admin/portfolio/data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promocode service error'], 500);
    }
    public function changeStatus($id, Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "admin/portfolio/{$id}/status", $request);
        Log::info("data1", ['data' => $response->json()]);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json(['message' => 'portfolio status updated successfully'], 200);
        }
        return redirect()->back()->with('message', 'Web service error');
    }
}
