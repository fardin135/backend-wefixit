<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Jobs\SendPasswordResetOtp;
use App\Jobs\SendRegistrationOtp;
use App\Models\PasswordResetOtp;
use App\Models\RegistrationOtp;
use App\Models\Role;
use App\Models\User;
use App\Traits\APIResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    use APIResponses;

    public function respondWithToken($token)
    {
        $user = $this->guard()->user();

        $data = [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];

        return $this->success('Login successful', $data, 200);
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|min:2|max:255',
            'lastName' => 'required|string|min:2|max:255',
            'phone' => 'required|string|min:10|max:20',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = DB::transaction(function () use ($request) {

                $user = User::create([
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $customerRole = Role::where('name', 'user')->firstOrFail();

                $user->assignRole($customerRole);

                // Generate OTP
                $otp = (string) random_int(100000, 999999);

                RegistrationOtp::create([
                    'email' => $request->email,
                    'otp_hash' => Hash::make($otp),
                    'expires_at' => now()->addMinutes(5),
                ]);

                SendRegistrationOtp::dispatch($request->email, $otp)->onQueue('high');

                return $user;
            });

            return $this->success('Registration successful. Please verify your OTP.', null, 201);

        } catch (Throwable $e) {
            report($e);

            return $this->serverError('Registration failed', $e->getMessage());
        }
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        try {
            $email = strtolower(trim($request->email));

            $result = DB::transaction(function () use ($email, $request) {
                $otpRecord = RegistrationOtp::where('email', $email)
                    ->whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->latest('created_at')
                    ->lockForUpdate()
                    ->first();

                if (!$otpRecord) {
                    return ['status' => 'error', 'message' => 'Invalid or expired OTP.', 'status_code' => 400];
                }

                if ($otpRecord->attempts >= 5) {
                    return ['status' => 'error', 'message' => 'Too many attempts. Request a new OTP.', 'status_code' => 429];
                }

                if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
                    $otpRecord->increment('attempts');
                    return ['status' => 'error', 'message' => 'Invalid OTP.', 'status_code' => 400];
                }

                $otpRecord->update(['verified_at' => now()]);

                $user = User::where('email', $email)->first();
                $user->update(['email_verified_at' => now()]);

                return ['status' => 'success', 'user' => $user];
            });

            if ($result['status'] === 'error') {
                return $this->error($result['message'], null, $result['status_code']);
            }

            return $this->success('Email verified successfully. Please login.', null, 200);

        } catch (Throwable $e) {
            return $this->serverError('Verification failed', $e->getMessage());
        }
    }

    public function resendRegistrationOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $email = strtolower(trim($request->email));
            $user = User::where('email', $email)->first();

            if (!$user || $user->email_verified_at) {
                return $this->error('User not found or already verified', null, 400);
            }

            $otp = (string) random_int(100000, 999999);
            RegistrationOtp::create([
                'email' => $email,
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
            ]);

            SendRegistrationOtp::dispatch($email, $otp)->onQueue('high');

            return $this->success('OTP resent successfully.');
        } catch (Throwable $e) {
            return $this->serverError('Failed to resend OTP', $e->getMessage());
        }
    }

    // rate limiting to be implemented
    public function login(AuthRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        $user = $this->guard()->getProvider()->retrieveByCredentials($credentials);

        if ($user && !$user->email_verified_at) {
            return $this->error('Please verify your email address first.', null, 403);
        }

        $token = $this->guard()->attempt($credentials);

        if ($token) {
            return $this->respondWithToken($token);
        }

        return $this->unauthorized('Invalid credentials');
    }

    public function me()
    {
        $user = $this->guard()->user()->load('roles');

        $user->setRelation('roles', $user->roles->pluck('name'));

        return $this->success('User retrieved successfully', $user);
    }

    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function logout()
    {
        $this->guard()->logout();

        return $this->success('Logged out successfully');
    }

    //database attempts is calculated but redis is yet to be implemented
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $email = strtolower(trim($request->email));

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'If an account exists for this email, a password reset code has been sent.',
                ], 200);
            }

            PasswordResetOtp::where('user_id', $user->id)
                ->whereNull('used_at')
                ->update([
                    'used_at' => now(),
                ]);

            // Generate a 6-digit OTP.
            $otp = (string) random_int(100000, 999999);

            // Store the hashed OTP.
            PasswordResetOtp::create([
                'user_id' => $user->id,
                'email' => $email,
                'otp_hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(5),
            ]);

            // Queue the email
            SendPasswordResetOtp::dispatch($email, $otp)
                ->onQueue('high');

            return response()->json([
                'message' => 'If an account exists for this email, a password reset code has been sent.',
            ], 200);

        } catch (Throwable $e) {
            return $this->error(
                'Something went wrong',
                500,
                $e->getMessage()
            );
        }
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        try {
            $email = strtolower(trim($request->email));

            $result = DB::transaction(function () use ($email, $request) {

                $otpRecord = PasswordResetOtp::where('email', $email)
                    ->whereNull('used_at')
                    ->whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->latest('created_at')
                    ->lockForUpdate()
                    ->first();

                if (!$otpRecord) {
                    return [
                        'status' => 'error',
                        'message' => 'Invalid or expired password reset code.',
                        'status_code' => 400,
                    ];
                }

                if ($otpRecord->attempts >= 5) {
                    return [
                        'status' => 'error',
                        'message' => 'Too many invalid attempts. Please request a new code.',
                        'status_code' => 429,
                    ];
                }

                if (!Hash::check($request->otp, $otpRecord->otp_hash)) {

                    $otpRecord->increment('attempts');

                    return [
                        'status' => 'error',
                        'message' => 'Invalid or expired password reset code.',
                        'status_code' => 400,
                    ];
                }

                $resetToken = bin2hex(random_bytes(32));

                $otpRecord->update([
                    'verified_at' => now(),
                    'reset_token_hash' => hash('sha256', $resetToken),
                    'reset_token_expires_at' => now()->addMinutes(10),
                ]);

                return [
                    'status' => 'success',
                    'message' => 'OTP verified successfully.',
                    'status_code' => 200,
                    'reset_token' => $resetToken,
                ];
            });

            if ($result['status'] === 'error') {
                return $this->error(
                    $result['message'],
                    null,
                    $result['status_code']
                );
            }

            return $this->success(
                $result['message'],
                [
                    'reset_token' => $result['reset_token'],
                    'expires_in' => 600,
                ],
                $result['status_code']
            );

        } catch (Throwable $e) {

            Log::error('Password reset OTP verification failed', [
                'exception' => $e,
            ]);

            return $this->serverError(
                'Something went wrong. Please try again later.'
            );
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'reset_token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        try {
            $tokenHash = hash('sha256', $request->reset_token);

            $result = DB::transaction(function () use ($tokenHash, $request) {

                $otpRecord = PasswordResetOtp::where(
                    'reset_token_hash',
                    $tokenHash
                )
                    ->whereNotNull('verified_at')
                    ->whereNull('used_at')
                    ->where('reset_token_expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();

                if (!$otpRecord) {
                    return [
                        'status' => 'error',
                        'message' => 'Invalid or expired password reset token.',
                        'status_code' => 400,
                    ];
                }

                $user = User::find($otpRecord->user_id);

                if (!$user) {
                    return [
                        'status' => 'error',
                        'message' => 'Invalid password reset request.',
                        'status_code' => 400,
                    ];
                }

                $user->update([
                    'password' => Hash::make($request->password),
                ]);

                $otpRecord->update([
                    'used_at' => now(),
                    'reset_token_hash' => null,
                    'reset_token_expires_at' => null,
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Password successfully reset.',
                    'status_code' => 200,
                ];
            });

            if ($result['status'] === 'error') {
                return $this->error(
                    $result['message'],
                    null,
                    $result['status_code']
                );
            }

            return $this->success(
                $result['message'],
                null,
                $result['status_code']
            );

        } catch (Throwable $e) {

            Log::error('Password reset failed', [
                'exception' => $e,
            ]);

            return $this->serverError(
                'Something went wrong. Please try again later.'
            );
        }
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
            ],
        ]);

        try {
            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to change password',
            ], 500);
        }
    }
}