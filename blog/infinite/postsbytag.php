<?php
include_once '../../includes/sql-manager.php';
include_once '../view/pagination.php';
include_once '../view/rendering.php';

if (isset($_GET['page']) ) {
	$page = $_GET['page'];
} else {
	$page = "";
}

if (isset($_GET['tag']) ) {
	$tag = $_GET['tag'];
} else {
	$tag = "";
}

if ($page && $tag) {
	echo "<link rel=\"canonical\" href=\"".Conf::URL."/tag/$tag/$page/\"/>";
}

$sqlget = new sqlGetter();

$total = $sqlget->countPostsByTag($tag);

$paginator = new Paginator(Conf::Home_PerPage,$total);

$paginator->setPage($page);

$totalpages = $paginator->totalPages;
$page = $paginator->page;

$posts = $sqlget->getPostsByTag($tag,$paginator->startIndex,$paginator->perPage);

//Set the admin parameter
if (isset($_GET['admin'])) {
	$GLOBALS['admin'] = true;
}

//Get the posts using paginators variables
$tags = $sqlget->getPostsByTag($tag,$paginator->startIndex,$paginator->perPage);
if ( count($posts) > 0 ) {
	echo renderPostList($posts);
	echo <<<HTML
	<div class="nav">
	<p class="text-center">Page {$page} / {$totalpages}</p>
	</div>
HTML;
} else {
	//No Content
	http_response_code(204);
}
?>