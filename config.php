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
 * IP Whitelist for login without 2FA / regular expression
 * f.e. '/^192\.168\.178\.([2-4][0-9]|50)$/' for 192.168.178.20 - 192.168.178.50
 * Generate regular expression: http://www.analyticsmarket.com/freetools/ipregex
 */
define('PLUGIN_GOOGLE2FA_WHITELIST', '');

?>
