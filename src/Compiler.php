<?php

namespace Seven\OneskyDownloader;

use Symfony\Component\Finder\Finder;

class Compiler
{
    public function compile($pharFile = 'onesky.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'onesky.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finders = array();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__)
        ;
        $finders[] = $finder;

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in(dirname(__DIR__) . "/vendor")
        ;
        $finders[] = $finder;

        foreach ($finders as $finder) {
            foreach ($finder as $file) {
                $this->addFile($phar, $file);
            }
        }

        $this->addBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        $phar->compressFiles(\Phar::GZ);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace(dirname(__DIR__).DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        }

        $phar->addFromString($path, $content);
    }

    private function addBin($phar)
    {
        $content = file_get_contents(dirname(__DIR__).'/onesky');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('onesky', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php

Phar::mapPhar('onesky.phar');

require 'phar://onesky.phar/onesky';

__HALT_COMPILER();
EOF;
    }
}