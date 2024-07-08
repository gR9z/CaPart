<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Event name should not be blank")]
    #[Assert\Length(min: 4, max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "Start date should not be blank")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $startDateTime = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Duration should not be blank")]
    #[Assert\Positive(message: "Duration should be a positive number")]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Registration deadline should not be blank")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $registrationDeadline = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Max registrations should not be blank")]
    #[Assert\Positive(message: "Max registrations should be a positive number")]
    private ?int $maxRegistrations = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Event details should not be blank")]
    #[Assert\Length(min: 20, max: 500, minMessage: "Event details should be longer than 20 characters", maxMessage: "Event details should not be longer than 500 characters")]
    private ?string $eventDetails = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Event place should not be null")]
    private ?Place $place = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Event location should not be null")]
    private ?Location $location = null;

    #[ORM\ManyToOne(inversedBy: 'organizedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participatedEvents')]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTimeInterface $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(int $maxRegistrations): static
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getEventDetails(): ?string
    {
        return $this->eventDetails;
    }

    public function setEventDetails(string $eventDetails): static
    {
        $this->eventDetails = $eventDetails;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

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

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function isUserRegistered(User $user): bool
    {
        return $this->participants->contains($user);
    }
}