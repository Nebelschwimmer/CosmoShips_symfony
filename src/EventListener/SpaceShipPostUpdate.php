<?php

namespace App\EventListener;

use App\Entity\SpaceShip;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\Entity\User;


#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Spaceship::class)]
class SpaceShipPostUpdate
{

  public function postUpdate(Spaceship $spaceship, PostUpdateEventArgs $event): void
  {
    // $em = $event->getObjectManager();
    // $user = $em->getRepository(User::class)->find($spaceship->getUser()->getId());
    // $publication = $user->findPublicationBySpaceShipId($spaceship->getId());
    // $publication
    //   ->setName($spaceship->getName())
    //   ->setSpaceshipId($spaceship->getId())
    //   ->setType('space_ship')
    //   ->setUpdatedAt(new \DateTime());

    // $user->updatePublication($publication);
    // $em->persist($publication);
    // $em->persist($user);

    // $em->flush();
  }
}