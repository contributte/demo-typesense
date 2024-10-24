<?php declare(strict_types = 1);

namespace App\UI\Home;

use App\Model\User;
use App\UI\BasePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Utils\Random;
use Typesense\Client;

class HomePresenter extends BasePresenter
{

	#[Inject]
	public EntityManagerInterface $em;

	#[Inject]
	public Client $typesense;

	#[Inject]
	public Generator $faker;

	public function actionDefault(): void
	{
		$this->template->users = $this->em->getRepository(User::class)->findAll();
	}

	public function createComponentSearch()
	{
		$form = new Form();

		$form->addText('query', 'Query');
		$form->addSubmit('submit', 'Search');

		$form->onSuccess[] = function ($form) {
			$result = $this->typesense->collections['users']->documents->search([
				'q' => $form->values->query,
				'query_by' => 'username,address,phone,company,bio',
			]);
			$this->template->result = $result;
		};

		return $form;
	}

	public function handleCreateUser(): void
	{
		// Random seed
		$this->faker->seed(Random::generate(10, '0-9'));

		// Store to DB
		$user = new User(
			username: $this->faker->userName,
			address: $this->faker->address,
			phone: $this->faker->phoneNumber,
			company: $this->faker->company,
			bio: $this->faker->text,
		);

		$this->em->persist($user);
		$this->em->flush();
		$this->flashMessage('Saved to DB');

		// Store to Typesense
		$this->typesense->collections['users']->documents->upsert($user->toSearch());
		$this->flashMessage('Saved to Typesense');

		$this->redirect('this');
	}

	public function handleDeleteUsers(): void
	{
		$this->em->getRepository(User::class)
			->createQueryBuilder('u')
			->delete()
			->getQuery()
			->execute();

		$this->flashMessage('Removed');
		$this->redirect('this');
	}

}
