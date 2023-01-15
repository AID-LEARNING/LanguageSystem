<?php

namespace SenseiTarzan\LanguageSystem\Component;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\LanguageSystem\Class\Language;
use SenseiTarzan\LanguageSystem\Main;

class LanguageManager
{

    use SingletonTrait;

    /**
     * @var Config
     */
    public Config $config;
    /**
     * @var Config
     */
    public Config $data;
    /**
     * @var PluginBase
     */
    public Plugin $plugin;
    /**
     * @var Language[]
     */
    private array $language = [];

    public function __construct(Plugin $pl)
    {
        self::setInstance($this);
        @mkdir($pl->getDataFolder() . "Language/");
        @mkdir($pl->getDataFolder() . "Language/data");
        $this->plugin = $pl;
        $this->config = new Config($pl->getDataFolder() . 'Language/config.yml', Config::YAML);
        $this->data = new Config($pl->getDataFolder() . 'Language/data.json', Config::JSON);
        $this->loadLanguage();
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function loadLanguage(): void
    {
        foreach ($this->config->getAll() as $name => $info){
            if (isset($info['default']) && $info['default'] === true && !isset($this->language['default'])){
                $this->language['default'] = new Language($this->plugin, $name, $info["mini"], $info["image"] ?? "textures\blocks\barrier", ($info["path"] ?? "Language/data/$name.ini"));
            }
            $this->language[$info["mini"]] = new Language($this->plugin, $name, $info["mini"], $info["image"] ?? "textures\blocks\barrier", ($info["path"] ?? "Language/data/$name.ini"));
        }
    }

    /**
     * @param Player $player
     */
    public function getUILanguage(Player $player): void
    {
        $all_lang = $this->getAllLang();
        $ui = new SimpleForm(function (Player $player, $data) use ($all_lang){
            if ($data === null){
                return;
            }
            $lang = $all_lang[$data]?->getMini();
            $this->data->set($player->getName(), $lang);
            $this->data->save();
            $player->sendMessage($this->getTranslate($lang, "Language.change", [], ["language" => $all_lang[$data]]));
        });

        foreach ($all_lang as $lang){
            $ui->addButton($lang->getName(), SimpleForm::IMAGE_TYPE_PATH, $lang->getImage());
        }
        $player->sendForm($ui);
    }

    /**
     * @return array
     */
    public function getAllLang(): array
    {
        return $this->language;
    }

    /**
     * @param Player|string|CommandSender|ConsoleCommandSender $player
     * @param string $cat
     * @param array|null $labels
     * @param mixed|string $default
     * @return string|array
     */
    public function getTranslate(Player|string|CommandSender|ConsoleCommandSender $player, string $cat, ?array $labels = [], mixed $default = ''): string|array
    {
        if (is_string($player)){
            $player = Server::getInstance()->getPlayerExact($player) ?? $player;
        }
        return $this->getLanguagePlayer($player)?->translate($cat,$labels,$default);
    }

    /**
     * @param Player|string|CommandSender|ConsoleCommandSender $player
     * @param Translatable $translatable
     * @param mixed|string $default
     * @return string|array
     */
    public function getTranslateWithTranslatable(Player|string|CommandSender|ConsoleCommandSender $player,Translatable $translatable, mixed $default = ''): string|array
    {
        if (is_string($player)){
            $player = Server::getInstance()->getPlayerExact($player) ?? $player;
        }
        $labels = [];
        foreach($translatable->getParameters() as $i => $p){
            $labels[$i] = $p instanceof Translatable ? $this->getTranslateWithTranslatable($player, $p) : $p;
        }
        return $this->getLanguagePlayer($player)?->translate($translatable->getText(),$labels,$default);
    }

    /**
     * @param Player|string|CommandSender|ConsoleCommandSender $player
     * @return Language|null
     */
    public function getLanguagePlayer(Player|string|CommandSender|ConsoleCommandSender $player): ?Language
    {
        return $this->getLanguage($this->data->get(is_string($player) ? $player : $player->getName(), "fra"));
    }


    public function getLanguage(string $languageName): Language{
        return $this->language[$languageName] ?? $this->language['default'];
    }

}