<?php

namespace Michaelbelgium\Mybbtoflarum;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logger
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }


    public function info($message): void
    {
        $this->output->writeln("<info>$message</info>");
    }

    public function debug($message): void
    {
        $this->output->writeln($message, OutputInterface::OUTPUT_NORMAL| OutputInterface::VERBOSITY_DEBUG);
    }

    public function error($message): void
    {
        if ($this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->writeln("<error>$message</error>");
        } else {
            $this->output->writeln("<error>$message</error>");
        }
    }


}
