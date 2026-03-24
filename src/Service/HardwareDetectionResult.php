<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Immutable value object that carries the result of hardware-version detection.
 *
 * Mirrors the variables inferred by the original ConnectedSiteController:
 *   $stBool, $gdBool, $isLCI, $lciHwType
 */
final class HardwareDetectionResult
{
    public function __construct(
        /** Whether the hardware belongs to the ST (CIC) product line. */
        public readonly bool $isSt,
        /** Whether the hardware belongs to the GD (NBT/EVO) product line. */
        public readonly bool $isGd,
        /** True for LCI-generation hardware (B_C_, B_N_G_, B_E_G_ prefixes). */
        public readonly bool $isLci,
        /**
         * For LCI hardware: "CIC", "NBT", or "EVO".
         * Empty string for standard hardware.
         */
        public readonly string $lciHwType,
    ) {}

    public static function unknown(): self
    {
        return new self(false, false, false, '');
    }

    public function isKnown(): bool
    {
        return $this->isSt || $this->isGd;
    }
}
