<?php
namespace App\Entity;

use App\Entity\Base\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ORM\Table(name="teams")
 */
class Team
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;
    /**
     * @ORM\Column(name="fifa_code", type="string")
     */
    protected $fifaCode;
    /**
     * @ORM\Column(name="iso2", type="string")
     */
    protected $iso2;
    /**
     * @ORM\Column(name="flag", type="string")
     */
    protected $flag;
    /**
     * @ORM\Column(name="emoji", type="string")
     */
    protected $emoji;
    /**
     * @ORM\Column(name="emoji_string", type="string")
     */
    protected $emojiString;

    /**
     * @ORM\Column(name="czech_name", type="string")
     */
    protected $czechName;

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

    public function getFifaCode(): ?string
    {
        return $this->fifaCode;
    }

    public function setFifaCode(string $fifaCode): self
    {
        $this->fifaCode = $fifaCode;

        return $this;
    }

    public function getIso2(): ?string
    {
        return $this->iso2;
    }

    public function setIso2(string $iso2): self
    {
        $this->iso2 = $iso2;

        return $this;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): self
    {
        $this->flag = $flag;

        return $this;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(string $emoji): self
    {
        $this->emoji = $emoji;

        return $this;
    }

    public function getEmojiString(): ?string
    {
        return $this->emojiString;
    }

    public function setEmojiString(string $emojiString): self
    {
        $this->emojiString = $emojiString;

        return $this;
    }

    public function getCzechName(): ?string
    {
        return $this->czechName;
    }

    public function setCzechName(string $czechName): self
    {
        $this->czechName = $czechName;

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s', $this->getCzechName());
    }


}