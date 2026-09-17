<?php

namespace App\Helpers;

class AppSetting
{
    protected static function getSettingsPath(): string
    {
        return storage_path('app/app_settings.json');
    }

    public static function all(): array
    {
        $path = static::getSettingsPath();
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return array_merge(static::defaults(), $data);
            }
        }

        return static::defaults();
    }

    public static function get(string $key, $default = null)
    {
        $all = static::all();

        return $all[$key] ?? $default;
    }

    public static function set(array $settings): void
    {
        $current = static::all();
        $updated = array_merge($current, $settings);
        file_put_contents(static::getSettingsPath(), json_encode($updated, JSON_PRETTY_PRINT));
    }

    public static function defaults(): array
    {
        return [
            'app_name' => config('app.name', 'Assetika'),
            'app_slogan' => 'Enterprise Asset & Maintenance Management System',
            'company_name' => 'PT Diastika Biotekindo Tbk',
            'footer_text' => 'Assetika. All rights reserved.',
            'logo_light' => '/images/assetika.png',
            'logo_dark' => '/images/assetika.png',
            'favicon' => '/images/favicon_assetika.png',
        ];
    }
}
