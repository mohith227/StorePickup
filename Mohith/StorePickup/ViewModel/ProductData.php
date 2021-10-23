<?php

namespace Mohith\StorePickup\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

class ProductData extends View implements ArgumentInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context                                 $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface                        $jsonEncoder,
        StringUtils                             $string,
        Product                                 $productHelper,
        ConfigInterface                         $productTypeConfig,
        FormatInterface                         $localeFormat,
        Session                                 $customerSession,
        ProductRepositoryInterface              $productRepository,
        PriceCurrencyInterface                  $priceCurrency,
        array                                   $data = [],
        SearchCriteriaBuilder                   $searchCriteriaBuilder,
        SourceItemRepositoryInterface           $sourceItemRepository,
        SourceRepositoryInterface               $sourceRepository,
        LoggerInterface                         $logger
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;
    }

    /**
     * Retrieves links that are assigned to stockId
     *
     * @return SourceItemInterface[]
     */
    public function getSourceItemDetailBySKU(): array
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $this->getProduct()->getSku())
                ->create();

            return $this->sourceItemRepository->getList($searchCriteria)->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get source details
     *
     * @return SourceInterface[]|null
     */
    public function getSourcesDetails()
    {
        $sources = $this->getSourceItemDetailBySKU();
        $sourcesDetails = [];
        foreach ($sources as $source) {
            $sourceInfo = null;
            $sourceCode = $source->getSourceCode();
            try {
                $sourceInfo = $this->sourceRepository->get($sourceCode);
                if ($sourceInfo->getData('is_pickup_location_active') == '1') {
                    array_push($sourcesDetails, $sourceInfo);
                }
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $sourceInfo = null;
            }
        }
        return $sourcesDetails;
    }
}
