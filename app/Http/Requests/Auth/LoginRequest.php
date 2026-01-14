<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        // 🔹 Kiểm tra user có tồn tại không
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ]);
        }

        // 🔹 Chặn login nếu chưa xác thực email
        if (is_null($user->email_verified_at)) {
            throw ValidationException::withMessages([
                'email' => 'Email chưa được xác thực. Vui lòng kiểm tra hộp thư.',
            ]);
        }

        // 🔹 Thử đăng nhập
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Rate limit
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Bạn đăng nhập quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.",
        ]);
    }

    /**
     * Throttle key
     */
    public function throttleKey(): string
    {
        return Str::lower($this->string('email')) . '|' . $this->ip();
    }
}