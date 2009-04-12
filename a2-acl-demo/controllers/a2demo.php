<?php

class A2demo_Controller extends Controller {
	
	public function __construct()
	{
		parent::__construct();
		new Profiler;
		$this->a2 = A2::instance();
		$this->a1 = $this->a2->a1();
		echo '<div style="position:absolute;top:0px;right:0px;background-color:#f0f0f0;font-weight:bold;padding:5px;">',html::anchor('a2demo/','index'),'-',html::anchor('a2demo/db','DB'),'</div>';
	}
	
	public function index()
	{
		$blogs = ORM::factory('blog')->find_all();
		
		// show user info
		echo $this->_user_info();
		
		// show blogs
		echo '<hr>';
		if(count($blogs) === 0)
		{
			echo 'No blogs yet<br>';
		}
		else
		{
			foreach($blogs as $blog)
			{
				echo $blog->text,'<br>';
				echo html::anchor('a2demo/edit/'.$blog->id,'Edit'),'-',html::anchor('a2demo/delete/'.$blog->id,'Delete'),'<hr>';
			}
		}
		echo html::anchor('a2demo/add','Add');
	}
	
	private function _user_info()
	{
		if( ($user = $this->a2->get_user()))
		{
			$s =  '<b>'.$user->username.' <i>('.$user->role . ')</i></b> ' . html::anchor('a2demo/logout','Logout');
		}
		else
		{
			$s = '<b>Guest</b> ' . html::anchor('a2demo/login','Login') . ' - ' . html::anchor('a2demo/create','Create account');
		}
		
		return '<div style="width:100%;padding:5px;background-color:#AFB6FF;">' . $s . '</div>';
	}
	
	public function create()
	{
		if($this->a2->logged_in()) //cannot create new accounts when a user is logged in
			$this->index();
		
		// Create a new user
		$user = ORM::factory('user');

		$post = $_POST;

		if ($user->validate($post,TRUE))
		{
			// user  created, show login form
			$this->login();
		}
		else
		{
			//show form
			echo form::open();
			echo 'username:' . form::input('username') . '<br>';
			echo 'password:' . form::password('password') . '<br>';
			echo 'password confirm:' . form::password('password_confirm') . '<br>';
			echo 'role:' . form::dropdown('role',array('user'=>'user','admin'=>'admin')) . '<br>';
			echo form::submit(array('value'=>'create'));
			echo form::close();
		}
		
		echo Kohana::debug($post->as_array(),$user->as_array());
	}
	
	public function login()
	{
		if($this->a2->logged_in()) //cannot create new accounts when a user is logged in
			return $this->index();
		
		$post = Validation::factory($_POST)
			->pre_filter('trim')
			->add_rules('username', 'required', 'length[4,127]')
			->add_rules('password', 'required');		
			
		if($post->validate())
		{
			if($this->a1->login($post['username'],$post['password']))
			{
				// login succesful
				url::redirect( 'a2demo/index' );
			}
		}
		
		//show form
		echo form::open();
		echo 'username:' . form::input('username') . '<br>';
		echo 'password:' . form::password('password') . '<br>';
		echo form::submit(array('value'=>'login'));
		echo form::close();
	}
	
	public function logout()
	{
		$this->a1->logout();
		return $this->index();
	}
	
	public function add()
	{
		if(!$this->a2->allowed('blog','add'))
		{
			echo '<b>You are not allowed to add blogs</b><br>';
			return $this->index();
		}
		
		$blog = ORM::factory('blog');
		
		$this->_editor($blog);
	}
	
	public function edit($blog_id)
	{
		$blog = ORM::factory('blog',$blog_id);
		
		// NOTE the use of the actual blog object in the allowed method call!
		if(!$this->a2->allowed($blog,'edit')) 
		{
			echo '<b>You are not allowed to edit this blog</b><br>';
			return $this->index();
		}
		
		$this->_editor($blog);
	}
	
	private function _editor($blog)
	{
		if($blog->validate($_POST))
		{
			$blog->user_id = $this->a2->get_user()->id;
			$blog->save();
			return $this->index();
		}
		
		//show form
		echo form::open();
		echo 'text:' . form::textarea('text',$blog->text) . '<br>';
		echo form::submit(array('value'=>'post'));
		echo form::close();		
	}
	
	public function delete($blog_id)
	{
		$blog = ORM::factory('blog',$blog_id);
		
		// NOTE the use of the actual blog object in the allowed method call!
		if(!$this->a2->allowed($blog,'delete')) 
		{
			echo '<b>You are not allowed to delete this blog</b><br>';
		}
		else
		{
			$blog->delete();
		}
		
		$this->index();
	}
	
	public function db()
	{
		echo "<b>Mysql DB structure</b><pre>
		CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(12) unsigned NOT NULL auto_increment,
		  `username` varchar(32) NOT NULL default '',
		  `password` char(50) NOT NULL,
		  `logins` int(10) unsigned NOT NULL default '0',
		  `last_login` int(10) unsigned default NULL,
		  `role` enum('user','admin') NOT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `uniq_username` (`username`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		
		CREATE TABLE IF NOT EXISTS `blogs` (
		  `id` int(12) unsigned NOT NULL auto_increment,
		  `user_id` int(12) unsigned NOT NULL,
		  `text` text NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `user_id` (`user_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		</pre>";
	}

} 