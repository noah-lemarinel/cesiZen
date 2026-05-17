<?php

namespace App\Command;

use App\Entity\BreathingExercise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-breathing-exercises',
    description: 'Create default breathing exercises (748, 55, 46)',
)]
class CreateBreathingExercisesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Default breathing exercises data
        $exercisesData = [
            '748' => [
                'name' => '748',
                'description' => 'Technique de respiration 7-4-8: Inspiration 7 secondes, Apnée 4 secondes, Expiration 8 secondes. Excellente pour réduire le stress et l\'anxiété.',
                'inhaleSeconds' => 7,
                'holdSeconds' => 4,
                'exhaleSeconds' => 8,
                'cycles' => 5,
            ],
            '55' => [
                'name' => '55',
                'description' => 'Technique de respiration 5-0-5: Inspiration 5 secondes, Expiration 5 secondes. Technique simple et équilibrée pour la relaxation.',
                'inhaleSeconds' => 5,
                'holdSeconds' => 0,
                'exhaleSeconds' => 5,
                'cycles' => 5,
            ],
            '46' => [
                'name' => '46',
                'description' => 'Technique de respiration 4-0-6: Inspiration 4 secondes, Expiration 6 secondes. Favorise l\'activation du système nerveux parasympathique.',
                'inhaleSeconds' => 4,
                'holdSeconds' => 0,
                'exhaleSeconds' => 6,
                'cycles' => 5,
            ],
        ];

        $count = 0;
        $existingExercises = $this->entityManager->getRepository(BreathingExercise::class)->findAll();
        $existingNames = array_map(fn ($e) => $e->getName(), $existingExercises);

        foreach ($exercisesData as $name => $data) {
            if (!in_array($name, $existingNames)) {
                $exercise = new BreathingExercise();
                $exercise->setName($data['name']);
                $exercise->setDescription($data['description']);
                $exercise->setInhaleSeconds($data['inhaleSeconds']);
                $exercise->setHoldSeconds($data['holdSeconds']);
                $exercise->setExhaleSeconds($data['exhaleSeconds']);
                $exercise->setCycles($data['cycles']);

                $this->entityManager->persist($exercise);
                ++$count;
                $io->writeln("✓ Créé: {$name}");
            } else {
                $io->writeln("→ Existe déjà: {$name}");
            }
        }

        $this->entityManager->flush();

        $io->success("Exercices de respiration créés avec succès! ({$count} nouveaux exercices créés)");

        return Command::SUCCESS;
    }
}
