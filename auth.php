class UserIdentity extends CUserIdentity

public function authenticate($type=null)
	{
		//~ if (strpos($this->username,"@")) {
		$user=User::model()->notsafe()->findByAttributes(array('email'=>$this->username));
		//~ } else {
			//~ $user=User::model()->notsafe()->findByAttributes(array('username'=>$this->username));
		//~ }
		if($type)
		{
			if($user===null)
			//~ if (strpos($this->username,"@")) {
			$this->errorCode=self::ERROR_EMAIL_INVALID;
			else if($this->password!==$user->facebook_id)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
			else {
				$this->_id=$user->id;
				$this->username=$user->username;
				$this->errorCode=self::ERROR_NONE;
			}
		}else
		{
			if($user===null)
				//~ if (strpos($this->username,"@")) {
					$this->errorCode=self::ERROR_EMAIL_INVALID;
				//~ } else {
					//~ $this->errorCode=self::ERROR_USERNAME_INVALID;
				//~ }
			else if(Yii::app()->getModule('user')->encrypting($this->password)!==$user->password)
				$this->errorCode=self::ERROR_PASSWORD_INVALID;
			else if($user->status==0&&Yii::app()->getModule('user')->loginNotActiv==false)
				$this->errorCode=self::ERROR_STATUS_NOTACTIV;
			else if($user->status==-1)
				$this->errorCode=self::ERROR_STATUS_BAN;
			else {
				$this->_id=$user->id;
				$this->username=$user->username;
				$this->errorCode=self::ERROR_NONE;
			}
		}
		return !$this->errorCode;
	}
	
	
	
	
	public function actionFblogin()
	{
		if(isset($_POST['email']) && isset($_POST['id']))
		{
			$fbid=$_POST['id'];
			$email=$_POST['email'];
			$fbuser=$this->getuserbyattribute(array('email'=>$email));
			if($fbuser)
			{
				if(!$fbuser->facebook_id)
				{
					$fbuser->facebook_id=$fbid;
					$fbuser->save();
				}
				$identity=new UserIdentity($fbuser->email,$fbuser->facebook_id);
				$identity->authenticate('facebook');
				if($login=Yii::app()->user->login($identity,0))
				echo json_encode(array('status'=>'login','redirect'=>$this->createUrl('/gplus')));
				else
				echo json_encode(array('status'=>'error'));
			}else
			{
				$fistname=$_POST['first_name'];   $lastname=$_POST['last_name']; 
				if($_POST['gender']=='male')  $gender='m'; else if($_POST['gender']=='female') $gender='f';
				$model=array('email'=>$email,'gender'=>$gender,'facebook_id'=>$fbid);
				$profile=array('firstname'=>$fistname,'lastname'=>$lastname);
				Yii::app()->user->setState("fbregister",array('profile'=>$profile,'user'=>$model));
				echo json_encode(array('status'=>'register'));
			}
		}
	}
	
	
	
	
	
	
	
	
	
	function fb_login(fb_id)
{	
    if(fb_id != "")
    {
        
        FB.init({
              appId      : fb_id,                        // App ID from the app dashboard
              status     : true, 
              cookie	 : true,                                // Check Facebook Login status
              xfbml      : true                                  // Look for social plugins on the page
            });
         FB.login(function(response) {
                 if (response.authResponse) {
                     FB.api('/me?fields=email,name,gender,first_name,last_name,birthday,bio', function(response) {
                         console.log(response);
                      
                        sociallogin(response);
                     });
                   } 
         }, {scope: 'public_profile,email,user_likes,user_birthday,user_location,user_about_me'});
    }
}
function sociallogin(response)
{
	var url=base_url+'/user/login/fblogin/';
	ajaxcall(url, response, function(output) 
	{
		var data=JSON.parse(output);
		if(data.status=='register')
			window.location.href = base_url;
		else if(data.status=='login')
			window.location.href = data.redirect;
		else
			displayflash('error','some error to process your request');	
	});
}



<div id="fb-root"></div>
	<script type="text/javascript" src="//connect.facebook.net/en_US/all.js"></script>
