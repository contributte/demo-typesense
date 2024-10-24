<?php declare(strict_types = 1);

namespace App\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public int $id;

	#[ORM\Column(type: 'string')]
	public string $username;

	#[ORM\Column(type: 'string')]
	public string $address;

	#[ORM\Column(type: 'string')]
	public string $phone;

	#[ORM\Column(type: 'string')]
	public string $company;

	#[ORM\Column(type: 'string')]
	public string $bio;

	#[ORM\Column(type: 'datetime')]
	public DateTime $createdAt;

	#[ORM\Column(type: 'datetime', nullable: true)]
	public ?DateTime $updatedAt = null;

	public function __construct(
		string $username,
		string $address,
		string $phone,
		string $company,
		string $bio
	)
	{
		$this->username = $username;
		$this->address = $address;
		$this->phone = $phone;
		$this->company = $company;
		$this->bio = $bio;
		$this->createdAt = new DateTime();
	}

	#[ORM\PreUpdate]
	public function preUpdate(): void
	{
		$this->updatedAt = new DateTime();
	}

	public function toSearch(): array
	{
		return [
			'id' => (string) $this->id,
			'username' => $this->username,
			'address' => $this->address,
			'phone' => $this->phone,
			'company' => $this->company,
			'bio' => $this->bio,
			'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
			'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
		];
	}

}
