<?php

/*
 *
 *            _____ _____         _      ______          _____  _   _ _____ _   _  _____
 *      /\   |_   _|  __ \       | |    |  ____|   /\   |  __ \| \ | |_   _| \ | |/ ____|
 *     /  \    | | | |  | |______| |    | |__     /  \  | |__) |  \| | | | |  \| | |  __
 *    / /\ \   | | | |  | |______| |    |  __|   / /\ \ |  _  /| . ` | | | | . ` | | |_ |
 *   / ____ \ _| |_| |__| |      | |____| |____ / ____ \| | \ \| |\  |_| |_| |\  | |__| |
 *  /_/    \_\_____|_____/       |______|______/_/    \_\_|  \_\_| \_|_____|_| \_|\_____|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author AID-LEARNING
 * @link https://github.com/AID-LEARNING
 *
 */

declare(strict_types=1);

namespace SenseiTarzan\LanguageSystem\Utils;

use function is_array;

class Utils
{
	public static function GetAllModeNested(string|null $key, array $subArray) : \Generator
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

	public static function GetAllKeyNested(array $data) : array {
		$keys = [];
		foreach (Utils::GetAllModeNested(null, $data) as $key => $_) {
			$keys[] = $key;
		}
		return $keys;
	}
}
