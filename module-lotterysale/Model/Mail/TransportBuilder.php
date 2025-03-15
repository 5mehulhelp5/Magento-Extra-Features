<?php

namespace Casio\LotterySale\Model\Mail;

use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class TransportBuilder
{
    /**
     * Mail transport builder
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * TransportBuilder constructor.
     * @param StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * @param string|int $templateIdentifier
     * @param array $templateOptions
     * @param array $templateVars
     * @param string|array $from
     * @param array|string $to
     * @return TransportBuilder
     */
    public function sendMail($templateIdentifier, array $templateOptions, array $templateVars, $from, $to, $storeId = null): TransportBuilder
    {
        $this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder->setTemplateIdentifier(
                $templateIdentifier
            )->setTemplateOptions(
                $templateOptions
            )->setTemplateVars(
                $templateVars
            )->setFromByScope(
                $from,
                $storeId
            )->addTo(
                $to
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error('Sitemap sendErrors: '.$e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }

        return $this;
    }
}
