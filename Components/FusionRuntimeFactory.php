<?php declare(strict_types=1);

namespace SitegeistShopwareFusionRenderer\Components;

use Sitegeist\Fusion\Standalone\Core\Runtime;
use Sitegeist\Fusion\Standalone\FusionObjects as FusionObjects;
use Sitegeist\Eel\Standalone\Utility as EelUtility;
use Sitegeist\Eel\Standalone\Helper as EelHelper;
use Sitegeist\Eel\Standalone\CompilingEvaluator;

use Neos\Utility\Files;
use Neos\Utility\Arrays;

use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class FusionRuntimeFactory
 * @package SitegeistShopwareFusionRenderer\Components
 */
class FusionRuntimeFactory
{
    const EEL_HELPER_IMPLEMENTATIONS = [
        'Array' => EelHelper\ArrayHelper::class,
        'Date' => EelHelper\DateHelper::class,
        'Json' => EelHelper\JsonHelper::class,
        'Math' => EelHelper\MathHelper::class,
        'String' => EelHelper\StringHelper::class,
        'Type' => EelHelper\TypeHelper::class
    ];

    const FUSION_OBJECT_IMPLEMENTATIONS = [
        'Neos.Fusion:Array' => FusionObjects\ArrayImplementation::class,
        'Neos.Fusion:Value' => FusionObjects\ValueImplementation::class,
        'Neos.Fusion:Component' => FusionObjects\ComponentImplementation::class,
        'Neos.Fusion:Collection' => FusionObjects\CollectionImplementation::class,
        'Neos.Fusion:Augmenter' => FusionObjects\AugmenterImplementation::class,
        'Neos.Fusion:Attributes' => FusionObjects\AttributesImplementation::class,
        'Neos.Fusion:Tag' => FusionObjects\TagImplementation::class,
        'Neos.Fusion:RawArray' => FusionObjects\RawArrayImplementation::class,
        'Neos.Fusion:RawCollection' =>  FusionObjects\RawCollectionImplementation::class,
        'PackageFactory.AtomicFusion:Component' => FusionObjects\ComponentImplementation::class,
        'PackageFactory.AtomicFusion:Augmenter' => FusionObjects\AugmenterImplementation::class
    ];

    /** @var FusionRuntimeFactory $instance */
    static protected $instance;

    /** @var Runtime[] $runtimes */
    protected $runtimes = [];

    /** @var KernelInterface $shopwareKernel */
    protected $shopwareKernel;

    /**
     * @return FusionRuntimeFactory
     */
    static public function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new FusionRuntimeFactory();
        }
        return self::$instance;
    }

    /**
     * FusionRuntimeFactory constructor.
     */
    private function __construct()
    {
        $this->shopwareKernel = Shopware()->Container()->get('kernel');
    }

    /**
     * Get the runtime for the given astFile
     *
     * @param string $astFile filename for the fusion ast in json
     * @return Runtime
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function getRuntime(string $astFile): Runtime
    {
        if (array_key_exists($astFile, $this->runtimes) === false) {
            //
            // read fusion ast
            //
            $ast = json_decode(file_get_contents($astFile), true);
            //
            // prepare ast with local implementations from conf vars
            //
            $fusionObjectImplementations = self::FUSION_OBJECT_IMPLEMENTATIONS;
            foreach ($fusionObjectImplementations as $fusionObjectName => $fusionObjectImplementation) {
                if (Arrays::getValueByPath($ast, ['__prototypes', $fusionObjectName])) {
                    $ast = Arrays::setValueByPath($ast, ['__prototypes', $fusionObjectName, '__meta', 'class'], $fusionObjectImplementation);
                }
            }
            //
            // create eel cache and evaluator
            //
            $eelCacheDirectory = $this->shopwareKernel->getCacheDir().'/fusion_renderer';
            Files::createDirectoryRecursively($eelCacheDirectory);
            $cache = new FilesystemCache('eel', 0, $eelCacheDirectory);
            $eelEvaluator = new CompilingEvaluator($cache);
            //
            // create eel context
            //
            $eelHelperConfiguration = self::EEL_HELPER_IMPLEMENTATIONS;
            $context = EelUtility::getDefaultContextVariables($eelHelperConfiguration);
            //
            // create runtime
            //
            $runtime = new Runtime($ast, $eelEvaluator, $context);
            $this->runtimes[$astFile] = $runtime;
        }

        return $this->runtimes[$astFile];
    }
}