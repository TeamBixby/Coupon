<?php

declare(strict_types=1);

namespace TeamBixby\Coupon;

use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\Player;
use TeamBixby\Coupon\util\CouponUseResult;
use function array_map;
use function count;
use function date;
use function time;

final class Coupon{

	protected string $name;

	protected string $couponId;

	protected int $money;
	/** @var Item[] */
	protected array $items = [];

	protected array $players = [];

	protected int $expireDate = -1;

	protected int $maxPlayer = -1;

	public function __construct(string $name, string $couponId, int $money, array $items, int $expireDate = -1, int $maxPlayer = -1){
		$this->name = $name;
		$this->couponId = $couponId;
		$this->money = $money;
		$this->items = array_map(function(array $data) : Item{
			return Item::jsonDeserialize($data);
		}, $items);
		$this->expireDate = $expireDate;
		$this->maxPlayer = $maxPlayer;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getCouponId() : string{
		return $this->couponId;
	}

	public function getMoney() : int{
		return $this->money;
	}

	/**
	 * @return Item[]
	 */
	public function getItems() : array{
		return $this->items;
	}

	public function getExpireDate() : int{
		return $this->expireDate;
	}

	public function getMaxPlayer() : int{
		return $this->maxPlayer;
	}

	public function tryClaim(Player $player) : CouponUseResult{
		if(isset($this->players[$player->getName()])){
			return CouponUseResult::ALREADY_CLAIMED();
		}
		if($this->expireDate > 0){
			if(time() > $this->expireDate){
				return CouponUseResult::EXPIRED();
			}
		}
		if($this->maxPlayer > 0){
			if(count($this->players) >= $this->maxPlayer){
				return CouponUseResult::REACHED_MAX_PLAYER();
			}
		}
		if(count($this->items) > 0){
			foreach($this->items as $item){
				if(!$player->getInventory()->canAddItem($item)){
					return CouponUseResult::NOT_ENOUGH_INVENTORY();
				}
			}
		}
		if(count($this->items) > 0){
			$player->getInventory()->addItem(...$this->items);
		}
		if($this->money > 0){
			EconomyAPI::getInstance()->addMoney($player, $this->money);
		}
		$this->players[$player->getName()] = date("m/d/Y H:i:s");
		return CouponUseResult::SUCCESS();
	}

	public function getClaimers() : array{
		return $this->players;
	}
}