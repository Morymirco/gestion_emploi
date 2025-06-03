<?php

namespace App\Entity;

use App\Enum\RoleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class Utilisateur implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', enumType: RoleType::class)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\ManyToOne(targetEntity: Département::class)]
    private ?Département $département = null;

    #[ORM\ManyToOne(targetEntity: Niveau::class)]
    private ?Niveau $niveau = null;

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'enseignant')]
    private Collection $cours;

    #[ORM\OneToMany(targetEntity: Disponibilité::class, mappedBy: 'enseignant')]
    private Collection $disponibilites;

    #[ORM\OneToMany(targetEntity: Absence::class, mappedBy: 'enseignant')]
    private Collection $absences;

    #[ORM\OneToMany(targetEntity: Suit::class, mappedBy: 'utilisateur')]
    private Collection $suits;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->disponibilites = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->suits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
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

    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setEnseignant($this);
        }
        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            if ($cour->getEnseignant() === $this) {
                $cour->setEnseignant(null);
            }
        }
        return $this;
    }

    public function getDisponibilites(): Collection
    {
        return $this->disponibilites;
    }

    public function addDisponibilite(Disponibilité $disponibilite): self
    {
        if (!$this->disponibilites->contains($disponibilite)) {
            $this->disponibilites->add($disponibilite);
            $disponibilite->setEnseignant($this);
        }
        return $this;
    }

    public function removeDisponibilite(Disponibilité $disponibilite): self
    {
        if ($this->disponibilites->removeElement($disponibilite)) {
            if ($disponibilite->getEnseignant() === $this) {
                $disponibilite->setEnseignant(null);
            }
        }
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
            $absence->setEnseignant($this);
        }
        return $this;
    }

    public function removeAbsence(Absence $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            if ($absence->getEnseignant() === $this) {
                $absence->setEnseignant(null);
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
            $suit->setUtilisateur($this);
        }
        return $this;
    }

    public function removeSuit(Suit $suit): self
    {
        if ($this->suits->removeElement($suit)) {
            if ($suit->getUtilisateur() === $this) {
                $suit->setUtilisateur(null);
            }
        }
        return $this;
    }

    // UserInterface methods
    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // No sensitive data to erase
    }
}