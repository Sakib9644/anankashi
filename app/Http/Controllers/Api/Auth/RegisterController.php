<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\RegistrationNotificationEvent;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use App\Mail\UserVerificationMail;
use Illuminate\Support\Facades\Mail;
use App\Notifications\RegistrationNotification;
use Illuminate\Support\Facades\DB;
use App\Traits\SMS;
use Illuminate\Support\Facades\Log;
use Str;
use Throwable;

class RegisterController extends Controller
{

    use SMS;

    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'otp', 'avatar', 'otp_verified_at', 'last_activity_at'];
    }
    public function register(Request $request)
    {
        DB::beginTransaction();

        $validatedData = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ]);
        try {

            $verificationToken = Str::random(64);

            $user = User::create([
                'first_name'          => $validatedData['first_name'],
                'last_name'           => $validatedData['last_name'],
                'name'                => $validatedData['first_name'] . ' ' . $validatedData['last_name'], // optional
                'email'               => $validatedData['email'],
                'password'            => Hash::make($validatedData['password']),
                'otp_verified_at'     => null,
                'verification_token' => $verificationToken,
                'slug'                => Str::random(8),
            ]);

            $verificationUrl = route('verify.email', [
                'token' => $user->verification_token
            ]);

            Mail::to($user->email)
                ->send(new UserVerificationMail($user, $verificationUrl));

            DB::commit(); // ✅ important

            return response()->json([
                'status'  => true,
                'code'    => 200,
                'message' => 'Registration successful. Please check your email.',
            ], 200);
        } catch (Throwable $e) {

            DB::rollBack(); // ✅ now it works


            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again.',
                'code'    => 500,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // public function VerifyEmail(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email',
    //         'otp'   => 'required|digits:4',
    //     ]);
    //     try {
    //         $user = User::where('email', $request->input('email'))->first();

    //         //! Check if email has already been verified
    //         if (!empty($user->otp_verified_at)) {
    //             return  Helper::jsonErrorResponse('Email already verified.', 409);
    //         }

    //         if ((string)$user->otp !== (string)$request->input('otp')) {
    //             return Helper::jsonErrorResponse('Invalid OTP code', 422);
    //         }

    //         //* Check if OTP has expired
    //         if (Carbon::parse($user->otp_expires_at)->isPast()) {
    //             return Helper::jsonErrorResponse('OTP has expired. Please request a new OTP.', 422);
    //         }

    //         //* Verify the email
    //         $user->otp_verified_at   = now();
    //         $user->otp               = null;
    //         $user->otp_expires_at    = null;
    //         $user->save();

    //         return Helper::jsonResponse(true, 'Email verification successful.', 200);
    //     } catch (Exception $e) {
    //         return Helper::jsonErrorResponse($e->getMessage(), $e->getCode());
    //     }
    // }

    public function verifyEmail(Request $rquest)
    {

        $token = $rquest->input('token');
        try {
            $user = User::where('verification_token', $token)->first();

            if (!$user) {
                // Invalid token
                return redirect(Config('settings.frontend') . '?error=invalid_token&message=' . urlencode('Invalid verification token.'));
            }

            // Check if already verified
            if ($user->otp_verified_at) {
                return redirect(Config('settings.frontend') . '?error=already_verified&message=' . urlencode('Email is already verified. You can login now.'));
            }

            DB::beginTransaction();

            try {
                // Update user verification status
                $user->otp_verified_at = now();
                $user->otp = null; // clear token
                $user->save();



                DB::commit();

                // redirect with success message
                return redirect(Config('settings.frontend') . '?verified=true&message=' . urlencode('Email verified successfully.'));
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Email verification process failed: ' . $e->getMessage());

                return redirect(Config('settings.frontend') . '?error=verification_failed&message=' . urlencode('Verification failed. Please try again or contact support.'));
            }
        } catch (Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return redirect(Config('settings.frontend') . '?error=server_error&message=' . urlencode('Something went wrong. Please try again later.'));
        }
    }

    public function ResendOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->otp_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

             $verificationUrl = route('verify.email', [
                'token' => $user->verification_token
            ]);

            Mail::to($user->email)
                ->send(new UserVerificationMail($user, $verificationUrl));

            $newOtp               = rand(1000, 9999);
            $otpExpiresAt         = Carbon::now()->addMinutes(60);
            $user->otp            = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            //* Send the new OTP to the user's email

            return Helper::jsonResponse(true, 'A new verification link has been sent to your email.', 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 200);
        }
    }
}
