<?php

namespace App\Services\Api;

use App\Models\User;
use App\Services\Security\SuspiciousLoginService;
use App\Services\Security\TwoFactorService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

final class MobileAuthService
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
        private readonly SuspiciousLoginService $suspiciousLogin,
    ) {}

    /**
     * @param  array{email: string, password: string, device_name?: string, two_factor_code?: string|null}  $input
     * @return array{token: string, token_type: string, expires_at: string|null, user: User}
     *
     * @throws ValidationException
     */
    public function login(Request $request, array $input): array
    {
        $email = Str::lower(trim((string) $input['email']));
        $password = (string) $input['password'];
        $throttleKey = Str::transliterate($email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
                ])],
            ]);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        $authenticated = false;

        if ($user instanceof User) {
            if (Hash::check($password, (string) $user->password)) {
                $authenticated = true;
            } elseif (! app()->environment('production')) {
                $legacy = $this->attemptLegacyPlaintextUser($email, $password);
                if ($legacy instanceof User) {
                    $user = $legacy;
                    $authenticated = true;
                }
            }
        }

        if (! $authenticated || ! $user instanceof User) {
            RateLimiter::hit($throttleKey);
            $this->suspiciousLogin->recordFailure($email, $request, 'invalid_credentials');
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($user->isPlatformOwner()) {
            // Platform owner uses the dedicated mobile persona.
        } elseif ($user->isSuperAdmin() || $user->clinic_id === null) {
            throw ValidationException::withMessages([
                'email' => [__('api.errors.platform_account')],
            ]);
        }

        if (config('security.two_factor.enabled', false) && $this->twoFactor->mustEnforce($user) && ! $this->twoFactor->isEnabled($user)) {
            throw ValidationException::withMessages([
                'two_factor_code' => [__('api.errors.two_factor_setup_required')],
            ]);
        }

        if (config('security.two_factor.enabled', false) && $this->twoFactor->isEnabled($user)) {
            $code = trim((string) ($input['two_factor_code'] ?? ''));
            if ($code === '') {
                throw ValidationException::withMessages([
                    'two_factor_code' => [__('api.errors.two_factor_required')],
                ])->status(422)->errorBag('two_factor');
            }

            $valid = $this->twoFactor->verifyCode($user, $code)
                || $this->twoFactor->consumeRecoveryCode($user, $code);

            if (! $valid) {
                throw ValidationException::withMessages([
                    'two_factor_code' => [__('api.errors.two_factor_invalid')],
                ]);
            }
        }

        $this->suspiciousLogin->recordSuccess($user, $request);

        AuditLogger::security(
            'login',
            'auth',
            $user->id,
            'تسجيل دخول API (موبايل): '.$user->email,
            null,
            ['email' => $user->email, 'device' => $input['device_name'] ?? 'mobile']
        );

        $deviceName = trim((string) ($input['device_name'] ?? ''));
        if ($deviceName === '') {
            $deviceName = 'mobile';
        }

        $accessToken = $this->createToken($user, $deviceName);

        return [
            'token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $accessToken->accessToken->expires_at?->toIso8601String(),
            'user' => $user->fresh(['clinic.plan']),
        ];
    }

    public function logout(User $user, ?string $bearerToken = null): void
    {
        if (is_string($bearerToken) && $bearerToken !== '') {
            PersonalAccessToken::findToken($bearerToken)?->delete();

            return;
        }

        $user->currentAccessToken()?->delete();
    }

    private function createToken(User $user, string $deviceName): NewAccessToken
    {
        $expirationDays = max(1, (int) config('sanctum.mobile_token_expiration_days', 30));

        return $user->createToken(
            $deviceName,
            ['*'],
            now()->addDays($expirationDays),
        );
    }

  /**
     * @return User|null
     */
    private function attemptLegacyPlaintextUser(string $email, string $password): ?User
    {
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            return null;
        }

        $stored = (string) $user->getAuthPassword();
        if (! hash_equals($stored, $password)) {
            return null;
        }

        $user->forceFill(['password' => Hash::make($password)])->save();

        return $user;
    }
}
