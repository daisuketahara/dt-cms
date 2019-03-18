<?php

namespace App\Service;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

use App\Entity\Block;
use App\Entity\Locale;

class CacheService
{
    protected $cache;
    protected $prefix;

    public function __construct()
    {
        $this->cache = new FilesystemCache();
        $this->prefix = $_SERVER['SERVER_NAME'].'.';
    }


    public function has($key)
    {
        if ($this->cache->has($this->prefix.$key)) return true;
        return false;
    }

    public function get($key)
    {
        if ($this->has($key)) {
            $value = $this->cache->get($this->prefix.$key);
            return $value;
        }
        return false;
    }

    public function set($key, $value)
    {
        $this->cache->set($this->prefix.$key, $value);
        return true;
    }

    public function delete($key)
    {
        if ($this->has($key)) $this->cache->delete($this->prefix.$key);
        return true;
    }

    public function clear()
    {
        $this->cache->clear();
        return true;
    }


}
