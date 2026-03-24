<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SoftwareVersion;
use App\Repository\SoftwareVersionRepository;

/**
 * Finds the {@see SoftwareVersion} database entry that corresponds to a given
 * customer-supplied software version string and detected hardware type.
 *
 * Matching rules (preserved from the original ConnectedSiteController):
 *   1. Strip a leading "v" or "V" from the version string.
 *   2. Compare against system_version_alt case-insensitively.
 *   3. LCI hardware must only match entries whose name begins with "LCI".
 *   4. Standard hardware must not match LCI entries.
 *   5. For LCI hardware the type token (CIC / NBT / EVO) must appear in the entry name.
 */
final class SoftwareVersionMatcher
{
    public function __construct(
        private readonly SoftwareVersionRepository $repository,
    ) {}

    public function match(string $rawVersion, HardwareDetectionResult $hw): ?SoftwareVersion
    {
        $version = $this->normaliseVersion($rawVersion);

        $candidates = $this->repository->findByVersionAlt($version);

        foreach ($candidates as $entry) {
            $entryIsLci = $entry->isLci();

            if ($hw->isLci !== $entryIsLci) {
                continue;
            }

            if ($hw->isLci && !$this->lciTypeMatches($hw->lciHwType, $entry->getName())) {
                continue;
            }

            return $entry;
        }

        return null;
    }

    private function normaliseVersion(string $version): string
    {
        if (str_starts_with(strtolower($version), 'v')) {
            return substr($version, 1);
        }

        return $version;
    }

    private function lciTypeMatches(string $lciHwType, string $entryName): bool
    {
        return stripos($entryName, $lciHwType) !== false;
    }
}
