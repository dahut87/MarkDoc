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
	  $("#content").html(emoji.replace_colons($("#content").html()));
	$("#editor").hide();
	$("#image").hide();
	$("#save").hide();
	setTimeout(function(){ $("#forkongithub").fadeOut(1500); }, 5000);
	$('#content > p>  img').magnifik({ratio:1.0});
	tocgenerate();

	$("#files").on("select_node.jstree", function (e, nodes) { 
   		file="/"+$("#files").jstree("get_path",nodes.node,"/").replace(/^.+?[/]/, '');
		if ($("#files").jstree("is_leaf",nodes.node))
			openlink(file+".md",false);
	});

	$("input[name=submit]").click(function(e) {
		e.preventDefault();
		search($("#search").val());
	});

	$("input[name=toc]").hover(function(e) {
		e.preventDefault();
		tocshow();
	});

	majlink('head');
});

function openlink(dest,majtree)
{
	$.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "open", file: encodeURIComponent(dest) },
	  success: function(data){
	 	$("#content").html(emoji.replace_colons(data));
		Prism.highlightAll();
		majlink('content');
		$(window).scrollTop(0);
		if (majtree) searchtree(dest);
		$('#content > p>  img').magnifik({ratio:1.00});
		tocgenerate();
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md",false);
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
	  }
	});
}

function tocgenerate()
{
	tocshow();
	$('.toc').toc({
		'selectors': 'h2,h3,h4',
		'container': '#content'
	})
	$('.toc').append('<i class="fa fa-2x fa-caret-up" aria-hidden="true"></i>');
	$('.toc > i').off().hover(function(e) {
		e.preventDefault();
		tochide();
	});
}

function tochide()
{
	$('.toc').slideUp()
	$('#toc').show();
}

function tocshow()
{
	$('.toc').slideDown()
	$('#toc').hide();
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
			openlink(dest,true);
		}
	});
}

function searchtree(file)
{
	var flag;
	$("#files").jstree("deselect_all");
	node=$("#files").jstree("get_node", "ul > li:first");
	file.split('/').forEach(function (item) {
		flag=false;
		subnodes=$("#files").jstree("get_children_dom",node);
		subnodes.each(function (i,subnode)
		{
			text=$("#files").jstree("get_text",subnode);
			if ((text==item) || (text+".md"==item))
			{
				$("#files").jstree("open_node",subnode);
				if ($("#files").jstree("is_leaf",subnode))
				{
					$("#files").jstree("select_node",subnode,true);
					flag=false;
					return false;
				}
				node=subnode;
				flag=true;
				return false;
			}
		});
		if (!flag) return false;
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
      	$("#content").html(data);
		Prism.highlightAll();
		majlink('content');
		$(window).scrollTop(0);
		tocgenerate();
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md",false);
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
	  }
	});
}
