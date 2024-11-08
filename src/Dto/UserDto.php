<?php
namespace App\Dto;
class UserDto
{

  public function __construct(
    public readonly ?string $password = '',
    public readonly ?string $email = '',
    public readonly ?string $username = '',
    public readonly ?string $gender = '',
    public readonly ?string $avatar = '',
    public readonly ?string $about = '',
    public readonly ?string $dateOfBirth = ''
  ) {
  }
}