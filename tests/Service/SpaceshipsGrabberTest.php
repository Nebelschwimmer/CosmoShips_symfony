<?php
namespace Tests\Kernel\Service;

use App\Factory\UserFactory;
use App\Repository\UserRepository;
use PhpParser\Node\Expr\ArrayItem;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Repository\SpaceShipRepository;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Service\HttpClient;

use App\Service\SpaceshipsGrabber;
final class SpaceshipsGrabberTest extends KernelTestCase
{
  use ResetDatabase, Factories;
  public function testSomething(): void
  {
    self::bootKernel();
    $user = UserFactory::createOne();
    $userRepository = $this->createMock(UserRepository::class);
    $userRepository->method('find')->willReturn($user);
    static::getContainer()->set(UserRepository::class, $userRepository);

    $httpClient = $this->createMock(HttpClient::class);
    static::getContainer()->set(HttpClient::class, $httpClient);
    $httpClient
      ->method('get')
      ->with('https://www.slashfilm.com/1325058/star-trek-most-important-ships-ranked/')
      ->willReturn('');
    assert($httpClient instanceof HttpClient);
    $spaceshipsGrabber = static::getContainer()->get(SpaceshipsGrabber::class);
    assert($spaceshipsGrabber instanceof SpaceshipsGrabber);

    $logger = $this->createMock(LoggerInterface::class);

    $spaceshipsGrabber->setLogger($logger)->importSpaceships();

    $spaceshipsRepository = static::getContainer()->get(SpaceShipRepository::class);
    assert($spaceshipsRepository instanceof SpaceShipRepository);

    $spaceships = $spaceshipsRepository->findAll();

    $this->assertCount(5, $spaceships);

  }
}
