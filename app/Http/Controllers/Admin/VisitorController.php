<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitorEmail;
use Yajra\DataTables\Facades\DataTables;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = VisitorEmail::orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('ip_address', function ($row) {
                    return $row->ip_address ?? '-';
                })

                ->editColumn('visit_count', function ($row) {
                    return $row->visit_count ?? 0;
                })

                ->editColumn('first_visited_at', function ($row) {
                    return $row->first_visited_at ?? '-';
                })

                ->editColumn('last_visited_at', function ($row) {
                    return $row->last_visited_at ?? '-';
                })
                
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ?? '-';
                })

                ->rawColumns([])
                ->make(true);
        }

        return view('admin.visitors.index');
    }
}
