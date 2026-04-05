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

namespace SenseiTarzan\LanguageSystem\Commands\subCommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class ReloadSubCommand extends BaseSubCommand
{

	public function __construct(private readonly \WeakReference $languageManager, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
	{
		parent::__construct($plugin, $name, $description, $aliases);
	}

	protected function prepare() : void
	{
		$this->setPermission("command.reload-language.permissions");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void
	{
	   $this->languageManager->get()->loadLanguage();
	}
	public function getPermission() : string
	{
		return "command.reload-language.permissions";
	}
}
