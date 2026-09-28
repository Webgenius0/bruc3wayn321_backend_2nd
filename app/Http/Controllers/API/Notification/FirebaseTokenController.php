<?php

namespace App\Http\Controllers\API\Notification;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\FirebaseToken;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FirebaseTokenController extends Controller
{
    use ApiResponse;

    public function updateFirebaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'device_id' => 'required',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422, $validator->errors());
        }

        $user = auth()->user();

        $token = $request->token;
        $device_id = $request->device_id;


        $firebaseToken = FirebaseToken::where('device_id', $device_id)->where('user_id', $user->id)->first();

        if ($firebaseToken) {
            $firebaseToken->update([
                'user_id' => $user->id,
                'token' => $token
            ]);
            return $this->successResponse('Token updated successfully', $firebaseToken, 200);
        } else {
            $firebaseToken = FirebaseToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'device_id' => $device_id
            ]);
            return $this->successResponse('Token created successfully', $firebaseToken, 200);
        }
    }

    public function deleteFirebaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422, $validator->errors());
        }

        $device_id = $request->device_id;

        $firebaseToken = FirebaseToken::where('device_id', $device_id)->first();

        if ($firebaseToken) {
            $firebaseToken->delete();
            return $this->successResponse('Token deleted successfully', [], 200);
        } else {
            return $this->errorResponse('Token not found', 404);
        }
    }

    public function test()
    {
        $tokens = FirebaseToken::where('user_id', auth()->id())
            ->pluck('token');

        $fcmService = app(FCMService::class);

        foreach ($tokens as $token) {
            try {
                $fcmService->sendMessage(
                    $token,
                    'Test Push',
                    'Success'
                );
            } catch (\Throwable $e) {
                // Log and continue with the next token
                \Log::warning("Failed to send notification to {$token}: {$e->getMessage()}");
            }
        }


        return "Success";
    }
}
