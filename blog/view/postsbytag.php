<?php 
include_once '../../includes/sql-manager.php';
include_once '../common.php';
include_once 'rendering.php';
include_once 'pagination.php';

$sqlget = new sqlGetter();
$tagobj = $sqlget->getTagInfo($_GET['tag']);

if (isset($_GET['page'])) {
	$page = $_GET['page'];
} else {
	$page = "";
}

if (isset($_GET['tag'])) {
	$tag = $_GET['tag'];
} else {
	$tag = "";
}

$total = $sqlget->countPostsByTag($tag);
$paginator = new Paginator(Conf::Home_PerPage,$total);
$paginator->setPage($page);
$page = $paginator->page;
$posts = $sqlget->getPostsByTag($tag,$paginator->startIndex,$paginator->perPage);

if ($posts instanceof sqlError) {
	$mess = new Message("error",$posts->message);
	//500, internal server error
	http_response_code(500);
} elseif (count($posts) == 0) {
	$mess = new Message("error","No Posts Found");
	//404, not found
	http_response_code(404);
}

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

$tagurl = htmlspecialchars($tag,ENT_QUOTES);

if ($tagobj instanceof sqlError) {
	$mess = new Message("error",$tagobj->message);
	$GLOBALS['pageTitle'] = "Error";
} else {
	$tagname = htmlspecialchars($tagobj['tag']);
	//display the header
	$GLOBALS['pageTitle'] = "Posts Tagged ".$tagname;
}

if ($page == 1) {
	$GLOBALS['canonical'] = "/tag/$tagname/";
} else {
	$GLOBALS['canonical'] = "/tag/$tagname/$page/";
}

include_once 'header.php';

if (!$prevDisabled) {
	if ($prev == 1) {
		echo "<link rel=\"prev\" href=\"/tag/$tagname/\" />";
	} else {
		echo "<link rel=\"prev\" href=\"/tag/$tagname/$prev/\" />";
	}
}
if (!$nextDisabled) {
	echo "<link rel=\"next\" href=\"/tag/$tagname/$next/\" />";
}

//Set the global variable for page number
echo "<script>PAGENUM = $page;  TAG=\"$tag\"; MODE=\"postsByTag\";</script>";

include_once 'header-body.php';
?>
<div id="pagecontainer">
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

//Navigation bar
echo <<<"HTML"
<div id="pagenav" class="nav">
<a href="/tag/{$tagurl}/" {$firstDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-step-backward"></span></a>
<a href="/tag/{$tagurl}/{$prev}/" {$prevDisabled} class="btn btn-orange pull-left"><span class="glyphicon glyphicon-chevron-left"></span></a>
<a href="/tag/{$tagurl}/{$totalpages}/" {$lastDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-step-forward"></span></a>
<a href="/tag/{$tagurl}/{$next}/" {$nextDisabled} class="btn btn-orange pull-right"><span class="glyphicon glyphicon-chevron-right"></span></a>
<p class="text-center">Page {$page} / {$totalpages}</p>
</div>
<a id="toggleInfinite" class="full-btn nav">
	Click here to enable infinite scroll
</a>
</div>
<p class="text-center hidden" id="infiniLoading"><b>Loading...</b></p>
HTML;
include_once "footer.php";
?>