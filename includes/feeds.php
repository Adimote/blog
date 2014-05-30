<?php
include_once '../../includes/settings.php';
include_once '../../includes/sql-manager.php';

Class feed {
	
	private function getEntries($posts){
		$result = "";
		foreach ($posts as $post) {
			$id = $post['id'];
			$url = Conf::URL."/".$post['urlname']."/";
			$title = $post['title'];
			$date = $post['date'];
			$author = $post['who'];
			$flavour = htmlspecialchars($post['flavour']);
			$content = htmlspecialchars($post['content']);

			$result.= <<<HTML
	<entry>
		<id>{$id}</id>
		<link rel="alternate" type="text/html" href="{$url}" />
		<title>{$title}</title>
		<updated>{$date}</updated>
		<author><name>{$author}</name></author>
		<summary type="text" xml:lang="en">{$flavour}</summary>
		<content type="html" xml:lang="en">{$content}</content>
	</entry>
HTML;
		}
		return $result;
	}

	public function generate() {
		$sqlget = new sqlGetter();
		//Get the posts
		$posts = $sqlget->getPostsDateOrder(0,Conf::Feed_Backlog);

		$entries = self::getEntries($posts);

		//Article metadata
		$title = htmlspecialchars(Conf::Title);
		$subtitle = htmlspecialchars(Conf::SubTitle);

		$homeUrl = Conf::URL."/";
		$atomUrl = Conf::URL."/".Conf::AtomLocation;
		$curDate = date('c');

		$string = <<<HTML
<?xml version="1.0" encoding"utf-8"?>
<feed xlns="http://wwww.w3.org/2005/Atom">
	<title>{$title}</title>
	<subtitle>{$subtitle}</subtitle>
	<link rel="alternate" type="text/html" href="{$homeUrl}"/>
	<link rel="self" type="application/atom+xml" href="{$atomUrl}"/>
	<updated>{$curDate}</updated>
	{$entries}
</feed>
HTML;
		return $string;
	}

	//Update the atom feed
	public function updateFeeds() {
		$file = Conf::RootDir."blog/".Conf::AtomLocation;
		file_put_contents($file, self::generate());
	}
}
?>