<?php


namespace br\Controllers;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

abstract class Controller
{
    /** @var ContainerInterface  */
    protected $container;

    /** @var EntityManager */
    protected $manager;
  
  /**
   * Controller constructor.
   * @param ContainerInterface $container
   */
  public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $container[EntityManager::class];
    }
}
