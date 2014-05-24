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
		const Home_PerPage = 3;
		//URL of website, used for canonical urls
		const URL = "http://localhost";

	}
?>