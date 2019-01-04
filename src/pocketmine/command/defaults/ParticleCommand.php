<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\block\BlockFactory;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\EnchantParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\InstantEnchantParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RainSplashParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\level\particle\SporeParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;
use function count;
use function explode;
use function max;
use function microtime;
use function mt_rand;
use function strpos;
use function strtolower;

class ParticleCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.particle.description",
			"%pocketmine.command.particle.usage"
		);
		$this->setPermission("pocketmine.command.particle");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 7){
			throw new InvalidCommandSyntaxException();
		}

		if($sender instanceof Player){
			$level = $sender->getLevel();
			$pos = new Vector3(
				$this->getRelativeDouble($sender->getX(), $sender, $args[1]),
				$this->getRelativeDouble($sender->getY(), $sender, $args[2], 0, Level::Y_MAX),
				$this->getRelativeDouble($sender->getZ(), $sender, $args[3])
			);
		}else{
			$level = $sender->getServer()->getDefaultLevel();
			$pos = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
		}

		$name = strtolower($args[0]);

		$xd = (float) $args[4];
		$yd = (float) $args[5];
		$zd = (float) $args[6];

		$count = isset($args[7]) ? max(1, (int) $args[7]) : 1;

		$data = isset($args[8]) ? (int) $args[8] : null;

		$particle = $this->getParticle($name, $data);

		if($particle === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.particle.notFound", [$name]));
			return true;
		}


		$sender->sendMessage(new TranslationContainer("commands.particle.success", [$name, $count]));

		$random = new Random((int) (microtime(true) * 1000) + mt_rand());

		for($i = 0; $i < $count; ++$i){
			$level->addParticle($pos->add(
				$random->nextSignedFloat() * $xd,
				$random->nextSignedFloat() * $yd,
				$random->nextSignedFloat() * $zd
			), $particle);
		}

		return true;
	}

	/**
	 * @param string   $name
	 * @param int|null $data
	 *
	 * @return Particle|null
	 */
	private function getParticle(string $name, int $data = null){
		switch($name){
			case "explode":
				return new ExplodeParticle();
			case "hugeexplosion":
				return new HugeExplodeParticle();
			case "hugeexplosionseed":
				return new HugeExplodeSeedParticle();
			case "bubble":
				return new BubbleParticle();
			case "splash":
				return new SplashParticle();
			case "wake":
			case "water":
				return new WaterParticle();
			case "crit":
				return new CriticalParticle();
			case "smoke":
				return new SmokeParticle($data ?? 0);
			case "spell":
				return new EnchantParticle();
			case "instantspell":
				return new InstantEnchantParticle();
			case "dripwater":
				return new WaterDripParticle();
			case "driplava":
				return new LavaDripParticle();
			case "townaura":
			case "spore":
				return new SporeParticle();
			case "portal":
				return new PortalParticle();
			case "flame":
				return new FlameParticle();
			case "lava":
				return new LavaParticle();
			case "reddust":
				return new RedstoneParticle($data ?? 1);
			case "snowballpoof":
				return new ItemBreakParticle(ItemFactory::get(Item::SNOWBALL));
			case "slime":
				return new ItemBreakParticle(ItemFactory::get(Item::SLIMEBALL));
			case "itembreak":
				if($data !== null and $data !== 0){
					return new ItemBreakParticle(ItemFactory::get($data));
				}
				break;
			case "terrain":
				if($data !== null and $data !== 0){
					return new TerrainParticle(BlockFactory::get($data));
				}
				break;
			case "heart":
				return new HeartParticle($data ?? 0);
			case "ink":
				return new InkParticle($data ?? 0);
			case "droplet":
				return new RainSplashParticle();
			case "enchantmenttable":
				return new EnchantmentTableParticle();
			case "happyvillager":
				return new HappyVillagerParticle();
			case "angryvillager":
				return new AngryVillagerParticle();
			case "forcefield":
				return new BlockForceFieldParticle($data ?? 0);

		}

		if(strpos($name, "iconcrack_") === 0){
			$d = explode("_", $name);
			if(count($d) === 3){
				return new ItemBreakParticle(ItemFactory::get((int) $d[1], (int) $d[2]));
			}
		}elseif(strpos($name, "blockcrack_") === 0){
			$d = explode("_", $name);
			if(count($d) === 2){
				return new TerrainParticle(BlockFactory::get(((int) $d[1]) & 0xff, ((int) $d[1]) >> 12));
			}
		}elseif(strpos($name, "blockdust_") === 0){
			$d = explode("_", $name);
			if(count($d) >= 4){
				return new DustParticle(((int) $d[1]) & 0xff, ((int) $d[2]) & 0xff, ((int) $d[3]) & 0xff, isset($d[4]) ? ((int) $d[4]) & 0xff : 255);
			}
		}

		return null;
	}
}
