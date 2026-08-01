<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Mail\SupportIssueReplyMail;
use App\Models\SupportIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SupportIssueController extends Controller
{
    public function index(Request $request)
    {
        $issues = SupportIssue::query()
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('backend/support-issues/index', [
            'issues' => $issues,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(SupportIssue $supportIssue)
    {
        return Inertia::render('backend/support-issues/show', [
            'issue' => $supportIssue->load('repliedBy:id,first_name,last_name,email'),
        ]);
    }

    public function reply(Request $request, SupportIssue $supportIssue)
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $supportIssue->update([
            'reply' => $validated['reply'],
            'status' => $validated['status'],
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
        ]);

        try {
            Mail::to($supportIssue->email)->send(new SupportIssueReplyMail($supportIssue));
        } catch (\Throwable $exception) {
            Log::error('Support issue reply email could not be sent.', [
                'support_issue_id' => $supportIssue->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Reply saved and sent by email.');
    }
}
