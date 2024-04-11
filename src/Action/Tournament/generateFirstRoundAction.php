<?php

namespace App\Action\Tournament;

use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class generateFirstRoundAction
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request, Tournament $tournament) : JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $players = $content['players'] ?? ['test'];

        dd($players);

        $tournament->generateSingleEliminationFirstRound($players);

        return new JsonResponse($tournament, Response::HTTP_CREATED);
    }
}