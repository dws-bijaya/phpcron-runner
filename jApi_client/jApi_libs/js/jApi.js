var jApi =( function(){
	return new function () {
		this.__callbacks = {};
		this.err_debug = 0 ;
		this.auth_token = '';
		this.uri = '';
		this.id  = '';
		this.__auth_refresh_tokens = [];
		this.__cmObj = function(_jApi_method, _jApi_sufix)
		{
			var cm= _jApi_method.split(/\./);
			var c = 'default';
			var m = cm[0];
			if (cm.length>1){c=cm[0];m=cm[1];}
			try{
				cm = eval( "jApi_" + c + "." + m +  "." +_jApi_sufix ) ;
				if ( typeof cm == 'function') return cm;
			}catch(e){}
			return false;
		};

		this.__getRegFunction = function (_jApi_method, _jApi_script_id, _jApi_event_name) 
		{
			try	{
					var m = _jApi_method + "_" + _jApi_script_id;
					callback = jApi.__callbacks[m][_jApi_event_name] ? jApi.__callbacks[m][_jApi_event_name] : null;
					if ( typeof callback == 'function' ) {
						delete jApi.__callbacks[m][_jApi_event_name];			
				}
			}catch(e){callback = null;alert(e.message)};
			return callback;	
		};

		this.__onCall=function(_jApi_method, _jApi_params )
		{
			var cm = jApi.__cmObj(_jApi_method, 'onCall');
			if ( cm == false )
				return null;
			return cm(_jApi_params);
		};

		this.__onComplete=function(_jApi_script_id, _jApi_method, _jApi_params)
		{
			var args = _jApi_params ;
			args.unshift(_jApi_method);
			args.unshift(_jApi_script_id);		
			onComplete =  jApi.__getRegFunction(_jApi_method, _jApi_script_id, 'onComplete');
			if ( typeof onComplete == 'function' ) {
				onComplete.apply(this, args);
				return ;
			}

			//User on complete
			var cm = jApi.__cmObj(_jApi_method, 'onComplete');
			if ( cm == false )
				return false;
			//
			return cm.apply(null, args);
		};

		this.__onError = function (e, _jApi_script_id, _jApi_method, _jApi_exec ) 
		{
			onError =  jApi.__getRegFunction(_jApi_method, _jApi_script_id, 'onError');
			if ( typeof onError == 'function' ) {
				onError(e.name, e.message, e.fileName, e.lineNumber,_jApi_script_id, _jApi_method, _jApi_exec);
				return true;
			}
			if ( jApi.err_debug )
				alert("jApi Error: \n=======\n\nErr Type: " + e.name + "\nErr Msg: " + e.message + "\nErr File: "  + e.fileName  +  "\n Err Line: " + e.lineNumber);		
		};

		this.execute = function(_jApi_err_no, _jApi_err, _jApi_exec, _jApi_params, _jApi_debug_out,_jApi_err_debug,_jApi_script_id, _jApi_method, _jApi_auth_token, _jApi_refresh_token) 
		{
 			// Remove script id 
 			try{
				jApiScriptObj = document.getElementById(_jApi_script_id);
				if ( jApiScriptObj && jApiScriptObj.parentNode)
					jApiScriptObj.parentNode.removeChild(jApiScriptObj);
			}catch(e){jApi.__onError(e, _jApi_script_id, _jApi_method)}

			// 
 			var debugDivObj = document.getElementById('jApiDebuger');
	 		if ( !debugDivObj ) {
	 			var nDiv = document.createElement('DIV');
	 			nDiv.style.display = 'none';
	 			nDiv.setAttribute('id', 'jApiDebuger');
	 			document.getElementsByTagName('body')[0].appendChild(nDiv);
	 			debugDivObj = document.getElementById('jApiDebuger');	
	 		}
	 		var _jApi_debug_out = _jApi_debug_out!= null ? _jApi_debug_out: '';
			if (  _jApi_debug_out  ) {
				preObj = document.createElement('pre');
				preObj.innerHTML ="<hr />" +  _jApi_debug_out;
				debugDivObj.appendChild(preObj);
			}

 			//
 			jApi.err_debug=_jApi_err_debug;
			jApi.__auth_refresh_tokens[_jApi_auth_token]=_jApi_refresh_token;

			//
			if (_jApi_err_no) {
				// Custom error if any
				onError =  jApi.__getRegFunction(_jApi_method, _jApi_script_id, 'onError');
				if ( typeof onError == 'function' ) {
					onError(_jApi_err_no, _jApi_err,'','',_jApi_script_id, _jApi_method);
					return ;
				}
				// Raise onError 
				var cm = jApi.__cmObj(_jApi_method, 'onError');
				if ( cm == false )
					return false;
				//
				return cm(_jApi_err_no, _jApi_err, '', '', _jApi_script_id, _jApi_method);
			}

			//
 			try { 
 				// call on complete
				jApi.__onComplete.call(null,_jApi_script_id, _jApi_method,_jApi_params);
				//
 				eval(_jApi_exec);

 			}catch(e){ jApi.__onError(e, _jApi_script_id, _jApi_method, _jApi_exec);}
 		
 		};

 		this.doApiCall = function(_jApi_method, _jApi_params, _jApi_script_id, _jApi_uri, _jApi_auth_token)
 		{
			var jApi_script_id = _jApi_script_id;		
	    	if ( _jApi_script_id == null ) 
	    		jApi_script_id = 'jApi_' + new Date().getTime();    	
	    	if ( _jApi_script_id == 0  ) {
	    		if (jApi.id == '' )
	    			jApi.id = 'jApi_' + new Date().getTime(); 
	    		jApi_script_id = jApi.id;
	    	}

	    	var onComplete = onCall  = onError = null;
	    	if ( typeof _jApi_method == 'object' ) {
	    		onComplete = typeof _jApi_method['onComplete'] == 'function' ? _jApi_method['onComplete'] : null;
	    		onCall = typeof _jApi_method['onCall'] == 'function' ? _jApi_method['onCall'] : null;
	    		onError = typeof _jApi_method['onError'] == 'function' ? _jApi_method['onError'] : null;
	    		_jApi_method = typeof _jApi_method['method'] ? _jApi_method['method']  : 'null' ; 
		   	}	   	
	    	if ( _jApi_method == null || typeof  _jApi_method != 'string' )
	    		_jApi_method = 'get';
	    	
	    	_jApi_method = ( /\./.test(_jApi_method) ? '' : "default.") + _jApi_method ;
			_jApi_params =  (_jApi_params == null ) ? {} : _jApi_params;

	      	// OnBefore Call, so user change or add params
	    	var resOnCall = typeof onCall == 'function' ? onCall (_jApi_params) : jApi.__onCall(_jApi_method, _jApi_params );
	       	if ( resOnCall === false  )
	    		return false;

	    	//
	    	jApi_method =  escape(_jApi_method);
	    	//
	    	var jApi_params = '';
	    	if (_jApi_params) {
	    		for(var k in _jApi_params) {
	    			if ( k.toLowerCase() == 'jApiauthtoken' )
	    				continue;
	    			if ( k.toLowerCase() == 'japiauthrefreshtoken' )
	    				continue;
	    			if ( k.toLowerCase() == 'japiscriptid' )
	    				continue;
	    			if ( k.toLowerCase() == 'japimethod' )
	    				continue;
	    			
	    			if ( k.toLowerCase() == 'frm_name' && typeof $  == 'function') {
	    				var theFrm = $(_jApi_params[k]);
	    				if (!theFrm.length) continue;
	    				theFrm = theFrm.serialize();
	    				if (!theFrm.length) continue;
	    				if (jApi_params == '') 
	    					jApi_params = theFrm;
	    				else
	    					jApi_params += "&" + theFrm ;
	    				continue;
	    			}
	       			if (jApi_params == '')
	    				jApi_params = k + "=" + escape(_jApi_params[k]);
	    			else
	    				jApi_params += "&" + k + "=" + escape(_jApi_params[k]);
	    		}
	    	} 
	       	var jApi_auth_token = ( _jApi_auth_token == null ? ( jApi.auth_token == null ? null : jApi.auth_token ) : _jApi_auth_token );    	
	    	if ( jApi_auth_token == null )
	    		return ;
	       	if ( !jApi.__auth_refresh_tokens[jApi_auth_token] )
	    		jApi.__auth_refresh_tokens[jApi_auth_token] = "";
		   	var jApi_uri = ( _jApi_uri == null ? ( jApi.uri == null ? null : jApi.uri ) : _jApi_uri );
	    	if ( jApi_uri == null )
	    		return ;
	    	var api_uri = jApi_uri + "?jApiAuthToken=" + escape(jApi_auth_token);
	    		api_uri += "&jApiAuthRefreshToken=" + escape(jApi.__auth_refresh_tokens[jApi_auth_token]);
	    		api_uri += "&jApiMethod=" + jApi_method;
	    		api_uri += "&jApiScriptId=" + jApi_script_id;
	    		api_uri += "&" + jApi_params;
	     	var jApiDirectCallScriptObj = document.getElementById(jApi_script_id);
	     	try{
		 		if ( jApiDirectCallScriptObj ) 
					document.getElementsByTagName('head')[0].removeChild(jApiDirectCallScriptObj) ;
			}catch(e){};

			//
			jApiDirectCallScriptObj = document.createElement('script');
			jApiDirectCallScriptObj.type ='text/javascript';
			jApiDirectCallScriptObj.async = 'true';
			jApiDirectCallScriptObj.src = api_uri;
			jApiDirectCallScriptObj.id = jApi_script_id;
			jApiDirectCallScriptObj.onreadystatechange=function(){
															  if (this.readyState == 'complete') jApi.directCallOnload (jApi_script_id);
													   }
			jApiDirectCallScriptObj.onload = jApi.directCallOnload(jApi_script_id) ;
			jApi.__callbacks[ _jApi_method + "_" + jApi_script_id] = {} ;
			if ( onComplete ) {
				jApi.__callbacks[ _jApi_method + "_" + jApi_script_id]['onComplete'] = onComplete;
			}
			if ( onError) {
				jApi.__callbacks[ _jApi_method + "_" + jApi_script_id]['onError'] = onError;
			}
			var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(jApiDirectCallScriptObj, s) ;
 		};

 		this.directCallOnload = function (jApi_script_id) {
 		
 		}
 	};
})();