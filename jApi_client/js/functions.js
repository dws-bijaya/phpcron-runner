var jApi_default = {
		showParam :  { onCall: function(p) { p['name'] = 'bijaya'; } } 
	}
var example4 ={
	method: 'implement_event',
	onCall:function(params){
		params['var2'] = 40 ;
		return true ;
	},
	onComplete:function(sid, mname, var1, var2, sum){
		alert("Sum of " + var1 + " and " + var2 + "  is " + sum );
	},
	onError:function(name,message,fileName,lineNumber){
		alert(name + "," + message + "," + fileName + "," + lineNumber);
	}
};



var jApi_default ={
		sum:{ 
				onCall:function(params){
					params['var2'] = 40 ;
					return true ;
				},
				onComplete:function(sid, mname, var1, var2, sum){
					alert("Sum of " + var1 + " and " + var2 + "  is " + sum );
				},
				onError:function(name,message,fileName,lineNumber){
					alert(name + "," + message + "," + fileName + "," + lineNumber);
				}
			},
		error : {
			onError:function(name,message,fileName,lineNumber){
					alert(name + "," + message + "," + fileName + "," + lineNumber);
			}
		} 	
};