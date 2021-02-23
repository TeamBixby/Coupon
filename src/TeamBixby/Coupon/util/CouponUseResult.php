<?php

declare(strict_types=1);

namespace TeamBixby\Coupon\util;

use BadMethodCallException;
use InvalidArgumentException;
use function count;
use function get_class;
use function strtolower;
use function strtoupper;

/**
 * @method static CouponUseResult SUCCESS()
 * @method static CouponUseResult NOT_ENOUGH_INVENTORY()
 * @method static CouponUseResult ALREADY_CLAIMED()
 * @method static CouponUseResult EXPIRED()
 * @method static CouponUseResult REACHED_MAX_PLAYER()
 */

class CouponUseResult{
	/** @var CouponUseResult[] */
	protected static array $registries = [];

	private static function lazyInit() : void{
		self::registerAll(
			new CouponUseResult("success"),
			new CouponUseResult("not_enough_inventory"),
			new CouponUseResult("already_claimed"),
			new CouponUseResult("expired"),
			new CouponUseResult("reached_max_player")
		);
	}

	protected static function register(CouponUseResult $result) : void{
		self::$registries[strtoupper($result->name())] = $result;
	}

	protected static function registerAll(CouponUseResult ...$results) : void{
		foreach($results as $result){
			self::register($result);
		}
	}

	public static function callStatic(string $name, array $arguments = []){
		self::lazyInit();
		if(!isset(self::$registries[strtolower($name)])){
			throw new BadMethodCallException("Call to undefined static method " . get_class(self::class) . "::" . $name . "()");
		}
		if(count($arguments) > 0){
			throw new InvalidArgumentException("Can't pass any argument on " . get_class(self::class) . "::" . $name . "()");
		}
		return self::$registries[strtoupper($name)];
	}

	protected string $name;

	public function __construct(string $name){
		$this->name = $name;
	}

	public function name() : string{
		return $this->name;
	}

	public function equals(CouponUseResult $other) : bool{
		return $this->name() === $other->name();
	}
}