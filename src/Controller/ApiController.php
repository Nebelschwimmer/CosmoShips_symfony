<?php

namespace App\Controller;

use App\Dto\SpaceshipDto;
use App\Entity\Like;
use App\Entity\Publisher;
use App\Entity\SpaceShipCategory;
use App\Entity\SpaceShip;
use App\Repository\PublicationRepository;
use App\Repository\SpaceShipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SpaceShipCategoryRepository;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class ApiController extends AbstractController
{

  #[Route('/api/spaceships', name: 'api_space_ship_index', methods: ['GET'])]
  public function index(
    SpaceShipRepository $spaceShipRepository,
    #[MapQueryParameter('offset')] ?int $offset = 0,
    #[MapQueryParameter('limit')] ?int $limit = 8,
    #[MapQueryParameter('search')] ?string $searchQuery = null,
    #[MapQueryParameter('sortBy')] ?string $sortBy = 'name',
    #[MapQueryParameter('order')] ?string $order = 'ASC',
  ): Response {
    $spaceShips = $spaceShipRepository->findAllWithQueryParams($offset, $limit, $searchQuery, $sortBy, $order);
    $allSpaceships = $spaceShipRepository->findAll();

    $totalSpaceshipsListNumber = count($allSpaceships);

    $maxPages = ceil($totalSpaceshipsListNumber / $limit);

    return $this->json(
      [
        'spaceships' => $spaceShips,
        'maxpages' => $maxPages,
        'total' => $totalSpaceshipsListNumber
      ],
      Response::HTTP_OK
    );
  }

  #[Route('/api/spaceships/list', name: 'api_spaceship_list', methods: ['GET'])]
  public function listSpaceships(SpaceShipRepository $spaceShipRepository): Response
  {
    return $this->json($spaceShipRepository->findAll(), Response::HTTP_OK);
  }


  #[Route('/api/spaceships/{id}', name: 'api_space_ship_show', methods: ['GET'])]
  public function show(SpaceShip $spaceShip): Response
  {
    return $this->json($spaceShip, Response::HTTP_OK);
  }

  #[Route('/api/spaceships/{id}', name: 'api_spaceship_delete_one', methods: ['DELETE'])]
  public function deleteSpaceshipById(
    Request $request,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository
  ): Response {
    $userId = $request->getPayload()->get('userId');

    $user = $userRepository->findUserById($userId);

    if (!$user) {
      return $this->json(['status' => 'user not found'], Response::HTTP_NOT_FOUND);
    }
    $likes = $spaceShip->getLikes();

    if (count($likes) !== 0) {
      foreach ($likes as $like) {
        if ($like->getUser() === $user) {
          $spaceShip->removeLike($like);
          $user->removeLike($like);
          $entityManager->persist($spaceShip);
          $entityManager->persist($user);
        }
      }
    }

    $entityManager->persist($spaceShip);
    $entityManager->remove($spaceShip);
    $entityManager->flush();
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');
    return $this->json(['status' => 'deleted'], Response::HTTP_OK);
  }

  #[Route('/api/spaceships/delete/all', name: 'api_spaceship_delete_all', methods: ['DELETE'])]
  public function deleteAllSpaceships(
    SpaceShipRepository $spaceShipRepository,
    EntityManagerInterface $entityManager
  ): Response {
    $spaceShips = $spaceShipRepository->findAll();
    foreach ($spaceShips as $spaceShip) {
      $entityManager->remove($spaceShip);

    }
    $entityManager->flush();

    return $this->json(['status' => 'deleted'], Response::HTTP_OK);
  }

  #[Route('/api/spaceships/categories/list', name: 'api_spaceship_categories_list', methods: ['GET'])]
  public function listCategories(SpaceShipCategoryRepository $spaceShipCategoryRepository): Response
  {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    return $this->json($spaceShipCategoryRepository->findAll(), Response::HTTP_OK);
  }

  #[Route('/api/spaceships/add', name: 'api_spaceship_add', methods: ['POST'])]
  public function addSpaceship(
    #[MapRequestPayload()] SpaceshipDto $spaceshipDto,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShipCategoryRepository $spaceShipCategoryRepository,
  ): Response {
    $user = $userRepository->findUserById($spaceshipDto->userId);
    if (!$user) {
      return $this->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
    }

    $spaceShip = new SpaceShip();
    $spaceShip->setName( $spaceshipDto->name)
      ->setDescription($spaceshipDto->description)
      ->setImage($spaceshipDto->image);
    $category = new SpaceShipCategory;
    $category = $spaceShipCategoryRepository->find($spaceshipDto->categoryId);
    $publisher = new Publisher();
    $publisher->setName($user->getUsername())->setUserId($user->getId());

    $spaceShip
      ->setCategory($category)
      ->setPublisher($publisher);
    $entityManager->persist($spaceShip);
    $entityManager->flush();

    return $this->json($spaceShip, Response::HTTP_CREATED);
  }


  #[Route('/api/spaceships/{id}/upload', name: 'api_spaceship_upload', methods: ['POST'])]
  public function upload(
    Request $request,
    FileUploader $fileUploader,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager
  ): Response {

    /** @var UploadedFile $uploadedImageFile */
    $uploadedImageFile = $request->files->get('imageFile');

    if ($uploadedImageFile) {
      $imageFileName = $fileUploader->upload($uploadedImageFile);
      if (null !== $imageFileName) {
        $directory = $fileUploader->getTargetDirectory();
        $fullpath = $directory . '/' . $imageFileName;
        $fileUrl = '/uploads' . '/' . $imageFileName;
        if (file_exists($fullpath)) {
          $spaceShip->setImage($fileUrl);
          $entityManager->persist($spaceShip);
          $entityManager->flush();
        } else {
          return $this->json(['message' => 'File not found.'], Response::HTTP_BAD_REQUEST);
        }
      } else {
        return $this->json(['message' => 'File not uploaded because of an error.'], Response::HTTP_BAD_REQUEST);
      }
    }
    return $this->json($spaceShip);
  }


  #[Route('/api/spaceships/{id}/edit', name: 'api_spaceship_edit', methods: ['POST', 'PUT'])]
  public function editSpaceshipDto(
    #[MapRequestPayload(
    acceptFormat: 'json',
  )] SpaceshipDto $spaceshipDto,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager,
    SpaceShipCategoryRepository $spaceShipCategoryRepository,

  ): Response {
    $category = new SpaceShipCategory;
    $category = $spaceShipCategoryRepository->find($spaceshipDto->categoryId);
    $updatedSpaceship = SpaceShip::updateFromDto($spaceShip, $category, $spaceshipDto);

    $entityManager->persist($updatedSpaceship);
    $entityManager->flush();
    return $this->json($updatedSpaceship, Response::HTTP_OK);

  }

  #[Route('/api/spaceships/import/swapi', name: 'api_spaceship_import_swapi', methods: ['POST', 'PUT'])]
  public function importFromSwapi(
    EntityManagerInterface $entityManager,
    SpaceShipCategoryRepository $spaceShipCategoryRepository,
    UserRepository $userRepository
  ): Response {
    $swapiUrl = 'https://swapi.dev/api/starships/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $swapiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    $spaceShips = $data['results'];
    $fakeUser = $userRepository->find(4);
    $publisher = new Publisher();
    $publisher->setName($fakeUser->getUsername())->setUserId($fakeUser->getId());

    foreach ($spaceShips as $key => $value) {
      $spaceShip = new SpaceShip($fakeUser);
      $spaceShip
        ->setName($value['name'])
        ->setDescription($value['manufacturer'])
        ->setCategory($spaceShipCategoryRepository->find(1))
        ->setPublisher($publisher)
      ;
      $entityManager->persist($spaceShip);
      $entityManager->flush();

    }
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');
    return $this->json(['message' => 'SpaceShips imported successfully.'], Response::HTTP_OK);
  }


  #[Route('/api/spaceships/{id}/like', name: 'api_spaceship_like_add', methods: ['POST'])]
  public function addLike(
    Request $request,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShipRepository $spaceShipRepository,
    PublicationRepository $publicationRepository,
    int $id,
  ): Response {
    $user = $userRepository->findUserById($request->getPayload()->get('userId'));
    $spaceShip = $spaceShipRepository->find($id);

    $like = new Like();
    $like->setUser($user);
    $spaceShip->addLike($like);

    $publication = $publicationRepository->findOneBy(['spaceship_id' => $spaceShip->getId()]);
    if ($publication) {
      $likesCount = $publication->getLikesCount() ?? 0;
      $publication->setLikesCount($likesCount + 1);
    }

    $user->addLike($like);

    $entityManager->persist($spaceShip);
    $entityManager->persist($like);
    $entityManager->persist($user);
    $entityManager->flush();

    return $this->json($spaceShip, Response::HTTP_CREATED);
  }

  #[Route('/api/spaceships/{id}/like', name: 'api_spaceship_like_delete', methods: ['DELETE'])]
  public function removeLike(
    Request $request,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShip $spaceShip,
    PublicationRepository $publicationRepository
  ): Response {
    $user = $userRepository->findUserById($request->getPayload()->get('userId'));
    $publication = $publicationRepository->findOneBy(['spaceship_id' => $spaceShip->getId()]);
    
    
    $publication = $user->findPublicationBySpaceShipId($spaceShip->getId());
    if (!$publication) {
      return $this->json(['status' => 'publication not found'], Response::HTTP_NOT_FOUND);
    }
    $user->removePublication($publication);



    if ($publication) {
      $publication->setLikesCount($publication->getLikesCount() - 1);
    }
    $likes = $spaceShip->getLikes();
    foreach ($likes as $like) {
      if ($like->getUser() === $user) {
        $spaceShip->removeLike($like);
        $user->removeLike($like);
        $entityManager->persist($spaceShip);

        $entityManager->flush();
      }
    }
    return $this->json($spaceShip, Response::HTTP_OK);
  }

}