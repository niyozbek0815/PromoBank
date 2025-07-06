<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function data(Request $request)
    {
        $locale = $request->get('locale', 'uz');
        Log::info('Requested locale:', ['locale' => $locale]);

        $query = Company::select('companies.*');
        Log::info('company data:', ['company' => $query->get()]);

        return DataTables::of($query)
            ->addColumn('name', fn($row) => $row->getTranslation('name', $locale) ?? '')
            ->addColumn('title', fn($row) => $row->getTranslation('title', $locale) ?? '')
            ->addColumn('description', fn($row) => $row->getTranslation('description', $locale) ?? '')
            ->addColumn('status', function ($user) {

                switch ((int) $user->status) {
                    case 1:
                        return '<span class="badge bg-success bg-opacity-10 text-success">Faol</span>';
                    case 0:
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary">Nofaol</span>';
                    default:
                        return '<span class="badge bg-info bg-opacity-10 text-info">Kutilmoqda</span>';
                }
            })
            ->addColumn('actions', function ($user) {
                return view('admin.actions', compact('user'))->render();
            })
            ->rawColumns(['actions', 'status']) // HTML ustunlar
            ->make(true);
    }
    public function delete(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
    }

    public function changeStatus(Request $request, $id)
    {
        $company         = Company::findOrFail($id);
        $company->status = ! $company->status;
        $company->save();
        Log::info('User status changed', [
            'user_id'    => $company->id,
            'new_status' => $company->status,
        ]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status'  => $company->status,
        ]);
    }
}