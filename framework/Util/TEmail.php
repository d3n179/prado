<?php

class TEmail extends TCompositeControl
{
	private $_subject = '';

	private $_template = null;
	private $_templatePath = '';
	
	private $_htmlContent = null;
	private $_textContent = null;
	
	private $_emailAddresses = null;
	private $_attachments = null;
	
	public function setSubject($subject)
	{
		$this->_subject = TPropertyValue::ensureString($subject);
	}
	
	public function getSubject()
	{
		return $this->_subject;
	}
	
	public function getEmailType()
	{
		$this->ensureChildControls();
		if($this->_htmlContent !== null && $this->_textContent !== null) {
			return TEmailType::HtmlWithText;
		} elseif($this->_htmlContent !== null) {
			return TEmailType::Html;
		} elseif($this->_textContent !== null) {
			return TEmailType::Text;
		} else {
			throw new Exception('An email has to contain at least one content object.');
		}
	}
	
	public function setHtmlContent($content)
	{
		if($this->_htmlContent !== null) {
			$this->getControls()->remove($this->_htmlContent);
			$this->_htmlContent = null;
		}
		if(is_string($content)) {
			$this->_htmlContent = new TEmailHtmlContent;
			$this->_htmlContent->setTemplate(new TTemplate($content, '', null, 0, false));
			$this->_htmlContent->ensureChildControls();
		} elseif($content instanceof TTemplate) {
			$this->_htmlContent = new TEmailHtmlContent;
			$this->_htmlContent->setTemplate($content);
			$this->_htmlContent->ensureChildControls();
		} elseif($content instanceof TEmailHtmlContent) {
			$this->_htmlContent = $content;
			$this->_htmlContent->ensureChildControls();
		} else {
			throw new Exception('Wrong parameter type.');
		}
		$this->getControls()->add($this->_htmlContent);
	}
	
	public function setTextContent($content)
	{
		if($this->_textContent !== null) {
			$this->getControls()->remove($this->_textContent);
			$this->_textContent = null;
		}
		if(is_string($content)) {
			$this->_textContent = new TEmailTextContent;
			$this->_textContent->setTemplate(new TTemplate($content, '', null, 0, false));
			$this->_textContent->ensureChildControls();
		} elseif($content instanceof TTemplate) {
			$this->_textContent = new TEmailTextContent;
			$this->_textContent->setTemplate($content);
			$this->_textContent->ensureChildControls();
		} elseif($content instanceof TEmailTextContent) {
			$this->_textContent = $content;
			$this->_textContent->ensureChildControls();
		} else {
			throw new Exception('Unsupported parameter type.');
		}
		$this->getControls()->add($this->_textContent);
	}
	
	public function getHtmlContent()
	{
		$this->ensureChildControls();
		return $this->_htmlContent;
	}
	
	public function getTextContent()
	{
		$this->ensureChildControls();
		return $this->_textContent;
	}
	
	public function setEmailTemplatePath($path)
	{
		$path = TPropertyValue::ensureString($path);
		$this->_templatePath = Prado::getPathOfNamespace($path, '.tpl');
	}
	
	private function getEmailTemplatePath()
	{
		return $this->_templatePath;
	}

	/*public function createChildControls()
	{
		if($this->_templatePath) {
			$template = $this->getService()->getTemplateManager()->getTemplateByFileName($this->_templatePath);
			foreach($template->getDirective() as $name => $value) {
				if(is_string($value)) {
					$this->setSubProperty($name, $value);
				} else {
					throw new TConfigurationException('templatecontrol_directive_invalid', get_class($this), $name);
				}
			}
			$template->instantiateIn($this);
		}
	}*/
	public function createChildControls()
	{
		if($this->_templatePath) {
			$template = $this->getService()->getTemplateManager()->getTemplateByFileName($this->_templatePath);
			foreach($template->getDirective() as $name => $value) {
				if (is_array($value)) {
		                     $value = $this->evaluateExpression($value[1]);
		                }
		                $this->setSubProperty($name, $value);
			}
			$template->instantiateIn($this);
		}
	}
	
	public function getEmailAddresses()
	{
		$this->ensureChildControls();
		if($this->_emailAddresses === null) {
			$this->_emailAddresses = new TEmailAddressCollection;
		}
		return $this->_emailAddresses;
	}
	
