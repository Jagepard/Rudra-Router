<?php

declare(strict_types = 1);

/**
 * Date: 26.04.17
 * Time: 16:30
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router\Interfaces;


use Rudra\Container\ContainerInterface;


/**
 * Interface MatchRequestMethodInterfaces
 *
 * @package Rudra\Router\Interfaces
 */
interface MatchRequestMethodInterfaces
{

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    public function setRequestMethod(string $param = null);

    /**
     * @return ContainerInterface
     */
    public function container(): ContainerInterface;
}