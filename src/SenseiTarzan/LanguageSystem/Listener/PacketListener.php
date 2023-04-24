<?php

namespace SenseiTarzan\LanguageSystem\Listener;

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\LanguageSystem\Class\Language;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class PacketListener
{

    #[EventAttribute(EventPriority::LOWEST)]
    public function onSendPacket(DataPacketSendEvent $event): void
    {
        $listPacket = $event->getPackets();
        $listNetworkSession = $event->getTargets();
        foreach ($listPacket as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                foreach ($packet->commandData as $index => $commandDatum) {

                    foreach ($listNetworkSession as $networkSession) {
                        $player = $networkSession->getPlayer();
                        if ($player === null) continue;
                        $description = $commandDatum->description;
                        $descriptionTranslate = ($description instanceof Translatable ?
                            LanguageManager::getInstance()->getTranslateModeNoSaveDefaultWithTranslatable($player, $description, Language::NO_EXIST_TRANSLATE) :
                            LanguageManager::getInstance()->getTranslateModeNoSaveDefault($player, $description, default: Language::NO_EXIST_TRANSLATE));
                        if ($descriptionTranslate === Language::NO_EXIST_TRANSLATE) continue;
                        $commandDatum->description = $descriptionTranslate;
                    }
                }
            }
        }
    }

}