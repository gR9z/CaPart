<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Username can't be blank")]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: "Username must be at least {{ limit }} characters long",
        maxMessage: "Username cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9_]+$/",
        message: "Username can only contain letters, numbers, and underscores"
    )]
    private ?string $username = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "First name can't be blank")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "First name must be at least {{ limit }} characters long",
        maxMessage: "First name cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÖØ-öø-ÿ' -]+$/",
        message: "First name can only contain letters, apostrophes, hyphens, and spaces"
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "First name can't be blank")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Lastname must be at least {{ limit }} characters long",
        maxMessage: "Lastname cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÖØ-öø-ÿ' -]+$/",
        message: "Lastname can only contain letters, apostrophes, hyphens, and spaces"
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Phone number can't be blank")]
    #[Assert\Length(
        min: 8,
        max: 20,
        minMessage: "Phone number must be at least {{ limit }} characters long",
        maxMessage: "Phone number cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^\+?[0-9\s\-\(\)]+$/",
        message: "Phone number can only contain digits, spaces, hyphens, parentheses, and optionally start with a plus sign"
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[Assert\NotNull(message: "Location must be selected.")]
    private ?Location $location = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organizer', orphanRemoval: true)]
    private Collection $organizedEvents;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $participatedEvents;

    #[ORM\Column(length: 255)]
    private ?string $profilImage = null;

    public function __construct()
    {
        $this->organizedEvents = new ArrayCollection();
        $this->participatedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getOrganizedEvents(): Collection
    {
        return $this->organizedEvents;
    }

    public function addOrganizedEvent(Event $organizedEvent): static
    {
        if (!$this->organizedEvents->contains($organizedEvent)) {
            $this->organizedEvents->add($organizedEvent);
            $organizedEvent->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizedEvent(Event $organizedEvent): static
    {
        if ($this->organizedEvents->removeElement($organizedEvent)) {
            // set the owning side to null (unless already changed)
            if ($organizedEvent->getOrganizer() === $this) {
                $organizedEvent->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getParticipatedEvents(): Collection
    {
        return $this->participatedEvents;
    }

    public function addParticipatedEvent(Event $participatedEvent): static
    {
        if (!$this->participatedEvents->contains($participatedEvent)) {
            $this->participatedEvents->add($participatedEvent);
            $participatedEvent->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatedEvent(Event $participatedEvent): static
    {
        if ($this->participatedEvents->removeElement($participatedEvent)) {
            $participatedEvent->removeParticipant($this);
        }

        return $this;
    }

    public function getProfilImage(): ?string
    {
        return $this->profilImage;
    }

    public function setProfilImage(string $profilImage): self
    {
        $this->profilImage = $profilImage;

        return $this;
    }
}
