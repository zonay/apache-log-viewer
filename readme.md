
  

  

# Apache Logs Viewer

  

  

This is a PHP-based Apache Logs Viewer that provides a password-protected interface to browse, download, and view Apache logs from the `/var/log/apache2/` directory.

  

  

## Features

  

  

-  **Password Protection**: Secure access to the log viewer with a password.

  

-  **Download Individual Logs**: Provides download links for each `.log` and `.gz` log file in the directory.

  

-  **View Logs**: Displays the contents of `access.log` and `error.log` within the interface with line numbers and alternating row colors for better readability.

  

-  **Download All Logs**: Allows downloading all available logs as a single `.zip` archive.
  

  

## Requirements

  

  

- PHP 7.2+ with `ZipArchive` extension enabled.

  

- Tailwind CSS (linked via CDN in the script).

  

-  **ZIP Support**: ZIP support is required to enable the "Download All Logs" feature. Below are instructions for installing it.

  

  

## Installing ZIP Support

  

  

### 1. For PHP (ZipArchive Extension)

  

  

To enable ZIP file handling in PHP, you need the `ZipArchive` extension. To install it:

  

  

#### On Ubuntu/Debian:

  

```bash
 
sudo  apt  update
 
sudo  apt  install  php-zip
 
sudo  systemctl  restart  apache2  # or restart your web server
 
```

  

  

#### On CentOS/RHEL:

  

```bash
 
sudo  yum  install  php-pecl-zip
 

sudo  systemctl  restart  httpd  # or restart your web server
 
```

  

  

#### On Windows:

  

- Download and enable the `php_zip.dll` from the official PHP website or use the one bundled with your PHP installation.

  

- Ensure the following line is present in your `php.ini` file:

  

```

  

extension=php_zip.dll

  

```

  

  

### 2. Installing ZIP Utility (General System)

  

  

To ensure that your server has the `zip` utility available (used to compress files):

  

  

#### On Ubuntu/Debian:

  

```bash
 
sudo  apt  update
 
sudo  apt  install  zip
 
```

  

  

#### On CentOS/RHEL:

  

```bash
 
sudo  yum  install  zip

```

  

  

#### On macOS (via Homebrew):

  

```bash
 
brew  install  zip
 
```

  

  

#### On Windows:

  

- ZIP is generally included by default, but you can use third-party software like 7-Zip or WinRAR if needed.

  

  

## Installation

  

  

1. Copy the `log-viewer.php` file to your web directory (e.g., `/var/www/html`).

  

2. Make sure the Apache logs are stored in `/var/log/apache2/` (default location).

  

3. Ensure the web server has read access to the log directory.

  

4. Ensure PHP has the `ZipArchive` extension enabled for the "Download All" feature to work.

  

5. Make sure that the correct permissions are applied to the logs, especially after log rotation (see below).

  

  

## Correct Permissions on Log Rotation

  

  

To ensure that your web server can access the log files after log rotation, you'll need to configure the correct file permissions and ownership.

  

  

### 1. Adjusting Log Rotation Configuration

  

  

By default, logrotate manages Apache logs. To ensure logs have the correct permissions after rotation:

  

  

1. Open the logrotate configuration for Apache logs:

  

  

```bash
 
sudo  nano  /etc/logrotate.d/apache2
 
```

  

  

2. Modify the `create` directive to ensure the correct permissions and ownership are set. For example:

  

  

```bash
 
create  644  root  adm
 
```

  

  

-  `0644`: Ensures that the owner (root) has read/write access and everyone else has read-only access.

  

-  `root adm`: Ensures that the files are owned by the user `root` and the group `adm`.

  

  

The full configuration might look like this:

  

  

```bash
/var/log/apache2/*.log  { 
weekly
missingok
rotate  52
compress 
delaycompress
notifempty 
create  644  root  adm
sharedscripts
postrotate

if [ -f /var/run/apache2.pid ]; then

/etc/init.d/apache2  reload  >  /dev/null;
fi;
endscript

}

```

  

  

3. Save and close the file.

  

  

### 2. Manually Fixing Permissions After Log Rotation

  

  

If you encounter permission issues after log rotation, you can manually adjust the permissions as follows:

  

  

1. Change the ownership of the log files to `root:adm`:

  

  

```bash 
sudo  chown  root:adm  /var/log/apache2/*.log
```

  

  

2. Adjust the file permissions to allow the web server to read the logs:

  

  

```bash 
sudo  chmod  644  /var/log/apache2/*.log 
```

  

  

  

  

## Usage

  

  

1. Navigate to the script in your browser: `http://your-server/log-viewer.php`.

  

2. Enter the password (`demopassword`) to log in.

  

3. From the interface:

  

-  **Download Individual Logs**: Click on the download icon next to any log file.

  

-  **View Logs**: Scroll down to view the `access.log` and `error.log`.

  

-  **Download All Logs**: Click the "Download All Logs" button to download a `.zip` archive containing all logs.

  

-  **Logout**: Click the "Logout" button to end the session.

  

  

## Customization

  

  

-  **Change the password**: Edit the `$password` variable in the `log-viewer.php` file.

  

-  **Log directory**: If your logs are stored in a different directory, modify the `$logDir` variable to point to the correct path.

  

-  **Add more log files**: If you want to display more than just `access.log` and `error.log`, you can edit the `$displayFiles` array.

  

  

## License

  

  

This project is licensed under the GNU General Public License. You are free to redistribute and modify this project under the terms of the GPL.

  

  

---

  

  

© 2024 Zafer Onay. All rights reserved.

This project is licensed under the GNU General Public License (GPL).