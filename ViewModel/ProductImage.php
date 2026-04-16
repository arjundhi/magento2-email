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

namespace Rameera\Email\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Api\Data\StoreInterface;

class ProductImage implements ArgumentInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get media path to the product image or placeholder
     *
     * @param OrderItemInterface $item
     * @param StoreInterface     $store
     *
     * @return string|null
     */
    public function getImageUrl(OrderItemInterface $item, StoreInterface $store): ?string
    {
        $product = $item->getProduct();
        $productImage = $product->getImage();

        if ($product->getTypeId() == ConfigurableType::TYPE_CODE) {
            try {
                $childProduct = $this->productRepository->get($item->getSku());
                $childProductImage = $childProduct->getImage();
                if ($childProductImage && $childProductImage != 'no_selection') {
                     $productImage = $childProductImage;
                }
            } catch (NoSuchEntityException $e) {
            }
        }

        if ($productImage && $productImage != 'no_selection') {
            return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $productImage;
        }

        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'email/product_placeholder.png';
    }
}
