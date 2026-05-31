<?php

namespace App\Services\Security;

use App\Models\ClinicSetting;
use App\Models\TrustedDevice;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorService
{
    public const SESSION_USER_ID = 'two_factor.login.id';

    public const SESSION_VERIFIED = 'two_factor.verified';

    public const TRUST_COOKIE = 'clinic_trusted_device';

    public function __construct(
        private readonly Google2FA $google2fa = new Google2FA,
    ) {}

    public function isEnabled(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null
            && $user->two_factor_secret !== null;
    }

    public function mustEnforce(User $user): bool
    {
        if (! config('security.two_factor.enabled', false)) {
            return false;
        }

        if ($this->isEnabled($user)) {
            return false;
        }

        if ($user->isSuperAdmin() && config('security.two_factor.enforce_platform_owner')) {
            return true;
        }

        if ($user->clinic_id === null) {
            return false;
        }

        $settings = ClinicSetting::query()
            ->where('clinic_id', $user->clinic_id)
            ->first();

        if (! ($settings?->require_two_factor ?? false)) {
            return false;
        }

        $roles = config('security.two_factor.enforce_roles', []);

        return $user->hasAnyRole($roles);
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrSvg(User $user, string $secret): string
    {
        $otpUrl = $this->google2fa->getQRCodeUrl(
            config('security.two_factor.issuer'),
            $user->email,
            $secret,
        );

        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($otpUrl);
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            preg_replace('/\s+/', '', $code) ?? '',
        );
    }

    /**
     * @return array<int, string> Plain recovery codes (show once to user)
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        $count = max(4, (int) config('security.two_factor.recovery_codes', 8));

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    /**
     * @param  array<int, string>  $plainCodes
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_map(fn (string $code) => Hash::make(strtoupper($code)), $plainCodes);
    }

    /**
     * @param  array<int, string>  $hashedCodes
     */
    public function storeRecoveryCodes(User $user, array $hashedCodes): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($hashedCodes, JSON_THROW_ON_ERROR),
        ])->save();
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', $code) ?? '');
        $stored = json_decode((string) $user->two_factor_recovery_codes, true);

        if (! is_array($stored)) {
            return false;
        }

        foreach ($stored as $index => $hash) {
            if (! is_string($hash)) {
                continue;
            }
            if (Hash::check($normalized, $hash)) {
                unset($stored[$index]);
                $this->storeRecoveryCodes($user, array_values($stored));

                return true;
            }
        }

        return false;
    }

    public function confirmSetup(User $user, string $secret, array $plainRecoveryCodes): void
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->storeRecoveryCodes($user, $this->hashRecoveryCodes($plainRecoveryCodes));
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        TrustedDevice::query()->where('user_id', $user->id)->delete();
    }

    public function markVerified(Request $request): void
    {
        $request->session()->put(self::SESSION_VERIFIED, true);
        $request->session()->forget(self::SESSION_USER_ID);
    }

    public function isVerified(Request $request): bool
    {
        return (bool) $request->session()->get(self::SESSION_VERIFIED, false);
    }

    public function hasTrustedDevice(Request $request, User $user): bool
    {
        $token = (string) $request->cookie(self::TRUST_COOKIE);
        if ($token === '') {
            return false;
        }

        $hash = hash('sha256', $token);

        return TrustedDevice::query()
            ->where('user_id', $user->id)
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function trustDevice(Request $request, User $user): void
    {
        $plain = Str::random(64);
        $days = (int) config('security.two_factor.trusted_device_days', 30);

        TrustedDevice::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'device_name' => $this->deviceNameFromAgent($request->userAgent()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addDays($days),
            'last_used_at' => now(),
        ]);

        Cookie::queue(cookie(
            self::TRUST_COOKIE,
            $plain,
            $days * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    public function deviceNameFromAgent(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown device';
        }

        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile browser';
        }
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'Mac')) {
            return 'Mac';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Browser';
    }
}
