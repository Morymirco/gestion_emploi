<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Département
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $nomDépartement = null;

    #[ORM\Column(length: 10, unique: true)]
    private ?string $code = null;

    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'département')]
    private Collection $utilisateurs;

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'département')]
    private Collection $cours;

    #[ORM\OneToMany(targetEntity: EmploiDuTemps::class, mappedBy: 'département')]
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

    public function getNomDépartement(): ?string
    {
        return $this->nomDépartement;
    }

    public function setNomDépartement(string $nomDépartement): self
    {
        $this->nomDépartement = $nomDépartement;
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
            $utilisateur->setDépartement($this);
        }
        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            if ($utilisateur->getDépartement() === $this) {
                $utilisateur->setDépartement(null);
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
            $cour->setDépartement($this);
        }
        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            if ($cour->getDépartement() === $this) {
                $cour->setDépartement(null);
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
            $emploiDuTemps->setDépartement($this);
        }
        return $this;
    }

    public function removeEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemps)) {
            if ($emploiDuTemps->getDépartement() === $this) {
                $emploiDuTemps->setDépartement(null);
            }
        }
        return $this;
    }
}