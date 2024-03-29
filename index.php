<?php
/*

 * MarkDoc
 * PHP MarkDown document manager
 * Hordé Nicolas
 * https://github.com/dahut87/MarkDoc
 * Release under GPLv3.0 license
 * Base on Pheditor "PHP file editor" By Hamid Samak Release under MIT license
 */

### Global defines
define('ROOT_DIR', realpath(dirname(__FILE__)) .'/');
define('LIB_DIR', ROOT_DIR.'lib/');
define('HOST_DIR',ROOT_DIR.'hosts/'.$_SERVER['SERVER_NAME'].'/');

### Hosts defines
include HOST_DIR."./config.php";
define('CONTENT_DIR', HOST_DIR.SUBCONTENT_DIR.'/');
define('LOG_DIR', HOST_DIR.LOG_FILE);
define('HISTORY_DIR', HOST_DIR.HISTORY_FILE);
include LIB_DIR."/Parsedown.php";
include LIB_DIR."/ParsedownExtra.php";
include LIB_DIR."/ParsedownExtraPlus.php";
include LIB_DIR."/functions.php";
$supported_image = array('gif','jpg','jpeg','png','webp','bmp','svg','ico');

### Translations
loadlang(LANGUAGE);
global $LANG,$JSLANG;

### Security
testip();
logprotect();

### Sessions
session_name('markdoc');
session_start();
//echo "<br><br>";
//var_dump($_POST);
//var_dump($_GET);

