<?php

declare(strict_types=1);

/*
 * This file is part of Biurad opensource projects.
 *
 * PHP version 7.1 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 Biurad Group (https://biurad.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Biurad\Cache;

use Biurad\Cache\Exceptions\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

final class CacheItem implements CacheItemInterface
{
    /**
     * Reserved characters that cannot be used in a key or tag.
     */
    public const RESERVED_CHARACTERS = '{}()/\@:';

    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /** @var bool */
    private $isHit = false;

    /** @var float|int|null */
    private $expiry;

    /** @var int */
    private $defaultLifetime;

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if (null === $expiration) {
            return $this->setDefaultExpiration();
        }

        if (!$expiration instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('Expiration date must implement DateTimeInterface or be null.');
        }

        $this->expiry = (float) $expiration->format('U.u');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if (null === $time) {
            return $this->setDefaultExpiration();
        }

        if ($time instanceof \DateInterval) {
            $interval = \DateTime::createFromFormat('U', '0')->add($time);
            $this->expiry = \microtime(true) + (int) $interval->format('U.u');
        } elseif (\is_int($time)) {
            $this->expiry = $time + \microtime(true);
        } else {
            throw new InvalidArgumentException('Expiration date must be an integer, a DateInterval or null.');
        }

        return $this;
    }

    /**
     * @internal
     */
    public function getExpiry(): ?float
    {
        return $this->expiry;
    }

    /**
     * @return static
     */
    private function setDefaultExpiration(): self
    {
        $this->expiry = $this->defaultLifetime > 0 ? \microtime(true) + $this->defaultLifetime : null;

        return $this;
    }
}
