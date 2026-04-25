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

namespace Rameera\Email\Plugin\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Rameera\Email\Model\Customer\EmailNotification;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;

class UpdateCustomerAccountPlugin
{
    /**
     * @var EmailNotification
     */
    protected EmailNotification $emailNotification;

    /**
     * Constructor
     *
     * @param EmailNotification $emailNotification
     */
    public function __construct(EmailNotification $emailNotification)
    {
        $this->emailNotification = $emailNotification;
    }

    /**
     * After customer account update plugin
     *
     * @param UpdateCustomerAccount $subject
     * @param                       $result
     * @param CustomerInterface     $customer
     * @param array                 $data
     * @param StoreInterface        $store
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(
        UpdateCustomerAccount $subject,
        $result,
        CustomerInterface $customer,
        array $data,
        StoreInterface $store
    ) {
        if (isset($data['firstname'], $data['lastname'])) {
            $this->emailNotification->sendEmailAccountChanged($customer);
        }

        if (isset($data['email'])) {
            $this->emailNotification->sendEmailAccountChanged($customer, true);
        }

        return $result;
    }
}
