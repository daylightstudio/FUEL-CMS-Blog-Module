# BLOG MODULE FOR FUEL CMS
This is a [FUEL CMS](http://www.getfuelcms.com) blog module for adding blog like functionality including posts, categories, and commenting.

## INSTALLATION
There are a couple ways to install the module. If you are using GIT you can use the following method
to create a submodule:

### USING GIT
1. Open up a Terminal window, "cd" to your FUEL CMS installation then type in: 
Type in:
``php index.php fuel/installer/add_git_submodule git://github.com/daylightstudio/FUEL-CMS-Blog-Module.git blog``

2. Then to install, type in:
``php index.php fuel/installer/install blog``


### MANUAL
1. Download the zip file from GitHub:
[https://github.com/daylightstudio/FUEL-CMS-Blog-Module](https://github.com/daylightstudio/FUEL-CMS-Blog-Module)

2. Create a "blog" folder in fuel/modules/ and place the contents of the blog module folder in there.

3. Import the fuel_blog_install.sql and fuel_blog_permissions_install.sql from the blog/install folder into your database

4. Add "blog" to the the `$config['modules_allowed']` in fuel/application/config/MY_fuel.php

## UNINSTALL

To uninstall the module which will remove any permissions and database information:
``php index.php fuel/installer/uninstall blog``

### TROUBLESHOOTING
1. You may need to put in your full path to the "php" interpreter when using the terminal.
2. You must have access to an internet connection to install using GIT.


## DOCUMENTATION
To access the documentation, you can visit it [here](http://docs.getfuelcms.com/modules/blog).

## TEAM
* David McReynolds, Daylight Studio, Main Developer

## BUGS
To file a bug report, go to the [issues](https://github.com/daylightstudio/FUEL-CMS-Blog-Module/issues) page.

## LICENSE
The blog Module for FUEL CMS is licensed under [APACHE 2](http://www.apache.org/licenses/LICENSE-2.0).