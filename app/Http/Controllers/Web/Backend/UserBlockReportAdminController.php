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
            "reviewer",
        ])->paginate($request->items ?? 10);

        return Inertia::render('backend/reports/index', [
            'reports' => $reports
        ]);
    }
    public function reportDetails($id)
    {
        $report = UserReport::where('id', $id)->with([
            "reporter",
            "reportedUser",
            "reviewer",
        ])->first();

        return Inertia::render('backend/reports/show', [
            'report' => $report
        ]);
    }
    public function reportEdit($id)
    {
        $report = UserReport::where('id', $id)->with([
            "reporter",
            "reportedUser",
            "reviewer",
        ])->first();

        return Inertia::render('backend/reports/edit', [
            'report' => $report
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,declined,canceled',
            'admin_note' => 'nullable|string',
        ]);

        $report = UserReport::findOrFail($id);
        $report->status = $request->status;
        $report->admin_note = $request->admin_note;
        $report->reviewed_at = now();
        $report->reviewer_id = auth()->id();
        $report->save();

        return redirect()->back()->with('success', 'Report updated successfully');
    }

    public function toggleActiveStatus($id)
    {
        $report = UserReport::with('reportedUser')->findOrFail($id);
        $user = $report->reportedUser;
        if ($user) {
            $user->status = !$user->status;
            $user->save();
        }

        return redirect()->back();
    }
}
