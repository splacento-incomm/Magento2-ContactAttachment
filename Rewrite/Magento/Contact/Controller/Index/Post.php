<?php
namespace Debuglabs\ContactAttachment\Rewrite\Magento\Contact\Controller\Index;

use Debuglabs\ContactAttachment\Rewrite\Magento\Framework\Mail\Template\TransportBuilder;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File;

/**
 * Post controller class
 */
class Post extends \Magento\Contact\Controller\Index\Post
{
    const FOLDER_LOCATION = 'contactattachment';

    public function __construct(
        private Context                         $context,
        private MailInterface                   $mail,
        private readonly DataPersistorInterface $dataPersistor,
        private ?LoggerInterface                $logger,
        private readonly UploaderFactory        $fileUploaderFactory,
        private readonly Filesystem             $fileSystem,
        private readonly StateInterface         $inlineTranslation,
        private readonly ConfigInterface        $contactsConfig,
        private readonly TransportBuilder       $transportBuilder,
        private readonly StoreManagerInterface  $storeManager,
        ScopeConfigInterface                    $scopeConfig,
        File                                    $file
    ) {
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->scopeConfig = $scopeConfig;
        $this->file = $file;
        parent::__construct($context, $contactsConfig, $mail, $dataPersistor, $logger);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $this->sendEmail($this->validatedParams());
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    private function sendEmail($post)
    {
        $this->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    public function send($replyTo, array $variables)
    {
        $filePath = null;
        $fileName = null;
        $uploaded = false;

        try {
            $fileCheck = $this->fileUploaderFactory->create(['fileId' => 'attachment']);
            $file = $fileCheck->validateFile();
            $attachment = $file['name'] ?? null;
        } catch (\Exception $e) {
            $attachment = null;
        }
        $filesData = $this->getRequest()->getFiles();
        if ($attachment) {
            $upload = $this->fileUploaderFactory->create(['fileId' => 'attachment']);
            $upload->setAllowRenameFiles(true);
            $upload->setFilesDispersion(true);
            $upload->setAllowCreateFolders(true);
            $upload->setAllowedExtensions(['txt', 'jpg', 'jpeg', 'gif', 'png']);

            $path = $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::FOLDER_LOCATION);
            $result = $upload->save($path);
            $uploaded = self::FOLDER_LOCATION . $upload->getUploadedFilename();
            $filePath = $result['path'] . $result['file'];
            $fileName = $result['name'];
        }

        /** @see \Magento\Contact\Controller\Index\Post::validatedParams() */
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;

        $this->inlineTranslation->suspend();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($variables)
            ->setFrom($this->contactsConfig->emailSender())
            ->addTo($this->contactsConfig->emailRecipient())
            ->setReplyTo($replyTo, $replyToName)
            ->getTransport();

        if ($uploaded && !empty($filePath) && $this->file->fileExists($filePath)) {
            $mimeType = mime_content_type($filePath);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->addAttachment($this->file->read($filePath), $fileName, $mimeType)
                ->setTemplateVars($variables)
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($this->contactsConfig->emailRecipient())
                ->setReplyTo($replyTo, $replyToName)
                ->getTransport();
        }

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    private function validatedParams(): array
    {
        $request = $this->getRequest();
        return $request->getParams();
    }
}
