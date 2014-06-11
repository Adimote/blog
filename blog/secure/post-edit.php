<?php 
	// TODO: uploading images and returning the location?
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'post.php';

	//Main class, handles HTTP Posts of form
	class PostEditHandler {
		public $message;
		private $sqlset;

		//Constructor
		public function __construct() {
			$this->message = new Message();
			$this->sqlset = new sqlSetter();
		}

		//Parse a $_POST of the edit-a-post form
		public function parsePost($post){
			if (post\checkForm($this,$post)) {
				// editid will be sent
				$postID = $post['editid'];
				//$q is either a postID or an sqlError
				$q = $this->sqlset->editPost(
						$postID,
						$post['who'],
						$post['title'],
						$post['flavour'],
						$post['content'],
						$post['date']);
				if($q instanceof sqlError) {
					//if error, show it and stop
					$this->message = new Message("error",$q->message);
					return;
				} else {
					$this->message = new Message("success","Post Successfully modified, ID is #$postID");
				}
				//Set the tags if it's editing or posting
				post\setTags($this,$this->sqlset,$post['tags'],$postID);
			}
		}

		//---------------- Managing GETs ----------------
		
		//Parse a $_POST of the add-a-post form
		public function editPost($postID){
			$post = $this->sqlset->getPostById($postID);
			//Also prevents XSS
			if ($post instanceof sqlError) {
				$this->message = new Message("error",$post->message);
				return False;
			}
			//throw an error if the post doesn't exist
			if (!$post){
				$this->message = new Message("error","Post doesn't exist");
				return False;
			}
			$this->post = $post;
			//set postID after error check, to prevent SQL Injection
			$this->postID = $postID;
			$tags = $this->getTags($postID);
			if ($tags instanceof sqlError) {
				$this->message = new Message("error",$tags->message);
				return False;
			} else {
				$this->post['tags'] = $tags;
			}
			return True;
		}

		//Get all of the tags associated with this post formatted as a CSV of names
		private function getTags($postID) {
			$tags = $this->sqlset->getTagsByPost($postID);
			if ($tags instanceof sqlError) {
				$this->message = new Message("error",$tags->message);
				//return nothing
				return "";
			}
			foreach ($tags as $i=>$t) {
				$tags[$i] = htmlspecialchars($t['tag']);
			}
			return join(", ",$tags);
		}
	}
	$phr = new PostEditHandler();

	if ($_POST) {
		//if the user is submitting a post or an edit, apply the changes
		$phr->parsePost($_POST);
	}

	//if the user is editing a post, retrieve the info into $phr->post
	$phr->edit = $phr->editPost($_GET['id']);

	if ($phr->edit) {
		//Place the form
		post\placeForm($phr);
	} else {
		$title = Conf::Title." | Edit a Post";
		$head = "Edit a Post on ".SQLSETTINGS::HOST;
		$message = $phr->message->formatMessage();
		echo <<< "HTML"
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<title>{$title}</title>
<!-- Body -->
<div class="well">
<form class="form-horizontal" id="post-form" method='POST'>
	<legend class="text-center">
	{$head}
	</legend>
	{$message}
</form>
</div>
HTML;
	}

include_once "../view/footer.php";
?>