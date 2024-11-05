<?php

namespace App\Services;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class VideoCache
{
    protected string $baseDir;
    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->baseDir = getenv('CACHE_DIR') . 'video_cache/';
        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->baseDir));
    }

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    public function get(string $uuid, int $start, int $end): ?string
    {
        if ($this->has($uuid, $start, $end)) {
            $key = $uuid . DIRECTORY_SEPARATOR . "$start-$end";

            return $this->filesystem->read($key);
        }

        return null;
    }

    public function set(string $uuid, int $start, int $end, string $value): void
    {
        $key = $uuid . DIRECTORY_SEPARATOR . "$start-$end";
        $this->filesystem->write($key, $value);
    }

    public function delete(string $uuid): void
    {
        $this->filesystem->deleteDirectory($uuid);
    }

    public function has(string $uuid, int $start, int $end): bool
    {
        $key = $uuid . DIRECTORY_SEPARATOR . "$start-$end";

        return $this->filesystem->has($key);
    }

    public function clear(): void
    {
        $max = getenv('CACHE_S3_SIZE') * 1024 * 1024; // Bytes
        $used = explode("\t", shell_exec("du -sb $this->baseDir"))[0];

        $diff = $used - $max;
        if ($diff > 0) {
            $dirs = explode(PHP_EOL, shell_exec("ls -tru1 $this->baseDir"));
            foreach ($dirs as $dir) {
                $size = explode("\t", shell_exec("du -sb $this->baseDir$dir"))[0];
                $diff -= $size;
                shell_exec("rm -rf $this->baseDir$dir");
                if ($diff <= 0) {
                    break;
                }
            }
        }
    }
}