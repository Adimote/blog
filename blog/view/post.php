<?php 
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	include_once 'rendering.php';

	$sqlget = new sqlGetter();
	if ($_GET['name']) {
		$post = $sqlget->getPostByUrlname($_GET['name']);
	} elseif ($_GET['id']) {
		$post = $sqlget->getPostById($_GET['id']);
	}

	if (!$post) {
		$mess = new Message("error","No posts found by this name");
		logger::log("Invalid Page Number ".htmlspecialchars($page));
		//404, Not found
		http_response_code(404);
	}
	
	//set the variables for the header to add
	$GLOBALS['pageTitle'] = htmlspecialchars($post['title']);
	$GLOBALS['breadcrumbs'] = array(
			'blog'=>'/',
			$post['title']=>'/'.$post['urlname']
		);
	$GLOBALS['canonical'] = '/'.$post['urlname']."/";
	//display the header
	include_once 'header.php';
	include_once 'header-body.php';
?>
<?php
if ($post instanceof sqlError) {
	$mess = new Message("error",$post->message);
	//500, internal server error
	http_response_code(500);
}
if (isset($mess)):
	echo $mess->formatMessage();
else:
?>
<div class="row">
	<div class="col-md-9">
		<?php
			echo renderPost($post);
		?>
	</div>
	<div class="col-md-3">
		<div class="well">
		<h4>Tags:</h4>
			<div class="text-center tagcontainer">
			<?php
			$tags = $sqlget->getTagsByPost($post['id']);
			if (count($tags) != 0) {
				echo renderTagList($tags);
			} else {
				echo "No Tags Found";
			}
			?>
			</div>
		</div>
	</div>
</div>
<?php 
	endif;
	include_once 'footer.php';
?>