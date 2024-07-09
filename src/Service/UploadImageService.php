<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadImageService
{
    private string $imageDirectory;
    private SluggerInterface $slugger;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger)
    {
        $this->imageDirectory = $params->get('image_directory');
        $this->slugger = $slugger;
    }

    public function handleImage(?UploadedFile $image): ?string
    {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

            try {
                $image->move($this->imageDirectory, $newFilename);
            } catch (FileException $e) {
                throw new FileException($e->getMessage());
            }

            return $newFilename;
        }

}