<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
#[ApiResource]
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

    #[ORM\Column]
    private ?int $numberMaxOfRounds = null;

    const TOURNAMENT_TYPE = [
        'Single Elimination',
        //'Double Elimination',
        //'Multilevel',
        //'Straight Round Robin',
        //'Round Robin Double Split',
        //'Semi-Round Robins',
    ];

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

    public function getNumberMaxOfRounds(): ?int
    {
        return $this->numberMaxOfRounds;
    }

    public function setNumberMaxOfRounds(int $numberMaxOfRounds): static
    {
        $this->numberMaxOfRounds = $numberMaxOfRounds;

        return $this;
    }
}
