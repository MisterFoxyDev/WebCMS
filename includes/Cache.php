<?php

class Cache
{
    private static $instance = null;
    private $cacheDir;
    private $defaultTTL = 3600; // 1 heure par défaut

    private function __construct()
    {
        $this->cacheDir = __DIR__ . '/../cache/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        $filename = $this->getCacheFilename($key);
        if (!file_exists($filename)) {
            return null;
        }

        $data = file_get_contents($filename);
        $cache = unserialize($data);

        if ($cache['expires'] < time()) {
            $this->delete($key);
            return null;
        }

        return $cache['data'];
    }

    public function set($key, $data, $ttl = null)
    {
        $filename = $this->getCacheFilename($key);
        $ttl = $ttl ?? $this->defaultTTL;

        $cache = [
            'data' => $data,
            'expires' => time() + $ttl
        ];

        return file_put_contents($filename, serialize($cache)) !== false;
    }

    public function delete($key)
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    public function exists($key)
    {
        $filename = $this->getCacheFilename($key);
        if (!file_exists($filename)) {
            return false;
        }

        $data = file_get_contents($filename);
        $cache = unserialize($data);

        return $cache['expires'] > time();
    }

    private function getCacheFilename($key)
    {
        return $this->cacheDir . md5($key) . '.cache';
    }

    public function getOrSet($key, $callback, $ttl = null)
    {
        $data = $this->get($key);
        if ($data !== null) {
            return $data;
        }

        $data = $callback();
        $this->set($key, $data, $ttl);
        return $data;
    }
}