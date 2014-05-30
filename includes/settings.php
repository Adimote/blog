<?php 

	//Set the default timezone to UTC
	date_default_timezone_set('UTC');

	class Conf {
		//Homepage settings

		//Title of blog
		const Title = "Test Blog";
		//Subtitle of blog
		const SubTitle = "a blog";

		//Items per page on the homepage
		const Home_PerPage = 5;
		//URL of website, used for canonical urls
		const URL = "http://localhost";

		//Feed settings

		//The number of posts to give in a feed update
		const Feed_Backlog = 5;

		const AtomLocation = "feed.atom";

		const RootDir = "/var/www/";

	}
?>