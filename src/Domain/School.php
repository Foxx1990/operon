<?php

namespace App\Domain;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\SchoolMatchRequest;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['school:read']]),
        new GetCollection(normalizationContext: ['groups' => ['school:read']]),
        new Post(
            uriTemplate: '/schools/match',
            input: SchoolMatchRequest::class,
            controller: \App\Controller\Api\SchoolMatchController::class,
            description: 'Search for a school by name or alias using the optimized matching engine.'
        )
    ]
)]
#[ORM\Entity]
#[ORM\Table(name: 'schools')]
class School
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['school:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'json')]
    #[ApiProperty(readable: false, writable: false)]
    private readonly array $searchTerms;

    public function __construct(
        #[ORM\Column(length: 255)]
        private readonly string $name,
        #[ORM\Column(type: 'json')]
        private readonly array $aliases,
        #[ORM\Column(length: 255)]
        private readonly string $city,
        #[ORM\Column(length: 50)]
        private readonly string $type,
        array $searchTerms = []
    ) {
        $this->searchTerms = !empty($searchTerms) ? $searchTerms : array_map(
            fn($term) => strtolower(trim($term)),
            array_merge([$this->name], $this->aliases)
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['school:read'])]
    public function getName(): string
    {
        return $this->name;
    }

    #[Groups(['school:read'])]
    public function getAliases(): array
    {
        return $this->aliases;
    }

    #[Groups(['school:read'])]
    public function getCity(): string
    {
        return $this->city;
    }

    #[Groups(['school:read'])]
    public function getType(): string
    {
        return $this->type;
    }

    public function getSearchTerms(): array
    {
        return $this->searchTerms;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'aliases' => $this->aliases,
            'city' => $this->city,
            'type' => $this->type,
        ];
    }
}
