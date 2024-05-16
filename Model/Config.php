<?php
namespace Debuglabs\ContactAttachment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_ALLOWED_FILE_TYPES = 'contactattachment/general/allowed_file_types';
    const XML_PATH_MAX_FILE_SIZE = 'contactattachment/general/max_file_size';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig){}

    public function getAllowedFileTypes()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ALLOWED_FILE_TYPES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMaxFileSize()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAX_FILE_SIZE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
