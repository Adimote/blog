<?php
	include_once '../../includes/sql-manager.php';
	include_once '../common.php';
	class PostRemoveHandler {
		
		//Constructor
		public function __construct(){
			$this->message = new Message();
			$this->sqlset = new sqlSetter();
		}

		//Returns true if the string is only numbers
		protected function isOnlyNumbers($string) {
			return (preg_match("/[^0-9]/",$string)===0);
		}

		public function Remove($get){
			$id = $get["id"];
			$status = $this->sqlset->deleteTag($id);
			if ($status instanceof sqlError) {
				$this->message = new Message("error",$status->message);
				return -1;
			}
			return $status;
		}
	}
	
 	$phr = new PostRemoveHandler();
	if ($_GET) {
		$result = $phr->Remove($_GET);
		if ($result instanceof sqlError) {
			$phr->message = new Message("error",$result->message);
		} else {
			$phr->message = new Message("success",htmlspecialchars($_GET['type'])." Successfully Removed!");
		}
	}
?>
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<!-- Head -->
<title>Remove a Tag</title>
<!-- Body -->
<div class="well">
	<?php
	echo $phr->message->formatMessage();
	?>
	<div class=" text-right">
		<a class="btn btn-info" onclick="window.history.back()">Go Back</a>
	</div>
</div>
<?php
include_once "../view/footer.php";
?>