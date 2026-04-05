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
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
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
	public function onSendPacket(DataPacketSendEvent $event) : void
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
