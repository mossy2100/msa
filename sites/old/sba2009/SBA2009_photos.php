<?php include("http://www.marssociety.org.au/sba2009/header.php"); ?>

<?php include("http://www.marssociety.org.au/sba2009/left_bar.php"); ?>



<!-- Main Content -->
<div id="centercontent">  


<center> <img style="border:8px solid white;" src="/sba2009/SBA2009_header.jpg" alt="Spaceward Bound Australia 2009"> </center>


<h2 align="center" style="color: rgb(255, 0, 0);"><i>Photo Galleries</i></h2>

<hr width="100%" size="2">


<p>

Thursday 9th July<br>
Friday 10th July<br>
Saturday 11th July<br>
Sunday 12th July<br>
Monday 13th July<br>
Tuesday 14th July<br>
Wednesday 15th July<br>
Thursdays 16th July<br>
Friday 17th July<br>

<br><br>


 




<script type="text/javascript">
	window.addEvent('domready', function(){
		
		// Finds the 30 most recent public photos with a specific tag
		new MooPix().callFlickrUrl({ method: 'flickr.photos.search', tags: 'logo', user_id: '3671621258@N01', tag_mode: 'all', per_page: '100' });
		
		// Handle the default callback method from Flickr
		jsonFlickrApi = function(rsp){
			if (rsp.stat == 'ok' ){
				if (rsp.photos){				
					// New ShowPix slideshow instance if photos are found
					Slideshow = new ShowPix(rsp);
			    } else if (rsp.sizes){
					// If size data returned from Flickr, resize 
					Slideshow.endResize(rsp);
				}
			}
		}
	});
</script>





			<div id="PhotoBox">				
				<div id="Container">
					<img id="Photo" src="/sba2009/c.gif" alt="Photo" />
					<div id="Controls">
						<a id="PrevLink" href="javascript://" title="Previous Photo">Previous</a>

						<a id="PlayToggle" href="javascript://" title="Play/Pause Slideshow" class="play">Play</a>
						<a id="NextLink" href="javascript://" title="Next Photo">Next</a>
					</div>
				    <div id="Loading"><img src="/sba2009/loading.gif" width="16" height="16" alt="Loading..." /> Loading...</div>
				</div>
				<div id="CaptionContainer">
					<a id="ViewThumbs" href="javascript://">Thumbnails</a>

					<p><span id="Counter"></span> <span id="Caption"></span></p>
				</div>
				<div id="ThumbContainer"></div>
			</div>






</p>
<hr style="width: 100%; height: 2px;">




<!--[END]-->

<p class="nextlink"><A href="/sba2009/./../">Up</A></p>
<hr>

</div>
<!-- /Main Content -->



<?php include("http://www.marssociety.org.au/sba2009/right_bar.php"); ?>




