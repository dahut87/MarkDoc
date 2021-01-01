/*
 * MarkDoc
 * PHP MarkDown document manager
 * Hordé Nicolas
 * https://github.com/dahut87/MarkDoc
 * Release under GPLv3.0 license
 * Base on Pheditor "PHP file editor" By Hamid Samak Release under MIT license
 */

var external = RegExp('^((f|ht)tps?:)?//');
var javascript = RegExp('^javascript:');
var emoji;
$(function(){
	$("#files").jstree({
	    themes: { responsive: true },
	    plugins: [ "types" ],
	    core: 
	    {
	    	    strings:{ loading: "Chargement" },
		    check_callback: true,
		    data : {
      				type: "POST",
					dataType: "json",
                        	url : "/index.php",
                        	data : function (node) { 
                            		return { 'action' : 'children' }; 
					}
                       } 
	    }
	});
      emoji = new EmojiConvertor();
	$("#editor").hide();
	$("#image").hide();
	$("#save").hide();

	$("#files").on("select_node.jstree", function (e, nodes) { 
   		file="/"+$("#files").jstree("get_path",nodes.node,"/").replace(/^.+?[/]/, '');
		if ($("#files").jstree("is_leaf",nodes.node))
			openlink(file+".md");
	});

	$("input[name=submit]").click(function(e) {
		e.preventDefault();
		search($("#search").val());
	});

	majlink('head');
});

function openlink(dest)
{
	$.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "open", file: encodeURIComponent(dest) },
	  success: function(data){
	 	$("#content").html(emoji.replace_colons(data)+'<br><br>');
		Prism.highlightAll();
		majlink('content');
		$(window).scrollTop(0);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md");
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
	  }
	});
}

function alertBox(message, className) {
    $(".alert").removeClass("alert-success alert-warning alert-danger");
    $(".alert").html(message).addClass("alert-" + className).fadeIn();
    setTimeout(function(){
        $(".alert").fadeOut();
    }, 5000);
}

function majlink(context)
{
	$('#'+context+' a').click(function(e) {
		dest=$(this).attr('href');
		if (!external.test(dest) && !javascript.test(dest)) 
		{
			e.preventDefault();
			openlink(dest);
		}
	});
}

function search(arg)
{
	$("#search").val(arg);
	$.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "search", search: encodeURIComponent(arg), type: "js" },
	  success: function(data){
      	$("#content").html(data+'<br><br>');
		Prism.highlightAll();
		majlink('content');
		$(window).scrollTop(0);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md");
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
	  }
	});
}
