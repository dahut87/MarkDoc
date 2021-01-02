
# Usage

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

## Configuration

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
There is no default password. To generate it, use the sha512sum command line like this:

```bash
user@computer:~$ echo -en "password"|sha512sum|cut -d " " -f1
b109f3bbbc244eb82441917ed06d618b9008dd09b3befd1b5e07394c706a8bb980b1d7785e5976ec049b46df5f1326af5a2ea6d103fd07c95385ffab0cacbc86
```