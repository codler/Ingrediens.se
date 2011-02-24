<!DOCTYPE html>
<!--[if lt IE 8 ]> <html lang="sv" class="ie7"> <![endif]-->
<!--[if IE 8 ]> <html lang="sv" class="ie8"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html lang="sv"> <!--<![endif]-->
<head>
<!-- meta -->
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Ingredients.se<?php if (isset($_GET['search'])) : ?> - <?php echo htmlspecialchars($_GET['search']); ?><?php endif; ?></title>

<!-- css -->
<link rel="stylesheet" type="text/css" href="/yap-goodies/css/global2.css" />
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css" />

<!-- javascript -->
<script src="http://static.zencodez.net/js.php?f=jquery-1.5,jquery-ui-1.8,css3finalize-latest"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>

<style>
a {
	color: #333;
	text-decoration: none;
}

h1 {
	font-size: 60px;
	margin-bottom: 10px;
}

li {
	list-style: none;
}

input {
	font-size: 26px;
	line-height: 30px;
	padding: 2px 5px;
	width: 250px;
}

#wrapper-inner {
	background-color: rgba(255, 255, 255, 0.8);  
	text-align: center;
}

#link, #link a {
	font-size: 10px;
}

#link a {
	color: blue;
}

#link a:hover {
	text-decoration: underline;
}

#recipes > div {
	display: inline-block;
	margin: 20px 14px;
	width: 250px;
}
.ie7 #recipes > div {
	display: inline;
}

#recipes > div > a {
	background-color: #eee;
	border: 2px solid #ccc;
	display: block;
	font-weight: bold;
	padding: 7px 7px 3px;
	text-align: left;
	width: 240px;
}
#recipes > div > a:hover {
	background-color: #ccc;
	border: 2px solid #aaa;
	color: #000;
	text-shadow: 3px 3px 3px #fff;	
}

#recipes > div > a > span.image {
	display: inline-block;
	overflow: hidden;
	position: relative;
	height: 80px;
	width: 80px;
	
	border: 1px solid #fff;
	box-shadow: 1px 1px 5px #000;
	margin-right: 5px;
	transition: all 1s ease-in-out; 
}

#recipes > div > a > span.image > img {
	position:absolute;
	left: 0;
	top: 0;
	
	max-height: 80px;
}
#recipes > div > a:hover > span.image {
	box-shadow: 3px 3px 10px #000;
	transform: translateX(70px) scale(2,2);
	transition: all 1s ease-in-out; 
}

#recipes > div > a > span.text {
	display: inline-block;
	width: 149px;
	vertical-align: top;
}

.ie7 #recipes > div > a > span.text {
	display: inline;
}
</style>
<script>
// http://stackoverflow.com/questions/901115/get-querystring-values-with-jquery/901144#901144
function getParameterByName( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

/**
 * @author Han Lin Yap < http://zencodez.net/ >
 * @copyright 2011 zencodez.net
 * @license http://creativecommons.org/licenses/by-sa/3.0/
 * @package ingrediens.se
 * @version 1.1 - 2011-02-21
 */
 
 /* Core API start */
(function ($) {
	$.restApi = {};
	$.restApi.request = function (url, method, params, callback) {		
		$.ajax({
			url: url + method,
			dataType: "jsonp",
			data: params,
			success: function( data, status, xhr ) {
				callback(data);
			}
		});
	};

})(jQuery);
/* Core API end */

/* Studentkak API start 
	Require: coreApi
*/
(function ($) {
	var studentkak = {};
	studentkak.request = function (method, params, callback) {
		//var url = 'http://api.zencodez.net/data.php';
		var url = 'http://studentkak.se/data.php';
		
		params.from = method;
		
		if (this.appId)
			params.appId = this.appId;
		
		$.restApi.request(url, '', params, callback);
	};
	studentkak.setAppId = function(id) {
		this.appId = id;
	}
	
	// register studentkak API
	$.restApi.studentkak = studentkak;
})(jQuery);
/* Studentkak API end */


jQuery(function ($) {
	$.restApi.studentkak.setAppId('ingrediens.se');

	$( "#search" ).autocomplete({
		source: function( request, response ) {
			var params = {
				limit: 1000,
				search: request.term
			}
			
			$.restApi.studentkak.request('ingredients', params, function(data) {
				response( $.map( data, function( item ) {
					return {
						label: item.Ingredient,
						value: item
					}
				}));
			});
		},
		focus: function( event, ui ) {
			this.value = ui.item.value.Ingredient;
			return false;
		},
		select: function( event, ui ) {
			this.value = ui.item.value.Ingredient;
			setRecipes(ui.item.value.Recipes);
			return false;
		}

	});
	
	// Init
	var search;
	if ((search = getParameterByName('search')) != '') {
		var params = {
			limit: 1000,
			search: search
		}
		
		$.restApi.studentkak.request('ingredients', params, function(data) {
			if (data.length==0) return false;
			var mostRelevant = 0;
			$.each(data, function(i, v) {
				if (v.Ingredient == search) {
					mostRelevant = i;
					return false;
				}
			});
			setRecipes(data[mostRelevant].Recipes);
		});
	}
	
	function setRecipes(data) {
		$('#recipes').empty();
		$('#recipesTemplate').tmpl(data).appendTo('#recipes');
		
		$('body').css({
			background : "url('" + data[0].OriginalImage + "')"
		});
		$('#link a')
			.attr('href', 'http://ingrediens.se/?search=' + $('#search').val())
			.text('http://ingrediens.se/?search=' + $('#search').val());
			
		$('.image img').load(function () {
			$(this).css({left:-($(this).width() - 80) / 2});
		}).each(function() {
			if ( $(this).get(0).complete && $(this).get(0).naturalWidth !== 0 ) {
				$( this ).trigger('load');
			}
		});
	}
	
	/* $('.image img').live('mouseover mouseout', function(event) {
		if (event.type == 'mouseover') {
			$(this).attr('src', $(this).data('original-image'));
			$(this).css({left:-($(this).width() - 80) / 2});
		} else {
			$(this).attr('src', $(this).data('thumbnail'));
			$(this).css({left:-($(this).width() - 80) / 2});
		}
	}); */
});
</script>
</head>
<body>
<div id="wrapper">
	<div id="wrapper-inner">
<form>
	<h1><label for="search">Sök efter ingrediens</label></h1><input type="search" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>" />
	<div id="link">Länk: <a href="http://ingrediens.se/">http://ingrediens.se/</a></div>
</form>
<script id="recipesTemplate" type="text/x-jquery-tmpl"> 
    <div>
		<a href="${Url}">
			<span class="image"><img src="${OriginalImage}" data-thumbnail="${Image}" data-original-image="${OriginalImage}" /></span>
			<span class="text">${Title}</span>
		</a>
	</div>
</script>
<div id="recipes"></div>
	</div>
</div>
</body>
</html>