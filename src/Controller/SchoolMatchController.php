<?php

namespace App\Controller;

use App\Domain\Service\SchoolMatchingService;
use App\Dto\SchoolMatchRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class SchoolMatchController extends AbstractController
{
    public function __construct(
        private readonly SchoolMatchingService $matcher
    ) {}

    public function __invoke(
        #[MapRequestPayload] SchoolMatchRequest $request
    ): JsonResponse {
        $school = $this->matcher->match($request->schoolName);

        if (!$school) {
            return $this->json([
                'matched' => false,
                'message' => 'No matching school found.',
            ], 404);
        }

        return $this->json([
            'matched' => true,
            'school' => $school->toArray(),
        ]);
    }
}
