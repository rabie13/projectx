<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create:user',
    description: 'Create admin account',
)]
class CreateUserCommand extends Command
{

    public function __construct(
        public UserPasswordHasherInterface $passwordHasher, 
        public EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Your email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addOption('auto', null, InputOption::VALUE_NONE, 'Use default credentials')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        
        if (!$email || !$password) {
            $email = 'admin@projectx.com';
            $password = "admin";
            $name = 'admin';
        }else{
            $count = $this->em->getRepository(User::class)->countEntities();
            $name = 'user '.($count + 1);
        }
        $temp = $this->em->getRepository(User::class)->findOneByEmail($email);
        if ($temp) {
            $io->note(sprintf('There is already an account with the email:  %s', $email));
            exit;
        }
        $hash = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setEmail($email);
        $user->setPassword($hash);
        $user->setFullname($name);
        $this->em->persist($user);
        $this->em->flush();

        // if ($input->getOption('auto')) {
        //     // ...
        // }

        $io->success('A new account has been created for you.');
        $io->note(sprintf('Your email: %s', $email));
        $io->note(sprintf('Your password: %s', $password));

        return Command::SUCCESS;
    }
}
