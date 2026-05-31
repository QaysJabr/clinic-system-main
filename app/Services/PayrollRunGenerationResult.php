<?php

namespace App\Services;

/**
 * Summary counts after attempting to create staff_payments from a payroll run.
 */
final readonly class PayrollRunGenerationResult
{
    public function __construct(
        public int $created,
        public int $skippedExisting,
        public int $skippedInactiveStaff,
        public int $skippedBeforeProfileStart,
        public int $skippedIncompleteProfile,
    ) {}
}
