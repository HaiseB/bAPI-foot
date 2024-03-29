<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ApiResource]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $roundNumber = null;

    #[ORM\Column]
    private ?bool $isOver = null;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'games')]
    private Collection $teams;

    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $goals;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'games')]
    private Collection $users;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->goals = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoundNumber(): ?int
    {
        return $this->roundNumber;
    }

    public function setRoundNumber(?int $roundNumber): static
    {
        $this->roundNumber = $roundNumber;

        return $this;
    }

    public function isIsOver(): ?bool
    {
        if ($this->getUsersAndScoresRecap()->teamAScore > 9 || $this->getUsersAndScoresRecap()->teamBScore > 9) {
            $this->setIsOver(true);
        }

        return $this->isOver;
    }

    public function setIsOver(bool $isOver): static
    {
        $this->isOver = $isOver;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        $this->teams->removeElement($team);

        return $this;
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): static
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->setGame($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): static
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getGame() === $this) {
                $goal->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getUsersAndScoresRecap(): \stdClass
    {
        $usersAndScores = new \stdClass();
        $usersAndScores->teamAPlayers = $usersAndScores->teamBPlayers = [];
        $usersAndScores->teamAScore = $usersAndScores->teamBScore = 0;

        // Players part
        if (!empty($this->getTeams())) {
            foreach ($this->getTeams() as $team) {
                foreach ($team->getUsers() as $user) {
                    if (count($usersAndScores->teamAPlayers) < 2) {
                        $usersAndScores->teamAPlayers[$user->getId()] = ["id" => $user->getId(), "name" => $user->getName(), "score" => 0];
                    } else {
                        $usersAndScores->teamBPlayers[$user->getId()] = ["id" => $user->getId(), "name" => $user->getName(), "score" => 0];
                    }
                }
            }

            if (count($this->getTeams()) < 2 && empty($usersAndScores->teamBPlayers)) {
                $usersAndScores->teamBPlayers[ $this->getUsers()[0]->getId()] = ["id" => $user->getId(), "name" => $this->getUsers()[0]->getName(), "score" => 0];
            }
        } elseif (!empty($this->getUsers())) {
            foreach ($this->getUsers() as $user) {
                if (empty($usersAndScores->teamAPlayers)) {
                    $usersAndScores->teamAPlayers[$user->getId()] = ["id" => $user->getId(), "name" => $user->getName(), "score" => 0];
                } else {
                    $usersAndScores->teamBPlayers[$user->getId()] = ["id" => $user->getId(), "name" => $user->getName(), "score" => 0];
                }
            }
        }

        // Goals part
        foreach ($this->getGoals() as $goal) {
            foreach ($usersAndScores->teamAPlayers as $teamAPlayersUser) {
                if ($goal->getUser()->getId() === $teamAPlayersUser["id"]) {
                    $usersAndScores->teamAPlayers[$goal->getUser()->getId()]["score"] = $goal->getNumber();
                    $usersAndScores->teamAScore += $goal->getNumber();
                }
            }
            foreach ($usersAndScores->teamBPlayers as $teamBPlayersUser) {
                if ($goal->getUser()->getId() === $teamBPlayersUser["id"]) {
                    $usersAndScores->teamBPlayers[$goal->getUser()->getId()]["score"] = $goal->getNumber();
                    $usersAndScores->teamBScore += $goal->getNumber();
                }
            }
        }

        return $usersAndScores;
    }
}
