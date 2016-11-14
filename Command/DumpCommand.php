<?php

namespace Becklyn\AssetsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class DumpCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('becklyn:assets:dump')
            ->setDescription('Scans all loaded bundles for .html.twig files and dumps all discovered javascripts and stylesheets assets.');
    }


    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $io)
    {
        $io = new SymfonyStyle($input, $io);

        $io->title("Becklyn Assets Bundle");
        $this->getContainer()->get("becklyn.assets.handler")->regenerateCache($io);

        $io->success("All done.");
    }
}
