<?php

namespace SenseiTarzan\LanguageSystem\Class;


use pocketmine\plugin\Plugin;
use SenseiTarzan\Path\Config;

class Language 
{

    private string $name;
    private string $mini;
    private string $image;
    private Config $config;
    private Plugin $plugin;

    public function __construct(Plugin $plugin, string $name, string $mini, string $image, string $path)
    {
        $this->plugin = $plugin;
        $this->name = $name;
        $this->mini = $mini;
        $this->image = $image;
        $this->config = new Config($plugin->getDataFolder() . $path, Config::INI);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMini(): string
    {
        return $this->mini;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function translate(string $cat, ?array $labels = null, mixed $default = null): array|string
    {
        $search = ['%n', '\n'];
        $replace = ["\n", "\n"];
        if (is_null($labels)){
            $labels = [];
        }
        if (!empty($labels)){
            if (array_is_list($labels)){
                for ($i = 0; $i < count($labels); ++$i){
                    $search[] = '&' . ($i + 1);
                    $replace[] = $labels[$i];
                }
            }else{
                foreach ($labels as $sea => $rep){
                    $search[] = "{&" . $sea . '}';
                    $replace[] = $rep;
                }
            }
        }
        $msg = $this->getConfig()->getNested($cat);
        if (is_null($msg)){
            $msg = $default ?? $cat;
            $this->getConfig()->setNested($cat, $msg);
            $this->getConfig()->save();
        }
        return  str_replace($search, $replace, $msg);
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}