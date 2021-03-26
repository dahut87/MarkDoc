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
    global $LANG;
	if (empty(ACCESS_IP) === false && ACCESS_IP != $_SERVER['REMOTE_ADDR'])
	    die($LANG['NOIP']);
}

function logprotect()
{
    global $LANG;
	if (file_exists(LOG_DIR)) {
	    $log = unserialize(file_get_contents(LOG_DIR));
	    if (empty($log)) {
		  $log = [];
	    }
	    if (isset($log[$_SERVER['REMOTE_ADDR']]) && $log[$_SERVER['REMOTE_ADDR']]['num'] > 3 && time() - $log[$_SERVER['REMOTE_ADDR']]['time'] < 86400) {
		  die("<h1>".$LANG['BLOCKIP']."</h1>");
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

function specialurl($url,$quit)
{
    global $LANG;
    switch ($url) 
    {
        case ':APROPOS':
 	 	    $extra = new ParsedownExtraplus();
            $content=$extra->text($LANG['ABOUTMARKDOC']);
            break;
        case ':ADMIN':
            if (isset($_SESSION['md_admin']) === false || $_SESSION['md_admin'] !== true) 
                    $content = '<form method="post"><div style="text-align:center"><h1></h1>' . (isset($error) ? '<p style="color:#dd0000">' . $error . '</p>' : null) . '<input id="mdsimple_password" name="md_password" type="password" value="" placeholder="Password&hellip;" tabindex="1"><br><br><input type="hidden" id="action" name="action" value="ident"><input type="submit" value="'.$LANG['LOGIN'].'" tabindex="2"></div></form><script type="text/javascript">$("#md_password").focus();</script>';
            else
                $content = '<h1>'.$LANG['ALREADYLOG'].'</h1>'; 
            break;      	
        case ':SITEMAP':
            $content='<h1>'.$LANG['SITEMAP'].'</h1>';
            foreach(plan(CONTENT_DIR) as $file)
                $content.='<p class="fileletter"><a href="'.$file.'">'.$file.'</a></p>'	;
            break;
        case ':GLOSSAIRE':
            $content='<h1>'.$LANG['GLOSSARY'].'</h1>';
            foreach(glossary(CONTENT_DIR) as $letter => $files)
            {
                $content.='<p class="letter">'.$letter.'</p>';
                foreach($files as $file)
                    $content.='<p class="fileletter"><a href="'.$file.'">'.$file.'</a></p>'	;
            }
            break;
    }
    if ($quit)
    {
        print($content);
        exit;
    }
    else
        return $content;
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

function loadlang($language)
{
    global $LANG,$JSLANG;
    $JSLANG=file_get_contents(ROOT_DIR."lang/".$language.".json");
    $LANG=json_decode($JSLANG,true);
}

function history($file)
{
    if (is_numeric(MAX_HISTORY_FILES) && MAX_HISTORY_FILES > 0) {
        $file_dir = str_replace(CONTENT_DIR,"",dirname($file)."/");
        $file_name = basename($file);
        $file_history_dir = HISTORY_DIR . $file_dir;
        foreach ([HISTORY_DIR, $file_history_dir] as $dir) {
            if (file_exists($dir) === false || is_dir($dir) === false) {
                mkdir($dir,0755,true);
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
                    unlink($file_history_dir . '/' . $history_file);
                    unset($history_files[$key]);
                } else {
                    $newfile_name=pathinfo(basename($history_file))['filename'];
                    rename($file_history_dir . '/' . $history_file, $file_history_dir . '/' . $newfile_name . '.' . ($key - 1));
                }
            }
        }
        copy($file, $file_history_dir . '/' . $file_name . '.' . count($history_files));
    }
}


function setcontent($url,$data)
{
    global $LANG;
	$file = CONTENT_DIR.$url;
	if (!file_exists($file))
	{
             file_put_contents($file, $data);
             $content='success|'.$LANG['CREATED'];
	}
	else if (is_writable($file))
	{
            file_put_contents($file, $data);
		    history($file);
            $content='success|'.$LANG['SAVED'];
	}
	else if (!is_writable($file))
		$content='danger|'.$LANG['PROTECTED'];
	else
		$content='danger|'.$LANG['INDETERMINED'];
	return $content;
}

function getcontent($url,$md=true,$header=false)
{
    global $LANG;
   $file = CONTENT_DIR.$url;
   if (file_exists($file))
    	$content=file_get_contents($file);
   else
   {
      http_response_code(404);
      if (file_exists(CONTENT_DIR . "special/404.md")) 
		$content=getcontent("special/404.md");
	else
            $content='**'.$LANG['404X2'].'**';
   }
   if ($header) header('Content-type: '.mime_content_type($file),true);
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
        if (($node->getFilename() =="images") || ($node->getFilename() =="documents") || ($node->getFilename() =="special") || (SHOW_HIDDEN_FILES === false && substr($node->getFilename(), 0, 1) === '.') || (($node->getExtension() != VIEWABLE_FORMAT && $all==false) && $node->isFile())) continue;
        $data = array();
        if ( $node->isDir() && !$node->isDot() )
        {
            $data['text'] = $node->getFilename();
            $data['children'] = filesJSON($path.$node->getFilename()."/",$all,false);
            $data['icon'] = "far fa-folder";
        }
        else
        {
            if ($all)
            {
                $data['text'] = $node->getFilename();
                if ($node->getExtension() == VIEWABLE_FORMAT)
                    $data['icon'] = "fas fa-book";
                else if (strpos(IMAGE_EXT,$node->getExtension())>=0)
                    $data['icon'] = "far fa-images";
                else
                    $data['icon'] = "far fa-file";
            }
            else
            {
                $file = pathinfo($node->getFilename());
                $data['text'] = $file['filename'];
                $data['icon'] = "fas fa-book";
            }
        }
	  $alldata[]=$data;
    }
    if ($first)
    	return  array('icon'=>"fas fa-atlas",'text'=>$_SERVER['SERVER_NAME'],'children'=>$alldata,'state' => array('opened'=>true));
    else
	return $alldata;
}

function getnav()
{
    global $LANG;
	$menu=getcontent("special/nav.md");
	$menuitems=explode("\n",$menu);
	$data="";
	foreach($menuitems as $item)
	{
	 $item=str_replace("</p>","",str_replace("<p>","",$item));
	 $a = new SimpleXMLElement($item);
	 $data.='<a class="dropdown-item" href="'.$a['href'].'">'.$a[0].'</a>';
	}
	 $data.='<a class="dropdown-item" href=":APROPOS">'.$LANG['MARKDOC'].'</a>';
	return $data;
}
