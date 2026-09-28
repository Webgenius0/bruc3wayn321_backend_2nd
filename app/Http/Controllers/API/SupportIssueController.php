<?php

namespace App\Http\Controllers\API;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\NewSupportIssueMail;
use App\Models\Setting;
use App\Models\SupportIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportIssueController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        $issue = SupportIssue::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        $adminEmail = Setting::value('site_email') ?: config('mail.from.address');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new NewSupportIssueMail($issue));
            } catch (\Throwable $exception) {
                Log::error('Support issue admin email could not be sent.', [
                    'support_issue_id' => $issue->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return $this->successResponse('Your support issue has been submitted.', [
            'issue_id' => $issue->id,
            'status' => $issue->status,
        ], 201);
    }
}
