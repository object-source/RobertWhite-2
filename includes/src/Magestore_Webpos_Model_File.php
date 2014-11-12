<?php
class Magestore_Webpos_Model_File
{
	function writeFile($data,$file)
	 {
		// $_file = fopen($file, 'w');
		
		// foreach ($data as $string) {
			// $result = fwrite($_file, $string);
		// }
		// fclose($_file);
		 $dom = new DOMDocument();
		 $dom->formatOutput = true;
		 $wpos = $dom->createElement( "wpos" );
		 $dom->appendChild( $wpos );
			$onestepcheckout_admin_key = $dom->createElement( "onestepcheckout_admin_key" );
			$onestepcheckout_admin_key->appendChild(
			$dom->createTextNode( $data['onestepcheckout_admin_key'] ));
		 $wpos->appendChild( $onestepcheckout_admin_key );
			$onestepcheckout_admin_code = $dom->createElement( "onestepcheckout_admin_code" );
			$onestepcheckout_admin_code->appendChild(
			$dom->createTextNode( $data['onestepcheckout_admin_code'] ));
		 $wpos->appendChild( $onestepcheckout_admin_code );
			$onestepcheckout_admin_id = $dom->createElement( "onestepcheckout_admin_id" );
			$onestepcheckout_admin_id->appendChild(
			$dom->createTextNode( $data['onestepcheckout_admin_id'] ));
		$wpos->appendChild( $onestepcheckout_admin_id );
			$onestepcheckout_admin_adminlogout = $dom->createElement( "onestepcheckout_admin_adminlogout" );
			$onestepcheckout_admin_adminlogout->appendChild(
			$dom->createTextNode( $data['onestepcheckout_admin_adminlogout'] ));
		$wpos->appendChild( $onestepcheckout_admin_adminlogout );
			$onestepcheckout_admin_adminlogin = $dom->createElement( "onestepcheckout_admin_adminlogin" );
			$onestepcheckout_admin_adminlogin->appendChild(
			$dom->createTextNode( $data['onestepcheckout_admin_adminlogin'] ));
		$wpos->appendChild( $onestepcheckout_admin_adminlogin );
		
		//
			$firstname = $dom->createElement( "firstname" );
			$firstname->appendChild(
			$dom->createTextNode( $data['firstname'] ));
		$wpos->appendChild( $firstname );
		
			$lastname = $dom->createElement( "lastname" );
			$lastname->appendChild(
			$dom->createTextNode( $data['lastname'] ));
		$wpos->appendChild( $lastname );
		
			$username = $dom->createElement( "username" );
			$username->appendChild(
			$dom->createTextNode( $data['username'] ));
		$wpos->appendChild( $username );
			
		//
		
		
		$dom->saveXML();
		 $_file = fopen($file, 'w');
		
		// foreach ($data as $string) {
			$result = fwrite($_file, $dom->saveXML());
		// }
		fclose($_file);
		// die('-----');
		
	 }
	
	 
	
	 
	 function readFile($file){
		
		$dom = new DOMDocument();

		 $dom->load($file);
		
		$wposNode = $dom->getElementsByTagName("wpos")->item(0);	
		//Get the onestepcheckout_admin_key
		$onestepcheckout_admin_key = $wposNode->getElementsByTagName("onestepcheckout_admin_key")->item(0)->nodeValue;
		$onestepcheckout_admin_code = $wposNode->getElementsByTagName("onestepcheckout_admin_code")->item(0)->nodeValue;
		$onestepcheckout_admin_id =  $wposNode->getElementsByTagName("onestepcheckout_admin_id")->item(0)->nodeValue;
		$onestepcheckout_admin_adminlogout =  $wposNode->getElementsByTagName("onestepcheckout_admin_adminlogout")->item(0)->nodeValue;
		$onestepcheckout_admin_adminlogin =  $wposNode->getElementsByTagName("onestepcheckout_admin_adminlogin")->item(0)->nodeValue;
		$userFirstname =  $wposNode->getElementsByTagName("firstname")->item(0)->nodeValue;
		$userLastname =  $wposNode->getElementsByTagName("lastname")->item(0)->nodeValue;
		$userUsername =  $wposNode->getElementsByTagName("username")->item(0)->nodeValue;
		$result = array('onestepcheckout_admin_key'=>$onestepcheckout_admin_key,
						'onestepcheckout_admin_code'=>$onestepcheckout_admin_code,
						'onestepcheckout_admin_id'=>$onestepcheckout_admin_id,
						'onestepcheckout_admin_adminlogout'=>$onestepcheckout_admin_adminlogout,
						'onestepcheckout_admin_adminlogin'=>$onestepcheckout_admin_adminlogin,
						 'firstname'=>$userFirstname,
						   'lastname'=>$userLastname,
						   'username'=>$userUsername);
		return $result;
	}
	
}