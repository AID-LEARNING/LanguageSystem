<?php

namespace SenseiTarzan\LanguageSystem\Commands\subCommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class reloadSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("command.reload-language.permissions");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        LanguageManager::getInstance()->loadLanguage();
    }
    public function getPermission(): string
    {
        return "command.reload-language.permissions";
    }
}