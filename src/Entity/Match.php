<?php
namespace App\Entity;

use App\Entity\Base\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MatchRepository")
 * @ORM\Table(name="matches")
 */
class Match
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="matches")
     */
    protected $group;
    /**
     * @ORM\Column(type="string")
     */
    protected $name;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    protected $homeTeam;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    protected $awayTeam;
    /**
     * @ORM\Column(name="home_result", type="integer", nullable=true)
     */
    protected $homeResult;
    /**
     * @ORM\Column(name="away_result", type="integer", nullable=true)
     */
    protected $awayResult;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    protected $winner;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;
    /**
     * @ORM\Column(name="home_penalty", type="integer", nullable=true)
     */
    protected $homePenalty;
    /**
     * @ORM\Column(name="away_penalty", type="integer", nullable=true)
     */
    protected $awayPenalty;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $finished;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $matchday;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $tipsProcessed = false;

    /**
     * @ORM\OneToMany(targetEntity="Tip", mappedBy="match")
     */
    protected $tips;

    public function __construct()
    {
        $this->tips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getHomeResult(): ?int
    {
        return $this->homeResult;
    }

    public function setHomeResult(?int $homeResult): self
    {
        $this->homeResult = $homeResult;

        return $this;
    }

    public function getAwayResult(): ?int
    {
        return $this->awayResult;
    }

    public function setAwayResult(?int $awayResult): self
    {
        $this->awayResult = $awayResult;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHomePenalty(): ?int
    {
        return $this->homePenalty;
    }

    public function setHomePenalty(?int $homePenalty): self
    {
        $this->homePenalty = $homePenalty;

        return $this;
    }

    public function getAwayPenalty(): ?int
    {
        return $this->awayPenalty;
    }

    public function setAwayPenalty(?int $awayPenalty): self
    {
        $this->awayPenalty = $awayPenalty;

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getMatchday(): ?int
    {
        return $this->matchday;
    }

    public function setMatchday(?int $matchday): self
    {
        $this->matchday = $matchday;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getHomeTeam(): ?Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): ?Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getTipsProcessed(): ?bool
    {
        return $this->tipsProcessed;
    }

    public function setTipsProcessed(bool $tipsProcessed): self
    {
        $this->tipsProcessed = $tipsProcessed;

        return $this;
    }

    /**
     * @return Collection|Tip[]
     */
    public function getTips(): Collection
    {
        return $this->tips;
    }

    /**
     * @return Tip[]
     */
    public function getSortedTips(): array
    {
        $tips = $this->tips->toArray();
        usort($tips, function(Tip $tipA, Tip $tipB) {
            return $tipA->getUser()->getUsername() <=> $tipB->getUser()->getUsername();
        });
        return $tips;
    }

    public function addTip(Tip $tip): self
    {
        if (!$this->tips->contains($tip)) {
            $this->tips[] = $tip;
            $tip->setMatch($this);
        }

        return $this;
    }

    public function removeTip(Tip $tip): self
    {
        if ($this->tips->contains($tip)) {
            $this->tips->removeElement($tip);
            // set the owning side to null (unless already changed)
            if ($tip->getMatch() === $this) {
                $tip->setMatch(null);
            }
        }

        return $this;
    }

    public function userCanEdit(User $user): bool
    {
        return false;
    }

    /**
     * @param User $user
     * @return Tip|null
     */
    public function getUserTip(User $user): ?Tip
    {
        if ($tip = $this->tips->filter(function(Tip $tip) use ($user) {
            return $tip->getUser() === $user;
        })->first()) {
            return $tip;
        }
        return null;
    }

}