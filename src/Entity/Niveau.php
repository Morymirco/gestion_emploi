<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Niveau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $nomNiveau = null;

    #[ORM\Column(length: 10, unique: true)]
    private ?string $code = null;

    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'niveau')]
    private Collection $utilisateurs;

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'niveau')]
    private Collection $cours;

    #[ORM\OneToMany(targetEntity: EmploiDuTemps::class, mappedBy: 'niveau')]
    private Collection $emploiDuTemps;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->cours = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomNiveau(): ?string
    {
        return $this->nomNiveau;
    }

    public function setNomNiveau(string $nomNiveau): self
    {
        $this->nomNiveau = $nomNiveau;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setNiveau($this);
        }
        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            if ($utilisateur->getNiveau() === $this) {
                $utilisateur->setNiveau(null);
            }
        }
        return $this;
    }

    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setNiveau($this);
        }
        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            if ($cour->getNiveau() === $this) {
                $cour->setNiveau(null);
            }
        }
        return $this;
    }

    public function getEmploiDuTemps(): Collection
    {
        return $this->emploiDuTemps;
    }

    public function addEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if (!$this->emploiDuTemps->contains($emploiDuTemps)) {
            $this->emploiDuTemps->add($emploiDuTemps);
            $emploiDuTemps->setNiveau($this);
        }
        return $this;
    }

    public function removeEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemps)) {
            if ($emploiDuTemps->getNiveau() === $this) {
                $emploiDuTemps->setNiveau(null);
            }
        }
        return $this;
    }
}