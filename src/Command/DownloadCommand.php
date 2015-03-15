<?php

namespace Seven\OneskyDownloader\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('download')
            ->setDescription('Download translations')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Locale')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source file')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file name pattern', '[filename].[locale].[extension]')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pattern = $input->getOption('output');
        $sources = $this->getSourceFiles($input);
        $locales = $this->getLocales($input);

        foreach ($sources as $source) {
            $this->downloadFile($output, $source, $locales, $pattern);
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    protected function getLocales(InputInterface $input)
    {
        if ($locale = $input->getOption('locale')) {
            $this->checkLocale($locale);

            return array($locale);
        }

        return $this->getAllLocales();
    }

    /**
     * @param string $locale
     *
     * @throws \Exception
     */
    private function checkLocale($locale)
    {
        $available = $this->getAllLocales();

        if ($diff = array_diff($locale, $available)) {
            throw new \Exception(sprintf(
                'Locale "%s" is not available. Available locales are: "%s"',
                reset($diff),
                implode('", "', $available)
            ));
        }
    }

    /**
     * @return array
     */
    private function getAllLocales()
    {
        $client    = $this->getContainer()->getOneskyClient();
        $projectId = $this->getContainer()->getOneskyProjectId();
        $response  = json_decode($client->projects('languages', array('project_id' => $projectId)), true);
        $data      = $response['data'];

        return array_map(function($item) { return $item['locale']; }, $data);
    }

    /**
     * @param InputInterface $input
     * @return array
     * @throws \Exception
     */
    protected function getSourceFiles(InputInterface $input)
    {
        if ($source = $input->getOption('source')) {
            $this->checkSourceFile($source);

            return array($source);
        }

        return $this->getAllSourceFiles();
    }

    /**
     * @param string $source
     *
     * @throws \Exception
     */
    private function checkSourceFile($source)
    {
        $files = $this->getAllSourceFiles();

        if (!in_array($source, $files)) {
            throw new \Exception(sprintf(
                'File "%s" does not exist, available files are: "%s"',
                $source,
                implode('", "', $files)
            ));
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $source
     * @param array           $locales
     * @param string          $pattern
     */
    private function downloadFile($output, $source, array $locales, $pattern)
    {
        $output->writeln("<info>Downloading file \"$source\"...</info>");

        foreach ($locales as $locale) {
            $destinationFilename = $this->getOutputFilename($pattern, $locale, $source);
            $this->download($output, $source, $locale, $destinationFilename);
        }
    }

    /**
     * @param string $pattern
     * @param string $locale
     * @param string $source
     *
     * @return string
     */
    private function getOutputFilename($pattern, $locale, $source)
    {
        return strtr($pattern, array(
            '[dirname]'   => pathinfo($source, PATHINFO_DIRNAME),
            '[filename]'  => pathinfo($source, PATHINFO_FILENAME),
            '[locale]'    => $locale,
            '[extension]' => pathinfo($source, PATHINFO_EXTENSION),
        ));
    }

    /**
     * @param OutputInterface $output
     * @param string          $source
     * @param string          $locale
     * @param string          $filename
     */
    private function download($output, $source, $locale, $filename)
    {
        $output->writeln("Generate dictionary \"$filename\" for \"$locale\"...");

        $client = $this->getContainer()->getOneskyClient();
        $content = $client->translations(
            'export',
            array(
                'project_id' => $this->getContainer()->getOneskyProjectId(),
                'locale' => $locale,
                'source_file_name' => $source
            )
        );

        $this->writeFile($filename, $content);
    }

    /**
     * @return array
     */
    private function getAllSourceFiles()
    {
        $client = $this->getContainer()->getOneskyClient();
        $projectId = $this->getContainer()->getOneskyProjectId();

        $response = json_decode($client->files('list', array('project_id' => $projectId, 'per_page' => 100)), true);
        $data = $response['data'];

        $files = array_map(
            function ($item) {
                return $item['file_name'];
            },
            $data
        );

        return $files;
    }

    /**
     * @param string $filename
     * @param string $content
     */
    private function writeFile($filename, $content)
    {
        $this->createFilePath($filename);

        file_put_contents($filename, $content);
    }

    /**
     * @param string $filename
     *
     * @throws \Exception
     */
    private function createFilePath($filename)
    {
        if (file_exists($filename)) {
            if (!is_writable($filename)) {
                throw new \Exception(sprintf('File path "%s" is not writable', $filename));
            }

            return;
        }

        $dir = dirname($filename);

        if (is_dir($dir)) {
            if (!is_writable($dir)) {
                throw new \Exception(sprintf('Directory "%s" is not writable', $dir));
            }

            return;
        }

        if (!mkdir($dir, 0777, true)) {
            throw new \Exception(sprintf('Unable to create directory "%s"', $dir));
        }
    }
}
