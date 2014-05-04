<?php 
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'rendering.php';

	$sqlget = new sqlGetter();
	$posts = $sqlget->getPostsDateOrder(0,Conf::Home_PerPage);

	//display the header
	$GLOBALS['pageTitle'] = "Home";
	include_once 'header.php';
?>
<main>
	<div class="container">
		<?php
		if ($posts instanceof sqlError) {
			$mess = new Message("error",$posts->message);
			echo $mess->formatMessage();
		} else {
			echo renderPostList($posts);
		}
		?>
	</div>
</main>