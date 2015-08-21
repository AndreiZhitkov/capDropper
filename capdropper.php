<?php

/*
*	capDropper plugin
*	version 1.7 24/07/2015
*	Copyright (C) 2015 Andrei Zhitkov (zhitkov.andrei@gmail.com).

*	All rights reserved.
*	License GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*	Attribution: Original code 
*	Copyright Ewout Wierda  2008/2009. All rights reserved. 
*	capDropper is a Joomla 1.5 conversion and enhancement
*	of the dropcap mambot by by David Preston 
*	(dave@davepreston.me.uk, www.davepreston.me.uk).
*/
 
/* framework check */
defined( '_JEXEC' ) or die( 'Restricted access' );

/* plugin framework */
jimport('joomla.plugin.plugin');
 
class plgContentcapDropper extends JPlugin
{

	function plgContentcapDropper( &$subject, $params ) 
	{
		parent::__construct( $subject, $params );
	}
 
	/* put a span style around the first letter */
	function drop_a_cap( &$matches ) 
	{

		$cssclass = $this->params->get( 'cssclass', 'capdropper' );

		return $matches[1].'<span class="'.$cssclass.'">'.$matches[2].'</span>';

	}

	public function onContentPrepare($context, &$article, &$params ) 
	{
 
		global $mainframe, $option;
 
		/* get plugin params */
		$enabled = $this->params->get( 'enabled', 1 );
		$autoinsert = $this->params->get( 'autoinsert', 1 );
		$articlesonly = $this->params->get( 'articlesonly', 1 );
		$usetemplate = $this->params->get( 'usetemplate', 1 );
		$cssclass = $this->params->get( 'cssclass', 'capdropper' );
		$cssstyle = $this->params->get( 'cssstyle' );
		$autoignore = preg_replace('|\s*,\s*|', ",", $this->params->get('autoignore'));
        $autoignore = explode(",", $autoignore);
		//print_r ($autoignore);


		/* regex to find tag for switching plugin on or off manually in articles */
		$switchregex = "#{capdropper (on|off)}#s";

		/* regex to find the first letter */
		$replaceregex = "#(^.*?<p.*?>\r?\n? *?)([\p{Lu}])#u";

		/* clean plugin tags if plugin is not enabled, or if switch off tag is used in an article, or if autoinsert is off and there is no switch on tag, or if restricted to articles and page is not an article */
        $jinput = JFactory::getApplication()->input;
        $option = $jinput->get('option');
		$view = $jinput->get('view');

		if ( ( !$this->params->get( 'cd_is_enabled', 1 ) ) || ( strpos( $article->text, '{capdropper off}' ) ) || ( strpos( $article->text, '{capdropper on}' ) === false && $autoinsert != 1 ) || ( $articlesonly != 1 && !( $option == 'com_content' && $view == 'article' ) ) ) {
			$article->text = preg_replace( $switchregex, '', $article->text );
			return true;
		}

		/* otherwise, make a drop cap, add style, and clean the plugin switch on tag */
			$article->text = preg_replace_callback($replaceregex, array( &$this, 'drop_a_cap'), $article->text);
			$article->text = preg_replace( $switchregex, '', $article->text);
        if (!in_array($option,$autoignore)) {
			if ($usetemplate) {
				if (!defined('CSS_INSERTED')) {
					$doc = JFactory::getDocument();
					$headtag = addslashes('.'.$cssclass.' {'.$cssstyle.'}');
					$doc->addStyleDeclaration( $headtag );
					define('CSS_INSERTED', 1);
				}
			}
		}
	}
}
?>
