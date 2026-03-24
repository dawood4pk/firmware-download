<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SoftwareVersion;

/**
 * Builds the associative array payload that the API endpoint returns as JSON.
 *
 * All messages and logic are preserved verbatim from the original
 * ConnectedSiteController so that customer-facing output is identical.
 */
final class FirmwareResponseBuilder
{
    private const LATEST_VERSION_STANDARD = 'v3.3.7';
    private const LATEST_VERSION_LCI      = 'v3.4.4';

    /**
     * @return array{versionExist: bool, msg: string, link: string, st: string, gd: string}
     */
    public function buildFound(SoftwareVersion $entry, HardwareDetectionResult $hw): array
    {
        if ($entry->isLatest()) {
            return [
                'versionExist' => true,
                'msg'          => 'Your system is upto date!',
                'link'         => '',
                'st'           => '',
                'gd'           => '',
            ];
        }

        $latestLabel = $entry->isLci() ? self::LATEST_VERSION_LCI : self::LATEST_VERSION_STANDARD;

        return [
            'versionExist' => true,
            'msg'          => 'The latest version of software is ' . $latestLabel . ' ',
            'link'         => (string) $entry->getLink(),
            'st'           => $hw->isSt ? (string) $entry->getSt() : '',
            'gd'           => $hw->isGd ? (string) $entry->getGd() : '',
        ];
    }

    /**
     * @return array{versionExist: bool, msg: string, link: string, st: string, gd: string}
     */
    public function buildNotFound(): array
    {
        return [
            'versionExist' => false,
            'msg'          => 'There was a problem identifying your software. Contact us for help.',
            'link'         => '',
            'st'           => '',
            'gd'           => '',
        ];
    }

    /**
     * @return array{msg: string}
     */
    public function buildValidationError(string $message): array
    {
        return ['msg' => $message];
    }
}
