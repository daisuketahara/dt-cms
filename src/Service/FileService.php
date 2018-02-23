<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\File;

class FileService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFile($id)
    {
        $file = $this->em->getRepository(File::class)
            ->find($id);

        if ($file) return $file->getFilePath() . $file->getFileName();
        return false;
    }
}
