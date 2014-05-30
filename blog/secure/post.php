<?php
	namespace post;
	include_once '../common.php';


	//decodes a csv string of tags and adds them if they aren't already linked
	function setTags($phr,$sqlmanager, $tagsString, $postID) {
		$tags = explode(",",$tagsString);
		//Clear all the tags
		$e = $sqlmanager->removeAllTagsFrom($postID);
		if ($e instanceof sqlError) {
			$phr->message = new Message("error",htmlspecialchars($e->message));
			return;
		}

		//No need to trim the spaces, they're trimmed in attachTag.
		foreach ($tags as $t) {
			if (trim($t) != ""){
				$e = $sqlmanager->attachTag($postID,$t);
				if ($e instanceof sqlError) {
					$phr->message = new Message("error",htmlspecialchars($e->message));
				}
			}
		}
	}

	//Check the HTTP POST for any issues
	function checkForm($phr,$post) {
	  	if(!array_key_exists('who', $post)) {
			$phr->message = new \Message("error","HTTP POST FAILED: No Author submitted");
		} elseif(!array_key_exists('title', $post)) {
			$phr->message = new \Message("error","HTTP POST FAILED: No Title submitted");
		} elseif(!array_key_exists('flavour', $post)) {
			$phr->message = new \Message("error","HTTP POST FAILED: No Flavour Text submitted");
		} elseif(!array_key_exists('content', $post)) {
			$phr->message = new \Message("error","HTTP POST FAILED: No Content HTML submitted");
		} elseif(!array_key_exists('date', $post)) {
			$phr->message = new \Message("error","HTTP POST FAILED: No Date submitted");
		//if editid is something other than numbers, throw an error
		} elseif(preg_match('/[^0-9]/',$post['editid'])) {
			$phr->message = new \Message("error","HTTP POST FAILED: invalid Edit ID");
		//if the url name contains1 only numbers, fail because it'll confuse the pagination
		} elseif(!preg_match('/[^0-9]/',$post['urlname'])) {
			$phr->message = new \Message("error","HTTP POST FAILED: url for title must contain something other than numbers");
		} else {
			return true;
		}
		return false;
	}

	function placeForm($phr) {
		//Disable Caching, so everything is always re-evaluated
		header("Cache-Control: no-cache, must-revalidate");
		$title = \Conf::Title." | ";
		
		if ($phr->post) {
			//Date form element
			$date = htmlspecialchars($phr->post['date'],ENT_QUOTES);
			//Author form element
			$author = htmlspecialchars($phr->post['who'],ENT_QUOTES);
			//Title form element
			$postTitle = htmlspecialchars($phr->post['title'],ENT_QUOTES);
			//urlname
			$urlname = htmlspecialchars($phr->post['urlname'],ENT_QUOTES);
			//Flavour Text form element
			$flavour = htmlspecialchars($phr->post['flavour']);
			//Content form element
			$content = json_encode($phr->post['content']);
			//Tags form element
			$tags = htmlspecialchars($phr->post['tags']);
			if ($phr->edit){
				//Text to go on the submit button
				$submitName = "Update";
				//Title of page
				$title = $title."Edit a Post";
				//Header of page
				$head = "Edit a Post on ".\SQLSETTINGS::HOST;
				//ID for the editid hidden form element
				$id = $phr->postID;
				//Optional delete button
				$delete = <<<"HTML"
<a class="btn btn-danger" href="post-remove.php?id={$id}">Delete</a>
HTML;
			} else {
				//Text to go on the submit button
				$submitName = "Add";
				//Title of page
				$title = $title."Add a Post";
				//Header of page
				$head = "Add a Post to ".\SQLSETTINGS::HOST;
				//ID for the editid hidden form element
				$id = "";
				//Optional delete button
				$delete = "";
			}
		} else {
			//Title of page
			$title = $title."Add a Post";
			//Header of page
			$head = "Add a Post to ".\SQLSETTINGS::HOST;
			//Date form element
			$date = date('Y-m-d H:i:s');
			//Author form element
			$author = "Author";
			//Title form element
			$postTitle = "Title";
			//Flavour Text form element
			$flavour = "Flavour Text (usually first 3 lines)";
			//Content form element
			$content = "'Content'";
			//Tags form element
			$tags = "General, ";
			//Text to go on the submit button
			$submitName = "Add";
			//ID for the editid hidden form element
			$id = "";
			//Optional delete button
			$delete = "";
		}

		$message = $phr->message->formatMessage();
		echo <<<"HTML"
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

		<!-- Date input -->
		<div class="form-group">
        	<label class="col-md-2 control-label">Date:</label>
        	<div class="col-md-9">
				<input type="text" class="form-control" name='date' value="{$date}"/>
			</div>
		</div>

		<!-- Author input -->
		<div class="form-group">
        	<label class="col-md-2 control-label">Author:</label>
			<div class="col-md-9">
				<input type="text" class="form-control" name='who' value="{$author}"/>
			</div>
		</div>

		<!-- Title input -->
		<div class="form-group">
        	<label class="col-md-2 control-label">Title:</label>
			<div class="col-md-9">
				<div class="input-group">
					<input type="text" class="form-control" id="title" name='title' value="{$postTitle}"/>
  					<span class="input-group-addon">url: <span id="urlname">{$urlname}</span></span>
				</div>
			</div>
		</div>

		<!-- Flavour Text input -->
		<div class="form-group">
	        	<label class="col-md-2 control-label">Flavour Text:</label>
			<div class="col-md-9">
				<textarea class="form-control" id='flavour' name='flavour' style='resize:vertical;'>{$flavour}</textarea>
			</div>
		</div>

		<!-- Content input -->
		<div class="form-group">
        	<label class="col-lg-1 control-label">Content:</label>
        	<div class="col-md-5" >
        		<div class="rounded-edge">
					<div id='content' style='height:500px'></div>
				</div>
			</div>
			<article class="container">
				<div id='html-preview' class="col-md-6 well">
				</div>
			</article>
			<input type='hidden' name='content' id='content-inserter'/>
		</div>

		<!-- Hideen Input for ID when editing -->
		<input type='hidden' name='editid' value='{$id}'/>

		<!-- Preview management -->
		<div class="form-group">
			<div class= "col-md-12 text-right">
				<a id='preview-button' disabled onclick='preview()' class="btn btn-primary">Preview...</a>
		    	<label class="control-label" style="margin-left:10px">Auto</label>
				<input id='preview-toggle' type='checkbox' checked='true' onclick='togglePreview()'/>
			</div>
		</div>

		<!-- Tag inputs -->
		<div class="form-group">
			<label class="col-md-2 control-label">Tags:</label>
			<div class="col-md-9">
				<textarea class="form-control" name='tags' style='resize:vertical;'>{$tags}</textarea>
				<p>Separate with commas</p>
			</div>
		</div>
		<!-- Form actions -->
		<div class="col-md-12 text-right">
			{$delete}
			<input class="btn btn-primary" type='submit' name='submit' value='{$submitName}'>
		</div>
	</fieldset>
</form>
</div>
<script src='/js/ace/ace.js'></script>
<script>
	function i(id) {return document.getElementById(id);}
	function preview() {
		i('html-preview').innerHTML = content.getValue();
	}

	var Previewing = true;
	function togglePreview() {
		if (i('preview-toggle').checked) {
			Previewing = true;
			preview();
			i('preview-button').setAttribute('disabled','disabled');
		} else {
			Previewing = false;
			i('preview-button').removeAttribute('disabled');
		}
	}

	//ACE Editor Initialisation
	var content = ace.edit('content');
	content.setTheme('ace/theme/monokai');
	content.getSession().setMode('ace/mode/html');
	content.setValue({$content});
	preview();
	//re-preview on any change if it is toggled on
	content.getSession().on('change', function() {
		if (Previewing) {
			preview();
		}
	});

	window.onload = function() {
		var title = i("title");
		var update = function(){
			var text = title.value;
			text = text.toLowerCase().trim();
			text = text.replace(/[\t\b ]+/g, "-");
			text = text.replace(/[^a-z0-9\-]/g, "");
			i("urlname").innerHTML = escape(text);
		};
		title.onkeydown = update;
		title.onchange = update;
		title.onpaste = update;
		title.oninput = update;
		//call it on start
		update();

		//When the content is submitted, copy all of the text in the main box to a hidden input
		i("post-form").onsubmit = function() {
			i("content-inserter").value = content.getValue();
		};
	};
</script>
HTML;
	}
?>