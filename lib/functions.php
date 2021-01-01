<?php
/*

 * MarkDoc
 * PHP MarkDown document manager
 * Hordé Nicolas
 * https://github.com/dahut87/MarkDoc
 * Release under GPLv3.0 license
 * Base on Pheditor "PHP file editor" By Hamid Samak Release under MIT license
 */

function testip()
{
	if (empty(ACCESS_IP) === false && ACCESS_IP != $_SERVER['REMOTE_ADDR'])
	    die('Your IP address is not allowed to access this page.');
}

function logprotect()
{
	if (file_exists(LOG_DIR)) {
	    $log = unserialize(file_get_contents(LOG_DIR));
	    if (empty($log)) {
		  $log = [];
	    }
	    if (isset($log[$_SERVER['REMOTE_ADDR']]) && $log[$_SERVER['REMOTE_ADDR']]['num'] > 3 && time() - $log[$_SERVER['REMOTE_ADDR']]['time'] < 86400) {
		  die('This IP address is blocked due to unsuccessful login attempts.');
	    }
	    foreach ($log as $key => $value) {
		  if (time() - $value['time'] > 86400) {
		      unset($log[$key]);

		      $log_updated = true;
		  }
	    }
	    if (isset($log_updated)) {
		  file_put_contents(LOG_DIR, serialize($log));
	    }

	}
}

function redirect($address = null)
{
    if (empty($address)) {
        $address = $_SERVER['PHP_SELF'];
    }
    header('Location: ' . $address);
    exit;
}

