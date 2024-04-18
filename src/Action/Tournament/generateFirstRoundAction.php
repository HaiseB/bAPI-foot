<?php

namespace App\Action\Tournament;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\User;
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
    public function __invoke(Request $request, Tournament $tournament): JsonResponse
    {
        // @ToDo add sheme documentation to also handle sheme
        $content = json_decode($request->getContent(), true);
        if (!isset($content['players'])) {
            return new JsonResponse('request does not contain players array', Response::HTTP_BAD_REQUEST);
        } else {
            $playersPosted = $content['players'];
        }
        $players = [];

        try {
            foreach ($playersPosted as $playerPosted) {
                if (preg_match('/^api\/teams\/(\d+)$/', $playerPosted, $matches)) {
                    $teamId = $matches[1];

                    $team = $this->entityManager->getRepository(Team::class)->find($teamId);

                    if ($team) {
                        $players[] = $team;
                    }
                }

                if (preg_match('/^api\/users\/(\d+)$/', $playerPosted, $matches)) {
                    $userId = $matches[1];

                    $user = $this->entityManager->getRepository(User::class)->find($userId);

                    if ($user) {
                        $players[] = $user;
                    }
                }
            }

            switch ($tournament->getType()) {
                case Tournament::TOURNAMENT_TYPE_SINGLE_ELIMINATION:
                    $firstRoundGames = $tournament->generateSingleEliminationFirstRound($players);
                    break;
                default:
                    $firstRoundGames = [];
                    break;
            }

            if (!empty($firstRoundGames)) {
                foreach ($firstRoundGames as $game) {
                    $this->entityManager->persist($game);
                }

                $this->entityManager->flush();
            }

        } catch (\Exception $e) {
            return new JsonResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($firstRoundGames, Response::HTTP_CREATED);
    }
}
