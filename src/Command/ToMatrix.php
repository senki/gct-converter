<?php declare(strict_types = 1);
namespace App\Command;

use App\IO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ToMatrix
 */
class ToMatrix extends Command
{
    /**
     * @method  configure
     * @return  void
     */
    protected function configure()
    {
        $this->setName('to:matrix')
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('CSV -> GCT Converter.')
            ->setHelp('This command convert CSV file to GCT');
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
        $chunk = 100;
        $io    = new IO($input->getArgument('filename'), 'matrix.csv');

        // Count for steps with a message
        $section = $output->section();
        $section->write('Counting Records...');
        $max = (int) round(sqrt($io->count - 1));
        $section->write("  $max record found.");
        $section->clear();

        // Init CSVs
        $io->reader->setHeaderOffset(0);
        $io->writer->setDelimiter("\t");
        $io->writer->setFlushThreshold($chunk);

        // Creating Header with a message
        $section = $output->section();
        $section->write('Collecting Headers...');
        $this->createHeader($io);
        $section->clear();

        // Start Progress Bar
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setRedrawFrequency($chunk);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        // Convert Input CSV to Output
        $generator = $this->convertToMatrix($io);
        foreach ($generator as $value) {
            $progressBar->advance();
        }

        // Finish Progres Bar
        $progressBar->finish();
        $output->writeln('');
    }

    private function createHeader($io)
    {
        $header = ['Name'];
        $currentColumn = '';
        foreach ($io->reader as $offset => $record) {
            $joinRecord = array_values($record);

            if (strlen($currentColumn) === 0) {
                $currentColumn = $joinRecord[0];
                array_push($header, $joinRecord[2]);
                continue;
            }

            if ($joinRecord[0] === $currentColumn) {
                array_push($header, $joinRecord[2]);
            } else {
                $io->writer->insertOne(['#1.2']);
                $count = count($header) -1;
                $io->writer->insertOne([$count, $count]);
                $io->writer->insertOne($header);
                return;
            }
        }
    }

    private function convertToMatrix($io)
    {
        $buffer = [];
        $prevRecord = [];
        foreach ($io->reader as $offset => $record) {
            $joinRecord = array_values($record);

            if (empty($prevRecord)) {
                $prevRecord = $joinRecord;
                array_push($buffer, $joinRecord[0], $joinRecord[1]);
                continue;
            }

            if ($joinRecord[0] === $prevRecord[0]) {
                array_push($buffer, $joinRecord[1]);
            } else {
                $prevRecord = $joinRecord;
                $io->writer->insertOne($buffer);
                $buffer =[];
                array_push($buffer, $joinRecord[0], $joinRecord[1]);
                yield;
            }
        }
        $io->writer->insertOne($buffer);
    }
}