	public function setEmailAddresses($addresses)
	{
		$this->_emailAddresses = new TEmailAddressCollection;
		if(is_string($addresses)) {
			$template = new TTemplate($addresses, '', null, 0, false);
			foreach($template->getItems() as $item) {
				if($item[1] === 'TEmailAddress') {
					$address = new TEmailAddress;
					foreach($item[2] as $property => $value) {
						$address->$property = $value;
					}
					$this->_emailAddresses->add($address);
				} else {
					$string = trim($item[1]);
					if(!empty($string)) {
						throw new Exception('Only TEmailAddress Compontents may be set in the EmailAddresses property.');
					}
				}
			}
		} elseif(is_array($addresses)) {
			foreach($addresses as $address) {
				if($address instanceof TEmailAddress) {
					$this->_emailAddresses->add($address);
				} else {
					throw new Exception('Only TEmailAddress Compontents may be set in the EmailAddresses property.');
				}
			}
		} elseif($addresses instanceof TEmailAddressCollection) {
			$this->_emailAddresses = $addresses;
		} else {
			throw new Exception('Unsupported parameter type.');
		}
	}
	
	public function getAttachments()
	{
		$this->ensureChildControls();
		if($this->_attachments === null) {
			$this->_attachments = new TEmailAttachmentCollection;
		}
		return $this->_attachments;
	}
	
	public function setAttachments($attachments)
	{
		$this->_attachments = new TEmailAttachmentCollection;
		if(is_string($attachments)) {
			$template = new TTemplate($attachments, '', null, 0, false);
			foreach($template->getItems() as $item) {
				if($item[1] === 'TEmailFileAttachment' || $item[1] === 'TEmailStringAttachment') {
					$attachment = new $item[1];
					foreach($item[2] as $property => $value) {
						$attachment->$property = $value;
					}
					$this->_attachments->add($attachment);
				} else {
					$string = trim($item[1]);
					if(!empty($string)) {
						throw new Exception('Only TEmailAttachment Compontents may be set in the Attachments property.');
					}
				}
			}
		} elseif(is_array($attachments)) {
			foreach($attachments as $attachment) {
				if($attachment instanceof TEmailAttachment) {
					$this->_attachments->add($attachment);
				} else {
					throw new Exception('Only TEmailAttachment Compontents may be set in the Attachments property.');
				}
			}
		} elseif($attachments instanceof TEmailAttachmentCollection) {
			$this->_attachments = $attachment;
		} else {
			throw new Exception('Unsupported parameter type.');
		}
	}
}

class TEmailType extends TEnumerable
{
	const Text = 'PlainText';
	const Html = 'Html';
	const HtmlWithText = 'HtmlWithText ';
}

class TEmailBody extends TTemplateControl
{
	public function flush()
	{
		$textWriter = new TTextWriter;
		$htmlWriter = new THtmlWriter($textWriter);
		$this->renderControl($htmlWriter);
		return $textWriter->flush();
	}
}

class TEmailHtmlContent extends TEmailBody
{

}

class TEmailTextContent extends TEmailBody
{
	private $_wordWrap = null;
	
	public function setWordWrap($wrap)
	{
		$this->_wordWrap = TPropertyValue::ensureInteger($wrap);
	}
	
	public function getWordWrap()
	{
		return $this->_wordWrap;
	}
		
}

class TEmailAddress extends TComponent
{
	private $_address = '';
	private $_name = '';
	private $_field = '';
	
	public function setAddress($address)
	{
		$this->_address = $address;
	}
	
	public function getAddress()
	{
		return $this->_address;
	}
	
	public function getAddressIsValid()
	{
		if(empty($this->_address)) {
			return false;
		} else {
			return preg_match('/^' . TEmailAddressValidator::EMAIL_REGEXP . "\$/", $this->_address);
		}
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function setField($field)
	{
		$this->_field = TPropertyValue::ensureEnum($field, 'TEmailAddressField');
	}
	
	public function getField()
	{
		return $this->_field;
	}
}

class TEmailAddressCollection extends TList
{
	public function insertAt($index, $item)
	{
		if(!$item instanceof TEmailAddress) {
			throw new Exception('Unsupported parameter type.');
		}
		parent::insertAt($index, $item);
	}
	
	public function findAllByField($field)
	{
		$field = TPropertyValue::ensureEnum($field, 'TEmailAddressField');
		$returnArray = array();
		foreach($this as $address) {
			if($address->getField() === $field) {
				$returnArray[] = $address;
			}
		}
		return $returnArray;
	}
	
	public function getSender()
	{
		$array = $this->findAllByField(TEmailAddressField::Sender);
		if(count($array) > 1) {
			throw new Exception('Only one sender can be specified.');
		}
		return $array[0];
	}
	
	public function add($mixed, $address = '', $name = '')
	{
		if(is_object($mixed)) {
			parent::add($mixed);
		} else {
			$item = new TEmailAddress;
			$item->setField($mixed);
			$item->setAddress($address);
			$item->setName($name);
			parent::add($item);
		}
	}
	
