<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EmploiDuTemps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(length: 8)]
    private ?string $duree = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'emploiDuTemps')]
    private ?Cours $cours = null;

    #[ORM\ManyToOne(targetEntity: Salle::class, inversedBy: 'emploiDuTemps')]
    private ?Salle $salle = null;

    #[ORM\ManyToOne(targetEntity: Département::class, inversedBy: 'emploiDuTemps')]
    private ?Département $département = null;

    #[ORM\ManyToOne(targetEntity: Niveau::class, inversedBy: 'emploiDuTemps')]
    private ?Niveau $niveau = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getDurée(): ?string
    {
        return $this->duree;
    }

    public function setDurée(string $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): self
    {
        $this->cours = $cours;
        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): self
    {
        $this->salle = $salle;
        return $this;
    }

    public function getDépartement(): ?Département
    {
        return $this->département;
    }

    public function setDépartement(?Département $département): self
    {
        $this->département = $département;
        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }
}