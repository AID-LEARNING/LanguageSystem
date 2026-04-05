<?php

namespace SenseiTarzan\LanguageSystem\Commands\subCommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class ReloadSubCommand extends BaseSubCommand
{

    public function __construct(private readonly \WeakReference $languageManager, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

    protected function prepare(): void
    {
        $this->setPermission("command.reload-language.permissions");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
       $this->languageManager->get()->loadLanguage();
    }
    public function getPermission(): string
    {
        return "command.reload-language.permissions";
    }
}