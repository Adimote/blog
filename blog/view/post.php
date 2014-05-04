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
	
	//display the header
	$GLOBALS['pageTitle'] = htmlspecialchars($post['title']);
	include_once 'header.php';
?>
<main>
	<div class="container">
		<?php
		if ($post instanceof sqlError) {
			$mess = new Message("error",$post->message);
			echo $mess->formatMessage();
		}
		?>
		<div class="col-md-9">
			<?php
				echo renderPost($post);
			?>
		</div>
		<div class="col-md-3">
			<div class="well"><h4>Tags:</h4>
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
</main>