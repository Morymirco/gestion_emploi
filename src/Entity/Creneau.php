<?php

namespace App\Entity;

use App\Enum\TypeCreneau;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Creneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    private ?string $jourSemaine = null; // lundi, mardi, mercredi, jeudi, vendredi, samedi

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(type: 'string', enumType: TypeCreneau::class)]
    private ?TypeCreneau $typeCreneau = null; // seance, pause, heure_creuse

    #[ORM\ManyToOne(targetEntity: Cours::class)]
    private ?Cours $cours = null;

    #[ORM\ManyToOne(targetEntity: Salle::class)]
    private ?Salle $salle = null;

    #[ORM\ManyToOne(targetEntity: Niveau::class)]
    private ?Niveau $niveau = null;

    #[ORM\ManyToOne(targetEntity: Module::class)]
    private ?Module $module = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $enseignant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\OneToMany(targetEntity: Absence::class, mappedBy: 'creneau', cascade: ['remove'])]
    private Collection $absences;

    public function __construct()
    {
        $this->absences = new ArrayCollection();
        $this->actif = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJourSemaine(): ?string
    {
        return $this->jourSemaine;
    }

    public function setJourSemaine(string $jourSemaine): self
    {
        $this->jourSemaine = $jourSemaine;
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

    public function getTypeCreneau(): ?TypeCreneau
    {
        return $this->typeCreneau;
    }

    public function setTypeCreneau(TypeCreneau $typeCreneau): self
    {
        $this->typeCreneau = $typeCreneau;
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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;
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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getAbsences(): Collection
    {
        return $this->absences;
    }

    public function addAbsence(Absence $absence): self
    {
        if (!$this->absences->contains($absence)) {
            $this->absences->add($absence);
            $absence->setCreneau($this);
        }
        return $this;
    }

    public function removeAbsence(Absence $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            if ($absence->getCreneau() === $this) {
                $absence->setCreneau(null);
            }
        }
        return $this;
    }

    public function getDuree(): string
    {
        if ($this->heureDebut && $this->heureFin) {
            $debut = $this->heureDebut->format('H:i');
            $fin = $this->heureFin->format('H:i');
            return $debut . ' - ' . $fin;
        }
        return '';
    }

    public function __toString(): string
    {
        $jour = ucfirst($this->jourSemaine);
        $duree = $this->getDuree();
        $cours = $this->cours ? $this->cours->getNomCours() : 'Non assigné';
        
        return "{$jour} {$duree} - {$cours}";
    }

    // Méthode pour vérifier les conflits d'horaires
    public function hasConflictWith(Creneau $autre): bool
    {
        // Même jour de la semaine
        if ($this->jourSemaine !== $autre->getJourSemaine()) {
            return false;
        }

        // Vérifier le chevauchement des heures
        $debut1 = $this->heureDebut->format('H:i');
        $fin1 = $this->heureFin->format('H:i');
        $debut2 = $autre->getHeureDebut()->format('H:i');
        $fin2 = $autre->getHeureFin()->format('H:i');

        return !($fin1 <= $debut2 || $debut1 >= $fin2);
    }

    // Vérifier si c'est un créneau de cours
    public function isSeance(): bool
    {
        return $this->typeCreneau === TypeCreneau::SEANCE;
    }

    // Vérifier si c'est une pause
    public function isPause(): bool
    {
        return $this->typeCreneau === TypeCreneau::PAUSE;
    }

    // Vérifier si c'est une heure creuse
    public function isHeureCreuse(): bool
    {
        return $this->typeCreneau === TypeCreneau::HEURE_CREUSE;
    }
} 