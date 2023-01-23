<?php

namespace SenseiTarzan\LanguageSystem\Component;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\LanguageSystem\Class\Language;
use SenseiTarzan\LanguageSystem\Commands\languageCommand;

class LanguageManager
{

    use SingletonTrait;

    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var Config
     */
    private Config $data;
    /**
     * @var PluginBase
     */
    private Plugin $plugin;
    /**
     * @var Language[]
     */
    private array $language = [];

    private Language $defaultLanguage;

    public function __construct(Plugin $pl)
    {
        if (self::$instance !== null) {
            throw new PluginException("the " . self::getInstance()->getPlugin()->getName() . " plugin has already created an instance");
        }
        self::setInstance($this);
        @mkdir($pl->getDataFolder() . "Language/");
        @mkdir($pl->getDataFolder() . "Language/data");
        $this->plugin = $pl;
        $this->config = new Config($pl->getDataFolder() . 'Language/config.yml', Config::YAML);
        $this->data = new Config($pl->getDataFolder() . 'Language/data.json', Config::JSON);
        $this->loadLanguage();
    }

    public function loadLanguage(): void
    {
        $this->language = [];
        unset($this->defaultLanguage);
        foreach ($this->config->getAll() as $name => $info) {
            $this->language[$info["mini"]] = new Language($this->plugin, $name, $info["mini"], $info["image"] ?? "textures\blocks\barrier", ($info["path"] ?? "Language/data/$name.ini"));

            if (!isset($this->defaultLanguage) && ($info['default'] ?? false) === true) {
                $this->defaultLanguage = new Language($this->plugin, $name, $info["mini"], $info["image"] ?? "textures\blocks\barrier", ($info["path"] ?? "Language/data/$name.ini"));
            }
        }
        if (!isset($this->defaultLanguage)) {
            throw new PluginException("the " . $this->getPlugin()->getName() . " plugin has no default language");
        }
    }

    public function loadCommands(string $name): void{
        ($permissionManager = PermissionManager::getInstance())->addPermission(new Permission("command.change-language.permissions","language exchange authorization"));
        $permissionManager->addPermission(new Permission("command.reload-language.permissions","reload language authorization "));
        $permissionManager->getPermission(DefaultPermissions::ROOT_USER)->addChild("command.change-language.permissions", true);
        $permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild("command.reload-language.permissions", true);
        $this->plugin->getServer()->getCommandMap()->register("senseitarzan", new languageCommand($this->plugin, "language-" . $name = strtolower($name), aliases: [
            "lang-" . $name
        ]));
    }

    /**
     * @return Language[]
     */
    public function getAllLang(): array
    {
        return $this->language;
    }

    /**
     * @param Player $player
     */
    public function getUILanguage(Player $player): void
    {
        $all_lang = $this->getAllLang();
        $ui = new SimpleForm(function (Player $player, ?string $data) use ($all_lang): void {
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
     * @param CommandSender|string $player
     * @param string $cat
     * @param array|null $labels
     * @param mixed|string $default
     * @return string|array
     */
    public function getTranslate(CommandSender|string $player, string $cat, ?array $labels = [], mixed $default = ''): string|array
    {
        if (is_string($player)) {
            $player = Server::getInstance()->getPlayerExact($player) ?? $player;
        }
        return $this->getLanguagePlayer($player)->translate($cat, $labels, $default) ?? $default;
    }

    /**
     * @param CommandSender|string $player
     * @param Translatable $translatable
     * @param mixed|string $default
     * @return string|array
     */
    public function getTranslateWithTranslatable(CommandSender|string $player, Translatable $translatable, mixed $default = ''): string|array
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

    /**
     * @param string|CommandSender $player
     * @return Language
     */
    public function getLanguagePlayer(CommandSender|string $player): Language
    {
        return $this->getLanguage($this->data->get(is_string($player) ? $player : $player->getName(), "fra"));
    }


    public function getLanguage(string $languageName): Language
    {
        return $this->language[$languageName] ?? $this->defaultLanguage;
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }


}