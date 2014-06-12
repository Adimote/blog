<?php
include_once '../../includes/sql-manager.php';
include_once '../../includes/logging.php';
include_once '../common.php';
include_once 'rendering.php';
include_once 'pagination.php';

//Returns true if the string is only numbers
function isOnlyNumbers($string) {
	return (preg_match("/[^0-9\-]/",$string)===0);
}

if (isset($_GET['page'])) {
	$page = $_GET['page'];
} else {
	$page = "";
}

//if the page number has any letters, throw an error
if (!isOnlyNumbers($page)) {
	$mess = new Message("error","Invalid Page Number");
	logger::log("Invalid Page Number ".htmlspecialchars($page));
	//404, Not Found
	http_response_code(404);
}

$sqlget = new sqlGetter();

$total = $sqlget->countPosts();

$paginator = new Paginator(Conf::Home_PerPage,$total);

//set the page
$paginator->setPage($page);

//Get the posts using paginators variables
$posts = $sqlget->getPostsDateOrder($paginator->startIndex,$paginator->perPage);

if ($posts instanceof sqlError) {
	$mess = new Message("error",$post->message);
	//500, internal server error
	http_response_code(500);
}

//get the previous and next page numbers
$prev = $paginator->prev;
$page = $paginator->page;
$next = $paginator->next;
$totalpages = $paginator->totalPages;

//Make all the disableds either "disabled" or blank
$firstDisabled = $paginator->firstDisabled?"disabled":"";
$prevDisabled = $paginator->prevDisabled?"disabled":"";
$nextDisabled = $paginator->nextDisabled?"disabled":"";
$lastDisabled = $paginator->lastDisabled?"disabled":"";

//display the header
$GLOBALS['pageTitle'] = "Home";
$GLOBALS['breadcrumbs'] = array(
		'blog'=>'/'
	);
if ($page == 1) {
	$GLOBALS['canonical'] = "/";
} else {
	$GLOBALS['canonical'] = "/$page/";
}
include_once 'header.php';
if (!$prevDisabled) {
	if ($prev == 1) {
		echo "<link rel=\"prev\" href=\"/\" />";
	} else {
		echo "<link rel=\"prev\" href=\"/$prev/\" />";
	}
}
if (!$nextDisabled) {
	echo "<link rel=\"next\" href=\"/$next/\" />";
}

//Set the global variable for page number
echo "<script>PAGENUM = ".$page."; MODE=\"posts\"</script>";

include_once 'header-body.php';

echo "<div id=\"pagecontainer\">";

if (isset($mess)) {
	echo $mess->formatMessage();
} else {
	if ($page == 1) {
		echo renderPost($posts[0]);
		$posts = array_slice($posts, 1);
	}
	echo renderPostList($posts);
}

if ($page == 1 && !$nextDisabled) {
	$infinite = <<<"HTML"
<a id="toggleInfinite" class="full-btn bottom-nav">
	Click here to enable infinite scroll
</a>
HTML;
} else {
	$infinite = "";
}

//Navigation bar
echo <<<"HTML"
<div id="pagenav" class="bottom-nav">
	<a href="/" {$firstDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-step-backward"></span></a>
	<a href="/{$prev}/" {$prevDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-chevron-left"></span></a>
	<a href="/{$totalpages}/" {$lastDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-step-forward"></span></a>
	<a href="/{$next}/" {$nextDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-chevron-right"></span></a>
	<p class="text-center">Page {$page} / {$totalpages}</p>
</div>
{$infinite}
</div>
<p class="text-center hidden" id="infiniLoading"><b>Loading...</b></p>
HTML;
include_once 'footer.php';
?>