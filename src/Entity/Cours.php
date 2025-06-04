<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomCours = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'cours')]
    private ?Utilisateur $enseignant = null;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: 'cours')]
    #[ORM\JoinColumn(name: 'département_id', referencedColumnName: 'id')]
    private ?Departement $departement = null;

    #[ORM\ManyToOne(targetEntity: Niveau::class, inversedBy: 'cours')]
    private ?Niveau $niveau = null;

    #[ORM\OneToMany(targetEntity: EmploiDuTemps::class, mappedBy: 'cours')]
    private Collection $emploiDuTemps;

    #[ORM\OneToMany(targetEntity: Suit::class, mappedBy: 'cours')]
    private Collection $suits;

    public function __construct()
    {
        $this->emploiDuTemps = new ArrayCollection();
        $this->suits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCours(): ?string
    {
        return $this->nomCours;
    }

    public function setNomCours(string $nomCours): self
    {
        $this->nomCours = $nomCours;
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

    public function getEnseignant(): ?Utilisateur
    {
        return $this->enseignant;
    }

    public function setEnseignant(?Utilisateur $enseignant): self
    {
        $this->enseignant = $enseignant;
        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;
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

    public function getEmploiDuTemps(): Collection
    {
        return $this->emploiDuTemps;
    }

    public function addEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if (!$this->emploiDuTemps->contains($emploiDuTemps)) {
            $this->emploiDuTemps->add($emploiDuTemps);
            $emploiDuTemps->setCours($this);
        }
        return $this;
    }

    public function removeEmploiDuTemps(EmploiDuTemps $emploiDuTemps): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemps)) {
            if ($emploiDuTemps->getCours() === $this) {
                $emploiDuTemps->setCours(null);
            }
        }
        return $this;
    }

    public function getSuits(): Collection
    {
        return $this->suits;
    }

    public function addSuit(Suit $suit): self
    {
        if (!$this->suits->contains($suit)) {
            $this->suits->add($suit);
            $suit->setCours($this);
        }
        return $this;
    }

    public function removeSuit(Suit $suit): self
    {
        if ($this->suits->removeElement($suit)) {
            if ($suit->getCours() === $this) {
                $suit->setCours(null);
            }
        }
        return $this;
    }
}