<?php

declare(strict_types=1);

/*
 * Contao Package Indexer
 *
 * @copyright  Copyright (c) 2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 */

namespace App\Command;

use App\Indexer;
use GitWrapper\GitWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractIndexCommand extends Command
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var string
     */
    private $metaDataDir;

    public function __construct(Indexer $indexer, string $metaDataDir)
    {
        $this->indexer = $indexer;
        $this->metaDataDir = $metaDataDir;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addArgument('package', InputArgument::OPTIONAL, 'Restrict indexing to a given package name.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not index any data. Very useful together with -vvv.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitWrapper = new GitWrapper();
        $git = $gitWrapper->workingCopy($this->metaDataDir);

        if (!$git->isCloned()) {
            $git->cloneRepository('https://github.com/contao/package-metadata.git');
        }

        $git->pull();

        $this->indexer->index($input->getArgument('package'), (bool) $input->getOption('dry-run'));
    }

    abstract protected function getCommandName(): string;

    abstract protected function getCommandDescription(): string;
}