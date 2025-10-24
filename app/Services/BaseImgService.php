<?php

namespace App\Services;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Exception;

abstract class BaseImgService extends BaseService
{
    protected ImageService $imageService;
    protected array $imageConfig;
    protected string $imageFolder;
    protected array $imageFields;

    public function __construct($repository, ImageService $imageService)
    {
        parent::__construct($repository);
        $this->imageService = $imageService;
        $this->initializeImageConfig();
    }

    /**
     * Initialise la configuration des images
     * À surcharger dans les classes enfants
     */
    protected function initializeImageConfig(): void
    {
        $this->imageFolder = 'images';
        $this->imageFields = ['img', 'thumb_img'];
        $this->imageConfig = [
            'max_width' => 1920,
            'original_quality' => 90,
            'thumbnail_size' => [300, 300],
            'thumbnail_quality' => 85,
            'max_file_size' => 5120, // 5MB
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/avif']
        ];
    }

    /**
     * Crée un élément avec gestion d'images
     *
     * @param array $validated
     * @return array
     */
    public function createWithImage(array $validated): array
    {
        try {
            // Séparer les données d'images des autres données
            $imageFiles = $this->extractImageFiles($validated);
            // Supprimer les fichiers d'images des données validée
            $entityData = $this->removeImageFiles($validated);

            // Créer l'entité d'abord
            $entity = $this->repository->create($entityData);

            // Gérer les images si fournies
            if (!empty($imageFiles)) {
                $imageResult = $this->processImages($imageFiles, $entity);

                if (!$imageResult['success']) {
                    // Si l'upload des images échoue, supprimer l'entité créée
                    $this->repository->delete($entity);
                    return $this->errorResponse('Erreur lors de l\'upload des images : ' . $imageResult['error'], new Exception($imageResult['error']));
                }

                // Mettre à jour l'entité avec les chemins des images
                $this->updateEntityWithImagePaths($entity, $imageResult['paths']);
            }

            return $this->successResponse('Création effectuée avec succès', $entity->load($this->relation));

        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de la création', $e);
        }
    }

    /**
     * Met à jour un élément avec gestion d'images
     *
     * @param mixed $model
     * @param array $validated
     * @return array
     */
    public function updateWithImage($model, array $validated): array
    {
        try {
            // Séparer les données d'images des autres données
            $imageFiles = $this->extractImageFiles($validated);
            // Supprimer les fichiers d'images des données validée (mais garder les null)
            $entityData = $this->removeImageFiles($validated);

            // Récupérer les anciens chemins d'images de l'entité
            $oldImagePaths = $this->getOldImagePaths($model);

            // Mettre à jour les données de l'entité (inclut les champs null pour suppression)
            $this->repository->update($model, $entityData);

            // Supprimer les anciennes images si on a de nouveaux fichiers ou si on supprime
            if (!empty($imageFiles) || $this->hasImageDeletions($validated)) {
                if (!empty($oldImagePaths)) {
                    $this->imageService->deleteMultipleImages($oldImagePaths);
                }
            }

            // Gérer les nouvelles images si fournies
            if (!empty($imageFiles)) {
                $imageResult = $this->processImages($imageFiles, $model, []);

                if (!$imageResult['success']) {
                    return $this->errorResponse('Erreur lors de l\'upload des images : ' . $imageResult['error'], new Exception($imageResult['error']));
                }

                // Mettre à jour l'entité avec les nouveaux chemins des images
                $this->updateEntityWithImagePaths($model, $imageResult['paths']);
            }

            return $this->successResponse('Mise à jour effectuée avec succès', $model->load($this->relation));

        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de la mise à jour', $e);
        }
    }

    /**
     * Supprime un élément avec ses images
     *
     * @param mixed $model
     * @return array
     */
    public function deleteWithImage($model): array
    {
        try {
            // Supprimer les images associées
            $imagePaths = $this->getOldImagePaths($model);
            if (!empty($imagePaths)) {
                $this->imageService->deleteMultipleImages($imagePaths);
            }

            // Supprimer l'entité
            $this->repository->delete($model);

            return $this->successResponse('Suppression effectuée avec succès');

        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression', $e);
        }
    }

    /**
     * Extrait les fichiers d'images des données validées
     *
     * @param array $validated
     * @return array
     */
    protected function extractImageFiles(array $validated): array
    {
        $imageFiles = [];

        foreach ($this->imageFields as $field) {
            if (isset($validated[$field]) && $validated[$field] instanceof UploadedFile) {
                $imageFiles[$field] = $validated[$field];
            }
        }

        return $imageFiles;
    }

