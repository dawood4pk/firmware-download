<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FirmwareResponseBuilder;
use App\Service\HardwareVersionDetector;
use App\Service\SoftwareVersionMatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SoftwareDownloadController extends AbstractController
{
    public function __construct(
        private readonly HardwareVersionDetector $hwDetector,
        private readonly SoftwareVersionMatcher  $matcher,
        private readonly FirmwareResponseBuilder  $responseBuilder,
    ) {}

    #[Route('/carplay/software-download', name: 'software_download_page', methods: ['GET'])]
    public function page(): Response
    {
        return $this->render('software_download/index.html.twig');
    }

    /**
     * API endpoint that replicates the original
     * POST /api2/carplay/software/version behaviour exactly.
     */
    #[Route('/api/carplay/software/version', name: 'software_download_api', methods: ['POST'])]
    public function check(Request $request): JsonResponse
    {
        $version    = trim((string) $request->request->get('version', ''));
        $hwVersion  = trim((string) $request->request->get('hwVersion', ''));

        if ($version === '') {
            return new JsonResponse($this->responseBuilder->buildValidationError('Version is required'));
        }

        if ($hwVersion === '') {
            return new JsonResponse($this->responseBuilder->buildValidationError('HW Version is required'));
        }

        $hw = $this->hwDetector->detect($hwVersion);

        if (!$hw->isKnown()) {
            return new JsonResponse(
                $this->responseBuilder->buildValidationError(
                    'There was a problem identifying your software. Contact us for help.'
                )
            );
        }

        $entry = $this->matcher->match($version, $hw);

        if ($entry === null) {
            return new JsonResponse($this->responseBuilder->buildNotFound());
        }

        return new JsonResponse($this->responseBuilder->buildFound($entry, $hw));
    }
}
