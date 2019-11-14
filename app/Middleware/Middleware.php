<?php


namespace br\Middleware;

use Doctrine\ORM\EntityManager;

class Middleware
{
    /** @var EntityManager */
    protected $manager;

    public function __construct(EntityManager $em)
    {
        $this->manager = $em;
    }
}
