<?php

namespace App\Entity;

use App\Repository\BreathingExerciseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BreathingExerciseRepository::class)]
class BreathingExercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: false)]
    private ?int $inhaleSeconds = null;

    #[ORM\Column(nullable: false)]
    private ?int $holdSeconds = null;

    #[ORM\Column(nullable: false)]
    private ?int $exhaleSeconds = null;

    #[ORM\Column(nullable: false)]
    private ?int $cycles = 5;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInhaleSeconds(): ?int
    {
        return $this->inhaleSeconds;
    }

    public function setInhaleSeconds(int $inhaleSeconds): self
    {
        $this->inhaleSeconds = $inhaleSeconds;

        return $this;
    }

    public function getHoldSeconds(): ?int
    {
        return $this->holdSeconds;
    }

    public function setHoldSeconds(int $holdSeconds): self
    {
        $this->holdSeconds = $holdSeconds;

        return $this;
    }

    public function getExhaleSeconds(): ?int
    {
        return $this->exhaleSeconds;
    }

    public function setExhaleSeconds(int $exhaleSeconds): self
    {
        $this->exhaleSeconds = $exhaleSeconds;

        return $this;
    }

    public function getCycles(): ?int
    {
        return $this->cycles;
    }

    public function setCycles(int $cycles): self
    {
        $this->cycles = $cycles;

        return $this;
    }
}

