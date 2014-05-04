<?php 
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'rendering.php';

	$sqlget = new sqlGetter();
	$posts = $sqlget->getPostsByTag($_GET['tag']);
	$tag = $sqlget->getTagInfo($_GET['tag']);
	if ($posts instanceof sqlError) {
		$mess = new Message("error",$posts->message);
	}elseif (count($posts) == 0) {
		$mess = new Message("error","No Posts Found");
	}
	if ($tag instanceof sqlError) {
		$mess = new Message("error",$tag->message);
		$GLOBALS['pageTitle'] = "Error";
	} else {
		$tagname = htmlspecialchars($tag['tag']);
		//display the header
		$GLOBALS['pageTitle'] = "Posts Tagged ".$tag;
	}


	include_once 'header.php';
?>
<main>
	<div class="container">
		<?php if ($tagname):?>
			<h2>Viewing posts Tagged '<?php echo $tagname ?>'</h2>
		<?php endif; ?>
		<?php 
		//if there's an error
		if ($mess) {
			echo $mess->formatMessage();
		} else {
			echo renderPostList($posts);
		}
		?>
	</div>
</main>