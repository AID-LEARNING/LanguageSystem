<?php

namespace SenseiTarzan\LanguageSystem\Class;


use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use SenseiTarzan\IconUtils\IconForm;
use SenseiTarzan\LanguageSystem\Utils\Utils;
use SenseiTarzan\Path\Config;
use WeakReference;

class Language
{
    private const NEW_LINES_STRING = ['%n', '\n'];
    public const NO_EXIST_TRANSLATE = '3a5c4c91-8456-48bc-aa28-820311398941';

    private string $name;
    private string $mini;
    private IconForm $image;
    private Config $config;
    /**
     * @var WeakReference<Plugin>
     */
    private WeakReference $plugin;

    public function __construct(WeakReference $plugin, string $name, string $mini, string $image, string $path)
    {
        $this->plugin = $plugin;
        $this->name = $name;
        $this->mini = $mini;
        $this->image = IconForm::create($image);
        $this->config = new Config($this->plugin->get()->getDataFolder() . $path, Config::INI);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMini(): string
    {
        return $this->mini;
    }

    public function getImage(): IconForm
    {
        return $this->image;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function translate(string $cat, ?array $labels = null, mixed $default = null): array|string
    {
        $search = [...self::NEW_LINES_STRING];
        $replace = [PHP_EOL, PHP_EOL];
        if (is_null($labels)) {
            $labels = [];
        }
        if (!empty($labels)) {
            if (array_is_list($labels)) {
                for ($i = 0; $i < count($labels); ++$i) {
                    $search[] = '&' . ($i + 1);
                    $replace[] = $labels[$i];
                }
            } else {
                foreach ($labels as $sea => $rep) {
                    $search[] = "{&" . $sea . '}';
                    $replace[] = $rep;
                }
            }
        }
        $msg = $this->getConfig()->getNested($cat);
        if (is_null($msg)) {
            $msg = $default ?? $cat;
            $this->getConfig()->setNested($cat, $msg);
            $this->getConfig()->save();
        }
        return str_replace($search, $replace, $msg);
    }

    public function translateModeNoSaveDefault(string $cat, ?array $labels = null, mixed $default = null): array|string
    {
        $search = [...self::NEW_LINES_STRING];
        $replace = [PHP_EOL, PHP_EOL];
        if (is_null($labels)) {
            $labels = [];
        }
        if (!empty($labels)) {
            if (array_is_list($labels)) {
                for ($i = 0; $i < count($labels); ++$i) {
                    $search[] = '&' . ($i + 1);
                    $replace[] = $labels[$i];
                }
            } else {
                foreach ($labels as $sea => $rep) {
                    $search[] = "{&" . $sea . '}';
                    $replace[] = $rep;
                }
            }
        }
        $msg = $this->getConfig()->getNested($cat);
        if (is_null($msg)) {
            $msg = $default ?? $cat;
        }
        return str_replace($search, $replace, $msg);
    }

    public function setTranslate(string $cat, string $msg): void {
       $this->config->setNested($cat, $msg);
       $this->config->save();
    }

    /**
     * @param Player $player
     * @return void
     */
    public function addTranslateUI(Player $player): void
    {
        $ui = new CustomForm(function (Player $player, array $data): void {
            if(count($data) != 2)
                return ;
            $this->setTranslate($data[0], $data[1]);
        });
        $ui->setTitle("Message Translate");
        $ui->addInput("Translate Id: ");
        $ui->addInput("Message Translate:");

        $player->sendForm($ui);
    }

    /**
     * @param Player $player
     * @param string $cat
     * @return void
     */
    public function changeTranslateUI(Player $player, string $cat): void
    {
        $ui = new CustomForm(function (Player $player, array $data): void {
            var_dump($data);
            if(count($data) != 3)
                return ;
            $this->setTranslate($data[1], $data[3]);
        });
        $ui->setTitle("Message Translate");
        $ui->addHeader("Translate Id:");
        $ui->addLabel($cat);
        $ui->addDivider("");
        $ui->addInput("Message Translate:");

        $player->sendForm($ui);
    }

    /**
     * @param Player $player
     * @param int $mode 1 for change, 2 for delete
     * @param int $page
     * @param array|null $values
     * @return void
     */
    public function selectEditKey(Player $player, int $mode, int $page = 0, array|null $values = null): void
    {
        $values ??= $this->getCategoriesKey();
        $keys = array_slice($this->getCategoriesKey(), $page, 100);
        $lastIndexPage  = count($keys);
        $lastPage = count($values) / 100;
        $ui = new SimpleForm(function (Player $player, string $cat, int $index) use($mode, $page, $lastPage, $lastIndexPage, $values): void {
            if($page != 0 && $index == 0){
                $this->selectEditKey($player, $mode , $index - 1, $values);
                return;
            }
            if($page < $lastPage && $index == $lastIndexPage){
                $this->selectEditKey($player, $mode , $index + 1, $values);
                return;
            }
            switch ($mode) {
                case 1:
                {
                    $this->changeTranslateUI($player, $cat);
                    break;
                }
                case 2:
                {
                    $this->DeleteTranslateUI($player, $cat);
                    break;
                }
                default:
                    break;
            }
        });
        $ui->setTitle("Message Translate");
        if($page !== 0) {
            $ui->addButton("<- Previous", label: strval($page - 1));
        }
        foreach ($keys as $key) {
            $ui->addButton($key, label: strval($key));
        }
        if($page + 1 < $lastPage) {
            $ui->addButton("Next ->", label: strval($page + 1));
        }
        $player->sendForm($ui);
    }

    public function removeTranslate(string $cat): void
    {
        $this->config->removeNested($cat);
        $this->config->save();
    }


    public function getCategoriesKey(): array
    {
        return  Utils::GetAllKeyNested($this->getConfig()->getAll());
    }


    public function DeleteTranslateUI(Player $player, string $cat): void
    {
        $ui = new ModalForm(function (Player $player, bool $data) use($cat): void {
            if(!$data)
                return;
            $this->removeTranslate($cat);
        });
        $ui->setTitle($cat);
        $ui->setContent("Are you sure you want to remove this message?");
        $ui->setButton1("Accept");
        $ui->setButton2("Cancel");
        $player->sendForm($ui);
    }
}