<?php namespace CSI\BbCodeMediaWikiGetPageText\BbCode\Formatter;

/**
 * Class Base
 * @package CSI\BbCodeMediaWikiGetPageText\BbCode\Formatter
 */
class Base
{
  /**
   * @param array $tag
   * @param array $rendererStates
   * @param \XenForo_BbCode_Formatter_Base $formatter
   * @return mixed
   */
  public static function getBbCodeMediaWikiGetPageText(array $tag, array $rendererStates, \XenForo_BbCode_Formatter_Base $formatter)
  {
    $xenOptions = \XenForo_Application::get('options');
    $xenVisitor = \XenForo_Visitor::getInstance();
    $tagOption = array_map('trim', explode('|', $tag['option']));

    if (count($tagOption) > 1) {
      $optDefault = $tagOption[0];
    } else {
      $optDefault = $tag['option'];
    }

    $tagContent = $formatter->renderSubTree($tag['children'], $rendererStates);

    if (!$xenOptions->csiXF_bbCode_1D2274F3_onoff || !$xenOptions->csiXF_bbCode_1D2274F3_urlApi) {
      return $formatter->renderInvalidTag($tag, $rendererStates);
    }

    $tagContent = rawurlencode($tagContent);

    $getData = $xenOptions->csiXF_bbCode_1D2274F3_urlApi . '?action=query&prop=info|extracts&inprop=url&exchars=' . $xenOptions->csiXF_bbCode_1D2274F3_charsLimit . '&explaintext&titles=' . $tagContent . '&format=json';
    $decodeData = json_decode(file_get_contents($getData), true);
    $queryData = $decodeData['query']['pages'];

    foreach ($queryData as $page) {
      if (!isset($page['touched'])) {
        return $formatter->renderInvalidTag($tag, $rendererStates);
      }

      $tagTitle = $page['title'];
      $tagTouched = $page['touched'];
      $tagUrl = $page['fullurl'];
      $tagExtract = $page['extract'];
    }

    $tagTouched = date('Y-m-d H:i:s', strtotime($tagTouched));

    $view = $formatter->getView();

    if ($view) {
      $template = $view->createTemplateObject('csiXF_bbCode_1D2274F3_tag_wiki_text',
        array(
          'title' => $tagTitle,
          'touched' => $tagTouched,
          'url' => $tagUrl,
          'extract' => $tagExtract,
        ));

      $tagContent = $template->render();
      return trim($tagContent);
    }

    return $tagContent;
  }
}
