<?php

	//SQLSETTINGS
	include_once 'sql-config.php';
	include_once 'settings.php';
	include_once 'logging.php';

	//Error format
	class sqlError {
		public $message;
		public function __construct($Message = "") {
			$this->message = $Message;
			//log the error
			logger::log($Message);
		}
	}

	class sqlGetter {
		public function __construct() {
			//Set up the database handler
			$this->dbh = new PDO(
					"mysql:host=".SQLSETTINGS::HOST
					.";dbname=".SQLSETTINGS::DATABASE,
					SQLSETTINGS::GET_USER,
					SQLSETTINGS::GET_PASSWORD
				);
		}

		//---------------- Helper Functions ----------------

		//Always returns a TagID, if given either an ID or a Name
		protected function ensureID($tag) {
			if (is_numeric($tag)) {
				$tagID = $tag;
			} else {
				//Get the ID of the tag if it isn't already inputted
				$tagID = $this->getTagByName($tag);
				//if none of them matched, return -1
				if ($tagID == false){
					//ERROR
					return -1;
				}
			}
			return $tagID;
		}

		//Returns true if the string is only numbers
		protected function isOnlyNumbers($string) {
			return (preg_match("/[^0-9\-]/",$string)===0);
		}

		//Returns true if the string is only Lower Case letters
		protected function isOnlyLowerCaseLetters($string) {
			return (preg_match("/[^a-z]/",$string)===0);
		}

		//Get all the items in table X
		private function getAllFromTable($table,$start_at=0,$length=99999999) {
			//SQL injection protection
			if ($this->isOnlyNumbers($start_at) && 
				$this->isOnlyNumbers($length) && 
				$this->isOnlyLowerCaseLetters($table)) {
					//Find all posts from 'start_at' of length 'length'
					$stmt = $this->dbh->prepare("SELECT * FROM $table WHERE id >= :start_at LIMIT :length;");
					$stmt->bindValue(':start_at', $start_at, PDO::PARAM_INT);
					$stmt->bindValue(':length', $length, PDO::PARAM_INT);
					if ($stmt->execute()) {
						//Fetch all the results
						return $stmt->fetchAll(PDO::FETCH_ASSOC);
					} else {
						//if SQL failed, return it as a response
						return new sqlError("Failed to get ".$table."s, error is ".$stmt->errorInfo()[2]);
					}
			} else {
				if (!$this->isOnlyNumbers($start_at)){
					return new sqlError("invalid start_at value");
				} elseif (!$this->isOnlyNumbers($length)) {
					return new sqlError("invalid length value");
				} elseif (!$this->isOnlyLowerCaseLetters($table)) {
					return new sqlError("invalid table value");
				} else {
					//not sure how you're meant to even get this error
					return new sqlError("invalid inputs");
				}
			}
		}

		//make a title into a urlname
		public function makeURL($title) {
			$string = strtolower(trim($title));
			$string = preg_replace("/[\t\b ]+/", "-", $string);
			$string = preg_replace("/[^a-z0-9\-]/", "", $string);
			return $string;
		}

		//---------------- Getter Functions ----------------
		
		//---------------- Getters for Multiple items -------------

		//---------------- Posts ----------------		

		public function countPosts(){
			$stmt = $this->dbh->prepare("SELECT COUNT(*) AS total FROM post");
			if ($stmt->execute()) {
				//Fetch an associative array, which will only contain the total
				$fetch = $stmt->fetch(PDO::FETCH_ASSOC);
				return intval($fetch['total']);
			} else {
				//if SQL failed, return it as a response
				return new sqlError("Failed to count posts, error is ".$stmt->errorInfo()[2]);
			}
		}

		//Get all posts from start_at to length+start_at
		public function getPosts($start_at=0,$length=99999999) {
			return $this->getAllFromTable("post",$start_at,$length);
		}

		//get posts by order of date
		public function getPostsDateOrder($start_at=0,$length=99999999) {
			if ($this->isOnlyNumbers($start_at) &&
				$this->isOnlyNumbers($length)) {
					//Find all posts from 'start_at' of length 'length'
					$stmt = $this->dbh->prepare("SELECT * FROM post ORDER BY date DESC LIMIT :start_at, :length");
					$stmt->bindValue(':start_at', $start_at, PDO::PARAM_INT);
					$stmt->bindValue(':length', $length, PDO::PARAM_INT);
					if ($stmt->execute()) {
						//Fetch all the results
						return $stmt->fetchAll(PDO::FETCH_ASSOC);
					} else {
						//if SQL failed, return it as a response
						return new sqlError("Failed to get posts by date, error is ".$stmt->errorInfo()[2]);
					}
			} else {
				if (!$this->isOnlyNumbers($start_at)){
					return new sqlError("invalid start_at value");
				} elseif (!$this->isOnlyNumbers($length)) {
					return new sqlError("invalid length value");
				} else {
					//not sure how you're meant to even get this error
					return new sqlError("invalid inputs");
				}
			}
		}

		//Get a list of PostIDs by a Tag name/ID
		public function getPostsByTag($tag,$start_at=0,$length=99999999) {
			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);
			if ($tagID instanceof sqlError) {
				return $tagID;
			}
			$stmt = $this->dbh->prepare("SELECT * FROM post WHERE id in (SELECT postid from tagpost WHERE tagid = :tagID) ORDER BY date DESC LIMIT :start_at, :length;");
			$stmt->bindValue(':tagID', $tagID, PDO::PARAM_INT);
			$stmt->bindValue(':start_at', $start_at, PDO::PARAM_INT);
					$stmt->bindValue(':length', $length, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			//Fetch all the results
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		//Count the number of posts with this tag
		public function countPostsByTag($tag){
			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);
			if ($tagID instanceof sqlError) {
				return $tagID;
			}

			$stmt = $this->dbh->prepare("SELECT COUNT(*) AS total FROM post WHERE id in (SELECT postid from tagpost WHERE tagid = :tagID);");
			$stmt->bindValue(':tagID', $tagID, PDO::PARAM_INT);
			if ($stmt->execute()){
				$fetch = $stmt->fetch(PDO::FETCH_ASSOC);
				return intval($fetch['total']);
			} else {
				//if SQL failed, return it as a response
				return new sqlError("Failed to count posts by tag, error is ".$stmt->errorInfo()[2]);
			}
		}

		//---------------- Tags ----------------

		//Get all tags from start_at to length+start_at
		public function getTags($start_at=0,$length=99999999) {
			return $this->getAllFromTable("tag",$start_at,$length);
		}

		//Get all tagPosts from start_at to length+start_at
		public function getTagPosts($start_at=0,$length=99999999) {
			return $this->getAllFromTable("tagpost",$start_at,$length);
		}

		//Get a list of TagIDs by a Post ID
		public function getTagsByPost($postID) {
			$stmt = $this->dbh->prepare("SELECT * FROM tag WHERE id in (SELECT tagid from tagpost WHERE postid = :postID);");
			$stmt->bindValue(':postID', $postID, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			//Fetch all the results
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}


		//---------------- Getter Functions for Single items ----------------

		//---------------- Posts ----------------

		//Get the info of a post by its urlname
		public function getPostByUrlname($urlname) {
			$stmt = $this->dbh->prepare("SELECT * FROM post WHERE urlname = :urlname;");
			$stmt->bindValue(':urlname', $urlname, PDO::PARAM_STR);
			if ($stmt->execute()){
				//Fetch all the results
				$results =  $stmt->fetch(PDO::FETCH_ASSOC);
				return $results;
			} else {
				return new sqlError("Failed to get post info, error is: ".$stmt->errorInfo()[2]);
			}
		}

		//Get the info of a Post by ID
		public function getPostById($postID) {
			//SQL injection protection
			if ($this->isOnlyNumbers($postID)) {
					//Find all posts with a matching ID
					$stmt = $this->dbh->prepare("SELECT * FROM post WHERE id = :post_id;");
					$stmt->bindValue(':post_id', $postID, PDO::PARAM_INT);
					if ($stmt->execute()){
						//Fetch all the results
						$results =  $stmt->fetch(PDO::FETCH_ASSOC);
						return $results;
					} else {
						return new sqlError("Failed to get post info, error is: ".$stmt->errorInfo()[2]);
					}
			} else {
				return new sqlError("invalid postID");
			}
		}

		//---------------- Tags ----------------

		//Get the name of a Tag by its ID
		public function getTagInfo($tagID) {
			//SQL injection protection
			$tagID = $this->ensureID($tagID);
			if ($tagID instanceof sqlError) {
				return $tagID;
			}
			//Find all posts from 'start_at' of length 'length'
			$stmt = $this->dbh->prepare("SELECT * FROM tag WHERE id = :tag_id;");
			$stmt->bindValue(':tag_id', $tagID, PDO::PARAM_INT);
			if ($stmt->execute()){
				//Fetch all the results
				$results =  $stmt->fetch(PDO::FETCH_ASSOC);
				return $results;
			} else {
				return new sqlError("Failed to get tag name, error is: ".$stmt->errorInfo()[2]);
			}
		}

		//Get a tag ID by a literal name
		public function getTagByName($name) {
			$stmt = $this->dbh->prepare("SELECT id FROM tag WHERE tag = :name;");
			$stmt->bindValue(':name', $name, PDO::PARAM_STR);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			$array = $stmt->fetch(PDO::FETCH_NUM);
			return (int)$array[0];
		}

		//Get a Tagpost by the Tag and Post ID
		public function getTagpostID($tag,$postID) {
			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);
			if ($tagID instanceof sqlError) {
				return $tagID;
			}

			$stmt = $this->dbh->prepare("SELECT id FROM tagpost WHERE tagid = :tagID AND postid = :postID;");
			$stmt->bindValue(':postID', $postID, PDO::PARAM_INT);
			$stmt->bindValue(':tagID', $tagID, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			$array = $stmt->fetch(PDO::FETCH_NUM);
			return (int)$array[0];
		}

	}//End of sqlGetter

	//sql Setter, less secure as it is assumed all posts to it require authentication,
	//child of sqlGetter
	class sqlSetter extends sqlGetter {

		public function __construct() {
			//Set up the database handler
			$this->dbh = new PDO(
					"mysql:host=".SQLSETTINGS::HOST
					.";dbname=".SQLSETTINGS::DATABASE,
					SQLSETTINGS::SET_USER,
					SQLSETTINGS::SET_PASSWORD
				);
		}

		//---------------- Private Functions ----------------

		//Insert a tag into the database
		private function addTag($name) {
			$stmt = $this->dbh->prepare("INSERT INTO tag VALUES (NULL,:name);");
			$stmt->bindValue(':name', $name, PDO::PARAM_STR);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			//Return the ID of the tag
			return $this->dbh->lastinsertId();
		}

		//---------------- Addition Function ----------------

		//Insert a post into the database
		public function addPost($author,$title,$flavour,$content,$date="") {
			if ($date == "") {
				//set the date if it hasn't already been set
				$date = date('Y-m-d H:i:s');
			}

			$urlname = $this->makeURL($title);

			//if the urlname already exists
			if ($this->getPostByUrlname($urlname)) {
				return new sqlError("Failed to add post, a title with this url already exists");
			}

			$stmt = $this->dbh->prepare("INSERT INTO post VALUES (NULL,:urlname,:datetime,:author,:title,:flavour,:content);");
			$stmt->bindValue(':urlname', $urlname, PDO::PARAM_STR);
			$stmt->bindValue(':datetime', $date, PDO::PARAM_STR);
			$stmt->bindValue(':author', $author, PDO::PARAM_STR);
			$stmt->bindValue(':title', $title, PDO::PARAM_STR);
			$stmt->bindValue(':flavour', $flavour, PDO::PARAM_STR);
			$stmt->bindValue(':content', $content, PDO::PARAM_STR);
			//Return the ID of the post if success
			if ($stmt->execute()) {
				return $this->dbh->lastinsertId();
			} else {
				return new sqlError("Failed to add post, error is:".$stmt->errorInfo()[2]);
			}
		}

		//Return a cleaned tagname
		private function cleanTagName($tag) {
			return preg_replace("/[\n\r\t]/","",trim($tag," ,"));
		}

		//Attach a tag to a post, if the tag doesn't exist, it creates it
		public function attachTag($postID,$tag) {
			//Remove some nasty user inputs
			$tag = $this->cleanTagName($tag);		

			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);

			//Return any errors that occur
			if ($tagID instanceof sqlError) {
				return $tagID;
			}

			//if there was no matching tag, but no sqlError
			if ($tagID < 0) {
				//Add it,
				$t = $this->addTag($tag);
				//if it failed, return an error.
				if ($t instanceof sqlError){
					return $t;
				}
				//then find it again
				$tagID = $this->getTagByName($tag);

				//again with the error
				if ($tagID instanceof sqlError){
					return $tagID;
				}
			}

			$tagposts = $this->getTagsByPost($postID);
			if ($tagposts instanceof sqlError) {
				return $tagposts;
			}
			//if tag already added to the post, return tagID
			if (in_array($tagID,$tagposts))
			{	
				return $tagID;
			}

			$stmt = $this->dbh->prepare("INSERT INTO tagpost VALUES (NULL,:postID,:tagID);");
			$stmt->bindValue(':postID', $postID, PDO::PARAM_INT);
			$stmt->bindValue(':tagID', $tagID, PDO::PARAM_INT);
			//Return the ID of the tagpost
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			return $this->dbh->lastinsertId();	
		}

		//---------------- Deletion ----------------

		//Remove a tag from the post
		public function removeTagFrom($postID,$tag) {
			$tagpostID = $this->getTagpostID($tag,$postID);
			if ($tagpostID instanceof sqlError){
				return $tagpostID;
			}
			$stmt = $this->dbh->prepare("DELETE FROM tagpost WHERE id = :id;");
			$stmt->bindValue(':id', $tagpostID, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			//remove the tag if no other posts are assigned to it
			if (count($this->getPostsByTag($tag,0,99999999)) == 0) {
				$this->deleteTag($tag);
			}

			return $tagpostID;
		}

		//Clear all the tags associated with a post
		public function removeAllTagsFrom($postID) {
			$tags = $this->getTagsByPost($postID);
			
			//if error, return it.
			if ($tags instanceof sqlError) {
				return $tags;
			}

			$removed = array();
			foreach ($tags as $tag) {
				$removed[] = $this->removeTagFrom($postID,$tag['id']);
			}
			return $removed;
		}

		//Clear all the tagposts associated with the Tag
		private function removeTagpostsforTag($tag) {

			//get the ID of the tag if it isn't already an ID
			$tagID = $this->ensureID($tag);

			if ($tagID instanceof sqlError) {
				return $tagID;
			}

			//Delete the tagposts
			$stmt = $this->dbh->prepare("DELETE FROM tagpost WHERE tagid = :id;");
			$stmt->bindValue(':id', $tagID, PDO::PARAM_INT);

			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}

			return $tagID;
		}

		//Remove a post
		public function deletePost($postID) {

			//if it isn't only numbers, refuse
			if (!$this->isOnlyNumbers($postID)){
				return new sqlError("Invalid PostID");
			}
			//Clear all tags
			$t = $this->removeAllTagsFrom($postID);
			//Stop if error
			if ($t instanceof sqlError) {
				return $t;
			}

			//Then remove the post
			$stmt = $this->dbh->prepare("DELETE FROM post WHERE id = :id;");
			$stmt->bindValue(':id', $postID, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			return $postID;
		}

		//Remove a tag
		public function deleteTag($tag) {
			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);

			if ($tagID instanceof sqlError) {
				return $tagID;
			} elseif ($tagID < 0) {
				return new sqlError("Tag doesn't Exist");
			}

			//First, clear the tagPosts
			$t = $this->removeTagpostsforTag($tagID);
			if ($t instanceof sqlError) {
				return $t;
			}

			//Delete a tag
			$stmt = $this->dbh->prepare("DELETE FROM tag WHERE id = :id;");
			$stmt->bindValue(':id', $tagID, PDO::PARAM_INT);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			return $tagID;
		}

		//---------------- Modification ----------------

		//Modify a post
		public function editPost($postID,$author="",$title="",$flavour="",$content="",$date="") {
			$query = "UPDATE post SET ";
			if ($this->isOnlyNumbers($postID)){
				if ($title != "") {
					$query = $query."urlname = :urlname";
				}
				if ($author != "") {
					$query = $query.", who = :author";
				}
				if ($title != "") {
					$query = $query.", title = :title, urlname = :urlname";
				}
				if ($flavour != "") {
					$query = $query.", flavour = :flavour";
				}
				if ($content != "") {
					$query = $query.", content = :content";
				}
				if ($date != "") {
					$query = $query.", date = :date";
				}
				
				$query = $query." WHERE id = :id;";
				$stmt = $this->dbh->prepare($query);
				$stmt->bindValue(':id', $postID, PDO::PARAM_INT);
				if ($author != "") {
					$stmt->bindValue(':author', $author, PDO::PARAM_STR);
				}
				if ($title != "") {
					$stmt->bindValue(':title', $title, PDO::PARAM_STR);
					$urlname = $this->makeURL($title);
					//if the urlname already exists
					if ($this->getPostByUrlname($urlname)['id'] != $postID) {
						return new sqlError("Failed to edit post, a post with this url already exists");
					}
					$stmt->bindValue(':urlname', $urlname, PDO::PARAM_STR);
				}
				if ($flavour != "") {
					$stmt->bindValue(':flavour', $flavour, PDO::PARAM_STR);
				}
				if ($content != "") {
					$stmt->bindValue(':content', $content, PDO::PARAM_STR);
				}
				if ($date != "") {
					$stmt->bindValue(':date', $date, PDO::PARAM_STR);
				}
				//Return the ID of the tagpost
				if (!$stmt->execute()){
					return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
				}
			} else {
				return new sqlError("Invalid postID");
			}
			return $postID;
		}

		//Renaming Tags
		public function editTag($tag,$newName) {
			//get the ID of the tag if it isn't already an ID.
			$tagID = $this->ensureID($tag);
			//stop if error
			if ($tagID instanceof sqlError){
				return $tagID;
			}
			$stmt = $this->dbh->prepare("UPDATE tag SET tag = :name WHERE id = :id");
			$stmt->bindValue(':id', $tagID, PDO::PARAM_INT);
			$stmt->bindValue(':name', $newName, PDO::PARAM_STR);
			if (!$stmt->execute()){
				return new sqlError("Failed to query database, error is: ".$stmt->errorInfo()[2]);
			}
			return $tagID;	
		}
	}
?>