<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\Security\SuspiciousLoginService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'email' => Str::lower(trim((string) $this->input('email'))),
            'password' => (string) $this->input('password'),
        ];

        $attempted = Auth::attempt($credentials, $this->boolean('remember'));
        if (! $attempted && ! app()->environment('production')) {
            $attempted = $this->attemptLegacyPlaintextPassword($credentials);
        }

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey());
            app(SuspiciousLoginService::class)->recordFailure(
                $credentials['email'],
                $this,
                'invalid_credentials',
            );

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Support legacy rows where passwords were stored in plaintext.
     * On successful legacy match, transparently upgrade to hashed password.
     *
     * @param  array{email:string,password:string}  $credentials
     */
    private function attemptLegacyPlaintextPassword(array $credentials): bool
    {
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$credentials['email']])
            ->first();

        if (! $user) {
            return false;
        }

        $storedPassword = (string) $user->getAuthPassword();
        $incomingPassword = $credentials['password'];

        if (! hash_equals($storedPassword, $incomingPassword)) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($incomingPassword),
        ])->save();

        Auth::login($user, $this->boolean('remember'));

        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
