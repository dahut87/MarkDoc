# MarkDoc

PHP MarkDown document manager, Free &amp; OpenSource :heart_eyes: for easily create your documentation website**
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

*Specials thanks to Hamid Samak*

2020 par Nicolas H.

---

## Features

### Actual

1. Simple Website with customizables menus
2. Markdown Editor with syntax highlighting and Emoji support
3. Very fast loading with ajax support
4. Multiple websites hosting with single MarkDoc installation
3. Integrated Image viewer
4. Restricted access by ip address
5. Admin access logging

### Planned

1. Complete File Manager
2. Password protected area
3. Keeping the history of edited files and changes
4. Configuration viewer
5. Emoji menu in Markdown editor
6. Uploading multiple files by drag and drop

## Installation

Installation using git

```
git clone https://github.com/dahut87/MarkDoc.git [website_root]
```

Install Emoji data and copy Emoji data to the "emoji-data"

```
wget https://github.com/iamcal/emoji-data/archive/v6.0.0.zip
unzip v6.0.0.zip
mv emoji-data-v6.0.0 emoji-data
```

## Usage

There is a sample website in the "[website_root]/hosts/locahost" directory. It's easy to add a new one, you create one subfolder by name served by your webserver.

```
[website_root]/hosts/example.com
[website_root]/hosts/test.sample.fr
```

## Configuration

The configuration file is named 'config.php', is located in the [website_root]/hosts/[host]/config.php.

List of the parameters you can modify
```
define('SUBCONTENT_DIR', 'content');
define('SHOW_HIDDEN_FILES', false);
define('VIEWABLE_FORMAT', 'md');
define('TITLE', 'Documentation');
define('ICON', 'fa-book-open');
define('ALLOWED_EXT','jpg,svg,gif,png,c,tgz,tar.gz,gz,tar,sql,ico');
define('PASSWORD', '[sha512 encoded password]');
define('LOG_FILE', 'log.txt');
define('ACCESS_IP', '');
define('HISTORY_FILE', 'history.txt');
define('MAX_HISTORY_FILES', 5);
```

**NOTE**:
The default password is `admin`. Please change the password after install or first login.

## Dependences

### Emoji Support

*Thanks to Cal Henderson*

 * https://github.com/iamcal/js-emoji
 * https://github.com/iamcal/php-emoji
 * https://github.com/iamcal/emoji-data

### Converting Markdown

*Thanks to Emanuil Rusev*

 * https://github.com/erusev/parsedown-extra
 * https://github.com/erusev/parsedown

### Editing markdown

*Thanks to Wes Cossick*

 * https://github.com/sparksuite/simplemde-markdown-editor

### Syntax highlighting

 * https://github.com/PrismLibrary/Prism

### Icons

 * https://github.com/FortAwesome/Font-Awesome

### Essential

 * https://github.com/twbs/bootstrap
 * https://github.com/jquery/jquery
 * https://github.com/popperjs/popper-core
 * https://github.com/zenorocha/clipboard.js




