<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SocialMediaController extends Controller
{

    public function data(Request $request, $id)
    {
        $query = SocialMedia::select('social_media.*')->with(['type'])->where('company_id', $id);
        Log::info('company data:', ['company' => $query->get()]);

        return DataTables::of($query)
            ->addColumn('url', fn($row) => '<a href="' . e($row->url) . '" target="_blank">' . e($row->url) . '</a>')
            ->addColumn('type', fn($row) => $row->type->name ?? 'Nomaʼlum')->addColumn('actions', function ($row) {
            return view('admin.actions', [
                'row'    => $row,
                'routes' => [
                    'delete' => "/admin/socialcompany/{$row->id}/delete",
                ],
            ])->render();
        })
            ->rawColumns(['url', 'actions'])
            ->make(true);

    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'type_id'    => 'required|exists:social_types,id',
            'url'        => 'required|url|max:255',
            'company_id' => 'required|exists:companies,id',
        ]);
        $existing = SocialMedia::where('company_id', $data['company_id'])
            ->where('type_id', $data['type_id'])
            ->first();
        if ($existing) {
            $existing->update(['url' => $data['url']]);
        } else {
            SocialMedia::create($data);
        }
        return response()->json(['message' => 'Ijtimoiy tarmoq saqlandi']);
    }
    public function delete(Request $request, $id)
    {
        $company = SocialMedia::findOrFail($id);
        $company->delete();
        return redirect()->back()->with('success', "Ijtimoiy tarmoq linklari o'chirildi.");
    }
}