<?php
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';

	class tagLister {

		//Constructor
		public function __construct() {
			$this->message = new Message();
			$this->sqlset = new sqlSetter();
		}

		//Get info to edit with
		public function parseGet($id) {
			$name = $this->sqlset->getTagInfo($id);
			if ($name instanceof sqlError) {
				$this->message = new Message("error",$name->message);
				$this->edit = false;
				return;
			}
			$this->tag = $name;
			$this->edit = true;
		}

		//Apply edits
		public function parsePost($post) {
			$t = $this->sqlset->editTag($post['editid'],$post['tag']);
			if ($t instanceof sqlError) {
				$this->message = new Message("error",$t->message);
			} else {
				$this->message = new Message("success","Tag Successfully modified");
			}
		}

		//Places all tags attached to a post, otherwise, places all of them
		public function formatTags($postID = -1) {
			//if postID not defined, return all of 'em
			if ($postID == -1) {
				$tags = $this->sqlset->getTags(0,99999);
			} else {
				$tags = $this->sqlset->getTagsByPost($postID);
			}
			//return any errors
			if ($tags instanceof sqlError) {
				$this->message = new Message("error",$tags->message);
				return;
			}
			foreach ($tags as $t) {
				//prevent javascript injection, just incase
				$t = htmlspecialchars($t['tag']);
				$id = htmlspecialchars($t['id'],ENT_QUOTES);
				echo <<<HTML
	<span class="label label-primary">{$t}</span>
HTML;
			}
		}

		//Place the form into the html
		public function placeForm(){
			if ($this->edit) {	
				//Disable Caching, so everything is always re-evaluated
				header("Cache-Control: no-cache, must-revalidate");
				$title = Conf::Title." | Edit a Tag"; 
				$head = "Edit a Tag on ".SQLSETTINGS::HOST;
				$message = $this->message->formatMessage();
				$name = htmlspecialchars($this->tag['tag'],ENT_QUOTES);
				$id = htmlspecialchars($this->tag['id'],ENT_QUOTES);
				echo <<<HTML
<!-- Head -->
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<title>{$title}</title>
<!-- Body -->
<div class="well">
<form class="form-horizontal" id="post-form" method='POST'>
	<fieldset>
		<legend class="text-center">
		{$head}
		</legend>

		{$message}

		<!-- Title input -->
		<div class="form-group">
        	<label class="col-md-2 control-label">Tag Name:</label>
			<div class="col-md-9">
				<input type="text" class="form-control" name='tag' value="{$name}"/>
			</div>
		</div>

		<!-- Hideen Input for ID when editing -->
		<input type='hidden' name='editid' value='{$id}'/>

		<!-- Form actions -->
		<div class="col-md-12 text-right">
			<a class="btn btn-danger" href="tag-remove.php?id={$id}">Delete</a>
			<input class="btn btn-primary" type='submit' name='submit' value='Apply'>
		</div>
	</fieldset>
</form>
</div>
HTML;
			} else {
				//Disable Caching, so everything is always re-evaluated
				header("Cache-Control: no-cache, must-revalidate");
				$title = Conf::Title." | Edit a Tag"; 
				$head = "Edit a Tag on ".SQLSETTINGS::HOST;
				$message = $this->message->formatMessage();
				echo <<<HTML
<!-- Head -->
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<title>{$title}</title>
<!-- Body -->
<div class="well">
	<legend class="text-center">
	{$head}
	</legend>

	{$message}
</div>			
HTML;
			}
		}
	}
	$lister = new tagLister();

	if ($_POST) {
		$lister->parsePost($_POST);
	}

	$lister->parseGet($_GET['id']);

	$lister->placeForm();
?>
