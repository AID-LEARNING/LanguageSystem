<?php

namespace SenseiTarzan\LanguageSystem\Utils;

class Utils
{
    public static function GetAllModeNested(string|null $key, array $subArray): \Generator
    {
        foreach ($subArray as $index => $data) {
            $realKey = $key ? "$key.$index" : $index;
            if (is_array($data)){
                foreach (self::GetAllModeNested($realKey, $data) as $key => $value) {
                    yield $key => $value;
                }
            } else {
                yield $realKey => $data;
            }
        }
    }


    public static function GetAllKeyNested(array $data): array {
        $keys = [];
        foreach (Utils::GetAllModeNested(null, $data) as $key => $_) {
            $keys[] = $key;
        }
        return $keys;
    }
}