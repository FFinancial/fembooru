<?php declare(strict_types=1);


abstract class CronUploaderConfig
{
    const DEFAULT_PATH = "cron_uploader";

    const KEY = "cron_uploader_key";
    const COUNT = "cron_uploader_count";
    const DIR = "cron_uploader_dir";
    const USER = "cron_uploader_user";

    public static function set_defaults(): void
    {
        global $config;
        $config->set_default_int(self::COUNT, 1);
        $config->set_default_string(self::DIR, data_path(self::DEFAULT_PATH));

        $upload_key = $config->get_string(self::KEY, "");
        if (empty($upload_key)) {
            $upload_key = self::generate_key();

            $config->set_string(self::KEY, $upload_key);
        }
    }

    public static function get_user(): int
    {
        global $config;
        return $config->get_int(self::USER);
    }

    public static function set_user(int $value): void
    {
        global $config;
        $config->set_int(self::USER, $value);
    }

    public static function get_key(): string
    {
        global $config;
        return $config->get_string(self::KEY);
    }

    public static function set_key(string $value): void
    {
        global $config;
        $config->set_string(self::KEY, $value);
    }

    public static function get_count(): int
    {
        global $config;
        return $config->get_int(self::COUNT);
    }

    public static function set_count(int $value): void
    {
        global $config;
        $config->set_int(self::COUNT, $value);
    }

    public static function get_dir(): string
    {
        global $config;
        $value = $config->get_string(self::DIR);
        if (empty($value)) {
            $value = data_path("cron_uploader");
            self::set_dir($value);
        }
        return $value;
    }

    public static function set_dir(string $value): void
    {
        global $config;
        $config->set_string(self::DIR, $value);
    }


    /*
     * Generates a unique key for the website to prevent unauthorized access.
     */
    private static function generate_key()
    {
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters [rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
