<?php

/**
 * @file plugins/generic/counter/SushiServiceDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SushiServiceDAO
 * @ingroup plugins_generic_counter
 *
 * @brief Class for managing SUSHI services.
 */

class SushiServiceDAO extends DAO {

	/**
	* Exchange credentials with the Synergies credential server
	*/
	function requestSynergiesService($journalId, &$errors) {
		$errstr = '';

		//$host = 'www.synergiescanada.org';
		//$path = '/org.synergiescanada.statistic.jsonapi/CredentialRegistration';
		$host = 'tspacetest.library.utoronto.ca';
		$path = '/org.synergiescanada.statistic.jsonapi/CredentialRegistration';
		$port = '8080';
		$timeout = 30;

		$base_url = Config::getVar('general','base_url');

		$post_body = '{';
		$post_body .= '"url": "' . $base_url . '",';
		$post_body .= '"requesterid": "synergiescanada",';
		$post_body .= '"customerid": "' . $journalId . '"}';
		$post_body .= "\r\n";

		$post_head = "POST $path HTTP/1.1\r\n";
		$post_head .= "Host: $host\r\n";
		$post_head .= "Content-Type: application/json\r\n";
		$post_head .= "User-Agent: PKP-OJS\r\n";
		$post_head .= "Content-length: ". strlen($post_body) ."\r\n";

		$content = '';
		$flag = false;
		$location='';
		$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
		if ($fp) {
			fputs($fp, $post_head);
			fputs($fp, "\r\n");
			fputs($fp, $post_body);
			while (!feof($fp)) {
				$line = fgets($fp, 10240);
				if ($flag) {
					$content .= $line;
				} else {
					if (stristr($line,"location:")!="") {
						$location = preg_replace("/location:/i","",$line);
						$location = trim($location);
					}
					$headers .= $line;
					if (strlen(trim($line)) == 0) {	$flag = true; }
				}
			}
			fclose($fp);
		} else {
			$errors = "Unable to connect to Synergies credential server ($errstr)\n";
			return false;
		}
		
		if (!$location == '') {
			$resource = fopen($location, "rb");
			$json = '';
			while (!feof($resource)) {
				$json .= fread($resource, 8192);
			}
			fclose($resource);
		} else {
			$errors = "No redirect available (No Location header)\n";
			return false;
		}

		// TODO: naive JSON decode, assumes one dimension
		$p = '';
		for ($i=0; $i < strlen($json); $i++) {
			if ($json[$i] == '{') { $p .= 'array('; continue; };
			if ($json[$i] == '}') { $p .= ')'; continue; };
			if ($json[$i] == ':') { $p .= '=>'; continue; };
			$p .= $json[$i];
		}
		$p = str_replace("http=>","http:",$p);
		$p = str_replace("\/","/",$p);
		if (!$p == '') {
			eval('$p = '.$p.';');
		} else {
			$errors = "Unable to parse response (Empty JSON object)\n";
			return false;
		}

		$this->_recordServiceDetails($p['customerid'], $p['requesterid'], $p['id'], $p['harvesteripv4']);

		return true;
	}


	/**
	* Record a harvester
	*/
	function _recordServiceDetails($journal_id, $service_name, $service_id, $ip_address) {
		$result = $this->update(
			'INSERT INTO sushi_service
				(journal_id, date_modified, service_name, service_id, ip_address)
			VALUES (?, ?, ?, ?, ?)',
			array(	(int) $journal_id,
					Core::getCurrentDate(),
					$service_name,
					$service_id,
					$ip_address)
		);
		return true;
	}


	/**
	* Check whether a particular harvester ip has access to this journal
	*/
	function checkService($journal_id, $ipv4) {
		$result =& $this->retrieve(
			'SELECT COUNT(id) FROM sushi_service WHERE journal_id = ? AND ip_address = ? ',
			array($journal_id, $ipv4)
		);
		$ret = false;
		if ($result->fields[0] > 0) $ret = true;
		$result->Close();
		return $ret;
	}

}

?>
