<?php

namespace SenseiTarzan\LanguageSystem\Commands\subCommand;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use WeakReference;

class EditSubCommand extends BaseSubCommand
{
    /**
     * @param WeakReference<LanguageManager> $languageManager
     * @param PluginBase $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(private readonly WeakReference $languageManager, PluginBase $plugin, string $name, string $description = "", array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $aliases);
    }

    protected function prepare(): void
    {
        $this->setPermission("command.edit-language.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {
            $this->languageManager->get()->EditUIIndex($sender);
        }
    }
    public function getPermission(): string
    {
        return "command.edit-language.permissions";
    }
}