<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    const TYPE_ENTRY      = 1;
    const TYPE_VACANCY    = 2;
    const TYPE_STATICPAGE = 3;
    const TYPE_EVENT      = 4;
    const TYPE_REPOST     = 5;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id = 0;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $url = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $title = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $commentsCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $favoritesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $hits = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $rating = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $platform = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $section = '';

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCommentsCount(): int
    {
        return $this->commentsCount;
    }

    public function setCommentsCount(int $commentsCount): self
    {
        $this->commentsCount = $commentsCount;

        return $this;
    }

    public function getFavoritesCount(): int
    {
        return $this->favoritesCount;
    }

    public function setFavoritesCount(int $favoritesCount): self
    {
        $this->favoritesCount = $favoritesCount;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }
}
