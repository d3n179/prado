<?php
//Prado::using('System.Web.UI.ActiveControls.TActiveControlAdapter');
/**
 * XActiveTableRow class file
 *
 * @author Johannes Dohmen <johannes.dohmen@web.de>
 * @copyright Copyright &copy; 2009 aixTeMa GmbH
 * @link http://www.aixtema.de/
 * @license http://www.pradosoft.com/license/
 * @version 0.1.0
 */

/**
 * XActiveTableRow is the active counter part to XTableRow which beside from
 * onClick and onCommand also raises the onCallback event.
 * Please refer to Prado documentation for further details how to handle
 * this events.
 * The code to this class is copied almost verbatim from the TActiveButton
 * and TActiveLinkButton class.
 *
 * Example Usage (in page-file):
 * <com:TTable>
 *	<com:XActiveTableRow onClick="click">
 *		<com:TTableCell >Click (Active)</com:TTableCell>
 *	</com:XActiveTableRow>
 *	<com:XActiveTableRow onCommand="command" CommandParameter="command_param">
 *		<com:TTableCell >Command (Active)</com:TTableCell>
 *	</com:XActiveTableRow>
 *	<com:XActiveTableRow onCallback="callback" ActiveControl.CallbackParameter="callback_param">
 *		<com:TTableCell >CallBack</com:TTableCell>
 *	</com:XActiveTableRow>
 * </com:TTable>
 */
class XActiveTableRow extends XTableRow implements ICallbackEventHandler, IActiveControl{
	public function __construct()
  {
    parent::__construct();
    $this->setAdapter(new TActiveControlAdapter($this));
  }

	public function onInit($param){
		parent::onInit($param);
		$this->getPage()->getClientScript()->registerBeginScript($this->getClientID(),
			$this->getClientClassName().' = Class.extend(Prado.WebUI.CallbackControl);');
	}

	protected function addAttributesToRender($writer)
	{
		parent::addAttributesToRender($writer);
		$writer->addAttribute('id',$this->getClientID());
		$this->getActiveControl()->registerCallbackClientScript(
			$this->getClientClassName(), $this->getPostBackOptions());
	}

	protected function renderClientControlScript($writer)
	{
	}

	protected function getClientClassName()
	{
		return 'XActiveTableRow';
	}

	public function getActiveControl()
	{
		return $this->getAdapter()->getBaseActiveControl();
	}

	public function getClientSide()
	{
		return $this->getAdapter()->getBaseActiveControl()->getClientSide();
	}

 	public function raiseCallbackEvent($param)
	{
		$this->raisePostBackEvent($param);
		$this->onCallback($param);
	}

	public function onCallback($param)
	{
		$this->raiseEvent('OnCallback', $this, $param);
	}
}
?>
