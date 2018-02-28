Ext.namespace('Zarafa.plugins.google2fa');
Zarafa.plugins.google2fa.ABOUT = '<p>Copyright &copy; 2015 Norman Thimm &lt;norman@familiethimm.de&gt;, <a href="http://www.familiethimm.de" target="_blank">http://www.familiethimm.de</a></p><p>This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.</p><p>This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.</p><p>You should have received a copy of the GNU Affero General Public License along with this program.  If not, see <a href="http://www.gnu.org/licenses/" target="_blank">http://www.gnu.org/licenses/</a>.</p><hr /><p>The Google2FA plugin contains the following third-party components:</p><h1>GoogleAuthenticator</h1><p>Copyright &copy; 2012, http://www.phpgangsta.de, Author Michael Kliewe (@PHPGangsta), licensed under the BSD License (<a href="https://www.freebsd.org/copyright/freebsd-license.html" target="_blank">https://www.freebsd.org/copyright/freebsd-license.html</a>)</p><h1>PHPQrCode</h1><p>Copyright &copy; 2010 by Dominik Dzienia, http://phpqrcode.sourceforge.net, LGPL Licensed (<a href="http://www.gnu.org/licenses/" target="_blank">http://www.gnu.org/licenses/</a>), based on C libqrencode by Kentaro Fukuchi</p><h1>Symfony HttpFoundation IpUtils</h1><p>Copyright &copy; 2004-2017 by Fabien Potencier, https://github.com/symfony/http-foundation, MIT Licensed (<a href="https://opensource.org/licenses/MIT" target="_blank">https://opensource.org/licenses/MIT</a>)</p>';

/**
 * @class Zarafa.plugins.google2fa.Google2FA
 * @extends Zarafa.core.Plugin
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 *
 * Plugin Google2FA
 */
Zarafa.plugins.google2fa.Google2FA = Ext.extend(Zarafa.core.Plugin, {

	/**
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	constructor : function (config)
	{
		config = config || {};
		Zarafa.plugins.google2fa.Google2FA.superclass.constructor.call(this, config);
	},

	/**
	 * Init plugin
	 */
	initPlugin : function()
	{
		Zarafa.plugins.google2fa.Google2FA.superclass.initPlugin.apply(this, arguments);
		Zarafa.plugins.google2fa.data.Configuration.init();
		this.registerInsertionPoint("context.settings.categories", this.createSettingCategories, this);
	},

	/**
	 * Create category in settings
	 */
	createSettingCategories: function() {
		return {
			xtype: "Zarafa.plugins.google2fa.settingsgoogle2facategory"
		};
	}
});

Zarafa.onReady(function() {
	var sm = container.getSettingsModel();
	var allowUserDisable = sm.get('zarafa/v1/plugins/google2fa/user_disable_allowed');

	container.registerPlugin(new Zarafa.core.PluginMetaData({
		name : 'google2fa',
		displayName : _('Google2FA Plugin'),
		allowUserDisable : allowUserDisable,
		about: Zarafa.plugins.google2fa.ABOUT,
		pluginConstructor : Zarafa.plugins.google2fa.Google2FA
	}));
});
