<?php declare(strict_types = 1);
namespace App\Command;

use App\IO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ToJoin
 */
class ToJoin extends Command
{
    private $headers = ['base', 'value', 'related'];

    /**
     * @method  configure
     * @return  void
     */
    protected function configure()
    {
        $this->setName('to:join')
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('GCT -> CSV Converter.')
            ->setHelp('This command convert GCT file to CSV');
    }

    /**
     * @method  execute
     * @param   InputInterface   $input
     * @param   OutputInterface  $output
     * @return  void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // redraw & write frequency
        $chunk = 10000;
        $io    = new IO($input->getArgument('filename'), 'join.csv');

        // Count for steps with a message
        $section = $output->section();
        $section->write('Counting Records...');
        $max = count($io->reader) ** 2;
        $section->clear();

        // Start Progress Bar
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setRedrawFrequency($chunk);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        // Init CSVs
        $io->reader->setDelimiter("\t");
        $io->reader->setHeaderOffset(2);
        $io->writer->insertOne($this->headers);
        $io->writer->setFlushThreshold($chunk);

        // Convert Input CSV to Output
        $generator = $this->convertToJoin($io);
        foreach ($generator as $value) {
            $progressBar->advance();
        }

        // Finish Progres Bar
        $progressBar->finish();
        $output->writeln('');
    }

    private function convertToJoin($io)
    {
        foreach ($io->reader as $offset => $record) {
            if ($offset < 2) {
                continue;
            }
            $base = array_shift($record);
            foreach ($record as $related => $value) {
                $buffer = array_combine($this->headers, [$base, $value, $related]);
                $io->writer->insertOne($buffer);
                yield;
            }
        }
    }
}
