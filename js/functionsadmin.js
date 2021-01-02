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
var editor;
var emoji;
var editfile="";
var viewfile="";

function drawWarning(editor) {
    var cm = editor.codemirror;
    var output = '';
    var selectedText = cm.getSelection();
    var text = selectedText || 'placeholder';
    output = '\n> :warning:' + text + ' !';
    cm.replaceSelection(output);
}

function drawDanger(editor) {
    var cm = editor.codemirror;
    var output = '';
    var selectedText = cm.getSelection();
    var text = selectedText || 'placeholder';
    output = '\n> :skull:' + text + ' !';
    cm.replaceSelection(output);
}

function drawTip(editor) {
    var cm = editor.codemirror;
    var output = '';
    var selectedText = cm.getSelection();
    var text = selectedText || 'placeholder';
    output = '\n> :bulb:' + text + ' .';
    cm.replaceSelection(output);
}

function drawNote(editor) {
    var cm = editor.codemirror;
    var output = '';
    var selectedText = cm.getSelection();
    var text = selectedText || 'placeholder';
    output = '\n> :memo:' + text + ' .';
    cm.replaceSelection(output);
}

$(function(){
	$("#files").jstree({
	    themes: { responsive: true },
	    plugins: ["contextmenu", "dnd" ],
	    core: 
	    {
	    	    strings:{ loading: "Chargement" },
		    check_callback: true,
		    data : {
      				type: "POST",
					dataType: "json",
                        	url : "/index.php",
                        	data : function (node) { 
                            		return { 'action' : 'allchildren' }; 
					}
                       } 
	    }
	});
      emoji = new EmojiConvertor();
      editor = new SimpleMDE({ 
		element: $("#editor>div")[0],
		spellChecker: false, 
            previewRender: function(plainText, preview) {
                setTimeout(function() {
                    preview.innerHTML = emoji.replace_colons(this.parent.markdown(plainText));
                    Prism.highlightAll();
                }.bind(this), 1)
                return "Chargement..."
            },
		toolbar: ["bold","italic","strikethrough","|","heading-1","heading-2","heading-3","|","quote","unordered-list","ordered-list","horizontal-rule","|",
		{
            name: "Warning",
            action: drawWarning,
            className: "fa fa-exclamation-triangle", // Look for a suitable icon
            title: "Warning field",
		},
		{
            name: "Tip",
            action: drawTip,
            className: "fa fa-lightbulb", // Look for a suitable icon
            title: "Tip field",
		},
		{
            name: "Danger",
            action: drawDanger,
            className: "fas fa-skull-crossbones", // Look for a suitable icon
            title: "Danger Field",
		},
		{
           name: "Notes",
            action: drawNote,
            className: "fas fa-sticky-note", // Look for a suitable icon
            title: "Note Field",
		},
		"|","code","link","image","table","|","preview","side-by-side","fullscreen","guide"]
	});
	$("#editor").hide();
	$("#image").hide();
	$("#save").hide();
	$("#content").html(emoji.replace_colons($("#content").html()));
	setTimeout(function(){ $("#forkongithub").fadeOut(1500); }, 5000);

	$("#files").on("ready.jstree", function (e) { 
		const urlParams = new URLSearchParams(window.location.search);
		const file = urlParams.get('doc');
		searchtree(file);
	});

    $("#voir").click(function(e){
      e.preventDefault();
	node=$("#files").jstree("get_selected");
   	file="/"+$("#files").jstree("get_path",node,"/").replace(/^.+?[/]/, '');
	 if ($("#files").jstree("is_leaf",node))
	  {
             action=$(this).val();
		 if (action=="Voir")
		{
			if (viewfile===file)
				viewmode();
			else
				openlink(file,false)
		}
		else
		{
			if (editfile===file)
				editmode();
			else
				editlink(file)
		}
	}
});



    $("#save").click(function(e){
		e.preventDefault();
		viewfile="";
	node=$("#files").jstree("get_selected");
   	  file="/"+$("#files").jstree("get_path",node,"/").replace(/^.+?[/]/, '');
	 if ($("#files").jstree("is_leaf",node))
	  {
	  data = editor.value();
	  $.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "save", file: encodeURIComponent(file), data:data },
	  success: function(data){
               data = data.split("|");
               alertBox(data[1], data[0]);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		alertBox('Erreur AJAX !','danger');
	  }
	  });
	}
	else
		alertBox('Erreur de sélection !', 'danger');
    });

	$("#files").on("select_node.jstree", function (e, nodes) { 
   		file="/"+$("#files").jstree("get_path",nodes.node,"/").replace(/^.+?[/]/, '');
		if ($("#files").jstree("is_leaf",nodes.node))
			editlink(file);
		else
			alertBox('Fonction non implémentée !','danger');
	});

	$("input[name=submit]").click(function(e) {
		e.preventDefault();
		search($("#search").val());
	});

	majlink('head');
});

function alertBox(message, className) {
    $(".alert").removeClass("alert-success alert-warning alert-danger");
    $(".alert").html(message).addClass("alert-" + className).fadeIn();
    setTimeout(function(){
        $(".alert").fadeOut();
    }, 5000);
}

function openlink(dest,majtree)
{
	$.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "open", file: encodeURIComponent(dest) },
	  success: function(data){
		viewmode(data);
		viewfile=dest;
		if (majtree) searchtree(dest);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md",true);
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
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
			if (text==item) 
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

function editmode(data)
{
	$(window).scrollTop(0);
	$("#editor").show();
	if (data !== undefined) editor.value(data);
	$("#content").hide();
	$("#save").show();
	$("#image").hide();
	$("#voir").show();
	$("#voir").val("Voir");
}

function imagemode(dest)
{
	$(window).scrollTop(0);
	$("#editor").hide();
	$("#content").hide();
	$("#save").hide();
	$("#image").show();
	$("#image>img").attr("src",dest);
	$("#voir").hide();
}

function viewmode(data)
{
      if (data !== undefined) {
		$("#content").html(emoji.replace_colons(data));
		Prism.highlightAll();
	}
	majlink('content');
	$(window).scrollTop(0);
	$("#editor").hide();
	$("#content").show();
	$("#save").hide();
	$("#image").hide();
	$("#voir").show();
	$("#voir").val("Editer");
}

function editlink(dest)
{
	if (dest.match(/.(jpg|jpeg|png|gif|webp|svg|ico)$/i))
		imagemode(dest);
	else
	{
		$.ajax({
		  type: "POST",
		  url: "/index.php",
		  data: { action: "realopen", file: encodeURIComponent(dest) },
		  success: function(data){
				editmode(data);
				editfile=dest;
		  },
		  error: function(XMLHttpRequest, textStatus, errorThrown) {
			alertBox('Erreur AJAX', 'danger');
		  }
		});
	}
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

function search(arg)
{
	$("#search").val(arg);
	$.ajax({
	  type: "POST",
	  url: "/index.php",
	  data: { action: "search", search: encodeURIComponent(arg), type: "js" },
	  success: function(data){
		viewmode(data);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		if (dest!="special/404.md")
	    		openlink("special/404.md",true);
		else
			$("#content").html("<b>Erreur 404 sur erreur 404: pas de /special/404.md !");
	  }
	});
}
