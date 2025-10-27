<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;
use Intervention\Image\ImageManager;

class ImageService
{
    private ImageManager $imageManager;
    private string $disk;
    private array $allowedMimes;
    private int $maxFileSize;
    private array $thumbnailSizes;
    private array $qualitySettings;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->disk = 'public';
        $this->allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/avif'];
        $this->maxFileSize = 5120; // 5MB en KB
        $this->thumbnailSizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600]
        ];
        $this->qualitySettings = [
            'original' => 90,
            'thumbnail' => 85,
            'compressed' => 80
        ];
    }

    /**
     * Upload et traite une image avec génération de miniatures
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $config Configuration personnalisée
     * @return array
     */
    public function uploadImage(UploadedFile $file, string $folder = 'images', array $config = []): array
    {
        try {
            // Appliquer la configuration personnalisée
            $this->applyConfig($config);

            // Validation du fichier
            $this->validateImage($file);

            // Générer un nom unique pour le fichier
            $fileName = $this->generateFileName($file);
            $originalPath = $this->getImagePath($folder, 'originals', $fileName);
            $thumbnailPath = $this->getImagePath($folder, 'thumbnails', $fileName);

            // Optimiser et sauvegarder l'image originale
            $optimizedImage = $this->optimizeImage($file, $config);
            Storage::disk($this->disk)->put($originalPath, $optimizedImage);

            // Générer et sauvegarder la miniature
            $thumbnailImage = $this->generateThumbnail($file, $config);
            Storage::disk($this->disk)->put($thumbnailPath, $thumbnailImage);

            return [
                'success' => true,
                'original_path' => $originalPath,
                'thumbnail_path' => $thumbnailPath,
                'file_name' => $fileName,
                'url' => $this->getImageUrl($originalPath),
                'thumbnail_url' => $this->getImageUrl($thumbnailPath),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload multiple images à la fois
     *
     * @param array $files
     * @param string $folder
     * @param array $config
     * @return array
     */
    public function uploadMultipleImages(array $files, string $folder = 'images', array $config = []): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->uploadImage($file, $folder, $config);
                $results[$index] = $result;

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }

        return [
            'success' => $errorCount === 0,
            'results' => $results,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($files)
        ];
    }

    /**
     * Génère une miniature d'une image
     *
     * @param UploadedFile $file
     * @param array $config
     * @return string
     */
    public function generateThumbnail(UploadedFile $file, array $config = []): string
    {
        $size = $config['thumbnail_size'] ?? $this->thumbnailSizes['medium'];
        $quality = $config['thumbnail_quality'] ?? $this->qualitySettings['thumbnail'];

        $image = $this->imageManager->read($file->getPathname());

        // Redimensionner en gardant les proportions
        $image->cover($size[0], $size[1]);

        // Encoder avec qualité optimisée
        return $image->toJpeg($quality);
    }

    /**
     * Optimise une image (compression, redimensionnement si nécessaire)
     *
     * @param UploadedFile $file
     * @param array $config
     * @return string
     */
    public function optimizeImage(UploadedFile $file, array $config = []): string
    {
        $maxWidth = $config['max_width'] ?? 1920;
        $quality = $config['original_quality'] ?? $this->qualitySettings['original'];

        $image = $this->imageManager->read($file->getPathname());

        // Redimensionner si l'image est trop large
        if ($image->width() > $maxWidth) {
            $image->scaleDown($maxWidth);
        }

        // Encoder avec qualité optimisée
        return $image->toJpeg($quality);
    }

    /**
     * Compresse une image existante
     *
     * @param string $imagePath
     * @param int $quality
     * @return bool
     */
    public function compressImage(string $imagePath, int $quality = 80): bool
    {
        try {
            if (!Storage::disk($this->disk)->exists($imagePath)) {
                return false;
            }

            $fullPath = Storage::disk($this->disk)->path($imagePath);
            $image = $this->imageManager->read($fullPath);

            $compressedImage = $image->toJpeg($quality);
            Storage::disk($this->disk)->put($imagePath, $compressedImage);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprime une image et ses miniatures
     *
     * @param string $imagePath
     * @return bool
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            // Supprimer l'image originale
            if (Storage::disk($this->disk)->exists($imagePath)) {
                Storage::disk($this->disk)->delete($imagePath);
            }

            // Supprimer la miniature correspondante
            $thumbnailPath = $this->getThumbnailPath($imagePath);
            if (Storage::disk($this->disk)->exists($thumbnailPath)) {
                Storage::disk($this->disk)->delete($thumbnailPath);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprime plusieurs images
     *
     * @param array $imagePaths
     * @return array
     */
    public function deleteMultipleImages(array $imagePaths): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($imagePaths as $index => $imagePath) {
            $result = $this->deleteImage($imagePath);
            $results[$index] = $result;

            if ($result) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success' => $errorCount === 0,
            'results' => $results,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($imagePaths)
        ];
    }

    /**
     * Valide un fichier image
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateImage(UploadedFile $file): void
    {
        // Vérifier le type MIME
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            throw new Exception('Type de fichier non autorisé. Types acceptés : JPEG, PNG, WebP, AVIF');
        }

        // Vérifier la taille du fichier
        if ($file->getSize() > ($this->maxFileSize * 1024)) {
            throw new Exception("Le fichier est trop volumineux. Taille maximale : {$this->maxFileSize}KB");
        }

        // Vérifier que c'est bien une image
        if (!getimagesize($file->getPathname())) {
            throw new Exception('Le fichier n\'est pas une image valide');
        }
    }

    /**
     * Applique une configuration personnalisée
     *
     * @param array $config
     */
    private function applyConfig(array $config): void
    {
        if (isset($config['disk'])) {
            $this->disk = $config['disk'];
        }

        if (isset($config['allowed_mimes'])) {
            $this->allowedMimes = $config['allowed_mimes'];
        }

        if (isset($config['max_file_size'])) {
            $this->maxFileSize = $config['max_file_size'];
        }

        if (isset($config['thumbnail_sizes'])) {
            $this->thumbnailSizes = $config['thumbnail_sizes'];
        }

        if (isset($config['quality_settings'])) {
            $this->qualitySettings = array_merge($this->qualitySettings, $config['quality_settings']);
        }
    }

    /**
     * Génère un nom de fichier unique
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y_m_d_H_i_s');
        $randomString = Str::random(8);

        return "{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Construit le chemin complet d'une image
     *
     * @param string $folder
     * @param string $subfolder
     * @param string $fileName
     * @return string
     */
    private function getImagePath(string $folder, string $subfolder, string $fileName): string
    {
        return "{$folder}/{$subfolder}/{$fileName}";
    }

    /**
     * Obtient le chemin de la miniature à partir du chemin original
     *
     * @param string $originalPath
     * @return string
     */
    private function getThumbnailPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Remplacer 'originals' par 'thumbnails' dans le chemin
        $thumbnailDir = str_replace('originals', 'thumbnails', $directory);

        return "{$thumbnailDir}/{$filename}.{$extension}";
    }

    /**
     * Génère l'URL publique d'une image
     *
     * @param string $imagePath
     * @return string
     */
    public function getImageUrl(string $imagePath): string
    {
        return Storage::disk($this->disk)->url($imagePath);
    }

    /**
     * Vérifie si une image existe
     *
     * @param string $imagePath
     * @return bool
     */
    public function imageExists(string $imagePath): bool
    {
        return Storage::disk($this->disk)->exists($imagePath);
    }

    /**
     * Obtient les informations d'une image
     *
     * @param string $imagePath
     * @return array|null
     */
    public function getImageInfo(string $imagePath): ?array
    {
        if (!$this->imageExists($imagePath)) {
            return null;
        }

        $fullPath = Storage::disk($this->disk)->path($imagePath);
        $imageInfo = getimagesize($fullPath);

        return [
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
            'mime_type' => $imageInfo['mime'] ?? '',
            'size' => Storage::disk($this->disk)->size($imagePath),
            'url' => $this->getImageUrl($imagePath)
        ];
    }

    /**
     * Obtient les statistiques d'utilisation du stockage
     *
     * @param string $folder
     * @return array
     */
    public function getStorageStats(string $folder): array
    {
        $originalPath = "{$folder}/originals";
        $thumbnailPath = "{$folder}/thumbnails";

        $originalFiles = Storage::disk($this->disk)->files($originalPath);
        $thumbnailFiles = Storage::disk($this->disk)->files($thumbnailPath);

        $originalSize = 0;
        $thumbnailSize = 0;

        foreach ($originalFiles as $file) {
            $originalSize += Storage::disk($this->disk)->size($file);
        }

        foreach ($thumbnailFiles as $file) {
            $thumbnailSize += Storage::disk($this->disk)->size($file);
        }

        return [
            'original_count' => count($originalFiles),
            'thumbnail_count' => count($thumbnailFiles),
            'original_size' => $originalSize,
            'thumbnail_size' => $thumbnailSize,
            'total_size' => $originalSize + $thumbnailSize,
            'space_saved' => $originalSize - $thumbnailSize
        ];
    }

    /**
     * Configure les paramètres par défaut du service
     *
     * @param array $config
     * @return self
     */
    public function configure(array $config): self
    {
        $this->applyConfig($config);
        return $this;
    }
}
