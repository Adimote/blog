<?php
	//struct for storing message info to display to the user
	class Message {
		public $type;
		public $message;
		public function __construct($Type="",$Message="") {
			$this->type = $Type;
			$this->message = $Message;
		}
		//Places an error message in the page
		public function formatMessage($float) {
			if ($this->type == "") {
				return;
			}
			$class = "";
			if ($float) {
				$class .= "alert-float ";
			}

			if ($this->type == "success") {
				$typeMess = 'Success!';
				$class = $class.'alert-success';
			} elseif ($this->type == "error") {
				$typeMess = 'Error!';
				$class = $class.'alert-danger';
			} else {
				$typeMess = 'Warning!';
				$class = $class.'alert-danger';
			}

			$html = <<<"HTML"
<div id='alert' class="alert {$class}">
<strong>
	{$typeMess}
</strong>
	{$this->message}
	<button type="button" class="close" onclick="dismissAlert()">&times;</button>
</div>
HTML;
$js = <<<'HTML'
<script>
	function $i(id) {return document.getElementById(id);}
	function dismissAlert() {
		$i('alert').parentElement.removeChild($i('alert'));
	}
</script>
HTML;
			return $html.$js;
		}
	}
?>
