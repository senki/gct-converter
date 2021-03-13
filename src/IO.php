<?php declare(strict_types = 1);
namespace App;

use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Webmozart\PathUtil\Path;

/**
 * Class IO
 */
class IO
{
    private $inputFile;
    private $outputFile;
    private $cwd;

    private $count;
    private $reader;
    private $writer;

    public function __construct($path, $suffix = 'output.ext')
    {
        $cwd = \getcwd();
        if ($cwd === false) {
            throw new InvalidArgumentException("CWD: $cwd not get. Wierd", 1);
        }
        $this->cwd        = $cwd;
        $this->inputFile  = $this->setInputFilePath($path);
        $this->outputFile = $this->setOutputFilePath($path, $suffix);
    }

    public function __get($type)
    {
        switch ($type) {
            case 'reader':
                return $this->reader ?? $this->initReader();
            case 'writer':
                return $this->writer ?? $this->initWriter();
            case 'count':
                return $this->count ?? $this->countLines();
            default:
                throw new InvalidArgumentException("$type not allowed", 1);
        }
    }

    private function setInputFilePath($path)
    {
        $path = Path::makeAbsolute($path, $this->cwd);
        if (!file_exists($path) || !is_file($path)) {
            throw new InvalidArgumentException("$path not exists or not a file", 1);
        }
        return $path;
    }

    private function setOutputFilePath($path, $suffix)
    {
        return Path::canonicalize(
            Path::getDirectory($path) .
            Path::getFilenameWithoutExtension($path) .
            '-' .
            $suffix
        );
    }

    private function initReader()
    {
        $this->reader = Reader::createFromPath($this->inputFile, 'r');
        return $this->reader;
    }

    private function initWriter()
    {
        $this->writer = Writer::createFromPath($this->outputFile, 'w+');
        return $this->writer;
    }

    private function countLines()
    {
        $this->count = 0;
        $handle = fopen($this->inputFile, "r");
        while (!feof($handle)) {
            $line = fgets($handle, 4096);
            if ($line !== false) {
                $this->count = $this->count + substr_count($line, PHP_EOL);
            }
        }
        fclose($handle);
        return $this->count;
    }
}
