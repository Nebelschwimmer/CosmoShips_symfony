<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\UserDto;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class UserController extends AbstractController
{

  #[Route('/api/user/list', name: 'api_user_list', methods: ['GET'])]
  public function index(UserRepository $userRepository): Response
  {
    return $this->json($userRepository->findAll());
  }
  
  
  #[Route('/api/user/{id}', name: 'api_user_show', methods: ['GET'])]
  public function show(User $user): Response
  {
    return $this->json($user);
  }

  #[Route('/api/user/{id}/delete', name: 'api_user_delete', methods: ['DELETE'])]
  public function delete(User $user, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($user);
    $entityManager->flush();
    return $this->json(['status' => 'deleted']);
  }

  #[Route('/api/user/{id}/edit', name: 'api_user_edit', methods: ['POST', 'PUT'])]
  public function edit(
    #[MapRequestPayload()] 
    UserDto $userDto, 
    EntityManagerInterface $entityManager, 
    User $user): Response
  {
    $newUser = User::updateFromDto($user, $userDto);
    $entityManager->persist($user);
    $entityManager->flush();
    return $this->json($newUser);
  }



  #[Route('/api/user/{id}/upload', name: 'api_user_upload', methods: ['POST'])]
  public function upload(
    Request $request,
    FileUploader $fileUploader,
    User $user,
    EntityManagerInterface $entityManager
  ): Response {

    /** @var UploadedFile $uploadedAvatar */
    $uploadedAvatar = $request->files->get('imageFile');

    if ($uploadedAvatar) {
      $imageFileName = $fileUploader->upload($uploadedAvatar);
      if (null !== $imageFileName) {
        $directory = $fileUploader->getTargetDirectory();
        $fullpath = $directory . '/' . $imageFileName;
        $fileUrl = '/uploads' . '/' . $imageFileName;
        if (file_exists($fullpath)) {
          $user->setAvatar($fileUrl);
          $entityManager->persist( $user);
          $entityManager->flush();
        } else {
          return $this->json(['message' => 'File not found.'], Response::HTTP_BAD_REQUEST);
        }
      } else {
        return $this->json(['message' => 'File not uploaded because of an error.'], Response::HTTP_BAD_REQUEST);
      }
    }
    return $this->json($user);
  }

}