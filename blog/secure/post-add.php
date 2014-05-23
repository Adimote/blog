<?php
	// TODO: uploading images and returning the location?
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'post.php';

	//Main class, handles HTTP Posts of form
	class PostAddHandler {
		public $message;
		private $sqlset;

		//Constructor
		public function __construct() {
			$this->message = new Message();
			$this->sqlset = new sqlSetter();
			$this->edit = false;
		}

		//Parse a $_POST of the add-a-post form
		public function parsePost($post){
			if (post\checkForm($this,$post)) {
				//$p is either a postID or an sqlError
				$p = $this->sqlset->addPost(
					$post['who'],
					$post['title'],
					$post['flavour'],
					$post['content'],
					$post['date']);
				if ($p instanceof sqlError) {
					$this->message = new Message("error",$p->message);
					#Keep our contents if error
					$this->post = $post;
					return;
				} else {
					$this->message = new Message("success","Post is posted! ID is $p <a href='post-edit.php?id=$p'>Edit it</a>");
				}
				$postID = $p;
				//Set the tags if it's editing or posting
				post\setTags($this,$this->sqlset,$post['tags'],$postID);
			} else {
				#Keep our contents if error
				$this->post = $post;
			}
		}
	}
	$phr = new PostAddHandler();

	if ($_POST) {
		//if the user is submitting a post or an edit, apply the changes
		$phr->parsePost($_POST);
	}

	//Place the form
	post\placeForm($phr);
	
?>