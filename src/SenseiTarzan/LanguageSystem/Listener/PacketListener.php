<?php

namespace SenseiTarzan\LanguageSystem\Listener;

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\LanguageSystem\Class\Language;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use WeakReference;

class PacketListener
{

    /**
     * @param WeakReference<LanguageManager> $langManager
     */
    public function __construct(
        private WeakReference $langManager,
    )
    {
    }

    #[EventAttribute(EventPriority::LOWEST)]
    public function onSendPacket(DataPacketSendEvent $event): void
    {
        $listPacket = $event->getPackets();
        $listNetworkSession = $event->getTargets();
        $languageManager = $this->langManager->get();
        foreach ($listPacket as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                foreach ($packet->commandData as $_ => $commandDatum) {

                    foreach ($listNetworkSession as $networkSession) {
                        $player = $networkSession->getPlayer();
                        if ($player === null) continue;
                        $description = $commandDatum->getDescription();
                        $descriptionTranslate = ($description instanceof Translatable ?
                            $languageManager->getTranslateModeNoSaveDefaultWithTranslatable($player, $description, default: $commandDatum->description) :
                            $languageManager->getTranslateModeNoSaveDefault($player, $description, default: $commandDatum->description));
                        $commandDatum->description = $descriptionTranslate;
                    }
                }
            }
        }
    }

}