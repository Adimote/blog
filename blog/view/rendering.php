<?php

//Renders an array of posts into HTML and returns it
function renderPostList($posts) {
	$result = "";
	foreach ($posts as $post) {
		//render the post in preview mode
		$text = renderPost($post,true);
		$result .= $text;
	}
	return $result;
}

//Renders an array of tags and tagIDs into HTML and returns it
//'$tags' must be laid out as an array [id=id,name=name]
function renderTagList($tags) {
	$result = "";
	foreach ($tags as $t) {
		//Compile it into a string
		$id = htmlspecialchars($t['id'],ENT_QUOTES);
		$name = htmlspecialchars($t['tag']);
		$text = <<<"HTML"
<a href="postsbytag.php?tag={$id}" class="label label-primary tag">
	{$name}
</a>&nbsp;
HTML;
		$result .= $text;
	}
	return $result;
}


//Render a post, $is_preview is true if you just want to show the flavour text
function renderPost($post,$is_preview) {
	$urlname = $post['urlname'];
	$posturl = "post.php?name=".$urlname;
	$title = htmlspecialchars($post['title']);

	$sqldate = htmlspecialchars($post['date'],ENT_QUOTES);
	$nicedate = date("G:i:s F jS, Y",strtotime($post['date']));

	$author = htmlspecialchars($post['who']);

	if ($is_preview) {
		$content = htmlspecialchars($post['flavour']);
	} else {
		$content = $post['content'];
	}

	if (isset($GLOBALS['admin'])) {
		$id = htmlspecialchars($post['id']);
		$admin = <<<"HTML"
<a class="btn btn-primary" href="/secure/post-edit.php?id={$id}
">
	Edit
</a>
HTML;
	} else {
		$admin = "";
	}

	//Compile it into a string
	$text = <<<"HTML"
<article class="well">
	<header>
		<small>
			<p class="text-right"> Published:
			<a href="{$posturl}"><time pubdate datetime="{$sqldate}">{$nicedate}</time></a></p>
		</small>
		<a href="{$posturl}">
		<h2>{$title}</h2>
		</a>
		<hr/>
	</header>
	<div>{$content}</div>
	<div class="text-right">
		<small>
			<p>{$admin}
			By: {$author}</p>
		</small>
	</div>
</article>
HTML;
	return $text;
}
?>