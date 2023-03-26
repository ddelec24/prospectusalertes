<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class prospectusalertes extends eqLogic {
  
  //* Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {
  	$eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType(__CLASS__, true);
    foreach ($eqLogics as $eqlogic) {
      if($eqlogic->getIsEnable() == 0 || $eqlogic->getConfiguration('type') == 'store') {
        continue;
      }
      $autorefresh = $eqlogic->getConfiguration('autorefresh');

      if ($autorefresh != '') {
        try {
          $c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
          if ($c->isDue()) {
            $eqlogic->checkNews();
          }
        } catch (Exception $exc) {
          log::add(__CLASS__, 'error', __('Expression cron non valide pour ', __FILE__) . $eqlogic->getHumanName() . ' : ' . $autorefresh);
        }
      }
    }
  }
  
  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {


    if($this->getConfiguration('type') == 'store') 
      return;

    $autorefresh = $this->getConfiguration('autorefresh');
    if(empty($autorefresh)) {
      $this->setConfiguration('autorefresh', '16 0,6,12,18 * * *'); // 4 refresh par jour par défaut
      $this->save();
    }

    $refresh = $this->getCmd(null, 'refresh');
    if (!is_object($refresh)) {
      $refresh = new prospectusalertesCmd();
      $refresh->setName(__('Rafraichir', __FILE__));
    }
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('refresh');
    $refresh->setType('action');
    $refresh->setSubType('other');
    $refresh->setIsVisible(0);
    $refresh->save();


    //SYNC STORES    
    $arrStores = array_map('trim', explode(',', $this->getConfiguration('stores')));
    $arrStores = array_filter($arrStores); // remove empty elements
    //log::add(__CLASS__, 'debug', "[postSave] Sync stores: " . json_encode($arrStores));

    $currentStores = eqLogic::byTypeAndSearchConfiguration(__CLASS__, ["type" => "store", "link" => $this->getId()]);

    
    foreach($arrStores as $store) {
      //log::add(__CLASS__, 'debug', "[postSave] Verif store: " . $store . ".");
      //log::add(__CLASS__, 'debug', "[postSave] currentStores: " . json_encode($currentStores));
      $exists = false;
      foreach($currentStores as $currentStore) {
        $name = $currentStore->getName();
        //log::add(__CLASS__, 'debug', "[postSave] Compare " . $name . " et " . $store);
        if(stripos($name, $store) !== false) { //existe déjà on passe
          $exists = true;
          continue;
        }

      } //foreach DB stores
      
	  // ADD STORES
      if(!$exists) {
        log::add(__CLASS__, 'debug', "[postSave] $store existe pas");
        // new eqLogic for the chosen store
        $newStore = new prospectusalertes();
        $newStore->setName($store . '_' . $this->getId());   	
        $newStore->setConfiguration('type', 'store');
        $newStore->setConfiguration('link', $this->getId());
        $newStore->setConfiguration('longname', $store);
        $newStore->setIsEnable(1);
        $newStore->setIsVisible(0);
        $newStore->setLogicalId(self::slugify($store) . '_' . $this->getId());
        $newStore->setEqType_name(__CLASS__);
        $newStore->save();

        // linked commands
        $info = $newStore->getCmd(null, 'lastSeen');
        if (!is_object($info)) {
          $info = new prospectusalertesCmd();
          $info->setName(__('Date dernier prospectus', __FILE__));
        }
        $info->setOrder(1);
        $info->setLogicalId('lastSeen');
        $info->setEqLogic_id($newStore->getId());
        $info->setType('info');
        $info->setSubType('string');
        $info->setIsVisible(1);
        $info->setIsHistorized(1);
        $info->save();

        $info = $newStore->getCmd(null, 'lastAlert');
        if (!is_object($info)) {
          $info = new prospectusalertesCmd();
          $info->setName(__('Date dernière alerte', __FILE__));
        }
        $info->setOrder(1);
        $info->setLogicalId('lastAlert');
        $info->setEqLogic_id($newStore->getId());
        $info->setType('info');
        $info->setSubType('string');
        $info->setIsVisible(1);
        $info->setIsHistorized(1);
        $info->save();

      } // !exists
    } // foreach stores
    
    // DELETE UNUSED STORES
    foreach($currentStores as $currentStore) {
      $name = $currentStore->getName();
      $exists = false;
      foreach($arrStores as $store) {
        //log::add(__CLASS__, 'debug', "[postSave] Compare " . $name . " et " . $store . " for deletion");
        if(stripos($name, $store) !== false)
          $exists = true;
      }
      if(!$exists)
        $currentStore->remove();
    }

  } // postSave

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
    $linkedStores = eqLogic::byTypeAndSearchConfiguration(__CLASS__, ["type" => "store", "link" => $this->getId()]);
    foreach($linkedStores as $store) {
     	$store->remove(); 
    }
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  public function checkNews() {
    $city = $this->getConfiguration('city','');
    
    if($this->getIsEnable() == 1) {
    	$url = "https://www.pubeco.fr/route.asp?mod=contenu_type&order=date&typeop=&cat=all&act=all&ville=".$city."&q=&periode%5B%5D=soon"; //typeop = 3 pour avoir que les prospectus?
    	log::add(__CLASS__, 'debug', "[checkNews] " . $city . " : " . $url);
      
      	//$html = shell_exec('curl ' . $url);
      	/*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $html = curl_exec($ch);
        curl_close($ch);*/
      	$html = @file_get_contents($url);
      	//log::add(__CLASS__, 'debug', "HTML: " . strlen($html));

      	if(strlen($html) > 100) {
          	$html = preg_replace('/\R+/', " ", $html);
          	$re = '/<div class="deal_column">(.*)<\/div>/mU';
          
          	preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
          
          	if(count($matches) > 0) {
              foreach($matches as $match) {
                $prospectus = $match[1];
                $reCover = '/(?:<p class="deal_cover"(?:.*))onclick="openActu\(\'(.*)\', {\'idc\':(.*)}\)"> <img src="(.*)"(?:.*)alt="(.*)"(?:.*)<\/p>/U';
                $reDate = '/<p class="deal_date">(.*)<\/p>/U';
                $reShop = '/<p class="deal_shop"><a(?:.*)>(.*)<\/a> <\/p>/U';
                
                preg_match($reCover, $prospectus, $infoCover);
                $link = "https://www.pubeco.fr" . $infoCover[1] . "?s=pubeco&ic=" . $infoCover[2];
                $image = strtok($infoCover[3], '?');
                $alt =html_entity_decode($infoCover[4], ENT_QUOTES);
                
                preg_match($reDate, $prospectus, $infoDate);
                $date = $infoDate[1];
                
                preg_match($reShop, $prospectus, $infoShop);
                $shop = html_entity_decode($infoShop[1], ENT_QUOTES);
                
                $linkedStores = eqLogic::byTypeAndSearchConfiguration(__CLASS__, ["type" => "store", "link" => $this->getId()]);
                
    			foreach($linkedStores as $store) {
                  $longName = $store->getConfiguration('longname');
                  log::add(__CLASS__, 'debug', "comparatif " . $alt . " || " . $longName );
                  if(stripos($alt, $longName) !== false) {
                    
                    log::add(__CLASS__, 'debug', "LINK: " . $link);
                    log::add(__CLASS__, 'debug', "IMAGE: " . $image);
                    log::add(__CLASS__, 'debug', "ALT: " . $alt);
                    log::add(__CLASS__, 'debug', "DATE: " . $date);
                    log::add(__CLASS__, 'debug', "SHOP: " . $shop);
                
                    $lastSeen = $store->getCmd(null, "lastSeen");
                    $lastSeen = $lastSeen->execCmd();
                    if($lastSeen != $date) {
                      	$store->checkAndUpdateCmd("lastSeen", $date);
						$store->checkAndUpdateCmd("lastAlert", date("Y-m-d H:i:s"));
                      
                      	$cmdNotif = $this->getConfiguration('cmdNotif');
                      	if(gettype($cmdNotif) === 'string') {
                          	self::sendNotification($cmdNotif, $alt, $date, $link);
                        } else if(gettype($cmdNotif) === 'array') {
                          	foreach($cmdNotif as $currentCmdNotif) {
                            	self::sendNotification($currentCmdNotif, $alt, $date, $link);
                            }
                        }
                    }
                    
                    log::add(__CLASS__, 'debug', "----------------");
                  }
                }
				
                
                
              }
            }
          
        } else {
          log::add(__CLASS__, 'debug', "[checkNews] html NOK");
        }

    } //isEnable
  } //checkNews

  public static function sendNotification($cmd, $title, $date, $link) {
		$sendAlert = cmd::byString($cmd);
		$sendAlert->execute(array('title' => $title, 'message' => "**" . $title . "** ( " . $date . " )" . PHP_EOL . $link));
  }
  
  public static function slugify($text, string $divider = '-') {
    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, $divider);

    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
      return 'n-a';
    }

    return $text;
  }
    
  
} // fin class

class prospectusalertesCmd extends cmd {

  // Exécution d'une commande
  public function execute($_options = array()) {
        log::add('prospectusalertes', 'debug', 'execute');
		$eqLogic = $this->getEqLogic(); // Récupération de l’eqlogic

		switch ($this->getLogicalId()) {
			case 'refresh': 
              $eqLogic->checkNews();
              break;
			default:
              throw new Error('This should not append!');
              log::add('prospectusalertes', 'warn', 'Error while executing cmd ' . $this->getLogicalId());
			break;
		}
		
		return;
    
  }

  /*     * **********************Getteur Setteur*************************** */

}