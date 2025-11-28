<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le texte du conseil est obligatoire.")]
    private ?string $advicetext = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le mois est obligatoire.")]
    #[Assert\Range(
        min: 1,
        max: 12,
        notInRangeMessage: "Le mois doit être compris entre {{ min }} et {{ max }}."
    )]
    private ?int $month = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdvicetext(): ?string
    {
        return $this->advicetext;
    }

    public function setAdvicetext(string $Advicetext): static
    {
        $this->advicetext = $Advicetext;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }
}
