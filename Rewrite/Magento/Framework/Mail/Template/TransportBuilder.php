<?php
namespace Debuglabs\ContactAttachment\Rewrite\Magento\Framework\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    protected array $attachments = [];
    private array $messageData = [];
    private EmailMessageInterfaceFactory $emailMessageInterfaceFactory;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        AddressConverter $addressConverter = null
    ) {
        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory);
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $objectManager->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $objectManager->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $objectManager->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $addressConverter ?: $objectManager->get(AddressConverter::class);
    }


    public function addCc($address, $name = ''): self
    {
        $this->addAddressByType('cc', $address, $name);
        return $this;
    }

    public function addTo($address, $name = ''): self
    {
        $this->addAddressByType('to', $address, $name);
        return $this;
    }

    public function addBcc($address): self
    {
        $this->addAddressByType('bcc', $address);
        return $this;
    }

    public function setReplyTo($email, $name = null): self
    {
        $this->addAddressByType('replyTo', $email, $name);
        return $this;
    }

    public function setFrom($from): self
    {
        return $this->setFromByScope($from);
    }

    public function setFromByScope($from, $scopeId = null): self
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);
        return $this;
    }

    public function setTemplateIdentifier($templateIdentifier): self
    {
        $this->templateIdentifier = $templateIdentifier;
        return $this;
    }

    public function setTemplateModel($templateModel): self
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    public function setTemplateVars($templateVars): self
    {
        $this->templateVars = $templateVars;
        return $this;
    }

    public function setTemplateOptions($templateOptions): self
    {
        $this->templateOptions = $templateOptions;
        return $this;
    }

    protected function reset(): self
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    protected function prepareMessage(): self
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        switch ($template->getType()) { //match WONT WORK HERE, don't try to refactor it.
            case TemplateTypesInterface::TYPE_TEXT:
                $partType = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $partType = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }


        $mimePart = $this->mimePartInterfaceFactory->create(['content' => $content]);
        $parts = count($this->attachments) ? array_merge([$mimePart], $this->attachments) : [$mimePart];
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(['parts' => $parts]);
        $this->messageData['subject'] = html_entity_decode((string)$template->getSubject(), ENT_QUOTES);
        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        return $this;
    }

    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge($this->messageData[$addressType], $convertedAddressArray);
        }
    }

    public function addAttachment(?string $content, ?string $fileName, ?string $fileType): self
    {
        $attachmentPart = new MimePart($content);
        $attachmentPart->type = $fileType;
        $attachmentPart->filename = $fileName;
        $attachmentPart->disposition = Mime::DISPOSITION_ATTACHMENT;
        $attachmentPart->encoding = Mime::ENCODING_BASE64;
        $this->attachments[] = $attachmentPart;
        return $this;
    }
}
