<?php declare(strict_types = 1);
namespace App;

use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class IO
 */
class IO
{
    private $inputFile;
    private $outputFile;
    private $suffix;

    private $count;
    private $reader;
    private $writer;

    public function __construct($path, $suffix = 'output')
    {
        $this->suffix = $suffix;
        $path = $this->checkInputFilePath($path);
        $this->assemblePaths($path);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'reader':
                return $this->reader ?? $this->initReader();
            case 'writer':
                return $this->writer ?? $this->initWriter();
            case 'count':
                return $this->count ?? $this->countLines();
            default:
                throw new InvalidArgumentException("$name not allowed", 1);
        }
    }

    private function checkInputFilePath($path)
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new InvalidArgumentException("$path not exists or not a file", 1);
        }
        return realpath($path) ?: realpath(__DIR__.DIRECTORY_SEPARATOR.$path) ;
    }

    private function assemblePaths($path)
    {
        $this->inputFile  = $path;
        $fileInfo         = pathinfo($this->inputFile);
        $this->outputFile = $fileInfo['dirname'].
            DIRECTORY_SEPARATOR.
            $fileInfo['filename'].
            '-'.
            $this->suffix;
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
            $line = fgets($handle);
            $this->count++;
        }
        fclose($handle);
        return $this->count;
    }
}
