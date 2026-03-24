<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SoftwareVersionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SoftwareVersionRepository::class)]
#[ORM\Table(name: 'software_version')]
#[ORM\HasLifecycleCallbacks]
class SoftwareVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Human-readable product name, e.g. "MMI Prime CIC" or "LCI MMI PRO NBT".
     * Names starting with "LCI" are matched exclusively against LCI hardware versions.
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Product name is required.')]
    private string $name = '';

    /**
     * The canonical version string with a leading "v", e.g. "v3.3.7.mmipri.c".
     * Displayed to users in admin lists.
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'System version is required.')]
    private string $systemVersion = '';

    /**
     * The version string without the leading "v". This is what customers enter in the form
     * and what the lookup query matches against (case-insensitive).
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Alternate system version is required.')]
    private string $systemVersionAlt = '';

    /**
     * Generic download link used for standard (non-LCI) entries.
     * Leave empty for LCI entries.
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $link = null;

    /**
     * Download link for ST hardware (CIC systems).
     * Leave empty if not applicable for this product / hardware type.
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $st = null;

    /**
     * Download link for GD hardware (NBT / EVO systems).
     * Leave empty if not applicable for this product / hardware type.
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $gd = null;

    /**
     * When true this is the most recent firmware for this product line.
     * Customers on this version will see "Your system is up to date".
     * Only one entry per product group should be marked as latest.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isLatest = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSystemVersion(): string
    {
        return $this->systemVersion;
    }

    public function setSystemVersion(string $systemVersion): static
    {
        $this->systemVersion = $systemVersion;
        return $this;
    }

    public function getSystemVersionAlt(): string
    {
        return $this->systemVersionAlt;
    }

    public function setSystemVersionAlt(string $systemVersionAlt): static
    {
        $this->systemVersionAlt = $systemVersionAlt;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link ?: null;
        return $this;
    }

    public function getSt(): ?string
    {
        return $this->st;
    }

    public function setSt(?string $st): static
    {
        $this->st = $st ?: null;
        return $this;
    }

    public function getGd(): ?string
    {
        return $this->gd;
    }

    public function setGd(?string $gd): static
    {
        $this->gd = $gd ?: null;
        return $this;
    }

    public function isLatest(): bool
    {
        return $this->isLatest;
    }

    public function setIsLatest(bool $isLatest): static
    {
        $this->isLatest = $isLatest;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isLci(): bool
    {
        return str_starts_with(strtoupper($this->name), 'LCI');
    }

    public function __toString(): string
    {
        return sprintf('%s — %s', $this->name, $this->systemVersion);
    }
}
