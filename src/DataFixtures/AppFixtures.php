<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(public UserPasswordHasherInterface $passwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@projectX.com');
        $user->setRoles(['ROLE_ADMIN']);
        $hash = $this->passwordHasher->hashPassword(
            $user,
            'admin'
        );
        $user->setPassword($hash);
        $user->setFullname('admin');
        $manager->persist($user);

        $status = ['In progress', 'Done', 'Blocked'];
        for ($i = 1; $i < 10; $i++ ) {
            $project = new Project();
            $project->setTitle('project '.$i)
                ->setCreatedBy($user)
                ->setTasks(mt_rand(0,20))
                ->setStatus($status[mt_rand(0,2)])
                ->setUrl('some url...')
                ->setImage('default.jpg')
                ->setDescription(
                    "Le lorem ipsum est, en imprimerie, une suite de mots sans signification utilisée à titre provisoire pour calibrer une mise en page."
                );
            $manager->persist($project);
        }

        $manager->flush();
    }

}
