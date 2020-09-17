<?php

namespace App\Command;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use MaddHatter\MarkdownTable\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnalyzeCommand extends Command
{
    protected static $defaultName = 'analyze';

    const platforms  = ['vc', 'tjournal', 'dtf'];
    const sortValues = ['date', 'rating', 'hits', 'commentsCount', 'favoritesCount'];
    const formats    = ['cli', 'csv', 'md'];

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
            ->setDescription('Show formatted info')
            ->addOption('platform', null, InputOption::VALUE_REQUIRED, implode(', ', self::platforms), 'vc')
            ->addOption('section', null, InputOption::VALUE_REQUIRED, 'dev', 'dev')
            ->addOption('sort', null, InputOption::VALUE_REQUIRED, implode(', ', self::sortValues), 'rating')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, '', -1)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, implode(', ', self::formats), 'cli');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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

        $sort = $input->getOption('sort');
        if (!in_array($sort, self::sortValues)) {
            $io->error('Invalid option "sort" value. Valid values: ' . implode(', ', self::sortValues));

            return Command::FAILURE;
        }

        $limit = (int)$input->getOption('limit');
        if ($limit <= 0) {
            $limit = -1;
        }

        $format = $input->getOption('format');
        if (!in_array($format, self::formats)) {
            $io->error('Invalid option "format" value. Valid values: ' . implode(', ', self::formats));

            return Command::FAILURE;
        }

        $qb = $this->articleRepository->createQueryBuilder('a');

        $qb->andWhere('a.platform = :platform');
        $qb->setParameter('platform', $platform);

        $qb->andWhere('a.section = :section');
        $qb->setParameter('section', $section);

        if (!empty($sort)) {
            $qb->orderBy(sprintf('a.%s', $sort), 'DESC');
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $rows = [
            [
                'id',
                'date',
                'url',
                'title',
                'commentsCount',
                'favoritesCount',
                'hits',
                'rating'
            ]
        ];

        foreach ($qb->getQuery()->getResult() as $article) {
            $rows[] = [
                $article->getId(),
                $article->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Moscow'))->format('Y-m-d H:i:s'),
                $article->getUrl(),
                $article->getTitle(),
                $article->getCommentsCount(),
                $article->getFavoritesCount(),
                $article->getHits(),
                $article->getRating()
            ];
        }

        switch ($format) {
            case 'cli':
                $this->renderCli($output, $rows);
                break;
            case 'csv':
                $this->renderCsv($rows);
                break;
            case 'md':
                $this->renderMd($rows);
                break;
        }

        return Command::SUCCESS;
    }

    private function renderCsv(array $rows): void
    {
        $stdout = STDOUT;
        foreach ($rows as $row) {
            fputcsv($stdout, $row);
        }
        fclose($stdout);
    }

    private function renderMd(array $rows): void
    {
        $tableBuilder = new Builder();

        $tableBuilder
            ->headers(array_shift($rows))
            ->rows($rows);
        echo $tableBuilder->render();
    }

    private function renderCli(OutputInterface $output, array $data): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(array_shift($data))
            ->setRows($data);
        $table->render();
    }
}







