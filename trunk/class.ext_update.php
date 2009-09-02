<?php

if (t3lib_extMgm::isLoaded('caretaker_instance') == true){
	require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_ServiceFactory.php'));
}

/**
 * Extension manager update class to generate public / private key pairs.
 *
 * @author		Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class ext_update {
	/**
	 * @var tx_caretakerinstance_ServiceFactory
	 */
	protected $factory;

	/**
	 * @return boolean Whether the update should be shown / allowed
	 */
	public function access() {
		
		$extConf = $this->getExtConf();
		
		$show = !strlen($extConf['crypto.']['instance.']['publicKey']) ||
			!strlen($extConf['crypto.']['instance.']['privateKey']);
		return $show;
	}

	/**
	 * Return the update process HTML content
	 *
	 * @return string
	 */
	public function main() {
		$extConf = $this->getExtConf();
		
		$this->factory = tx_caretakerinstance_ServiceFactory::getInstance();
		list($publicKey, $privateKey) = $this->factory->getCryptoManager()->generateKeyPair();
		
		$extConf['crypto.']['instance.']['publicKey'] = $publicKey;
		$extConf['crypto.']['instance.']['privateKey'] = $privateKey;

		$this->writeExtConf($extConf);

		$content = "Generated public / private key";
		return $content;
	}

	/**
	 * Write back configuration
	 *
	 * @param array $extConf
	 * @return void
	 */
	protected function writeExtConf($extConf) {
		$install = new t3lib_install();
		$install->allowUpdateLocalConf = 1;
		$install->updateIdentity = 'Caretaker Instance installation';
		
		$lines = $install->writeToLocalconf_control();
		$install->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\'caretaker_instance\']', serialize($extConf));
		$install->writeToLocalconf_control($lines);

		t3lib_extMgm::removeCacheFiles();	
	}

	/**
	 * Get the extension configuration
	 *
	 * @return array
	 */
	protected function getExtConf() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance']);
		if (!$extConf) {
			$extConf = array();
		}
		return $extConf;
	}
}
?>