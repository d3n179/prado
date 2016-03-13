<?php

Prado::using('System.3rdParty.phpmailer.class_phpmailer');
Prado::using('System.Util.TEmail');


class TEmailer extends TModule
{
	private $_type = '';
	private $_templateDirectory = 'Application.Emails';
	private $_charSet = '';
	
	private $_smtpConfig = null;
	private $_sendmailConfig = null;
	
	public function init($config)
	{
		$this->_smtpConfig = $config->getElementByTagName('smtp');
		$this->_sendmailConfig = $config->getElementByTagName('sendmail');
	}
	
	public function setType($type)
	{
		$type = TPropertyValue::ensureEnum($type, 'TEmailerType');
		$reflector = new ReflectionClass('TEmailerType');
		$this->_type = $reflector->getConstant($type);
	}
	
	public function getType()
	{
		if(empty($this->_type)) {
			return TEmailerType::Mail;
		} else {
			return $this->_type;
		}
	}
	
	public function setCharSet($charSet)
	{
		$this->_charSet = $charSet;
	}
	
	public function getCharSet()
	{
		if(empty($this->_charSet)) {
			return 'utf-8';
		} else {
			return $this->_charSet;
		}
	}
	
	public function setEmailTemplateDirectory($path)
	{
		$this->_templateDirectory = TPropertyValue::ensureString($path);
	}
	
	public function getEmailTemplateDirectory()
	{
		return $this->_templateDirectory;
	}

	public function createNewEmail($templateName = '')
	{
		$email = new TEmail;
		if(!empty($templateName)) {
			$email->setEmailTemplatePath($this->getEmailTemplateDirectory() . '.' . $templateName);
		}
		return $email;
	}
	
	public function send($email)
	{
		if(!$email instanceof TEmail) {
			throw new Exception('invalid email');
		}
		
		$mailer = new PHPMailer;
		$mailer->SetLanguage(Prado::getPreferredLanguage(), Prado::getPathOfNamespace('System.3rdParty.phpmailer.language/'));
		$mailer->Mailer = $this->getType();
		
		if($this->getType() === TEmailerType::Smtp) {
			if($this->_smtpConfig === null) {
				throw new Exception('Type is set to Smtp <smpt /> is required.');
			}
			if(!$this->_smtpConfig->getHasAttribute('Host')) {
				throw new Exception('<smpt Host="host.com" /> is required.');
			} else {
				$mailer->Host = $this->_smtpConfig->getAttribute('Host');
			}
			$mailer->Hostname = $this->_smtpConfig->getAttribute('HostName');
			if($this->_smtpConfig->getHasAttribute('Port')) {
				$mailer->Port = $this->_smtpConfig->getAttribute('Port');
			}
			if($this->_smtpConfig->getHasAttribute('Username') || $this->_smtpConfig->getHasAttribute('Password')) {
				$mailer->SMTPAuth = true;
				$mailer->Username = $this->_smtpConfig->getAttribute('Username');
				$mailer->Password = $this->_smtpConfig->getAttribute('Password');
			}
			if($this->_smtpConfig->getHasAttribute('Debug')) {
				$mailer->SMTPDebug = TPropertyValue::ensureBoolean($this->_smtpConfig->getAttribute('Debug'));
			}
		} elseif($this->getType() === TEmailerType::Sendmail) {
			if($this->_sendmailConfig && $this->_sendmailConfig->getHasAttribute('BinaryPath')) {
				$mailer->Sendmail = $this->_sendmailConfig->getAttribute('BinaryPath');
			}
		}

		$mailer->CharSet = $this->getCharSet();
		$mailer->Subject = $email->getSubject();
		
		if($email->getEmailType() === TEmailType::Html) {
			$mailer->isHtml(true);
			$mailer->Body = $email->getHtmlContent()->flush();
		} elseif($email->getEmailType() === TEmailType::Text) {
			$mailer->isHtml(false);
			if($email->getTextContent()->getWordWrap() !== null) {
				$mailer->WordWrap = $email->getTextContent()->getWordWrap();
			}
			$mailer->Body = $email->getTextContent()->flush();
		} else {
			$mailer->isHtml(true);
			$mailer->Body = $email->getHtmlContent()->flush();
			$mailer->AltBody = $email->getTextContent()->flush();
		}
				
		// Set all the Addresses (From, To, Cc, Bcc, ReplyTo)
		// First check whether all are valid addresses.
		if(!$email->getEmailAddresses()->getAllValid()) {
			throw new Exception('Not all EmailAddresses are valid.');
		}
		
		$mailer->From = $email->getEmailAddresses()->getSender()->getAddress();
		$mailer->FromName = $email->getEmailAddresses()->getSender()->getName();
		
		foreach($email->getEmailAddresses()->findAllByField(TEmailAddressField::Reciever) as $address) {
			$mailer->AddAddress($address->getAddress(), $address->getName());
		}
		foreach($email->getEmailAddresses()->findAllByField(TEmailAddressField::CarbonCopy) as $address) {
			$mailer->AddCC($address->getAddress(), $address->getName());
		}
		foreach($email->getEmailAddresses()->findAllByField(TEmailAddressField::BlindCarbonCopy) as $address) {
			$mailer->AddBCC($address->getAddress(), $address->getName());
		}
				
		// Check whether there are attachemnts. If yes -> add them to the email.
		foreach($email->getAttachments()->getFileAttachments() as $attachment) {
			$mailer->AddAttachment($attachment->getPath(), $attachment->getFileName(), $attachment->getEncoding(), $attachment->getMimeType());
		}
		foreach($email->getAttachments()->getStringAttachments() as $attachment) {
			$mailer->AddStringAttachment($attachment->getWriter()->flush(), $attachment->getFileName(), $attachment->getEncoding(), $attachment->getMimeType());
		}
		
		// Check for InlineAttachmentImages.
		if($email->getEmailType() === TEmailType::Html || $email->getEmailType() === TEmailType::HtmlWithText) {
			foreach($email->getHtmlContent()->findControlsByType('TInlineAttachmentImage') as $control) {
				$mailer->AddEmbeddedImage($control->getPath(), $control->getCid());
			}
		}
		
		if(!$mailer->Send()) {
			throw new Exception('PhpMailer produced an Error: ' . $mailer->ErrorInfo);
		}
	}
}

class TEmailerType extends TEnumerable
{
	const Mail = 'mail';
	const Sendmail = 'sendmail';
	const Smtp = 'smtp';
}

?>