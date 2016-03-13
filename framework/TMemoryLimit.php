<?php
/*
 * http://us2.php.net/manual/en/ini.core.php#ini.memory-limit
 * http://us2.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 */

class TMemoryLimit extends TModule {

  var $_memoryLimit = null;

  public function init($config) {
        if($this->_memoryLimit == null)
          throw new TConfigurationException('You need to set the memory limit');
        ini_set("memory_limit", $this->_memoryLimit);
  }

  public function getMemoryLimit() {return $this->_memoryLimit;}
  public function setMemoryLimit($v) {$this->_memoryLimit = $v;}
}
?>