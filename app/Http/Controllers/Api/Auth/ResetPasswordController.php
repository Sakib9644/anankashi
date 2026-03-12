<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Mail\OtpMail;
use App\Mail\VerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ResetPasswordController extends Controller
{
    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'avatar'];
    }

    public function verifyEmailLink(Request $request)
    {
        $user = User::findOrFail($request->user);

        $token = Str::random(60);

        $user->reset_password_token = $token;
        $user->reset_password_token_expire_at = Carbon::now()->addHour();
        $user->save();

        // Redirect to external page
        return redirect('https://cryptax-dev.vercel.app/reset-password/verified?email=' .  $user->email . '&token=' . $token);
    }
    public function forgotPassword(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            };

            $verificationUrl = URL::temporarySignedRoute(
                'verify.email.link',
                Carbon::now()->addMinutes(60),
                ['user' => $user->id]
            );

            Mail::to($user->email)->send(new VerificationMail($user, $verificationUrl));

            return Helper::jsonResponse(true, 'Verification link sent to your email.', 200);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function MakeOtpToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $email = $request->input('email');
            $otp   = $request->input('otp');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired.', 400);
            }

            if ($user->otp !== $otp) {
                return Helper::jsonErrorResponse('Invalid OTP', 400);
            }
            $token = Str::random(60);

            $user->otp = null;
            $user->otp_expires_at = null;
            $user->reset_password_token = $token;
            $user->reset_password_token_expire_at = Carbon::now()->addHour();

            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => 'OTP verified successfully.',
                'code'       => 200,
                'token'      => $token,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        try {
            $email       = $request->input('email');
            $newPassword = $request->input('password');

            $user = User::where('email', $email)->first();
            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (!empty($user->reset_password_token) && $user->reset_password_token === $request->token && $user->reset_password_token_expire_at >= Carbon::now()) {

                $user->password = Hash::make($newPassword);
                $user->reset_password_token = null;
                $user->reset_password_token_expire_at = null;

                $user->save();

                return Helper::jsonResponse(true, 'Password reset successfully.', 200);
            } else {
                return Helper::jsonErrorResponse('Invalid Token', 419);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }



    public function password_update(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => 'required|string|min:6|confirmed',
            ]);
            $user = auth('api')->user();
            $user->password = Hash::make($request->password);
            $user->save();

            return Helper::jsonResponse(true, 'Password Updated successfully.', 200);
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422);
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
