<?php

namespace App\Action\Tournament;

use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class generateNextRoundAction
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request, Tournament $tournament): JsonResponse
    {
        // @ToDo add sheme documentation to also handle sheme
        if ($tournament->isFinished()) {
            return new JsonResponse('This tournament is finished, you cannot get next round', Response::HTTP_BAD_REQUEST);
        }

        if (empty($tournament->getGames())) {
            return new JsonResponse('This tournament as not started yet, there is no game linked to it', Response::HTTP_BAD_REQUEST);
        }

        switch ($tournament->getType()) {
            case Tournament::TOURNAMENT_TYPE_SINGLE_ELIMINATION:
                $nextRoundGames = $tournament->generateSingleEliminationNextRound();
                break;
            default:
                $nextRoundGames = [];
                break;
        }

        dd($nextRoundGames);
        /* if (!empty($nextRoundGames)) {
            foreach ($nextRoundGames as $game) {
                $this->entityManager->persist($game);
            }

            $this->entityManager->flush();
        } */

        return new JsonResponse($nextRoundGames, Response::HTTP_CREATED);
    }
}