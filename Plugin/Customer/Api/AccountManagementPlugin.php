<?php declare(strict_types=1);
/**
 * Rameera_Email
 *
 * @category  Rameera
 * @package   Rameera\Email
 * @author    Rameera <arjundhiman90@gmail.com>
 * @copyright 2024 Rameera
 * @license   MIT
 */

namespace Rameera\Email\Plugin\Customer\Api;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\EmailNotificationInterface as CustomerEmailNotificationInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class AccountManagementPlugin
{
    /**
     * @var CustomerEmailNotificationInterface
     */
    protected CustomerEmailNotificationInterface $emailNotification;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * Constructor
     *
     * @param CustomerEmailNotificationInterface $emailNotification
     * @param CustomerRepositoryInterface        $customerRepository
     */
    public function __construct(
        CustomerEmailNotificationInterface $emailNotification,
        CustomerRepositoryInterface        $customerRepository
    ) {
        $this->emailNotification  = $emailNotification;
        $this->customerRepository = $customerRepository;
    }

    /**
     * After password change plugin
     *
     * @param AccountManagementInterface $subject
     * @param                                                  $result
     * @param                                                  $customerId
     * @param                                                  $currentPassword
     * @param                                                  $newPassword
     *
     * @return mixed
     * @throws InvalidEmailOrPasswordException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function afterChangePasswordById(
        AccountManagementInterface $subject,
        $result,
        $customerId,
        $currentPassword,
        $newPassword
    ) {
        if ($result === true) {
            try {
                $customer = $this->customerRepository->getById($customerId);

                $this->emailNotification->credentialsChanged($customer, $customer->getEmail(), true);
            } catch (NoSuchEntityException $e) {
                throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
            }
        }

        return $result;
    }

    /**
     * After password reset plugin
     *
     * @param AccountManagementInterface $subject
     * @param                            $result
     * @param                            $email
     * @param                            $resetToken
     * @param                            $newPassword
     *
     * @return bool|mixed
     * @throws InvalidEmailOrPasswordException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterResetPassword(
        AccountManagementInterface $subject,
        $result,
        $email,
        $resetToken,
        $newPassword
    ) {
        if ($result === true) {
            try {
                $customer = $this->customerRepository->get($email);

                $this->emailNotification->credentialsChanged($customer, $email, true);
            } catch (NoSuchEntityException $e) {
                throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
            }
        }

        return $result;
    }
}
