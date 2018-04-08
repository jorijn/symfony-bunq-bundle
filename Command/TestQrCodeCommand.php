<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use BaconQrCode\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestQrCodeCommand extends Command
{
    const STRING = 'string';

    /** @var Writer */
    protected $writer;

    /**
     * TestQrCodeCommand constructor.
     *
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {
        parent::__construct(null);

        $this->writer = $writer;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('Development command to test the generation of QR codes on the terminal')
            ->addArgument(self::STRING, InputArgument::REQUIRED, 'The string to encode');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $string = $input->getArgument(self::STRING);
        $contents = $this->writer->writeString($string);

        $output->write($contents);
    }
}
