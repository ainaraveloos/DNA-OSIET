<?php

namespace App\Services;

use App\Repositories\SocieteRepository;
use App\Services\BaseImgService;
use App\Services\ImageService;
use Carbon\Carbon;

class SocieteService extends BaseImgService
{
    protected array $scope = ['filter' => 'search','filterstatus' => "status"];

    public function __construct(SocieteRepository $societeRepository, ImageService $imageService)
    {
        parent::__construct($societeRepository, $imageService);
    }

    /**
     * Initialise la configuration des images pour les sociétés
     */
    protected function initializeImageConfig(): void
    {
        $this->imageFolder = 'societes';
        $this->imageFields = ['img'];
        $this->imageConfig = [
            'max_width' => 1920,
            'original_quality' => 90,
            'thumbnail_size' => [300, 300],
            'thumbnail_quality' => 85,
            'max_file_size' => 5120, // 5MB
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
        ];
    }

    /**
     * Initialise les filtres de recherche
     */
    public function initializeFilters(): void
    {
        $this->setFilterValue('id')->setFilterLabel('name');
    }

    /**
     * Crée une société avec gestion d'images
     */
    public function createSociete(array $validated): array
    {
        $validated['date_adhesion'] = Carbon::now();
        return $this->createWithImage($validated);
    }

    /**
     * Met à jour une société avec gestion d'images
     */
    public function updateSociete($societe, array $validated): array
    {
        return $this->updateWithImage($societe, $validated);
    }

    /**
     * Supprime une société avec ses images
     */
    public function deleteSociete($societe): array
    {
        return $this->deleteWithImage($societe);
    }
}
