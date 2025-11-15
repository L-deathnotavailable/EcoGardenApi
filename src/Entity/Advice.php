<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Advicetext = null;

    #[ORM\Column]
    private ?int $Month = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdvicetext(): ?string
    {
        return $this->Advicetext;
    }

    public function setAdvicetext(string $Advicetext): static
    {
        $this->Advicetext = $Advicetext;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->Month;
    }

    public function setMonth(int $Month): static
    {
        $this->Month = $Month;

        return $this;
    }
}
