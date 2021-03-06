<?php

trait MP_Debug_Model_Core_Email_Capture
{
    /**
     * Calls parent's real send method
     *
     * @return $this
     */
    public function parentSend()
    {
        return parent::send();
    }


    /**
     * Overwrites parent method to capture details for sent e-mail
     *
     * @return MP_Debug_Model_Core_Email
     */
    public function send()
    {
        try {
            $this->captureEmail();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this->parentSend();
    }


    /**
     * Adds e-mail information on current request profile info
     */
    public function captureEmail()
    {
        $email = Mage::getModel('mp_debug/email');
        $email->setFromEmail($this->getFromEmail());
        $email->setFromName($this->getFromName());
        $email->setToEmail($this->getToEmail());
        $email->setToName($this->getToName());
        $email->setSubject($this->getSubject());
        $email->setIsPlain($this->getType() != 'html');
        $email->setBody($this->getBody());
        $email->setIsSmtpDisabled((bool)Mage::getStoreConfigFlag('system/smtp/disable'));
        $email->setIsAccepted(true);  // Assume e-mail is accepted

        $requestInfo = Mage::getSingleton('mp_debug/observer')->getRequestInfo();
        $requestInfo->addEmail($email);
    }

}


/**
 * Class MP_Debug_Model_Core_Email rewrites core/email and overwrites send() to capture any e-mail information.
 *
 * @method string getType()
 * @method string getFromEmail()
 * @method string getFromName()
 * @method string getToEmail()
 * @method string getToName()
 *
 * @category MP
 * @package  MP_Debug
 * @license  Copyright: Pirate MP, 2016
 * @link     https://piratesheep.com
 */


if (Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')) {
    class MP_Debug_Model_Core_Email extends Aschroder_SMTPPro_Model_Email
    {
        use MP_Debug_Model_Core_Email_Capture;
    }
} else {

    class MP_Debug_Model_Core_Email extends Mage_Core_Model_Email
    {
        use MP_Debug_Model_Core_Email_Capture;
    }
}
