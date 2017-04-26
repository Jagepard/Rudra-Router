<?php

declare(strict_types = 1);

/**
 * Date: 25.04.17
 * Time: 18:09
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router\Interfaces;


use Rudra\Container\ContainerInterface;
use Rudra\Router\Router;


/**
 * Interface MatchAnnotationInterface
 *
 * @package Rudra
 */
interface MatchAnnotationInterface
{

    /**
     * MatchAnnotationInterface constructor.
     *
     * @param ContainerInterface $container
     * @param Router             $router
     */
    public function __construct(ContainerInterface $container, Router $router);

    /**
     * @param string $class
     * @param string $method
     * @param int    $number
     */
    public function annotation(string $class, string $method, int $number = 0): void;

    /**
     * @return ContainerInterface
     */
    public function container(): ContainerInterface;
}