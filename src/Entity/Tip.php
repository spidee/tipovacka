<?php

namespace App\Entity;

use App\Entity\Base\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tips")
 */
class Tip
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tips")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Match", inversedBy="tips")
     */
    protected $match;

    /**
     * @ORM\Column(name="home_goals_tip", type="integer", nullable=true)
     */
    protected $homeGoalsTip;

    /**
     * @ORM\Column(name="away_goals_tip", type="integer", nullable=true)
     */
    protected $awayGoalsTip;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Point", mappedBy="tip")
     */
    protected $point;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHomeGoalsTip(): ?int
    {
        return $this->homeGoalsTip;
    }

    public function setHomeGoalsTip(?int $homeGoalsTip): self
    {
        $this->homeGoalsTip = $homeGoalsTip;

        return $this;
    }

    public function getAwayGoalsTip(): ?int
    {
        return $this->awayGoalsTip;
    }

    public function setAwayGoalsTip(?int $awayGoalsTip): self
    {
        $this->awayGoalsTip = $awayGoalsTip;

        return $this;
    }

    public function getMatch(): ?Match
    {
        return $this->match;
    }

    public function setMatch(?Match $match): self
    {
        $this->match = $match;

        return $this;
    }

    public function getPoint(): ?Point
    {
        return $this->point;
    }

    public function setPoint(?Point $point): self
    {
        $this->point = $point;

        // set (or unset) the owning side of the relation if necessary
        $newTip = $point === null ? null : $this;
        if ($newTip !== $point->getTip()) {
            $point->setTip($newTip);
        }

        return $this;
    }

}