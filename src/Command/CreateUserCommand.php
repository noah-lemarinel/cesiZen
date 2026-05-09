<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user', description: 'Create a new user')]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The user email')
            ->addArgument('password', InputArgument::REQUIRED, 'The user password')
            ->addArgument('name', InputArgument::OPTIONAL, 'The user name')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Set this user as admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $name = $input->getArgument('name') ?? null;
        $isAdmin = (bool) $input->getOption('admin');

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);

        // Hash password
        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        if ($isAdmin) {
            // mark as admin in both field and roles
            $user->setIsAdmin(true);
            $roles = $user->getRoles();
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles(array_unique($roles));
        }

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(sprintf('User %s created. Admin: %s', $email, $isAdmin ? 'yes' : 'no'));

        return Command::SUCCESS;
    }
}

