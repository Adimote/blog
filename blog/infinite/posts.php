<?php
include_once '../../includes/sql-manager.php';
include_once '../view/pagination.php';
include_once '../view/rendering.php';

//pages to be loaded with infinite scrolling

if (isset($_GET['page'])) {
	$page = $_GET['page'];
	echo "<link rel=\"canonical\" href=\"".Conf::URL."/$page/\"/>";
} else {
	$page = "";
	echo "<link rel=\"canonical\" href=\"".Conf::URL."/\"/>";
}

$sqlget = new sqlGetter();

$total = $sqlget->countPosts();

$paginator = new Paginator(Conf::Home_PerPage,$total);

$paginator->setPage($page);

$totalpages = $paginator->totalPages;
$page = $paginator->page;

//Set the admin parameter
if (isset($_GET['admin'])) {
	$GLOBALS['admin'] = true;
}

//Get the posts using paginators variables
$posts = $sqlget->getPostsDateOrder($paginator->startIndex,$paginator->perPage);
if ( count($posts) > 0 ) {
	echo renderPostList($posts);
	echo <<<HTML
	<div class="nav">
	<p class="text-center">Page {$page} / {$totalpages}</p>
	</div>
HTML;
} else {
	http_response_code(204);
}
?>