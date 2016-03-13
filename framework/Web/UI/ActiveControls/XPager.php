<?php

/**
 * Class XPager
 *
 * Adds some extra options to standard TPager like: custom page number separator, custom
 * css class for active page number, and a new ButtonType option to make link paginator.
 *
 * @author Mauro Lewinzon <mauro@syberia-la.com>
 * @link http://www.syberia-la.com/
 * @copyright Copyright &copy; 2008 SYBERIA
 * @license http://www.pradosoft.com/license/
 * @package WebControls
 * @version 1.0
 */

class XPager extends TPager
{
	const PAGE_NUMBER = 'p';

	public function loadRecursive()
	{

		parent::loadRecursive();

		if($_GET[self::PAGE_NUMBER ]) {
			$pageIndex = intval($_GET[self::PAGE_NUMBER ] -1) ;

			if($pageIndex >= 0) {
				$this->onPageIndexChanged(new TPagerPageChangedEventParameter($sender,$pageIndex));
			}
		}


	}

	/**
	 * @return string text for the active page css class.
	 */
	public function getActiveCssClass()
	{
		return $this->getViewState('ActiveCssClass');
	}

	/**
	 * @param string text for the active page css class.
	 */
	public function setActiveCssClass($value)
	{
		$this->setViewState('ActiveCssClass',$value);
	}

	/**
	 * @param string separator to use between page numbers
	 */
	public function setSeparator($sep)
	{
		$this->setViewState('Separator',$sep);
	}

	/**
	 * @return string separator to use between page numbers
	 */
	public function getSeparator()
	{
		return $this->getViewState('Separator',"\n");
	}

	protected function buildNumericPager()
	{
		$buttonType=$this->getButtonType();
		$controls=$this->getControls();
		$pageCount=$this->getPageCount();
		$pageIndex=$this->getCurrentPageIndex()+1;
		$maxButtonCount=$this->getPageButtonCount();
		$buttonCount=$maxButtonCount>$pageCount?$pageCount:$maxButtonCount;
		$startPageIndex=1;
		$endPageIndex=$buttonCount;
		if($pageIndex>$endPageIndex)
		{
			$startPageIndex=((int)(($pageIndex-1)/$maxButtonCount))*$maxButtonCount+1;
			if(($endPageIndex=$startPageIndex+$maxButtonCount-1)>$pageCount)
				$endPageIndex=$pageCount;
			if($endPageIndex-$startPageIndex+1<$maxButtonCount)
			{
				if(($startPageIndex=$endPageIndex-$maxButtonCount+1)<1)
					$startPageIndex=1;
			}
		}

		if($startPageIndex>1)
		{
			if(($text=$this->getFirstPageText())!=='')
			{
				$button=$this->createPagerButton($buttonType,true,$text,self::CMD_PAGE_FIRST,'');
				$controls->add($button);
				$controls->add("\n");
			}
			$prevPageIndex=$startPageIndex-1;
			$button=$this->createPagerButton($buttonType,true,$this->getPrevPageText(),self::CMD_PAGE,"$prevPageIndex");
			$controls->add($button);
			$controls->add("\n");
		}

		for($i=$startPageIndex;$i<=$endPageIndex;++$i)
		{
			if($i===$pageIndex)
			{
				$label=$this->createPagerButton($buttonType,false,"$i",self::CMD_PAGE,'');
				$label->setCssClass($this->getActiveCssClass());
				$controls->add($label);
			}
			else
			{
				$button=$this->createPagerButton($buttonType,true,"$i",self::CMD_PAGE,"$i");
				$controls->add($button);
			}
			if($i<$endPageIndex)
				$controls->add($this->getSeparator());

		}

		if($pageCount>$endPageIndex)
		{
			$controls->add("\n");
			$nextPageIndex=$endPageIndex+1;
			$button=$this->createPagerButton($buttonType,true,$this->getNextPageText(),self::CMD_PAGE,"$nextPageIndex");
			$controls->add($button);
			if(($text=$this->getLastPageText())!=='')
			{
				$controls->add("\n");
				$button=$this->createPagerButton($buttonType,true,$text,self::CMD_PAGE_LAST,'');
				$controls->add($button);
			}
		}
	}

	protected function createPagerButton($buttonType,$enabled,$text,$commandName,$commandParameter)
	{
		if($buttonType == XPagerButtonType::HyperLink ) {
			if($enabled)
				$button=new THyperLink();
			else
			{
				$label=new TLabel;
				$label->setText($text);
				return $label;
			}
		} else {
			return parent::createPagerButton($buttonType,$enabled,$text,$commandName,$commandParameter);

		}

		$get = $_GET;
		$get[self::PAGE_NUMBER ] = $commandParameter;
		unset($get[$this->getRequest()->getServiceID()]);

		$url = $this->getService()->constructUrl($this->getRequest()->getServiceParameter(),$get);
		$button->setText($text);
		$button->setNavigateUrl($url);
		return $button;

	}

	/**
	 * @param TPagerButtonType the type of command button for paging.
	 */
	public function setButtonType($value)
	{
		$this->setViewState('ButtonType',TPropertyValue::ensureEnum($value,'XPagerButtonType'),TPagerButtonType::LinkButton);
	}
}

class XPagerButtonType extends TPagerButtonType
{
	const HyperLink='HyperLink';
}

?>