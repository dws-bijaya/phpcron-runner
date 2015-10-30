<?php
	class jApi_verify  {
		//
		static function verify ($vf, $sess_id = '', $userid = 0, $tout = 60, $params = array() ) {
			//
			$gets = array(
						'jApiAuthToken' => jApi_auth_token ,
						'jApiAuthRefreshToken'  => '',
						 /* */
		          		'jApi_sess_id'	=> $sess_id,
		          		'jApi_userid'    => $userid,
		          		'jApi_ua'    => $_SERVER['HTTP_USER_AGENT'],
		          		'jApi_ref_host'    =>'devserver', 
		          		'jApi_timeout'    => $tout,
		          		'jApi_ip'    => $_SERVER['REMOTE_ADDR'],
		          		'jApi_params'    => base64_encode(serialize($params)),
		          		/* */
		          		'params'=> $params
		          		);

			//
			$jApiAuthRefreshToken =  md5( jApi_auth_token . $sess_id  . $userid . jApi_security_salt);
			if ( $vf == 1 ) {
				$gets['jApiAuthRefreshToken'] = $jApiAuthRefreshToken;
				#print_r($gets); die;
				$qry_str =  http_build_query ( $gets, '&amp;');
    			$jApi_session_callback = jApi_session_callback . "?" . $qry_str;
    			$res=file_get_contents($jApi_session_callback);
                if ( $res !== $jApiAuthRefreshToken ) $jApiAuthRefreshToken ='' ;  
       		} else
				$jApiAuthRefreshToken ='';
			
    		//
    		$gets = array(
    						'jApiAuthToken' => jApi_auth_token ,
    						'jApiAuthRefreshToken'  =>$jApiAuthRefreshToken,
    						'callback' => isset($_GET['callback']) ? $_GET['callback'] : '' 
    						);
    		$qry_str =  http_build_query ( $gets, '&amp;');
    		// Finally redirect to  verify response .php
    		$jApi_verify_response_callback =  jApi_verify_response_callback . "?" . $qry_str;
    		
    		// pre redirect
    		self::redirect($jApi_verify_response_callback);
    		return $jApiAuthRefreshToken;
    	}

    	static function logout ($sess_uri, $auth_token, $refresh_token) {
            //
            $gets = array(
                        'jApiAuthToken' => $auth_token ,
                        'jApiAuthRefreshToken'  => $refresh_token 
                        );
            $qry_str =  http_build_query ( $gets, '&amp;');
            $sess_uri = $sess_uri . "?jApi_logout=1&" . $qry_str;
            $res=file_get_contents($sess_uri); 
        }
        static function redirect($rd=null){
    		static $location ;
    		if ( is_null($location) ) {
    			$location = $rd;
    			return ;
    		}
    		header("Location: $location");
    		exit;
    	}
	}
?>