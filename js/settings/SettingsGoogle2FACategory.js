Ext.namespace("Zarafa.plugins.google2fa.settings");

/**
 * @class Zarafa.plugins.google2fa.settings.SettingsGoogle2FACategory
 * @extends Zarafa.settings.ui.SettingsCategory
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 *
 * Category view for two-factor authentication in settings
 */
Zarafa.plugins.google2fa.settings.SettingsGoogle2FACategory = Ext.extend(Zarafa.settings.ui.SettingsCategory, {
	constructor: function(a) 
	{
        	a = a || {};
		Ext.applyIf(a, {
			title: dgettext("plugin_google2fa", "Two-factor authentication"),
			categoryIndex: 1,
			iconCls: "icon_google2fa_category",
			items: [{
				xtype: "Zarafa.plugins.google2fa.settingsgoogle2fawidget"
			}, container.populateInsertionPoint("context.settings.category.google2fa", this)]
		});
		Zarafa.plugins.google2fa.settings.SettingsGoogle2FACategory.superclass.constructor.call(this, a)
	}
});

Ext.reg("Zarafa.plugins.google2fa.settingsgoogle2facategory", Zarafa.plugins.google2fa.settings.SettingsGoogle2FACategory);
