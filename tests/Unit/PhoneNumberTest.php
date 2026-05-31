<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_normalizes_local_number_with_leading_zero(): void
    {
        $this->assertSame('972597360027', PhoneNumber::normalizeForSms('0597360027', '972'));
    }

    public function test_to_e164_adds_plus(): void
    {
        $this->assertSame('+972597360027', PhoneNumber::toE164('972597360027'));
    }
}
