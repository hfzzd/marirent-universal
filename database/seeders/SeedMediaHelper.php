<?php

namespace Database\Seeders;

class SeedMediaHelper
{
    protected static array $index = [];

    public static function resolve(string $subdir, string $name, ?string $model = null, ?string $brand = null): ?string
    {
        $files = self::index($subdir);
        if (!$files) {
            return null;
        }

        $nName = self::norm($name);
        if (isset($files[$nName])) {
            return $files[$nName];
        }

        if ($model) {
            if ($brand) {
                $cand = self::pick($files, self::norm($brand . ' ' . $model));
                if ($cand) {
                    return $cand;
                }
            }
            $cand = self::pick($files, self::norm($model));
            if ($cand) {
                return $cand;
            }
        }

        if ($brand) {
            $cand = self::pick($files, self::norm($brand));
            if ($cand) {
                return $cand;
            }
        }

        return null;
    }

    protected static function index(string $subdir): array
    {
        if (array_key_exists($subdir, self::$index)) {
            return self::$index[$subdir];
        }

        $result = [];
        $dir = storage_path('app/public/Gambar/' . $subdir);
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) {
                if (!is_file($file)) {
                    continue;
                }
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
                    continue;
                }
                $base = pathinfo($file, PATHINFO_FILENAME);
                $rel = 'Gambar/' . $subdir . '/' . basename($file);
                $rel = str_replace(['\\', '//'], '/', $rel);
                $result[self::norm($base)] = $rel;
            }
        }

        return self::$index[$subdir] = $result;
    }

    protected static function pick(array $files, string $needle): ?string
    {
        if ($needle === '') {
            return null;
        }
        if (isset($files[$needle])) {
            return $files[$needle];
        }

        $best = null;
        $bestKey = null;
        foreach ($files as $k => $path) {
            if (str_contains($k, $needle)) {
                $len = strlen($k);
                if ($bestKey === null || $len < strlen($bestKey) || ($len === strlen($bestKey) && $k < $bestKey)) {
                    $best = $path;
                    $bestKey = $k;
                }
            }
        }

        return $best;
    }

    protected static function norm(string $s): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', trim($s)));
    }
}