$file=explode('?', ($_GET['doc']=="")?$LANG['INDEXMD']:$_GET['doc'], 2)[0] ?? "";
$filedetail = pathinfo($file);
if (isset($_GET['logout'])) {
    unset($_SESSION['md_admin']);
    unset($_SESSION['md_user']);
    redirect();
}
else if (isset($_POST['action']))
{
	if ((isset($_SESSION['md_admin']) === false || $_SESSION['md_admin'] !== true) && (isset($_SESSION['md_user']) === false || $_SESSION['md_user'] !== true)) 
	{
		if ($_POST['action']=='ident')
		{
			if (isset($_POST['md_password']) && empty($_POST['md_password']) === false) 
			{
        			if (hash('sha512', $_POST['md_password']) === ADMIN_PASSWORD) 
				{
            			$_SESSION['md_admin'] = true;
            			redirect();
        			} 
        			else if (hash('sha512', $_POST['md_password']) === USER_PASSWORD) 
				{
            			$_SESSION['md_user'] = true;
            			redirect($file);
        			} 
				else 
	  			{
					$content = '<h1>'.$LANG['BADPASS'].'</h1>';
		     		$log = file_exists(LOG_DIR) ? unserialize(file_get_contents(LOG_DIR)) : array();
		      		if (isset($log[$_SERVER['REMOTE_ADDR']]) === false) 
					{
		         			 $log[$_SERVER['REMOTE_ADDR']] = array('num' => 0, 'time' => 0);
		      		}
		      		$log[$_SERVER['REMOTE_ADDR']]['num'] += 1;
		      		$log[$_SERVER['REMOTE_ADDR']]['time'] = time();
		      		file_put_contents(LOG_DIR, serialize($log));
				}
			}
			else
				$content='<h1>'.$LANG['NOPASS'].'</h1>';

		}
		else if (ACCESS_PRIVATE)
			switch ($_POST['action']) 
			{
				case 'allchildren':
				case 'children':
					print('{ "id" : "id1", "icon" : "fas fa-atlas", "parent" : "#", "text" : "'.$_SERVER["SERVER_NAME"].'" }');
					exit;
				default:
					$content=specialurl("/:ADMIN",true);
			}
	}
	switch ($_POST['action']) 
	{
	  	case 'children':
			print(json_encode(filesJSON(CONTENT_DIR,false)));
			exit;
		case 'new':
			$file=urldecode($_POST['file']);
			$filedetail = pathinfo($file);
			if (!isset($_SESSION['md_admin']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
			{
				setcontent($file,"## Titre");
				print(getcontent($file,$md=$filedetail['extension']=='md',true));
			}
			exit;
		case 'rename':
			$file=urldecode($_POST['file']);
			$file2=urldecode($_POST['file2']);
			if (!isset($_SESSION['md_admin']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
			{
				print(rencontent($file,$file2));
			}
			exit;
		case 'delete':
			$file=urldecode($_POST['file']);
			if (!isset($_SESSION['md_admin']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
			{
				print(delcontent($file));
			}
			exit;
		case 'sendfile':
			$file=urldecode($_POST['name']);
			$data=file_get_contents($_FILES["file"]["tmp_name"]);
			unlink($_FILES["file"]["tmp_name"]);
			if (!isset($_SESSION['md_admin']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
			{
				print(setcontent($file,$data));
			}
			exit;
	  	case 'allchildren':
			print(json_encode(filesJSON(CONTENT_DIR,true)));
			exit;
		case 'open':
			$file=urldecode($_POST['file']);
			$filedetail = pathinfo($file);
			if (substr($file,0,2)=="/:") 
				specialurl($file,true);
			else
			{
				if (ACCESS_LIMITED!="" && strpos($filedetail['dirname'],ACCESS_LIMITED)!==false && !isset($_SESSION['md_user']))
				{
					$content=specialurl("/:ADMIN",true);
				}
				print(getcontent($file,$md=$filedetail['extension']=='md',true));
				exit;
			}
        case 'realopen':
			$file=urldecode($_POST['file']);
			$filedetail = pathinfo($file);
			if (ACCESS_LIMITED!="" && strpos($filedetail['dirname'],ACCESS_LIMITED)!==false && !isset($_SESSION['md_user']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
				print(getcontent($file,false,true));
            		exit;
        case 'save':
			$file=urldecode($_POST['file']);
			$filedetail = pathinfo($file);
			if (!isset($_SESSION['md_admin']))
			{
				$content=specialurl("/:ADMIN",true);
			}
			else
				print(setcontent($file,$_POST['data']));
            exit;
        case 'search':
			$results=searchstr(CONTENT_DIR,$_POST['search']);
			$content=sprintf($LANG['FOUND'],$results['totalFiles']);
			foreach($results['files'] as $key => $value)
			{
				$filedetail = pathinfo($key);
				if (ACCESS_LIMITED=="" || strpos($filedetail['dirname'],ACCESS_LIMITED)===false || isset($_SESSION['md_user']))
					$content.='<p class="filefound"><a href="'.$key.'">'.$key.'</a></p><p class="textfound">'.$value.'</p>';
			}
			if ($_POST['type']=="js") 
			{
				print($content);
				exit;
			}
	}
}
else if (ACCESS_PRIVATE && !isset($_SESSION['md_admin']))
{
	$content=specialurl("/:ADMIN",false);
} 
else if (substr($file,0,1)==":")
{
    $content=specialurl("/".$file,false);
}
else if (ACCESS_LIMITED!="" && strpos($filedetail['dirname'],ACCESS_LIMITED)!==false && !isset($_SESSION['md_user']))
{
	$content=specialurl("/:ADMIN",false);
} 
else if ($filedetail['extension']=="md")
{
   $content=getcontent($file);
}
else if ($filedetail['extension']!="" && strpos(ALLOWED_EXT, $filedetail['extension']) !== false)
{
   if (file_exists(CONTENT_DIR . $file))
   {
   	header('Content-type: '.mime_content_type(CONTENT_DIR . $file),true);
    	print file_get_contents(CONTENT_DIR . $file, false);
	exit;
   }
   else
   {
      http_response_code(404);
      $content=getcontent("/special/404.md");
   }
   exit;
}
else
{
   $content=getcontent("/special/404.md");
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo TITLE." - ".$file; ?>
</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" type="text/css" href="/css/prism.css" />
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/css/jstree.min.css" />
<link rel="stylesheet" type="text/css" href="/css/emoji.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/vlitejs@4.0.5/dist/plugins/subtitle.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/vlitejs@4.0.5/dist/vlite.min.css" />
<?php print(($_SESSION['md_admin'] == true)?'<link rel="stylesheet" href="/css/codemirror.min.css" />':''); ?>
</head>
<body>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vlitejs@4.0.5/dist/plugins/subtitle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vlitejs@4.0.5/dist/plugins/pip.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vlitejs@4.0.5/dist/vlite.min.js"></script>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/popper.min.js"></script>
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jstree.min.js"></script>
<script type="text/javascript" src="/js/prism.js"></script>
<script type="text/javascript" src="/js/emoji.min.js"></script>
<script type="text/javascript" src="/js/toc.bundle.js"></script>
<script type="text/javascript" src="/js/magnifik.js"></script>
<script type="text/javascript">
var LANG = <?php echo $JSLANG; ?>
</script>
   <div id="title" style="display: none;"><?php echo TITLE; ?></div>
   <div id="head" class="">
   <span id="forkongithub"><a href="https://github.com/dahut87/MarkDoc"><?php print($LANG['FORK']); ?></a></span>
      <nav class="navbar fixed-top navbar-expand-md <?php print(($_SESSION['md_admin'] == true)?"navbar-custom":"bg-dark navbar-dark"); ?>">
        <a class="navbar-brand" href="/<?php echo $LANG['ABOUTMD']; ?>"><i class="fas <?php echo ICON; ?>"></i>&nbsp;<?php echo TITLE; ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" role="button"><?php print($LANG['MENU']); ?></a>
              <div class="dropdown-menu">
<?php
	print(getnav());
?>
              </div>
            </li>
<?php
print(($_SESSION['md_admin'] == true)?'<li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" role="button">'.$LANG['ADMIN'].'</a>
              <div class="dropdown-menu">
		<a class="dropdown-item" href="/:CONFIG">'.$LANG['CONFIG'].'</a>
		<a class="dropdown-item" href="/:PASSWORD">'.$LANG['PASSWORD'].'</a>
		<hr>
		<a class="dropdown-item" href="https://'.$_SERVER['SERVER_NAME'].'?logout">'.$LANG['LOGOUT'].'</a>
      </div></li>':'<li class="nav-item"><a class="nav-link " href="/:ADMIN">'.$LANG['ADMIN'].'</a></li>');
?>
            <li class="nav-item"><a class="nav-link " href="<?php print($LANG['ABOUTMD'].'">'.$LANG['ABOUT']); ?></a></li>
          </ul>
          <form class="form-inline" id="form" name="form" action="/index.php" method="POST">
            <input type="hidden" id="action" name="action" value="search"/>
		<?php print(($_SESSION['md_admin'] == true)?'<input class="btn btn-outline-light" my-2 my-sm-0" value="'.$LANG['DELETE'].'" name="del" id="del" type="submit"/>&nbsp;&nbsp;<input class="btn btn-outline-light" my-2 my-sm-0" value="'.$LANG['RENAME'].'" name="ren" id="ren" type="submit"/>&nbsp;&nbsp;<input class="btn btn-outline-light" my-2 my-sm-0" value="'.$LANG['NEW'].'" name="nouveau" id="nouveau" type="submit"/>&nbsp;&nbsp;<input class="btn btn-outline-light" my-2 my-sm-0" value="'.$LANG['VIEW'].'" name="voir" id="voir" type="submit"/>&nbsp;&nbsp;<input class="btn btn-outline-light" my-2 my-sm-0" value="'.$LANG['SAVE'].'" name="save" id="save" type="button"/>&nbsp;&nbsp;':''); ?>
		    <input class="btn <?php print(($_SESSION['md_admin'] == true)?"btn-outline-light":"btn-outline-info"); ?> my-2 my-sm-0" value="<?php print($LANG['TOC']); ?>" name="toc" id="toc" type="button" style="display: none;"/>&nbsp;&nbsp;
            <input class="form-control mr-sm-2" type="text" id="search" name="search"/>
            <input class="btn <?php print(($_SESSION['md_admin'] == true)?"btn-outline-light":"btn-outline-info"); ?> my-2 my-sm-0" value="<?php print($LANG['SEARCH']); ?>" name="submit" id="submit" type="submit"/>
              </form>
              </div>
            </nav>
        </div>
<div class="container-fluid">
<div class="float-right d-none d-md-block d-lg-block"><div class="toc fixed-top"></div></div>
<div class="row">
<div id="files" class="col-xs-12 order-last order-sm-last order-sm-last order-md-first order-lg-first col-md-4 col-lg-2"></div>
<div id="separate" class="bg-dark text-white col-xs-12 col-sm-12 order-3 d-md-none d-lg-none container">Documentations</div>
<div class="col-12 col-md-8 col-lg-10 order-2">
<div id="content" class="col-12 col-md-8 col-lg-10 order-2">
<?php
	print($content);
?><br><br>
</div>
<div id="image" class="imagepreview" style="display: none;"><img></div><div id="editor" style="display: none;"><textarea data-file="" class="form-control"></textarea></div>
</div>
</div>
</div>

<div id="footer" class="footer <?php print(($_SESSION['md_admin'] == true)?"navbar-custom":"bg-dark"); ?> text-white">
<?php
	print(getcontent("special/footer.md"));
?>
</div>
<div class="alert"></div>
<?php print(($_SESSION['md_admin'] == true)?'<link rel="stylesheet" href="/css/simplemde.min.css">
<script src="/js/simplemde.min.js"></script>
<script type="text/javascript" src="/js/functionsadmin.js"></script>':'<script type="text/javascript" src="/js/functions.js"></script>'); ?>
</body>
</html>
