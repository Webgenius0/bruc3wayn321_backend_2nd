<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserBlockReportAdminController extends Controller
{
    public function Reportindex(Request $request)
    {
        $reports = UserReport::latest()->with([
            "reporter",
            "reportedUser",
        ])->paginate($request->items ?? 1);

        return Inertia::render('backend/reports/index', [
            'reports' => $reports
        ]);
    }
}
