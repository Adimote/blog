<?php 
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'rendering.php';
	include_once 'pagination.php';

	$sqlget = new sqlGetter();
	$posts = $sqlget->getPostsByTag($_GET['tag'],0,9999999);
	$tag = $sqlget->getTagInfo($_GET['tag']);

	if ($posts instanceof sqlError) {
		$mess = new Message("error",$posts->message);
	}elseif (count($posts) == 0) {
		$mess = new Message("error","No Posts Found");
	}

	$page = $_GET['page'];

	$total = $sqlget->countPostsByTag($_GET['tag']);	
	$paginator = new Paginator(Conf::Home_PerPage,$total);
	$paginator->setPage($page);
	$tags = $sqlget->getPostsByTag($_GET['tag'],$paginator->startIndex,$paginator->perPage);

	$totalpages = $paginator->totalPages;

	//get the previous and next page numbers
	$prev = $paginator->prev;
	$page = $paginator->page;//setting $page to prevent negative page numbers
	$next = $paginator->next;

	//Make all the disableds either "disabled" or ""
	$firstDisabled = $paginator->firstDisabled?"disabled":"";
	$prevDisabled = $paginator->prevDisabled?"disabled":"";
	$nextDisabled = $paginator->nextDisabled?"disabled":"";
	$lastDisabled = $paginator->lastDisabled?"disabled":"";

	$tagurl = htmlspecialchars($_GET['tag'],ENT_QUOTES);

	if ($tag instanceof sqlError) {
		$mess = new Message("error",$tag->message);
		$GLOBALS['pageTitle'] = "Error";
	} else {
		$tagname = htmlspecialchars($tag['tag']);
		//display the header
		$GLOBALS['pageTitle'] = "Posts Tagged ".$tagname;
	}

	include_once 'header.php';
?>
<main class="container">
	<?php if ($tagname):?>
		<h2>Viewing posts Tagged '<?php echo $tagname ?>'</h2>
	<?php endif; ?>
	<?php
	//if there's an error
	if (isset($mess)) {
		echo $mess->formatMessage();
	} else {
		echo renderPostList($posts);
	}
	?>
	<?php
	//Navigation bar
	echo <<<"HTML"
<div class="nav">
<a href="?tag={$tagurl}&page=1" {$firstDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-step-backward"></span></a>
<a href="?tag={$tagurl}&page={$prev}" {$prevDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-chevron-left"></span></a>
<a href="?tag={$tagurl}&page={$totalpages}" {$lastDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-step-forward"></span></a>
<a href="?tag={$tagurl}&page={$next}" {$nextDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-chevron-right"></span></a>
<p class="text-center">Page {$page} / {$totalpages}</p>
</div>
HTML;
	?>
</main>