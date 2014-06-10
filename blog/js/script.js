window.onload = function(){

	function $i(id) {return document.getElementById(id);}	

	//Helper functions -------------------------------------------------------
	function getTopOffset(){ return (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0)}  

	function getWindowHeight() {
		return "innerHeight" in window 
               ? window.innerHeight
               : document.documentElement.offsetHeight;
	}

	function getDocHeight() {
	    var D = document;
	    return Math.max(
	        D.body.scrollHeight, doc.scrollHeight,
	        D.body.offsetHeight, doc.offsetHeight,
	        D.body.clientHeight, doc.clientHeight
	    );
	}

	//Global Variables
	var doc = document.documentElement;
	var httpReq = null;
	var anim = [];
	var timers = [];
	var infiniteEnabled = false;
	var infiniteAllLoaded = false;
	var infinitePage = 0;
	var infiniteMode = "";
	var requesting = false;

	var head = $i("pagehead");
	var toggleInfinite = $i("toggleInfinite");
	var unanimatedArticles = getUnanimatedArticles();

	//End Helper functions ---------------------------------------------------

	//Infinite Scroll functions ----------------------------------------------

	//toggle the infinite scroll function
	function toggleInfiniteScroll() {
		var nav = $i("pagenav");
		infiniteEnabled = !infiniteEnabled;
		if (infiniteEnabled) {
			infinitePage = PAGENUM;
			infiniteMode = MODE;
			nav.className = "bottom-nav hidden-nav";
			toggleInfinite.style.display = "none";
		} else {
			nav.className = "bottom-nav";
			toggleInfinite.style.display = "";
		}
		//call the scroll function, to trigger any needed infinite scrolls
		scroll();
	}

	if (toggleInfinite) {
		toggleInfinite.onclick = toggleInfiniteScroll;
	}

	//On posts http ready
	function onPostsHttpReady() {
		if (httpReq.readyState == 4) {
			if ( httpReq.status == 200 ) {
				addPage(httpReq.responseText);
				requesting = false;
			}
			//if it returns a 204, stop because we've loaded everything
			else if (httpReq.status == 204 ) {
				addPage("<p class=\"text-center\"><b>No More Posts</b></p>");
				requesting = false;
				infiniteAllLoaded = true;
			}
		}
	}

	//get the posts for a page
	function getPagePosts(page,tag){
		//tag default null
   		tag = typeof tag !== 'undefined' ? tag : null;

		requesting = true;
		var Url = "";
		if (tag != null) {
			Url = "/infinite/tag/" + tag + "/" + page + "/" + window.location.search;
		} else {
	    	Url = "/infinite/" + page + "/" + window.location.search;
		}
		
	    httpReq = new XMLHttpRequest();
	    httpReq.onreadystatechange = onPostsHttpReady;
	    httpReq.open( "GET", Url, true );
	    httpReq.send( null );
	    setLoading();
	}

	//Add another page of results
	function addPage(pageText) {
		clearLoading();
		var main = $i("pagecontainer");
		var newPage = document.createElement("div");
		newPage.innerHTML = pageText;
		main.appendChild(newPage);
		//Update the articles so they're all clickable
		makeClickable();

		//update the unanimated articles
		unanimatedArticles = getUnanimatedArticles();

		//Cause a scroll event
		scroll();
	}

	//Set the loading spinner
	function setLoading() {
		var loading = $i("infiniLoading");
		loading.className = "text-center";
	}

	//Clear the loading spinner
	function clearLoading() {
		var loading = $i("infiniLoading");
		loading.className = "text-center hidden";
	}

	//End Infinite Scroll functions ----------------------------------------------

	function makeClickable() {
		// Make articles with the 'href' attribute clickable  
		var articles = document.getElementsByTagName("article");
		for (var i = 0; i < articles.length; i++) {
			var article = articles[i];
			if (article.getAttribute("href") != null) {
				article.onclick = function() {
					window.location.href = this.getAttribute("href");
				}
			}
		}
	}
	makeClickable();

	// Fade the title on scroll
  	function fadeOnScroll(top) {
  		var text = [];
		text.push(
			"top:",
			Math.max((-top / 5),-100),
			"px;",
			"opacity:",
			Math.max(0,(1-(top / 100)))
		);
		head.style.cssText = text.join("");
  	}

	//Get all not-animated articles
	function getUnanimatedArticles(){
		//Make articles animate in one at a time
		var articles = document.getElementsByTagName("article");
		//Convert the articles into an array object
		var anim = [];
		for (var i = 0; i < articles.length; i++) {
			var article = articles[i];
			var attr = article.getAttribute("anim");
			if (attr == "1") {
				anim.push(article);
			}
		}
		return anim;
	}

	//Handles any functions for infinite scrolling
	function doInfinite(top,height,docHeight){
		//Load more when scrolling to the bottom of the page
		if (top + height > docHeight - 100) {
			if (!requesting) {
				infinitePage++;
				if (infiniteMode == "posts") {
					getPagePosts(infinitePage);
				} else {
					getPagePosts(infinitePage,TAG);
				}
			}
		}
	}

	//Handles flip-ins
	function doFlipins(top,height){
		var anim = 0;
		for (var i = 0; i < unanimatedArticles.length; i++) {
			var article = unanimatedArticles[i];
			var pos = article.getBoundingClientRect().top;
			//if the item is visible
			if (height > pos) {
				anim++;
			} else {
				//assuming the list is in order of height,
				//we can stop on the first fail
				break;
			}
		}
		animate(anim);
	}

	//Animate 'anim' number of articles
	function animate(anim){
		if (anim > 0 && unanimatedArticles.length > 0){
			article = unanimatedArticles[0];
			unanimatedArticles.shift();
			article.setAttribute("anim","0");
			article.style.opacity = "1";
			article.className += " animate";
			anim --;
			clearTimeout(timers);
			timers = [];
			timers.push(setTimeout(function(){
				animate(anim);
			},70));
		}
	}

	//On scroll event
	function scroll(){
		var top = getTopOffset();
		var height = getWindowHeight();
		var docHeight = getDocHeight();
		//fade the header
		fadeOnScroll(top);

		if (infiniteEnabled && !infiniteAllLoaded) {
			doInfinite(top,height,docHeight);
		}

		doFlipins(top,height);
  	}
	window.onscroll = scroll;

	//Executes every second
	function tick(){
		//call the scroll event incase window.onscroll failed (ie in chrome you can middle click scroll)
		scroll();

		setTimeout(function(){
			tick();
		},1000);
	}
	tick();
}