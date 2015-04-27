<?php

namespace Becklyn\AssetsBundle\Command;

use Becklyn\AssetsBundle\Entity\StatusMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Locates all assets in all loaded bundles and dumps them into a cache
 */
class DumpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure ()
    {
        $this
            ->setName('becklyn:assets:dump')
            ->setDescription("Scans all loaded bundles for .html.twig files and dumps all discovered javascripts and stylesheets assets.")
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Overrides the Asset Cache Table.'
            )
            ->addOption(
                'silent',
                null,
                InputOption::VALUE_NONE,
                'Suppresses printing all output but the final status message.'
            )
            ->addOption(
                'clear',
                null,
                InputOption::VALUE_NONE,
                'Clears the asset output directory, depending on the AssetCacheAdadpter'
            );
    }


    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int Process exit code
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $twigAssetsFinder   = $this->getContainer()->get('becklyn.assets.twig_assets_finder');
        $twigTemplateFinder = $this->getContainer()->get('becklyn.assets.twig_template_finder');
        $assetsDumper       = $this->getContainer()->get('becklyn.assets.assets_dumper');
        $cacheBuilder       = $this->getContainer()->get('becklyn.assets.cache.cache_builder');
        $formatterHelper    = $this->getHelper('formatter');
        $forceOverride      = $input->getOption('force');
        $clearOutput        = $input->getOption('clear');
        $silentOutput       = $input->getOption('silent');
        $assets             = [];
        $statusLog          = [];

        $output->writeln($formatterHelper->formatBlock(['', 'Becklyn Assets', ''], 'comment'));


        // #1: Retrieve all templates
        $bundleTemplatePaths = $twigTemplateFinder->getAllTemplatePaths();

        if (!$silentOutput)
        {
            $this->printBundleTemplatePaths($output, $bundleTemplatePaths);
            $output->writeln("\n");
        }


        // #2: Parse all templates and extract assets
        foreach ($bundleTemplatePaths as $bundle => $templatePaths)
        {
            $assets = array_merge_recursive($assets, $twigAssetsFinder->getAssetPaths($templatePaths));
        }

        if (!$silentOutput)
        {
            // Print any errors that may have occurred during template discovery
            $this->printTemplateDiscoveryErrors($output, $assets['errors']);
        }

        // Check if we've found any assets that we can dump
        if (empty($assets['assets']))
        {
            $output->writeln("\n<error>»» Dumping failed. No assets found.</error>");

            return 1;
        }
        // TODO: Check for errors?

        // Optionally clear the output directory
        if ($clearOutput)
        {
            $this->clearOutputDirectory();
        }


        // #3: Dump all assets and create the static cache
        foreach ($assets['assets'] as $assetCollection)
        {
            $statusLog[] = $assetsDumper->dumpAssets($assetCollection, $forceOverride);
        }

        if (!$silentOutput)
        {
            // Print the overview table showing which template assets have been dumped into which asset bundle
            $this->printStatusLogTable($output, $statusLog);
        }

        // Call each CacheAdapter to perform its persistent operation, if necessary
        $cacheBuilder->build(
            [
                'override' => $forceOverride,
                'clear'    => $clearOutput,
            ]
        );

        $output->writeln(
            sprintf("\n<info>»»</info> Dumping completed with <info>%s successful</info> exports and <info>%s errors</info>.",
                $this->getStatusLogCountByStatus($statusLog, StatusMessage::STATUS_SUCCESS),
                $this->getStatusLogCountByStatus($statusLog, StatusMessage::STATUS_ERROR)
            )
        );

        return 0;
    }


    /**
     * Prints a table with all templates found in a bundle that contain asset references
     *
     * @param OutputInterface $output
     * @param array           $templatePaths
     */
    private function printBundleTemplatePaths (OutputInterface $output, array $templatePaths)
    {
        $rows = [];
        foreach ($templatePaths as $bundle => $bundleTemplatePaths)
        {
            foreach ($bundleTemplatePaths as $templatePath)
            {
                $rows[] = [
                    $bundle, $templatePath
                ];
            }
        }

        $rows[] = ['', '<info>Total Templates: ' . count($rows) . '</info>'];

        $output->writeln("\n<info>Found the following Twig templates that are referencing assets.</info>");
        $this->getHelper('table')
             ->setHeaders(['Bundle', 'Path'])
             ->setRows($rows)
             ->render($output);
    }


    /**
     * Prints an exception block for any errors that have occurred during template discovery
     *
     * @param OutputInterface $output
     * @param array           $errors
     */
    private function printTemplateDiscoveryErrors (OutputInterface $output, array $errors)
    {
        if (empty($errors))
        {
            return;
        }

        $errorMessages = implode("\n   ", $errors);

        $errorBlock = $this->getHelper('formatter')->formatBlock(['', '  The following errors have occurred during template discovery:  ', "  {$errorMessages}  ", ''], 'error');
        $output->writeln("\n$errorBlock\n");
    }


    /**
     * Prints the Status Log to the output
     *
     * @param OutputInterface $output
     * @param StatusMessage[] $statusLogs
     */
    private function printStatusLogTable (OutputInterface $output, array $statusLogs)
    {
        $rows = [];
        foreach ($statusLogs as $statusLog)
        {
            $rows[] = [
                $statusLog->getSubject(),
                ($statusLog->getStatus() === 'success') ? "<info>{$statusLog->getStatus()}</info>" : "<error>{$statusLog->getStatus()}</error>",
                $statusLog->getMessage()
            ];
        }

        $output->writeln('<info>Asset Dumping Report:</info>');
        $this->getHelper('table')
             ->setHeaders(['Template', 'Status', 'Message'])
             ->setRows($rows)
             ->render($output);
    }


    /**
     * Returns the amount of entries with the given status
     *
     * @param StatusMessage[] $statusLogs
     * @param int             $status
     *
     * @return int
     */
    private function getStatusLogCountByStatus (array $statusLogs, $status)
    {
        $count = 0;

        foreach ($statusLogs as $statusLog)
        {
            if ($statusLog->getStatus() === $status)
            {
                $count++;
            }
        }

        return $count;
    }


    /**
     * Clears the output directory
     */
    private function clearOutputDirectory ()
    {
        $fileSystem    = new Filesystem();
        $configService = $this->getContainer()->get('becklyn.assets.configuration');

        $outputDirectories = [
            $configService->getLogicalJavascriptPath(),
            $configService->getLogicalStylesheetPath(),
        ];

        foreach ($outputDirectories as $outputDirectory)
        {
            if ($fileSystem->exists($outputDirectory))
            {
                try
                {
                    $fileSystem->remove($outputDirectory);
                }
                catch (IOException $e)
                {
                    // Swallow any exceptions
                }
            }
        }
    }
}
