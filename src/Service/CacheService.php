<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

use App\Entity\Block;
use App\Entity\Locale;

class CacheService
{
    protected $cachePool;
    protected $active = false;
    protected $prefix;

    public function __construct()
    {
        if (isset($_ENV['CACHE_TYPE'])) {
            $this->cacheType = $_ENV['CACHE_TYPE'];
            $this->active = true;
        } else {
            return false;
        }

        $this->prefix = $_SERVER['SERVER_NAME'].'.';

        if ($this->cacheType == 'memcached') {

            if (empty($_ENV['MEMCACHE_HOST'])) {
                $this->active = false;
                return false;
            }

            $client = MemcachedAdapter::createConnection(
                'memcached://' . $_ENV['MEMCACHE_HOST']
                // the DSN can include config options (pass them as a query string):
                // 'memcached://localhost:11222?retry_timeout=10'
                // 'memcached://localhost:11222?socket_recv_size=1&socket_send_size=2'
            );

            $this->cachePool = new MemcachedAdapter(
                // the client object that sets options and adds the server instance(s)
                $client,

                // a string prefixed to the keys of the items stored in this cache
                $namespace = $this->prefix,

                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until MemcachedAdapter::clear() is invoked or the server(s) are restarted)
                $defaultLifetime = 0
            );

        } elseif ($this->cacheType == 'apcu') {

            $this->cachePool = new ApcuAdapter(

                // a string prefixed to the keys of the items stored in this cache
                $namespace = $this->prefix,

                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until the APCu memory is cleared)
                $defaultLifetime = 0,

                // when set, all keys prefixed by $namespace can be invalidated by changing
                // this $version string
                $version = null
            );
        } else {
            $this->cachePool = new FilesystemAdapter(

                // a string used as the subdirectory of the root cache directory, where cache
                // items will be stored
                $namespace = $this->prefix,

                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until the files are deleted)
                $defaultLifetime = 0,

                // the main cache directory (the application needs read-write permissions on it)
                // if none is specified, a directory is created inside the system temporary directory
                $directory = null

            );
        }
    }

    public function has(string $key)
    {
        if ($this->active) if ($this->cachePool->hasItem($key)) return true;
        return false;
    }

    public function get(string $key)
    {
        if ($this->active && $this->has($key)) {
            $item = $this->cachePool->getItem($key);
            return $item->get();
        }

        return false;
    }

    public function set(string $key, $value)
    {
        if ($this->active) {
            $item = $this->cachePool->getItem($key);
            $item->set($value);
            $this->cachePool->save($item);
            return true;
        }
        return false;
    }

    public function delete(string $key)
    {
        if ($this->active) {
            $this->cachePool->deleteItem($key);
            return true;
        }
        return false;
    }

    public function clear()
    {
        if ($this->active) $this->cachePool->clear();
    }


}
