<?php
/**
 * XTableRow class file
 *
 * @author Johannes Dohmen <johannes.dohmen@web.de>
 * @copyright Copyright &copy; 2009 aixTeMa GmbH
 * @link http://www.aixtema.de/
 * @license http://www.pradosoft.com/license/
 * @version 0.1.0
 */

/**
 * XTableRow is a simple enhancement to TTableRow which raises the two
 * server-side events onCLick and onCommand.
 * Please refer to Prado documentation for further details how to handle
 * this events.
 * The code to this class is copied almost verbatim from the TButton class.
 *
 * Example Usage (in page-file):
 * <com:TTable>
 *	<com:XTableRow onClick="click">
 *		<com:TTableCell>Click</com:TTableCell>
 *	</com:XTableRow>
 *	<com:XTableRow onCommand="command" CommandParameter="command_param">
 *		<com:TTableCell >Command</com:TTableCell>
 *	</com:XTableRow>
 * </com:TTable>
 */
class XTableRow extends TTableRow implements IPostBackEventHandler{
	public function onInit($param){
		parent::onInit($param);
		$this->getPage()->getClientScript()->registerBeginScript($this->getClientID(),
			$this->getClientClassName().' = Class.extend(Prado.WebUI.PostBackControl);');
	}

	public function raisePostBackEvent($param){
    if($this->getCausesValidation())
      $this->getPage()->validate($this->getValidationGroup());
    $this->onClick(null);
    $this->onCommand(new TCommandEventParameter($this->getCommandName(),$this->getCommandParameter()));
  }

	public function onClick($param){
    $this->raiseEvent('OnClick',$this,$param);
  }

	public function onCommand($param)
	{
		$this->raiseEvent('OnCommand',$this,$param);
		$this->raiseBubbleEvent($this,$param);
	}

	public function getCommandName()
	{
		return $this->getViewState('CommandName','');
	}

	public function setCommandName($value)
	{
		$this->setViewState('CommandName',$value,'');
	}

	public function getCommandParameter()
	{
		return $this->getViewState('CommandParameter','');
	}

	public function setCommandParameter($value)
	{
		$this->setViewState('CommandParameter',$value,'');
	}

	protected function getClientClassName()
  {
    return 'XTableRow';
  }

	public function getCausesValidation()
  {
    return $this->getViewState('CausesValidation',true);
  }

  public function setCausesValidation($value)
  {
    $this->setViewState('CausesValidation',TPropertyValue::ensureBoolean($value),true);
  }

	public function getValidationGroup()
  {
    return $this->getViewState('ValidationGroup','');
  }

  public function setValidationGroup($value)
  {
    $this->setViewState('ValidationGroup',$value,'');
  }

	protected function getPostBackOptions()
  {
    $options['ID'] = $this->getClientID();
    $options['EventTarget'] = $this->getUniqueID();
    $options['CausesValidation'] = $this->getCausesValidation();
    $options['ValidationGroup'] = $this->getValidationGroup();
    $options['StopEvent'] = true;

    return $options;
  }

	protected function renderClientControlScript($writer)
  {
    $cs = $this->getPage()->getClientScript();
    $cs->registerPostBackControl($this->getClientClassName(),$this->getPostBackOptions());
  }

	protected function addAttributesToRender($writer){
		$page=$this->getPage();
    $page->ensureRenderInForm($this);
		$writer->addAttribute('id',$this->getClientID());
		parent::addAttributesToRender($writer);
		$this->renderClientControlScript($writer);
	}

}

?>
