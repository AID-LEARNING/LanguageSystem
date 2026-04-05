<?php

/*
 *
 *            _____ _____         _      ______          _____  _   _ _____ _   _  _____
 *      /\   |_   _|  __ \       | |    |  ____|   /\   |  __ \| \ | |_   _| \ | |/ ____|
 *     /  \    | | | |  | |______| |    | |__     /  \  | |__) |  \| | | | |  \| | |  __
 *    / /\ \   | | | |  | |______| |    |  __|   / /\ \ |  _  /| . ` | | | | . ` | | |_ |
 *   / ____ \ _| |_| |__| |      | |____| |____ / ____ \| | \ \| |\  |_| |_| |\  | |__| |
 *  /_/    \_\_____|_____/       |______|______/_/    \_\_|  \_\_| \_|_____|_| \_|\_____|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author AID-LEARNING
 * @link https://github.com/AID-LEARNING
 *
 */

declare(strict_types=1);

namespace SenseiTarzan\LanguageSystem\Listener;

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\raw\CommandRawData;
use ReflectionException;
use ReflectionProperty;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use WeakReference;

class PacketListener
{

    /** @var array<string, ReflectionProperty> */
    private static array $reflectionCache = [];
	/**
	 * @param WeakReference<LanguageManager> $langManager
	 */
	public function __construct(
		private WeakReference $langManager,
	)
	{
	}

    /**
     * @throws ReflectionException
     */
    #[EventAttribute(EventPriority::LOWEST)]
	public function onSendPacket(DataPacketSendEvent $event) : void
	{
		$listPacket = $event->getPackets();
		$listNetworkSession = $event->getTargets();
		$languageManager = $this->langManager->get();
        $commandDataRawDescriptionProperty = self::getReflectionProperty(CommandRawData::class, "description");
		foreach ($listPacket as $packet) {
			if ($packet instanceof AvailableCommandsPacket) {
				foreach ($packet->commandData as $_ => $commandDatum) {

					foreach ($listNetworkSession as $networkSession) {
						$player = $networkSession->getPlayer();
						if ($player === null) continue;
						$description = $commandDatum->getDescription();
						$descriptionTranslate = ($description instanceof Translatable ?
							$languageManager->getTranslateModeNoSaveDefaultWithTranslatable($player, $description, default: $description) :
							$languageManager->getTranslateModeNoSaveDefault($player, $description, default: $description));
                        $commandDataRawDescriptionProperty->setValue($commandDatum, $descriptionTranslate);
					}
				}
			}
		}
	}

    /**
     * @throws ReflectionException
     */
    private static function getReflectionProperty(object|string $object, string $property) : ReflectionProperty
    {
        if(is_string($object)){
            $key = $object . "::" . $property;
        }else{
            $key = $object::class . "::" . $property;
        }
        if (!isset(self::$reflectionCache[$key])) {
            $ref = new ReflectionProperty($object, $property);
            self::$reflectionCache[$key] = $ref;
        }
        return self::$reflectionCache[$key];
    }
}