    /**
     * Retire les fichiers d'images des données validées
     *
     * @param array $validated
     * @return array
     */
    protected function removeImageFiles(array $validated): array
    {
        $entityData = $validated;

        foreach ($this->imageFields as $field) {
            // Ne pas supprimer les champs null (suppression d'image)
            if (isset($validated[$field]) && $validated[$field] !== null) {
                unset($entityData[$field]);
            }
        }

        return $entityData;
    }

    /**
     * Obtient les anciens chemins d'images d'un modèle
     *
     * @param mixed $model
     * @return array
     */
    protected function getOldImagePaths($model): array
    {
        $paths = [];
        foreach ($this->imageFields as $field) {
            if (!empty($model->$field)) {
                $paths[] = $model->$field;
            }
        }

        return $paths;
    }

    /**
     * Traite les images uploadées
     *
     * @param array $imageFiles
     * @param mixed $model
     * @param array $oldImagePaths
     * @return array
     */
    protected function processImages(array $imageFiles, $model, array $oldImagePaths = []): array
    {
        $processedPaths = [];
        $errors = [];

        foreach ($imageFiles as $field => $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->imageService->uploadImage($file, $this->imageFolder, $this->imageConfig);

                if ($result['success']) {
                    $processedPaths[$field] = $result['original_path'];
                    $processedPaths[$field . '_thumb'] = $result['thumbnail_path'];
                } else {
                    $errors[] = "Erreur pour {$field}: " . $result['error'];
                }
            }
        }

        // Supprimer les anciennes images si tout s'est bien passé
        if (empty($errors) && !empty($oldImagePaths)) {
            $this->imageService->deleteMultipleImages($oldImagePaths);
        }

        return [
            'success' => empty($errors),
            'paths' => $processedPaths,
            'error' => implode(', ', $errors)
        ];
    }

    /**
     * Met à jour l'entité avec les chemins des images
     *
     * @param mixed $model
     * @param array $imagePaths
     */
    protected function updateEntityWithImagePaths($model, array $imagePaths): void
    {
        $updateData = [];

        foreach ($imagePaths as $field => $path) {
            if (str_ends_with($field, '_thumb')) {
                // Pour les miniatures, utiliser le nom correct thumb_img
                $updateData['thumb_img'] = $path;
            } else {
                // Pour les images originales
                $updateData[$field] = $path;
            }
        }

        if (!empty($updateData)) {
            $this->repository->update($model, $updateData);
        }
    }

    /**
     * Upload multiple images pour une entité
     *
     * @param array $files
     * @param mixed $model
     * @return array
     */
    public function uploadMultipleImages(array $files, $model): array
    {
        try {
            $result = $this->imageService->uploadMultipleImages($files, $this->imageFolder, $this->imageConfig);

            if ($result['success']) {
                // Traiter les résultats et mettre à jour le modèle
                $this->processMultipleImageResults($model, $result['results']);
            }

            return $this->successResponse('Upload multiple effectué', $result);

        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de l\'upload multiple', $e);
        }
    }

    /**
     * Traite les résultats d'un upload multiple
     *
     * @param mixed $model
     * @param array $results
     */
    protected function processMultipleImageResults($model, array $results): void
    {
        $updateData = [];
        $index = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $updateData["img_{$index}"] = $result['original_path'];
                $updateData["thumb_img_{$index}"] = $result['thumbnail_path'];
                $index++;
            }
        }

        if (!empty($updateData)) {
            $this->repository->update($model, $updateData);
        }
    }

    /**
     * Obtient les statistiques d'images
     *
     * @return array
     */
    public function getImageStats(): array
    {
        return $this->imageService->getStorageStats($this->imageFolder);
    }

    /**
     * Configure les paramètres d'images
     *
     * @param array $config
     * @return self
     */
    public function configureImages(array $config): self
    {
        $this->imageConfig = array_merge($this->imageConfig, $config);
        $this->imageService->configure($this->imageConfig);
        return $this;
    }

    /**
     * Définit le dossier de stockage des images
     *
     * @param string $folder
     * @return self
     */
    public function setImageFolder(string $folder): self
    {
        $this->imageFolder = $folder;
        return $this;
    }

    /**
     * Définit les champs d'images à gérer
     *
     * @param array $fields
     * @return self
     */
    public function setImageFields(array $fields): self
    {
        $this->imageFields = $fields;
        return $this;
    }

    /**
     * Vérifie s'il y a des suppressions d'images (champs null)
     *
     * @param array $validated
     * @return bool
     */
    protected function hasImageDeletions(array $validated): bool
    {
        foreach ($this->imageFields as $field) {
            if (isset($validated[$field]) && $validated[$field] === null) {
                return true;
            }
        }
        return false;
    }
}
