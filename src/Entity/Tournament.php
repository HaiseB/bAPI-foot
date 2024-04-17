<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Action\Tournament\generateFirstRoundAction;
use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
#[ApiResource]
#[ApiResource(operations: [
    new Post(
        name: 'tournament_generate_first_round',
        uriTemplate: 'api/tournaments/{id}/generate-first-round',
        controller:  generateFirstRoundAction::class,
    )
])]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?bool $finished = false;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'tournament')]
    private Collection $games;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $numberOfPlayers = null;

    const TOURNAMENT_TYPE_SINGLE_ELIMINATION = 'Single Elimination';

    /* TOURNAMENT_TYPE = [
        //'Double Elimination',
        //'Multilevel',
        //'Straight Round Robin',
        //'Round Robin Double Split',
        //'Semi-Round Robins',
    ]; */

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): static
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setTournament($this);
        }

        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getTournament() === $this) {
                $game->setTournament(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNumberOfPlayers(): ?int
    {
        return $this->numberOfPlayers;
    }

    public function setNumberOfPlayers(int $numberOfPlayers): static
    {
        $this->numberOfPlayers = $numberOfPlayers;

        return $this;
    }

    public function generateSingleEliminationFirstRound(array $players): ArrayCollection|Game
    {
        $games = new ArrayCollection();

        if ($this->getType() !== self::TOURNAMENT_TYPE_SINGLE_ELIMINATION || count($this->getGames()) > 0) {
            return $games;
        }

        // First Round
        while (count($players) > 1 ) {
            $game = new Game();
            $game->setRoundNumber(1);
            $game->setTournament($this);
            $game->setScheduledAt(new \DateTimeImmutable());

            for ($i = 0; $i < 2; $i++) {
                $randomIndex = array_rand($players);
                if ($players[$randomIndex] instanceof Team) {
                    $game->addTeam($players[$randomIndex]);
                } elseif ($players[$randomIndex] instanceof User) {
                    $game->addUser($players[$randomIndex]);
                }
                unset($players[$randomIndex]);
            }

            $games->add($game);
        }

        // Autowin for the last unchosen user/team
        if (count($players) === 1 ) {
            $game = new Game();
            $game->setRoundNumber(1);
            $game->setTournament($this);
            $game->setScheduledAt(new \DateTimeImmutable());

            $lastPlayer = reset($players);
            if ($lastPlayer instanceof Team) {
                $game->addTeam($lastPlayer);
            } elseif ($lastPlayer instanceof User) {
                $game->addUser($lastPlayer);
            }

            $games->add($game);
        }

        return $games;
    }

    public function getPlayers()
    {
        $players["users"] = $players["teams"] = [];

        foreach ($this->getGames() as $game) {

            foreach ($game->getUsers() as $user) {
                if (!in_array($user->getName(), $players["users"])) {
                    $players["users"][] = $user->getName();
                }
            }

            foreach ($game->getTeams() as $team) {
                if (!in_array($team->getName(), $players["teams"])) {
                    $players["teams"][] = $team->getName();
                }
            }
        }

        return $players;
    }
}
