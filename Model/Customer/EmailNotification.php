<?php declare(strict_types=1);
/**
 * Rameera_Email
 *
 * @category  MageMatch
 * @package   Rameera\Email
 * @author    MageMatch <arjundhiman90@gmail.com>
 * @copyright 2024 MageMatch
 * @license   MIT
 */

namespace Rameera\Email\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\EmailNotification as CustomerEmailNotification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class EmailNotification
{
    public const XML_PATH_CHANGE_ACCOUNT_TEMPLATE = 'customer/account_information/change_account_template';

    public const XML_PATH_PASSWORD_REVAMPED_WEBSITE_TEMPLATE = 'customer/password/revamped_website_email_template';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataProcessor;

    /**
     * @var CustomerViewHelper
     */
    protected CustomerViewHelper $customerViewHelper;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface         $scopeConfig
     * @param TransportBuilder             $transportBuilder
     * @param StoreManagerInterface        $storeManager
     * @param DataObjectProcessor          $dataProcessor
     * @param CustomerViewHelper           $customerViewHelper
     * @param SenderResolverInterface|null $senderResolver
     * @param Emulation|null               $emulation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        DataObjectProcessor $dataProcessor,
        CustomerViewHelper $customerViewHelper,
        SenderResolverInterface $senderResolver = null,
        Emulation $emulation = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->dataProcessor = $dataProcessor;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * Send Account updated email
     *
     * @param CustomerInterface $customer
     * @param bool              $emailUpdated
     *
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmailAccountChanged(CustomerInterface $customer, bool $emailUpdated = false): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $customerEmailData['name'] = $this->customerViewHelper->getCustomerName($customer);

        $this->sendEmailTemplate(
            $customer,
            !$emailUpdated
                ? self::XML_PATH_CHANGE_ACCOUNT_TEMPLATE
                : CustomerEmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE,
            CustomerEmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send Revamped Website Password
     *
     * @param CustomerInterface $customer
     * @param null              $rpToken
     *
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmailRevampedWebsitePassword(CustomerInterface $customer, $rpToken = null): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $customerEmailData['name'] = $this->customerViewHelper->getCustomerName($customer);
        if ($rpToken !== null) {
            $customerEmailData['rp_token'] = $rpToken;
        }

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_PASSWORD_REVAMPED_WEBSITE_TEMPLATE,
            CustomerEmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ): void {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND);
        $transport->sendMessage();
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getWebsiteStoreId($customer, $defaultStoreId = null): int
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }
}