function specialurl($url)
{
	switch ($url) {
        case ':APROPOS':
            $content = '# MarkDoc

**PHP MarkDown document manager, Free &amp; OpenSource :heart_eyes: for easily create your documentation website**
```
  __  __            _    _____             
 |  \/  |          | |  |  __ \            
 | \  / | __ _ _ __| | _| |  | | ___   ___ 
 | |\/| |/ _` | \'__| |/ / |  | |/ _ \ / __|
 | |  | | (_| | |  |   <| |__| | (_) | (__ 
 |_|  |_|\__,_|_|  |_|\_\_____/ \___/ \___|
```                                      

![gplV3](https://www.gnu.org/graphics/gplv3-127x51.png) Sous licence GPLv3 [Licence](/special/gpl-3.0.md) - *Sources téléchargéables sur [GitHub](https://github.com/dahut87/MarkDoc)*

Based on Pheditor "PHP file editor" By Hamid Samak Release under MIT license

2020 par Nicolas H.';
 	 	$extra = new ParsedownExtraplus();
		print($extra->text($content));
		exit;
        case ':ADMIN':
		if (isset($_SESSION['md_admin']) === false || $_SESSION['md_admin'] !== true) 
    			$content = '<form method="post"><div style="text-align:center"><h1></h1>' . (isset($error) ? '<p style="color:#dd0000">' . $error . '</p>' : null) . '<input id="mdsimple_password" name="md_password" type="password" value="" placeholder="Password&hellip;" tabindex="1"><br><br><input type="hidden" id="action" name="action" value="ident"><input type="submit" value="Login" tabindex="2"></div></form><script type="text/javascript">$("#md_password").focus();</script>';
		else
			$content = "<h1>Vous êtes déjà logué !</h1>";        	
		print($content);
		exit;
        case ':SITEMAP':
		$content="<h1>Plan de site</h1>";
		foreach(plan(CONTENT_DIR) as $file)
			$content.='<p class="fileletter"><a href="'.$file.'">'.$file.'</a></p>'	;
            return $content;
        case ':GLOSSAIRE':
		$content="<h1>Glossaire</h1>";
		foreach(glossary(CONTENT_DIR) as $letter => $files)
		{
			$content.='<p class="letter">'.$letter.'</p>';
			foreach($files as $file)
				$content.='<p class="fileletter"><a href="'.$file.'">'.$file.'</a></p>'	;
		}
		return $content;
        case ':GLOSSAIRE':
		
		return $content;
	}
}

function plan($path){
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $files = $matches = array();
    while($dir->valid()) 
    {
        if (!$dir->isDot())
	  {
		$ext = pathinfo($dir->getSubPathName());
	      if ($ext['extension']=="md")
            {
			array_push($files,$dir->getSubPathName());
            }
        }
        $dir->next();
    }
    ksort($files);
    return $files;
}

function glossary($path){
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $files = $matches = array();
    while($dir->valid()) 
    {
        if (!$dir->isDot())
	  {
		$ext = pathinfo($dir->getSubPathName());
	      if ($ext['extension']=="md")
            {
			$letter=strtoupper(substr(basename($dir->getSubPathName()),0,1));
			if (!array_key_exists($letter,$files))
		      	$files[$letter]=array();
			array_push($files[$letter],$dir->getSubPathName());
            }
        }
        $dir->next();
    }
    ksort($files);
    return $files;
}

function searchstr($path, $string){
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $files = array();
    $totalFiles = 0;
    while($dir->valid()) 
    {
        if (!$dir->isDot())
        {
		$ext = pathinfo($dir->getSubPathName());
	      if ($ext['extension']=="md")
            {
		      $content = file_get_contents(CONTENT_DIR.$dir->getSubPathName());
		      $pos=strpos($content, $string);
		      if ($pos > 0) 
                  {
		          $posmin=$pos-150;
		          $quantity=300;
		          if ($posmin<0)
				$posmin=0;
		          $files[$dir->getSubPathName()] = substr(str_replace($string,'<span class="found">'.$string."</span>",$content), $posmin, $quantity);
			    $totalFiles++;
			}
            }
        }
        $dir->next();
    }
    ksort($files);
    return array('files' => $files, 'totalFiles' => $totalFiles);
}

function history($file)
{
    if (is_numeric(MAX_HISTORY_FILES) && MAX_HISTORY_FILES > 0) {
        $file_dir = dirname($file);
        $file_name = basename($file);
        $file_history_dir = HISTORY_DIR . '/' . str_replace(MAIN_DIR, '', $file_dir);
        foreach ([HISTORY_DIR, $file_history_dir] as $dir) {
            if (file_exists($dir) === false || is_dir($dir) === false) {
                mkdir($dir);
            }
        }
        $history_files = scandir($file_history_dir);
        foreach ($history_files as $key => $history_file) {
            if (in_array($history_file, ['.', '..', '.DS_Store'])) {
                unset($history_files[$key]);
            }
        }
        $history_files = array_values($history_files);
        if (count($history_files) >= MAX_HISTORY_FILES) {
            foreach ($history_files as $key => $history_file) {
                if ($key < 1) {
                    unlink($file_history_dir . DS . $history_file);
                    unset($history_files[$key]);
                } else {
                    rename($file_history_dir . DS . $history_file, $file_history_dir . DS . $file_name . '.' . ($key - 1));
                }
            }
        }
        copy($file, $file_history_dir . DS . $file_name . '.' . count($history_files));
    }
}


function setcontent($url,$data)
{
	$file = CONTENT_DIR.$url;
	if (!file_exists($file))
	{
             file_put_contents($file, $data);
             $content='success|Fichier créé.';
	}
	else if (is_writable($file))
	{
            file_put_contents($file, $data);
		//history($file);
            $content='success|Fichier enregistré.';
	}
	else if (!is_writable($file))
		$content='danger|Fichier protégé.';
	else
		$content='danger|Erreur indéterminée.';
	return $content;
}

function getcontent($url,$md=true)
{
   $file = CONTENT_DIR.$url;
   if (file_exists($file))
    	$content=file_get_contents($file);
   else
   {
      http_response_code(404);
      if (file_exists(CONTENT_DIR . "special/404.md")) 
		$content=getcontent("special/404.md");
	else
            $content="** Erreur 404 sur erreur 404 : pas de fichier 404.md**";
   }
   if ($md==true)
   {
   	 $extra = new ParsedownExtraplus();
  	 return $extra->text($content);
   }
   else
  	 return $content;
}

function filesJSON($path,$all,$first=true)
{
    $alldata = array();
    $dir= new DirectoryIterator($path);
    foreach($dir as $node) 
    {
        if (($node->getFilename() =="special") || (SHOW_HIDDEN_FILES === false && substr($node->getFilename(), 0, 1) === '.') || (($node->getExtension() != VIEWABLE_FORMAT && $all==false) && $node->isFile())) continue;
        $data = array();
        if ( $node->isDir() && !$node->isDot() )
        {
		$data['text'] = $node->getFilename();
		$data['children'] = filesJSON($path.$node->getFilename()."/",$all,false);
        }
        else
        {
		if ($all)
			$data['text'] = $node->getFilename();
		else
		{
			$file = pathinfo($node->getFilename());
            	$data['text'] = $file['filename'];
		}
            $data['icon'] = "jstree-file";
        }
	  $alldata[]=$data;
    }
    if ($first)
    	return  array('text'=>$_SERVER['SERVER_NAME'],'children'=>$alldata,'state' => array('opened'=>true));
    else
	return $alldata;
}

function getnav()
{
	$menu=getcontent("special/nav.md");
	$menuitems=explode("\n",$menu);
	$data="";
	foreach($menuitems as $item)
	{
	 $item=str_replace("</p>","",str_replace("<p>","",$item));
	 $a = new SimpleXMLElement($item);
	 $data.='<a class="dropdown-item" href="'.$a['href'].'">'.$a[0].'</a>';
	}
	 $data.='<a class="dropdown-item" href=":APROPOS">Sur MarkDoc...</a>';
	return $data;
}
