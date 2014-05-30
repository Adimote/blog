<?php
	//Class that manages paginations. 
	class Paginator {

		//Total number of pages
		public $totalPages = 1;
		//Number of items per page
		public $perPage = 1;
		public $startIndex = 0;

		public $firstDisabled = True;

		//Number of next page
		public $next = 2;
		//Next page disabled
		public $nextDisabled = False;

		//Current page
		public $page = 1;

		//Number of previous page
		public $prev = 1;
		//Prev page disabled
		public $prevDisabled = True;

		public $lastDisabled = False;

		//Constructor
		//$perPage is the number of items in each page
		//$total is the total numer of items
		public function __construct($perPage,$total) {
			$this->perPage = $perPage;
			$this->total = $total;
			//calculate the total pages
			$this->totalPages = max(1,ceil(($total / $this->perPage)));
		}

		//gets the page input, and returns a page number
		private function parsePageNum($page,$totalPages) {
			//if page is not set, set it to 1
			if (empty($page)) {
				$page = 1;
			}

			//negative page numbers are reversed
			if ($page < 0) {
				$page = ($this->totalPages + $page)+1;
			}

			return $page;
		}
		
		//Update whether or not they're disabled
		private function updateDisables($page) {

			//Disable the previous button
			$this->prevDisabled = ($page <= 1);

			//Disable the first button
			$this->firstDisabled = $this->prevDisabled;

			//Disable the next button
			$this->nextDisabled = ($page >= $this->totalPages);

			//Disable the last button
			$this->lastDisabled = $this->nextDisabled;
		}

		//Update the page numbers and indexes
		public function setPage($page) {

			//Page number
			$this->page = $this->parsePageNum($page,$this->totalPages);

			//Get the next and prev pages
			$this->next = $this->page + 1;
			$this->prev = $this->page - 1;

			//Update the disabled
			$this->updateDisables($this->page);


			$this->startIndex = $this->perPage*($this->page-1);
			
		}
	}
?>