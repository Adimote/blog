<?php
function renderPostList($posts) {
	$result = "";
	foreach ($posts as $post) {
		$id = $post['id'];
		$title = htmlspecialchars($post['title']);

		$sqldate = htmlspecialchars($post['date'],ENT_QUOTES);
		$nicedate = date("G:i:s F jS, Y",strtotime($post['date']));

		$author = htmlspecialchars($post['who']);
		$flavour = htmlspecialchars($post['flavour']);

		//Compile it into a string
		$text = <<<"HTML"
<article>
	<header>
		<a href="post.php?id={$id}">
		<h2>{$title}</h2>
		</a>
		<small>
			<p> Published:
			<time pubdate datetime="{$sqldate}">{$nicedate}</time> By: {$author}</p>
		</small>
	</header>
	<div>{$flavour}</div>
</article>
HTML;
		$result .= $text;
	}
	return $result;
}
?>