<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;


use Doctrine\ORM\EntityManager;
use App\Entity\File;

class FileService
{
    protected $em;
    protected $container;
    private $targetDirectory;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->setTargetDirectory();
        $this->em = $em;
        $this->container = $container;
    }

    public function upload(UploadedFile $uploadedFile, int $group=0, bool $hide=false)
    {
        $filePath = $this->get('kernel')->getProjectDir() . '/';
        if (empty($hide)) $filePath .= 'public/';

        $envPath = getenv('FILES_PATH');
        $path = 'files/';
        if (!empty($envPath)) $path .= $envPath . '/';
        if (!empty($group)) $path .= $path . '/';

        $fileSystem = new Filesystem();
        $fileSystem->mkdir($filePath . $path);

        $this->targetDirectory = $path;

        $fileName = md5(uniqid()).'.'.$uploadedFile->guessExtension();
        $fileSize = $uploadedFile->getClientSize();
        $fileType = $uploadedFile->getMimeType();
        $uploadedFile->move($filePath . $path, $fileName);

        $file = new File();
        //$file->setGroup($group);
        $file->setUserId(0);
        $file->setName($uploadedFile->getClientOriginalName());
        $file->setFileName($fileName);
        $file->setFilePath($path);
        $file->setFileSize($fileSize);
        $file->setFileType($fileType);
        $file->setHidden($hide);
        $file->setActive(true);

        $this->em->persist($file);
        $this->em->flush();
        $fileId = $file->getId();

        return true;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function setTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function getFile(int $id)
    {
        $file = $this->em->getRepository(File::class)
            ->find($id);

        if ($file) return '/' . $file->getFilePath() . $file->getFileName();
        return false;
    }

    public function slugify(string $text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) return 'n-a';
        return $text;
    }
}
