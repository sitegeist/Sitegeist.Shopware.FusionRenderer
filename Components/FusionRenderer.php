<?php declare(strict_types=1);

namespace SitegeistShopwareFusionRenderer\Components;

/**
 * Class FusionRenderer
 * @package SitegeistShopwareFusionRenderer\Components
 */
class FusionRenderer
{
    /**
     * @param string $fusionAst
     * @param string $fusionPath
     * @param array $fusionContext
     * @return string
     * @throws \Exception
     */
    static function renderFusion(string $fusionAst, string $fusionPath, array $fusionContext = []): string
    {
        $runtime = FusionRuntimeFactory::getInstance()->getRuntime($fusionAst);
        $runtime->pushContextArray($fusionContext);
        $output = $runtime->render($fusionPath);
        $runtime->popContext();

        return $output;
    }
}