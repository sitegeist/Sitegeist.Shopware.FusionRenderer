<?php

use SitegeistShopwareFusionRenderer\Components\FusionRenderer;

function smarty_function_fusion_renderer($params)
{
    $fusionAst = $params['ast'];
    $fusionPath = $params['path'];
    $fusionContext = json_decode($params['context'], true);
    return FusionRenderer::renderFusion($fusionAst, $fusionPath, $fusionContext);
}

function smarty_function_fusion_example($params, &$template)
{
    $fusionAst = $template->smarty->tpl_vars['astFile']->value;
    $fusionPath = 'render_Vendor_Site_Example';
    $fusionContext = [];
    $fusionContext['content'] = $params['content'];
    $fusionContext['attribute'] = $params['attribute'];
    $fusionContext['augmentedAttribute'] = $params['augmentedAttribute'];
    return FusionRenderer::renderFusion($fusionAst, $fusionPath, $fusionContext);
}