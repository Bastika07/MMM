<link rel="stylesheet" type="text/css" href="css/jquery.bxslider.css" /> <!-- bxSlider CSS file -->
<script type="text/javascript" src="js/jquery.bxslider.min.js"></script> <!-- bxSlider Javascript file -->
<script language="javascript">
	$(document).ready(function(){
	  slider_foto = $('.bxslider_fotoslider').bxSlider({
			auto:true,
			autoHover:true
		});
	});
	$(document).ready(function(){
	  var slider = $('.bxslider_news').bxSlider({
			adaptiveHeight: true,
			pause: 20000,
			auto:true,
			speed: 2000,
			autoHover:true,
			onSlideAfter: function() {
				slider.stopAuto();
				slider.startAuto();
				}
		});
	});
</script>