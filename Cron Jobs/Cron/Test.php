<?php


namespace Codilar\HelloWorld\Cron;


class Test
{

    public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Hello');

        return $this;

    }
}