	public function getAllValid()
	{
		foreach($this as $item) {
			if(!$item->getAddressIsValid()) {
				return false;
			}
		}
		return true;
	}
}

class TEmailAddressField extends TEnumerable
{
	const Reciever = 'Reciever';
	const Sender = 'Sender';
	const CarbonCopy = 'CarbonCopy';
	const BlindCarbonCopy = 'BlindCarbonCopy';
	const ReplyTo = 'ReplyTo';
}

class TEmailAttachment extends TComponent
{
	private $_fileName = '';
	private $_encoding = '';
	private $_mimeType = '';
	
	public function setFileName($name)
	{
		$this->_fileName = TPropertyValue::ensureString($name);
	}
	
	public function getFileName()
	{
		return $this->_fileName;
	}
	
	public function setEncoding($encoding)
	{
		$encoding = TPropertyValue::ensureEnumerable($encoding, 'TEmailAttachmentEncoding');
		$reflector = new ReflectionClass('TEmailAttachmentEncoding');
		$this->_encoding = $reflector->getConstant($encoding);
	}
	
	public function getEncoding()
	{
		if(empty($this->_encoding)) {
			return TEmailAttachmentEncoding::Base64;
		} else {
			return $this->_encoding;
		}
	}
	
	public function setMimeType($type)
	{
		$this->_mimeType = TPropertyValue::ensureString($type);
	}
	
	public function getMimeType()
	{
		if(empty($this->_mimeType)) {
			return 'application/octet-stream';
		} else {
			return $this->_mimeType;
		}
	}
	
	public static function convertPath($path)
	{
		$path = TPropertyValue::ensureString($path);
		if(substr($path, 0, 1) !== DIRECTORY_SEPARATOR) {
			$path = realpath(Prado::getApplication()->getBasePath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path);
		}
		return $path;
	}
}

class TEmailAttachmentEncoding extends TEnumerable
{
	const Base64 = 'base64';
	const SevenBit = '7bit';
	const EightBit = '8bit';
	const Binary = 'binary';
	const QuotedPrintable = 'quoted-printable';
}

class TEmailFileAttachment extends TEmailAttachment
{
	private $_path = '';
	
	public function setPath($path)
	{

		$this->_path = self::convertPath($path);
	}
	
	public function getPath()
	{
		return $this->_path;
	}
}

class TEmailStringAttachment extends TEmailAttachment
{
	private $_writer = null;
	
	public function setWriter($textWriter)
	{
		if(!$textWriter instanceof TTextWriter) {
			throw new Exception('Unsupported parameter type.');
		} else {
			$this->_writer = $textWriter;
		}
	}
	
	public function getWriter()
	{
		return $this->_writer;
	}
}

class TEmailAttachmentCollection extends TList
{
	public function insertAt($index, $item)
	{
		if(!$item instanceof TEmailAttachment) {
			throw new Exception('Unsupported parameter type.');
		}
		parent::insertAt($index, $item);
	}
	
	public function getFileAttachments()
	{
		$returnArray = array();
		foreach($this as $attachment) {
			if($attachment instanceof TEmailFileAttachment) {
				$returnArray[] = $attachment;
			}
		}
		return $returnArray;
	}
	
	public function getStringAttachments()
	{
		$returnArray = array();
		foreach($this as $attachment) {
			if($attachment instanceof TEmailStringAttachment) {
				$returnArray[] = $attachment;
			}
		}
		return $returnArray;
	}
}

class TInlineAttachmentImage extends TImage
{
	private $_path = '';
	private $_imageUrl = '';
	
	public function getCid()
	{
		return md5($this->getPath());
	}
	
	/**
	 * @return string the URL of the image file
	 */
	public function getImageUrl()
	{
		return $this->_imageUrl;
	}

	/**
	 * @param string the URL of the image file
	 */
	public function setImageUrl($url)
	{
		if(strtolower(substr($url, 0, 3)) !== 'cid') {
			throw new Exception('ImageUrl for a TInlineAttachmentImage must be a CID.');
		}
		$this->_imageUrl = $url;
	}

	/**
	 * This method is required by {@link IDataRenderer}.
	 * It is the same as {@link getImageUrl()}.
	 * @return string the URL of the image file.
	 * @see getImageUrl
	 * @since 3.1.0
	 */
	public function getData()
	{
		return $this->getPath();
	}

	/**
	 * This method is required by {@link IDataRenderer}.
	 * It is the same as {@link setImageUrl()}.
	 * @param string the URL of the image file.
	 * @see setImageUrl
	 * @since 3.1.0
	 */
	public function setData($data)
	{
		$this->setPath($data);
	}
	
	public function setPath($path)
	{
		$this->_path = TEmailAttachment::convertPath($path);
		$this->setImageUrl('cid:' . $this->getCid());
	}
	
	public function getPath()
	{
		return $this->_path;
	}
}

?>