<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Parses a raw HW Version string and returns a {@see HardwareDetectionResult}.
 *
 * All regex patterns are taken verbatim from the original ConnectedSiteController
 * to guarantee identical matching behaviour.
 *
 * Standard hardware:
 *   CPAA_YYYY.MM.DD(_SUFFIX)?   → ST (CIC)
 *   CPAA_G_YYYY.MM.DD(_SUFFIX)? → GD (NBT / EVO)
 *
 * LCI hardware:
 *   B_C_YYYY.MM.DD   → LCI CIC  (ST)
 *   B_N_G_YYYY.MM.DD → LCI NBT  (GD)
 *   B_E_G_YYYY.MM.DD → LCI EVO  (GD)
 */
final class HardwareVersionDetector
{
    private const PATTERN_ST  = '/^CPAA_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';
    private const PATTERN_GD  = '/^CPAA_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';

    private const PATTERN_LCI_CIC = '/^B_C_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
    private const PATTERN_LCI_NBT = '/^B_N_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
    private const PATTERN_LCI_EVO = '/^B_E_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';

    public function detect(string $hwVersion): HardwareDetectionResult
    {
        if (preg_match(self::PATTERN_LCI_CIC, $hwVersion)) {
            return new HardwareDetectionResult(isSt: true, isGd: false, isLci: true, lciHwType: 'CIC');
        }

        if (preg_match(self::PATTERN_LCI_NBT, $hwVersion)) {
            return new HardwareDetectionResult(isSt: false, isGd: true, isLci: true, lciHwType: 'NBT');
        }

        if (preg_match(self::PATTERN_LCI_EVO, $hwVersion)) {
            return new HardwareDetectionResult(isSt: false, isGd: true, isLci: true, lciHwType: 'EVO');
        }

        $isSt = (bool) preg_match(self::PATTERN_ST, $hwVersion);
        $isGd = (bool) preg_match(self::PATTERN_GD, $hwVersion);

        if ($isSt || $isGd) {
            return new HardwareDetectionResult(isSt: $isSt, isGd: $isGd, isLci: false, lciHwType: '');
        }

        return HardwareDetectionResult::unknown();
    }
}
