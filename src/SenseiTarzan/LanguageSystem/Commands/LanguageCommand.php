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

namespace SenseiTarzan\LanguageSystem\Commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use SenseiTarzan\LanguageSystem\Commands\subCommand\EditSubCommand;
use SenseiTarzan\LanguageSystem\Commands\subCommand\ReloadSubCommand;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class LanguageCommand extends BaseCommand
{

	/**
	 * @param \WeakReference<LanguageManager> $languageManager
	 */
	public function __construct(private readonly \WeakReference $languageManager, Plugin $plugin, string $name, Translatable|string $description = "", array $aliases = [])
	{
		parent::__construct($plugin, $name, $description, $aliases);
	}

	protected function prepare() : void
	{
		$this->setPermission("command.change-language.permissions");
		$this->registerSubCommand(new ReloadSubCommand(\WeakReference::create($this->languageManager->get()), $this->plugin,"reload"));
		$this->registerSubCommand(new EditSubCommand(\WeakReference::create($this->languageManager->get()), $this->plugin,"edit"));
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void
	{
		if($sender instanceof Player) {
			$this->languageManager->get()->getUILanguage($sender);
		}
	}
	public function getPermission() : string
	{
		return "command.change-language.permissions";
	}
}
