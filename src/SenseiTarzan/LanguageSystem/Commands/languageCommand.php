<?php

namespace SenseiTarzan\LanguageSystem\Commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use SenseiTarzan\LanguageSystem\Commands\subCommand\reloadSubCommand;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class languageCommand extends BaseCommand
{


    protected function prepare(): void
    {
        $this->setPermission("command.change-language.permissions");
        $this->registerSubCommand(new reloadSubCommand($this->plugin,"reload"));
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        LanguageManager::getInstance()->getUILanguage($sender);
    }
    public function getPermission(): string
    {
        return "command.change-language.permissions";
    }
}