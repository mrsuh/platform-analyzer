<?php

namespace App\Command;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CollectCommand extends Command
{
    protected static $defaultName = 'collect';

    const platforms = ['vc', 'tjournal', 'dtf'];

    private $httpClient;
    private $entityManager;
    private $articleRepository;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $this->httpClient        = $httpClient;
        $this->entityManager     = $entityManager;
        $this->articleRepository = $articleRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Collect info')
            ->addOption('platform', null, InputOption::VALUE_REQUIRED, implode(', ', self::platforms), 'vc')
            ->addOption('section', null, InputOption::VALUE_REQUIRED, 'dev', 'dev');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if(Command::SUCCESS !== $this->createDatabase($output)) {
            $io->error('Create database schema command failed');
        }

        $platform = $input->getOption('platform');
        if (!in_array($platform, self::platforms)) {
            $io->error('Invalid option "platform" value. Valid values: ' . implode(', ', self::platforms));

            return Command::FAILURE;
        }

        $section = $input->getOption('section');
        if (empty($section)) {
            $io->error('Invalid option "section" value. Option must be filled');

            return Command::FAILURE;
        }

        $offset = 0;
        while (true) {
            $startTime = microtime(true);
            $count     = $this->load($platform, $section, $offset);

            if ($count === 0) {
                break;
            }

            if (microtime(true) - $startTime <= 0.3) {
                usleep(300000);
            }

            $offset += $count;
        }

        return Command::SUCCESS;
    }

    private function load(string $platform, string $section, int $offset): int
    {
        $url = sprintf('https://api.%s.ru/v1.8/timeline/%s/recent', $platform, $section);

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'count'  => 40,
                'offset' => $offset
            ]
        ]);

        $data = $response->toArray();

        foreach ($data['result'] as $item) {
            if ((int)$item['type'] !== Article::TYPE_ENTRY) {
                continue;
            }

            $id = (int)$item['id'];

            $article = $this->articleRepository->findOneBy(['id' => $id]);

            if (!$article) {
                $article = new Article();
                $article->setId($id);
                $this->entityManager->persist($article);
            }

            $article->setPlatform($platform);
            $article->setSection($section);
            $article->setUrl($item['url']);
            $article->setTitle($item['title']);
            $article->setCommentsCount((int)$item['commentsCount']);
            $article->setFavoritesCount((int)$item['favoritesCount']);
            $article->setHits((int)$item['hitsCount']);
            $article->setRating((int)$item['likes']['summ']);
            $article->setCreatedAt(\DateTime::createFromFormat('U', $item['date']));
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return count($data['result']);
    }

    public function createDatabase(OutputInterface  $output): int {
        $command = $this->getApplication()->find('doctrine:schema:update');
        $greetInput = new ArrayInput(['--quiet'  => true, '--force' => true]);
        return $command->run($greetInput, $output);
    }
}
