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
	//if the url name contains only numbers, fail because it'll confuse the pagination
	} elseif(!preg_match('/[^\-0-9]/',$post['title'])) {
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
	include_once "../view/header.php";
	echo <<<"HTML"
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
var htmlPreview = i('html-preview');
function preview() {
	htmlPreview.innerHTML = content.getValue().replace(/<pre><code(.*?)>\\n/,"<pre><code$1>");
	//update mathJax
	MathJax.Hub.Queue(["Typeset",MathJax.Hub,htmlPreview]);
	//update highlight.js
	var pres = htmlPreview.getElementsByTagName("pre");
	if (pres.length != 0) {
		for (var i = 0; i < pres.length; i++) {
			var pre = pres[i];
			var codes = pre.getElementsByTagName("code");
			if (codes.length != 0) {
				for (var j = 0; j < codes.length; j++) {
					var code = codes[j];
					hljs.highlightBlock(code);
				}
			}
		}
	};
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

function update(){
	var text = title.value;
	text = text.toLowerCase().trim();
	text = text.replace(/[\t\b ]+/g, "-");
	text = text.replace(/[^a-z0-9\-]/g, "");
	i("urlname").innerHTML = escape(text);
};

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
	title.onkeydown = update;
	title.onchange = update;
	title.onpaste = update;
	title.oninput = update;
	//call it on start
	update();

	//When the content is submitted, copy all of the text in the main box to a hidden input
	i("post-form").onsubmit = function() {
		preview();
		i("content-inserter").value = content.getValue().replace(/<pre><code(.*?)>\\n/,"<pre><code$1>");
	};
};
</script>
HTML;
}

?>