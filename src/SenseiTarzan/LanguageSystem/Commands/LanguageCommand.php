<?php

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
     * @param Plugin $plugin
     * @param string $name
     * @param Translatable|string $description
     * @param array $aliases
     */
    public function __construct(private readonly \WeakReference $languageManager, Plugin $plugin, string $name, Translatable|string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

    protected function prepare(): void
    {
        $this->setPermission("command.change-language.permissions");
        $this->registerSubCommand(new ReloadSubCommand(\WeakReference::create($this->languageManager->get()), $this->plugin,"reload"));
        $this->registerSubCommand(new EditSubCommand(\WeakReference::create($this->languageManager->get()), $this->plugin,"edit"));
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {
            $this->languageManager->get()->getUILanguage($sender);
        }
    }
    public function getPermission(): string
    {
        return "command.change-language.permissions";
    }
}