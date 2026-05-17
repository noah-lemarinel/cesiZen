<?php

namespace App\Command;

use App\Entity\Emotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-emotions',
    description: 'Create the emotion hierarchy (level 1 and level 2 emotions)',
)]
class CreateEmotionsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Color palette for primary emotions
        $emotionsData = [
            'Joie' => [
                'color' => '#FFD700',
                'description' => 'Émotion positive caractérisée par le bonheur et la satisfaction',
                'children' => [
                    'Fierté' => 'Sentiment de satisfaction personnelle',
                    'Contentement' => 'État de satisfaction tranquille',
                    'Enchantement' => 'Joie légère et merveilleuse',
                    'Joie' => 'Bonheur intense et manifeste',
                    'Excitation' => 'Enthousiasme et énergie positive',
                    'Émerveillement' => 'Sensation de découverte et de merveille',
                    'Gratitude' => 'Reconnaissance et appréciation',
                ],
            ],
            'Colère' => [
                'color' => '#FF0000',
                'description' => 'Émotion intense caractérisée par le mécontentement',
                'children' => [
                    'Frustration' => 'Sentiment d\'obstacle ou d\'insatisfaction',
                    'Irritation' => 'Agacement léger et persistant',
                    'Rage' => 'Colère intense et incontrôlable',
                    'Ressentiment' => 'Amertume face à une injustice perçue',
                    'Agacement' => 'Léger mécontentement répétitif',
                    'Hostilité' => 'Attitude opposée et combattive',
                ],
            ],
            'Peur' => [
                'color' => '#8B008B',
                'description' => 'Émotion défensive face au danger ou à l\'incertitude',
                'children' => [
                    'Inquiétude' => 'Préoccupation légère pour l\'avenir',
                    'Anxiété' => 'Malaise général et tension nerveuse',
                    'Terreur' => 'Peur extrême et paralysite',
                    'Appréhension' => 'Crainte anticipée d\'un événement',
                    'Panique' => 'Peur soudaine et irraisonnée',
                    'Crainte' => 'Peur raisonnée et prudente',
                ],
            ],
            'Tristesse' => [
                'color' => '#00008B',
                'description' => 'Émotion caractérisée par la mélancolie et le chagrin',
                'children' => [
                    'Chagrin' => 'Profonde douleur émotionnelle',
                    'Mélancolie' => 'Tristesse douce et rêveuse',
                    'Abattement' => 'Perte d\'énergie et de motivation',
                    'Désespoir' => 'Perte totale d\'espoir',
                    'Solitude' => 'Sentiment d\'isolement émotionnel',
                    'Dépression' => 'État prolongé de tristesse profonde',
                ],
            ],
            'Surprise' => [
                'color' => '#FFA500',
                'description' => 'Émotion face à quelque chose d\'inattendu',
                'children' => [
                    'Étonnement' => 'Surprise agréable et légère',
                    'Stupéfaction' => 'Surprise intense et incrédule',
                    'Sidération' => 'Choc et immobilisme momentaire',
                    'Incrédulité' => 'Doute face à ce qui semble impossible',
                    'Émerveillement' => 'Surprise positive et admiration',
                    'Confusion' => 'Désorientation face à quelque chose d\'imprévu',
                ],
            ],
            'Dégoût' => [
                'color' => '#228B22',
                'description' => 'Émotion de répulsion et d\'aversion',
                'children' => [
                    'Répulsion' => 'Rejet fort et viscéral',
                    'Déplaisir' => 'Manque d\'agrément ou de satisfaction',
                    'Nausée' => 'Malaise physique associé à une aversion',
                    'Dédain' => 'Mépris et désapprobation',
                    'Horreur' => 'Répulsion intense face au répugnant',
                    'Dégoût profond' => 'Répulsion totale et révulsion',
                ],
            ],
        ];

        $count = 0;
        $existingEmotions = $this->entityManager->getRepository(Emotion::class)->findAll();
        $existingNames = array_map(fn ($e) => $e->getName(), $existingEmotions);

        foreach ($emotionsData as $primaryName => $data) {
            // Create or get primary emotion
            if (!in_array($primaryName, $existingNames)) {
                $primary = new Emotion();
                $primary->setName($primaryName);
                $primary->setColor($data['color']);
                $primary->setDescription($data['description']);
                $this->entityManager->persist($primary);
                $this->entityManager->flush();
                ++$count;
                $io->writeln("✓ Créé: {$primaryName}");
            } else {
                $primary = $this->entityManager->getRepository(Emotion::class)
                    ->findOneBy(['name' => $primaryName]);
                $io->writeln("→ Existe déjà: {$primaryName}");
            }

            // Create secondary emotions
            foreach ($data['children'] as $secondaryName => $description) {
                if (!in_array($secondaryName, $existingNames)) {
                    $secondary = new Emotion();
                    $secondary->setName($secondaryName);
                    $secondary->setDescription($description);
                    $secondary->setParent($primary);
                    $this->entityManager->persist($secondary);
                    ++$count;
                    $io->writeln("  ├─ Créé: {$secondaryName}");
                } else {
                    $io->writeln("  ├─ Existe déjà: {$secondaryName}");
                }
            }
        }

        $this->entityManager->flush();

        $io->success("Émotions importées avec succès! ({$count} nouvelles émotions créées)");

        return Command::SUCCESS;
    }
}
