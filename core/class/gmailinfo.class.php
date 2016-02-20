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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

define('GMAIL_API_URL', 'https://mail.google.com/mail/feed/atom');




class gmailinfo extends eqLogic {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
    

    public static function pull($_id=null) {
        if ($_id != null) {      
            $gmailinfo = gmailinfo::byId($_id);
            if (is_object($gmailinfo)) {
                $gmailinfo->getInformations();
            }         
        }
        else {
            foreach (eqLogic::byType('gmailinfo') as $gmailinfo) {
                if (is_object($gmailinfo)) {
                    $gmailinfo->getInformations();
                }
            }    
        }
    }


    /*     * *********************Methode d'instance************************* */
    
    public function preUpdate() {
        if ($this->getConfiguration('email') == '') {
            throw new Exception(__('L\'email ne peut être vide', __FILE__));
        }
        if ($this->getConfiguration('password') == '') {
            throw new Exception(__('Le mot de passe ne peut être vide', __FILE__));
        }
        $this->setCategory('communication', 1);
    }

	public function postInsert() {
    	
        $gmail = new gmailinfoCmd();
        $gmail->setName(__('Mails non lus', __FILE__));
        $gmail->setEqLogic_id($this->id);
        $gmail->setLogicalId('unreadcount');
        $gmail->setUnite('');
        $gmail->setType('info');
        $gmail->setSubType('numeric');
		$gmail->setIsHistorized(0);
        $gmail->save();
    }

    /*     * **********************Getteur Setteur*************************** */
	public function postUpdate() {
        foreach (eqLogic::byType('gmailinfo') as $gmail) {
            $gmail->getInformations();
		}
    }
    
    public function getUnreadCount() {
		// sendRequest 
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, GMAIL_API_URL);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERPWD, $this->getConfiguration('email') . ":" . $this->getConfiguration('password'));
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_ENCODING, "");
		$curlData = curl_exec($curl);
		$res = curl_close($curl);
		//returning retrieved feed
        log::add('gmailinfo', 'debug', 'send request, result: '.$res);

		$xmlobjc = new SimpleXMLElement($curlData);
		$unreadcount = $xmlobjc->fullcount[0];
        log::add('gmailinfo', 'debug', 'send request, unreadcount: '.$unreadcount);
        
        return intval($unreadcount);
    }


    public function getInformations() {
    	log::add('gmailinfo', 'debug', 'Récupération des données', 'config');
        $cmd = $this->getCmd(null, 'unreadcount');

		$cmd->event($this->getUnreadCount());
        return ;
    }
	/*
    public function getShowOnChild() {
        return true;
    }*/

}

class gmailinfoCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */
/*
     public function dontRemoveCmd() {
        return true;
    }
*/
   public function execute($_options = null) {
        $eqLogic_gmail = $this->getEqLogic();

        if ($this->getLogicalId() == 'unreadcount') {
			$unreadcount = $eqLogic_gmail->getUnreadCount();
			return $unreadcount;
       }
       return false;
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>