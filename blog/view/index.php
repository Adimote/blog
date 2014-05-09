<?php
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'rendering.php';
	include_once 'pagination.php';

	//Returns true if the string is only numbers
	function isOnlyNumbers($string) {
		return (preg_match("/[^0-9\-]/",$string)===0);
	}

	$page = $_GET['page'];

	//if page is has any letters, throw an error
	if (!isOnlyNumbers($page)) {
		$mess = new Message("error","Invalid Page Number");
	}

	$sqlget = new sqlGetter();
	$postCallable = $sqlget->getPostsDateOrder;

	$total = $sqlget->countPosts();
	$paginator = new Paginator(Conf::Home_PerPage,$total);

	//set the page
	$paginator->setPage($page);

	//Get the posts using paginators variables
	$posts = $sqlget->getPosts($paginator->startIndex,$paginator->perPage);


	//get the previous and next page numbers
	$prev = $paginator->prev;
	$page = $paginator->page;//setting $page to prevent negative page numbers
	$next = $paginator->next;
	$totalpages = $paginator->totalPages;

	//Make all the disableds either "disabled" or blank
	$firstDisabled = $paginator->firstDisabled?"disabled":"";
	$prevDisabled = $paginator->prevDisabled?"disabled":"";
	$nextDisabled = $paginator->nextDisabled?"disabled":"";
	$lastDisabled = $paginator->lastDisabled?"disabled":"";


	//display the header
	$GLOBALS['pageTitle'] = "Home";
	include_once 'header.php';
?>
<main class="container">
	<?php
	if ($mess) {
		echo $mess->formatMessage();
	} else {
		echo renderPostList($posts);
	}
	?>

	<?php
	//Navigation bar
	echo <<<"HTML"
<div class="nav">
<a href="?page=1" {$firstDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-step-backward"></span></a>
<a href="?page={$prev}" {$prevDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-chevron-left"></span></a>
<a href="?page={$totalpages}" {$lastDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-step-forward"></span></a>
<a href="?page={$next}" {$nextDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-chevron-right"></span></a>
<p class="text-center">Page {$page} / {$totalpages}</p>
</div>
HTML;
	?>
</main>
<?php 
	include_once 'footer.php';
?>