<?php

namespace DestroBundle\Factory\Resolver;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\GoogleCloudStorage;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class GoogleStorageResolver implements ResolverInterface
{
    private $service;
    private $bucket;
    private $directory;
    private $acl = "public";

    /**
     * GoogleStorageResolver constructor.
     * @param \Google_Service_Storage                                   $service
     * @param array                                                     $settings
     */
    public function __construct(\Google_Service_Storage $service, array $settings = array())
    {
        $missingKey = isset($settings["Bucket"])? "Directory" : "Bucket";

        if(!array_key_exists("Bucket", $settings) || !array_key_exists("Directory", $settings))
        {
            throw new UndefinedOptionsException(sprintf("The %s must be configured", $missingKey));
        }

        if(array_key_exists("Acl", $settings))
        {
            $this->acl = $settings["Acl"];
        }

        $this->bucket = $settings["Bucket"];
        $this->directory = $settings["Directory"];
        $this->service = $service;
    }

    private function getOptions()
    {
        $options = array(
            'acl' => $this->acl,
            'directory' => $this->directory
        );

        return $options;
    }

    private function getStorage()
    {
        return New GoogleCloudStorage($this->service, $this->bucket, $this->getOptions(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter){
        return $this->objectExists($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter){
        return $this->getObjectUrl($this->getObjectPath($path, $filter));
    }


    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store.
     * @param string          $path   The path where the original file is expected to be.
     * @param string          $filter The name of the imagine filter in effect.
     */
    public function store(BinaryInterface $binary, $path, $filter){

        $filesystem = new Filesystem($this->getStorage());

        try{
            $filesystem->write($this->getObjectPath($path, $filter), $binary->getContent(), true);

        } catch (\Exception $e) {

            throw new NotStorableException('The object could not be created on Google Cloud Storage.', null, $e);
        }
    }

    /**
     * @param string[] $paths   The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     */
    public function remove(array $paths, array $filters)
    {
        $storage = New GoogleCloudStorage($this->service, $this->bucket);
        $filesystem = new Filesystem($storage);

        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            foreach ($filters as $filter)
            {
                $directory = sprintf("%s/cache/%s", $this->directory,  $filter);
                $files = $storage->listKeys($directory);

                foreach ($files as $file)
                {
                    if($storage->exists($file))
                    {
                        if(!$filesystem->delete($file)){
                            throw new NotStorableException('The object could not be deleted on Google Cloud Storage.', null);
                        }
                    }
                }
            }
        }


        foreach ($filters as $filter)
        {
            foreach ($paths as $path)
            {
                $directory = sprintf("%s/cache/%s", $path,  $filter);
                $files = $storage->listKeys($directory);

                foreach ($files as $file)
                {
                    if($storage->exists($file))
                    {
                        if(!$filesystem->delete($file)){
                            throw new NotStorableException('The object could not be deleted on Google Cloud Storage.', null);
                        }
                    }
                }
            }
        }
    }


    /**
     * Returns the object path within the bucket.
     *
     * @param string $path   The base path of the resource.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The path of the object on Google Cloud Storage.
     */
    public function getObjectPath($path, $filter){

        return str_replace('//', '/', sprintf('cache/%s/%s', $filter, $path));
    }

    /**
     * @param $path
     * @return string
     */
    public function getObjectUrl($path){

        $media = $this->service->objects->get($this->bucket, sprintf("%s/%s", $this->directory, $path));
        return $media->getMediaLink();

    }

    /**
     * @param $objectPath
     * @return bool
     */
    protected function objectExists($objectPath){
        return $this->getStorage()->exists($objectPath) ? true : false;
    }
}