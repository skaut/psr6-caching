<?php declare(strict_types = 1);

namespace Contributte\Psr6;

use Contributte\Psr6\Exception\InvalidArgumentException;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Nette\Caching\Cache;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{

	/** @var string */
	private $key;

	/** @var mixed */
	private $value;

	/** @var bool */
	private $hit;

	/** @var mixed[] */
	protected $dependencies = [];

	public function getKey(): string
	{
		return $this->key;
	}

	public function get(): mixed
	{
		return $this->value;
	}

	public function isHit(): bool
	{
		return $this->hit;
	}

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function set(mixed $value): static
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @param DateTimeInterface|mixed|null $expiration
	 * @return static
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function expiresAt($expiration): static
	{
		if ($expiration === null) {
			$this->dependencies[Cache::Expire] = null;

			return $this;
		}

		if ($expiration instanceof DateTimeInterface) {
			$this->dependencies[Cache::Expire] = $expiration->format('U.u');

			return $this;
		}

		throw new InvalidArgumentException(
			sprintf('Invalid type "%s" for $expiration', gettype($expiration))
		);
	}

	/**
	 * @param int|DateInterval|mixed|null $time
	 * @return static
	 */
	public function expiresAfter($time): static
	{
		if ($time === null) {
			$this->dependencies[Cache::Expire] = null;

			return $this;
		}

		if ($time instanceof DateInterval) {
			/** @var DateTimeImmutable $date */
			$date = DateTimeImmutable::createFromFormat('U', (string) time());
			$this->dependencies[Cache::Expire] = $date->add($time)->format('U');

			return $this;
		}

		if (is_int($time)) {
			$this->dependencies[Cache::Expire] = $time + time();

			// Infinite
			if ($time === 0) {
				unset($this->dependencies[Cache::Expire]);
			}

			return $this;
		}

		throw new InvalidArgumentException(
			sprintf('Invalid type "%s" for $time', gettype($time))
		);
	}

	/**
	 * @return mixed[]
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * @param mixed[] $dependencies
	 */
	public function setDependencies(array $dependencies): static
	{
		$this->dependencies = $dependencies;

		return $this;
	}

}
