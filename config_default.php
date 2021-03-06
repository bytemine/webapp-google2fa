<?php

/**
 * Configuration for Google2FA plugin
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */

/**
 * Application name in Authenticator
 */
define('PLUGIN_GOOGLE2FA_APPNAME', 'WebApp');

/**
 * Quantity timeless codes
 */
define('PLUGIN_GOOGLE2FA_TCODES', 9);

/** 
 * Select database ('settings', 'mysql')
 * Be careful if you change it later. You have to migrate data or the user has to configure again.
 */
define('PLUGIN_GOOGLE2FA_DATABASE', 'settings');

/**
 * DB connection data in case of using database like mysql
 * If PLUGIN_GOOGLE2FA_DATABASE_CREATETABLES is true, tables will be automatically created if not exist (db-user needs rights)
 */
define('PLUGIN_GOOGLE2FA_DATABASE_SERVERNAME', 'localhost');
define('PLUGIN_GOOGLE2FA_DATABASE_PORT', '3306'); // new key in config.php in 0.5.2
define('PLUGIN_GOOGLE2FA_DATABASE_DBNAME', 'google2fa');
define('PLUGIN_GOOGLE2FA_DATABASE_USERNAME', 'google2fa');
define('PLUGIN_GOOGLE2FA_DATABASE_PASSWORD', 'password');
define('PLUGIN_GOOGLE2FA_DATABASE_CREATETABLES', true);

/**
 * The cryption type ('auto', 'no', 'base64', 'mcrypt')
 * Has affect for new plugin users or in case of settings-database if a user resets plugin in settings.
 * To use mcrypt you need the php extension (php5-mcrypt).
 */
define('PLUGIN_GOOGLE2FA_CRYPT', 'auto');

/**
 * MCrypt configuration
 * Please change the mcrypt key!
 * Be careful if you change it later - in case of settings-database every plugin user has to reset the plugin in settings FIRST!
 * You can see the supported algorithms and modes with phpinfo().
 */
define('PLUGIN_GOOGLE2FA_MCRYPTKEY', 'GR6XTMQ45209zTRy8TYZ2F2PJ7pLAxM6mpYEaVyXEQ1igr2aOAEonLUV9S6x3OVZExEP9fJomdivPZY9z5ewcrCsN79KUW49');
define('PLUGIN_GOOGLE2FA_MCRYPTALGORITHM', MCRYPT_DES); // better (only libmcrypt > 2.4.x): MCRYPT_RIJNDAEL_128
define('PLUGIN_GOOGLE2FA_MCRYPTMODE', MCRYPT_MODE_ECB);
define('PLUGIN_GOOGLE2FA_MCRYPTRAND', strncasecmp(PHP_OS, 'WIN', 3)==0 ? MCRYPT_RAND : MCRYPT_DEV_URANDOM);

/**
 * Enable/Disable plugin
 * Default for new users, this doesn't mean the activation of two-factor authentication!
 */
define('PLUGIN_GOOGLE2FA_ENABLE', true);

/**
 * Enable plugin when plugin is loading, the user can't disable the plugin.
 * But if the activate button is enabled the user can deactivate the two-factor authentication!
 */
define('PLUGIN_GOOGLE2FA_ALWAYS_ENABLED', false);

/**
 * Activate/Deactivate 2FA
 * Default for new users.
 */
define('PLUGIN_GOOGLE2FA_ACTIVATE', false);

/**
 * Activate 2FA when plugin is loading.
 * If PLUGIN_GOOGLE2FA_ALWAYS_ACTIVATED is true, PLUGIN_GOOGLE2FA_ENBUTACTIV should be false
 */
define('PLUGIN_GOOGLE2FA_ALWAYS_ACTIVATED', false);

/**
 * Enable/Disable button configuration in settings.
 * For example for admin administration in mysql, ldap, ...
 */
define('PLUGIN_GOOGLE2FA_ENBUTCONF', true);

/**
 * Enable/Disable button activate/deactivate in settings.
 * For example for admin administration in mysql, ldap, ...
 */
define('PLUGIN_GOOGLE2FA_ENBUTACTIV', true);

/**
 * Enable/Disable button timeless codes in settings.
 * For example for admin administration in mysql, ldap, ...
 */
define('PLUGIN_GOOGLE2FA_ENBUTTCODES', true);

/**
 * Enable/Disable button reset configuration in settings.
 * For example for admin administration in mysql, ldap, ...
 */
define('PLUGIN_GOOGLE2FA_ENBUTTRESET', true);

/**
 * Whitelist with comma seperated IP addresses or subnets IPv4 and/or IPv6 for login without 2FA
 * Info: If the webserver allows IPv6 and the provider of the user supports IPv6, you have to use
 *       IPv6 in whitelist and every device has another IPv6 address!
 * Examples: '192.168.172.0/24,127.0.0.1' or gethostbyname('uri') or
 *           '2003:d5:b3d9:cf00::/64' or dns_get_record('uri', DNS_AAAA)[0]["ipv6"] . "/64"
 * Standard masks: '/32' (IPv4), '/128' (IPv6)
 */
define('PLUGIN_GOOGLE2FA_WHITELIST', '');

/**
 * List of trusted HTTP proxies with comma separated IP addresses or subnets.
 * Same input format as whitelist above since uses identical IP checking code.
 * When this is not set or blank (default) then 2FA will NOT trust any HTTP proxy.
 * ONLY add a HTTP proxy that you FULLY trust to safely set the X-Forwarded-For HTTP header.
 * Example: '127.0.0.1,::1' for Nginx reverse HTTPS proxy in front of Apache HTTP daemon.
 */
define('PLUGIN_GOOGLE2FA_TRUSTED_PROXIES', '');

/**
 * By default, the QR code is generated by Google. Here you can activate the generation on your server with PHPQrCode.
 */
define('PLUGIN_GOOGLE2FA_GENERATEQR', false);

?>
