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

namespace Rameera\Email\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderFormattedDate implements ObserverInterface
{
    /**
     * Date Format
     */
    public const DATE_FORMAT = 'd/m/y';

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

    /**
     * Constructor
     *
     * @param TimezoneInterface $timezone
     */
    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var DataObject $transportObject */
        $transportObject = $observer->getData('transportObject');

        /** @var OrderInterface $order */
        $order = $transportObject->getData('order');

        $transportObject->setData(
            'hudson_order_created_at_formatted',
            $this->timezone->date(new \DateTime((string)$order->getCreatedAt()))->format(self::DATE_FORMAT)
        );

        return $this;
    }
}
