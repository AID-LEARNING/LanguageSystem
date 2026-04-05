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

namespace SenseiTarzan\LanguageSystem\Component;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\Config;
use RuntimeException;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\LanguageSystem\Class\Language;
use SenseiTarzan\LanguageSystem\Commands\LanguageCommand;
use SenseiTarzan\LanguageSystem\Listener\PacketListener;
use WeakReference;
use function is_string;
use function mb_strtolower;
use function mkdir;
use function strtolower;

class LanguageManager
{

	private Config $config;
	private Config $data;
	/** @var WeakReference<PluginBase> */
	private WeakReference $plugin;
	/** @var array<string, Language> */
	private array $language = [];

	/** @var WeakReference<Language> */
	private WeakReference $defaultLanguage;

	public function __construct(PluginBase $pl)
	{
		@mkdir($pl->getDataFolder() . "Language/");
		@mkdir($pl->getDataFolder() . "Language/data");
		$this->plugin = WeakReference::create($pl);
		$this->config = new Config($pl->getDataFolder() . 'Language/config.yml', Config::YAML);
		$this->data = new Config($pl->getDataFolder() . 'Language/data.json', Config::JSON);
		$this->loadLanguage();
	}

	public function loadLanguage() : void
	{
		$this->language = [];
		unset($this->defaultLanguage);
		foreach ($this->config->getAll() as $name => $info) {
			$this->language[$info["mini"]] = $lang = new Language(WeakReference::create($this->plugin->get()), $name, $info["mini"], $info["image"] ?? "textures\blocks\barrier", ($info["path"] ?? "Language/data/$name.ini"));

			if (!isset($this->defaultLanguage) && ($info['default'] ?? false) === true) {
				$this->defaultLanguage = WeakReference::create($lang);
			}
		}
		if (!isset($this->defaultLanguage)) {
			throw new PluginException("the " . $this->getPlugin()->getName() . " plugin has no default language");
		}
	}

	public function loadCommands(string $name) : void{
		($permissionManager = PermissionManager::getInstance())->addPermission(new Permission("command.change-language.permissions","language exchange authorization"));
		$permissionManager->addPermission(new Permission("command.reload-language.permissions","reload language authorization "));
		$permissionManager->addPermission(new Permission("command.edit-language.permissions","reload language authorization "));
		$permissionManager->getPermission(DefaultPermissions::ROOT_USER)->addChild("command.change-language.permissions", true);
		$permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild("command.reload-language.permissions", true);
		$permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild("command.edit-language.permissions", true);
		$this->getPlugin()->getServer()->getCommandMap()->register(mb_strtolower($this->getPlugin()->getName()), new LanguageCommand(WeakReference::create($this), $this->getPlugin(), "language-" . ($name = strtolower($name)), aliases: [
			"lang-" . $name
		]));
		EventLoader::loadEventWithClass($this->getPlugin(), new PacketListener(WeakReference::create($this)));
	}

	/**
	 * @return Language[]
	 */
	public function getAllLang() : array
	{
		return $this->language;
	}

	public function getUILanguage(Player $player) : void
	{
		$all_lang = $this->getAllLang();
		$ui = new SimpleForm(function (Player $player, ?string $data) use ($all_lang) : void {
			if ($data === null) {
				return;
			}
			$this->data->set($player->getName(), $data);
			$this->data->save();
			$player->sendMessage($this->getTranslate($player, "Language.change", ["language" => $this->getLanguage($data)->getName()], "You have taken the language {&language}"));
		});

		foreach ($all_lang as $lang) {
			$ui->addButton($lang->getName(), $lang->getImage()->getType(), $lang->getImage()->getPath(), $lang->getMini());
		}
		$player->sendForm($ui);
	}

	/**
	 * @param mixed|string $default
	 */
	public function getTranslate(CommandSender|string $player, string $cat, ?array $labels = [], mixed $default = '') : string|array
	{
		if (is_string($player)) {
			$player = Server::getInstance()->getPlayerExact($player) ?? $player;
		}
		return $this->getLanguagePlayer($player)->translate($cat, $labels, $default) ?? $default;
	}

	public function getTranslateModeNoSaveDefault(CommandSender|string $player, string $cat, ?array $labels = [], mixed $default = '') : string|array
	{
		if (is_string($player)) {
			$player = Server::getInstance()->getPlayerExact($player) ?? $player;
		}
		return $this->getLanguagePlayer($player)->translateModeNoSaveDefault($cat, $labels, $default) ?? $default;
	}

	/**
	 * @param mixed|string $default
	 */
	public function getTranslateModeNoSaveDefaultWithTranslatable(CommandSender|string $player, Translatable $translatable, mixed $default = '') : string|array
	{
		if (is_string($player)) {
			$player = Server::getInstance()->getPlayerExact($player) ?? $player;
		}
		$labels = [];
		foreach ($translatable->getParameters() as $i => $p) {
			$labels[$i] = $p instanceof Translatable ? $this->getTranslateWithTranslatable($player, $p) : $p;
		}
		return $this->getTranslateModeNoSaveDefault($player,$translatable->getText(), $labels, $default);
	}

	/**
	 * @param mixed|string $default
	 */
	public function getTranslateWithTranslatable(CommandSender|string $player, Translatable $translatable, mixed $default = '') : string|array
	{
		if (is_string($player)) {
			$player = Server::getInstance()->getPlayerExact($player) ?? $player;
		}
		$labels = [];
		foreach ($translatable->getParameters() as $i => $p) {
			$labels[$i] = $p instanceof Translatable ? $this->getTranslateWithTranslatable($player, $p) : $p;
		}
		return $this->getTranslate($player,$translatable->getText(), $labels, $default);
	}

	public function getLanguagePlayer(CommandSender|string $player) : Language
	{
		return $this->getLanguage($this->data->get(is_string($player) ? $player : $player->getName(), "fra"));
	}

	public function getLanguage(string $languageName) : Language
	{
		return $this->language[$languageName] ?? $this->defaultLanguage->get();
	}

	public function EditUIIndex(Player $player) : void
	{
		$all_lang = $this->getAllLang();
		$ui = new SimpleForm(function (Player $player, ?string $langId) : void {
			if ($langId === null) {
				return;
			}
			$this->EditUI($player, $this->language[$langId]);
		});
		$ui->setTitle("Edit Language");
		foreach ($all_lang as $lang) {
			$ui->addButton($lang->getName(), $lang->getImage()->getType(), $lang->getImage()->getPath(), $lang->getMini());
		}
		$player->sendForm($ui);
	}

	public function EditUI(CommandSender|string $player, Language $language) : void
	{
		$ui = new SimpleForm(function (Player $player, ?string $data) use($language) : void {
			if ($data === null) {
				return;
			}
			if($data == "edit") {
				$language->selectEditKey($player, 1);
			}elseif($data == "delete"){
				$language->selectEditKey($player, 2);
			} elseif($data == "create") {
				$language->addTranslateUI($player);
			}
		});
		$ui->setTitle("Edit Language");
		$ui->addButton("Create new Translate", label: "create");
		$ui->addButton("Edit Translate", label: "edit");
		$ui->addButton("Delete Translate", label: "delete");
		$player->sendForm($ui);
	}

	/**
	 * @throws \Exception
	 */
	public function getPlugin() : PluginBase
	{
		return $this->plugin->get() ?? throw new RuntimeException("plugin null");
	}

}
