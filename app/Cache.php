<?php
namespace app;

class Cache {
    private string $dir;
    private int $maxSizeMB;
    private int $maxFiles;

    public function __construct(string $dir, int $maxSizeMB = 100, int $maxFiles = 10000) {
        $this->dir = rtrim($dir, '/');
        $this->maxSizeMB = $maxSizeMB;
        $this->maxFiles = $maxFiles;

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function get(string $key): mixed {
        $file = $this->getPath($key);

        if (!file_exists($file)) {
            return null;
        }

        $contents = @file_get_contents($file);
        if ($contents === false) {
            return null;
        }

        $data = @unserialize($contents);

        if (!$data || !isset($data['expires_at']) || $data['expires_at'] < time()) {
            @unlink($file);
            return null;
        }

        touch($file);
        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool {
        // Probabilistic cleanup (1% chance)
        if (rand(1, 100) === 1) {
            $this->probabilisticCleanup();
        }

        // Check limits
        if ($this->isOverLimit()) {
            $this->evictOldest();
        }

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
        ];

        $file = $this->getPath($key);
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    private function getPath(string $key): string {
        $hash = md5($key);
        $dir1 = $hash[0];
        $dir2 = $hash[1];
        return $this->dir . "/{$dir1}/{$dir2}/{$hash}.cache";
    }

    private function probabilisticCleanup(): void {
        $pattern = $this->dir . '/*/*/*\.cache';
        $files = glob($pattern);

        if (empty($files)) return;

        $sample = array_rand($files, min(10, count($files)));

        foreach ((array) $sample as $index) {
            $file = $files[$index];
            $contents = @file_get_contents($file);
            if (!$contents) continue;

            $data = @unserialize($contents);
            if (!$data || !isset($data['expires_at']) || $data['expires_at'] < time()) {
                @unlink($file);
            }
        }
    }

    private function isOverLimit(): bool {
        $pattern = $this->dir . '/*/*/*\.cache';
        $files = glob($pattern);

        if (count($files) > $this->maxFiles) {
            return true;
        }

        if (count($files) < 100) {
            $totalSize = array_sum(array_map('filesize', $files));
        } else {
            $sample = array_rand($files, 100);
            $sampleSize = 0;
            foreach ((array) $sample as $index) {
                $sampleSize += @filesize($files[$index]);
            }
            $totalSize = ($sampleSize / 100) * count($files);
        }

        return ($totalSize / 1024 / 1024) > $this->maxSizeMB;
    }

    private function evictOldest(): void {
        $pattern = $this->dir . '/*/*/*\.cache';
        $files = glob($pattern);

        usort($files, fn($a, $b) => fileatime($a) <=> fileatime($b));

        $toDelete = max(1, (int)(count($files) * 0.1));

        for ($i = 0; $i < $toDelete; $i++) {
            @unlink($files[$i]);
        }
    }
}
