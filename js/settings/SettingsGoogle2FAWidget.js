Ext.namespace("Zarafa.plugins.google2fa.settings");

/**
 * @class Zarafa.plugins.google2fa.settings.SettingsGoogle2FAWidget
 * @extends Zarafa.settings.ui.SettingsWidget
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 *
 * Widget view in settings for two-factor authentication
 */
Zarafa.plugins.google2fa.settings.SettingsGoogle2FAWidget = Ext.extend(Zarafa.settings.ui.SettingsWidget, {
	constructor: function(a) 
	{
		a = a || {};
		Ext.applyIf(a, {
			title: dgettext("plugin_google2fa", "Configuration two-factor authentication"),
			layout: "form",
			items: [{
				xtype: "displayfield",
				hideLabel: true,
				value: dgettext("plugin_google2fa", "The two-factor authentication provides an additional protection for the Zarafa WebApp.") + "<br />" +
					dgettext("plugin_google2fa", "After activation you need next to your password an one-time code to log in.") + "<br />" + "<br />" +
					dgettext("plugin_google2fa", "To generate an one-time code, you have to configure a second device, usually a smartphone.") + "<br />&nbsp;"
			}, {
				xtype: "button",
				text: dgettext("plugin_google2fa", "Configuration"),
				handler: this.openConfigurationDialog,
				scope: this,
				disabled: !container.getSettingsModel().get("zarafa/v1/plugins/google2fa/enable_but_conf"),
				width: 250
			}, {
				xtype: "displayfield",
				hideLabel: true,
				value: "<hr />" + dgettext("plugin_google2fa", "For alternative access generate time-independent codes, please.") + "<br />" +
					dgettext("plugin_google2fa", "For example, if you lose your smartphone, you can access using these codes.") + "<br />&nbsp;"
			}, {
				xtype: "button",
				text: dgettext("plugin_google2fa", "Time-independent codes"),
				handler: this.openTimelessCodesDialog,
				scope: this,
				disabled: !container.getSettingsModel().get("zarafa/v1/plugins/google2fa/enable_but_tcodes"),
				width: 250
			}, {
				xtype: "displayfield",
				hideLabel: true,
				value: "<hr />" + dgettext("plugin_google2fa", "Activate or deactivate the two-factor authentication.") + "<br />&nbsp;"
			}, {
				xtype: "displayfield",
				fieldLabel: dgettext("plugin_google2fa", "Current status"),
				value: this.getStatus(),
				htmlEncode: true,
				ref: "status",
				width: 250
                        }, {
				xtype: "displayfield",
				hideLabel: true,
				value: ""
			}, {
				xtype: "button",
				text: dgettext("plugin_google2fa", "Activation/Deactivation"),
				handler: this.activate,
				scope: this,
				disabled: !container.getSettingsModel().get("zarafa/v1/plugins/google2fa/enable_but_activ"),
				width: 250
			}, {
				xtype: "displayfield",
				hideLabel: true,
				value: "<hr />" + dgettext("plugin_google2fa", "You can completely reset the configuration.") + "<br />" +
					dgettext("plugin_google2fa", "This deletes the secret key, the time-independent and the recently used codes and deactivates the two-factor authentication.") + "<br />" +
					dgettext("plugin_google2fa", "If fundamental changes in the configuration were changed, for example the encryption type, this step may be useful.") + "<br />&nbsp;"
			}, {
				xtype: "button",
				text: dgettext("plugin_google2fa", "Reset"),
				handler: this.openResetConfigurationDialog,
				scope: this,
				disabled: !container.getSettingsModel().get("zarafa/v1/plugins/google2fa/enable_but_reset"),
				width: 250
			}]
		});
		Zarafa.plugins.google2fa.settings.SettingsGoogle2FAWidget.superclass.constructor.call(this, a)
	},
	getStatus: function()
	{
		return (Zarafa.plugins.google2fa.data.Configuration.isActivated() ? dgettext("plugin_google2fa", "Activated") : dgettext("plugin_google2fa", "Deactivated"))
	},
	openResetConfigurationDialog: function() 
	{
		Zarafa.common.dialogs.MessageBox.show({
			title: dgettext("plugin_google2fa", "Reset"),
			msg: dgettext("plugin_google2fa", "Do you really want to reset the configuration?"),
			icon: Zarafa.common.dialogs.MessageBox.QUESTION,
			buttons: Zarafa.common.dialogs.MessageBox.YESNO,
			fn: this.resetConfiguration,
			scope: this
		})
	},
	resetConfiguration: function(a) 
	{
		if (a === "yes") {
			container.getRequest().singleRequest("google2famodule", "resetconfiguration", {}, new Zarafa.plugins.google2fa.data.ResponseHandler({
                        	successCallback: this.openResetConfigurationFinishDialog.createDelegate(this)
	                }))
		}
	},
	openResetConfigurationFinishDialog: function(a) 
	{
		Zarafa.plugins.google2fa.data.Configuration.gotIsActivated(a);
		this.status.setValue(this.getStatus());
		Zarafa.common.dialogs.MessageBox.show({
			title: dgettext("plugin_google2fa", "Reset"),
			msg: dgettext("plugin_google2fa", "The configuration has been reset."),
			icon: Zarafa.common.dialogs.MessageBox.INFO,
			buttons: Zarafa.common.dialogs.MessageBox.OK,
			scope: this
		})
	},
	openTimelessCodesDialog: function() 
	{
		container.getRequest().singleRequest("google2famodule", "gettimelesscodes", {generate: false}, new Zarafa.plugins.google2fa.data.ResponseHandler({
			successCallback: this.openTimelessCodesDialogX.createDelegate(this)
		}))
	},
	openTimelessCodesDialogX: function(a) 
	{
		var codeLabel = dgettext("plugin_google2fa", "Code");
		var codes = "";
		for (var i = 0; i < a.codes.length; ++i)
		{
			var l = String.format(dgettext("plugin_google2fa", "Code {0}"), "" + (i+1)) + ": ";
			var c = Zarafa.plugins.google2fa.data.Helper.Base64.decode(a.codes[i]);
			codes = codes + l + c + "<br />"
		}
		Zarafa.common.dialogs.MessageBox.addCustomButtons({
			title: dgettext("plugin_google2fa", "Time-independent codes"),
			msg: dgettext("plugin_google2fa", "Write down these time-independent codes safely.") + "<br /><hr />" + codes + "<hr />" +
				dgettext("plugin_google2fa", "Remember that you can use each code only one-time.") + "<br />"+
				dgettext("plugin_google2fa", "You can always generate new codes."),
			fn: this.generateTimelessCodes,
			customButton: [{
				text: dgettext("plugin_google2fa", "Generate new codes"),
				name: "generate"
			}, {
				text: dgettext("plugin_google2fa", "Ok"),
				name: "ok"
			}],
			scope: this,
			width: 500
		})
	},
	generateTimelessCodes: function(a) 
	{
		if (a === "generate") 
		{
			container.getRequest().singleRequest("google2famodule", "gettimelesscodes", {generate: true}, new Zarafa.plugins.google2fa.data.ResponseHandler({
				successCallback: this.openTimelessCodesDialogX.createDelegate(this)
			}))
		}
	},
	openConfigurationDialog: function() 
	{
		container.getRequest().singleRequest("google2famodule", "getsecret", {}, new Zarafa.plugins.google2fa.data.ResponseHandler({
			successCallback: this.openConfigurationDialogX.createDelegate(this)
		}))
	},
	openConfigurationDialogX: function(a) 
	{
		var secret = Zarafa.plugins.google2fa.data.Helper.Base64.decode(a.secret);
		var qRCodeGoogleUrl = Zarafa.plugins.google2fa.data.Helper.Base64.decode(a.qRCodeGoogleUrl);
		Zarafa.common.dialogs.MessageBox.addCustomButtons({
			title: dgettext("plugin_google2fa", "Configuration"),
			msg: dgettext("plugin_google2fa", "Please install an authentication App on second device:") + "<br />" +
				dgettext("plugin_google2fa", "Google Authenticator (Android, iOS, BlackBerry), Authenticator (Windows Phone)") + "<hr />" +
				dgettext("plugin_google2fa", "Open and configure the authentication app by scanning the QR code below.") + "<br /><br />" +
				"<img src='" + qRCodeGoogleUrl + "' /><br /><br />" + 
				dgettext("plugin_google2fa", "Alternatively, you can manually create an account with the following information.") + "<br /><br />" +
				dgettext("plugin_google2fa", "Account") + ": " + a.username + "@" + a.application + "<br />" + dgettext("plugin_google2fa", "Key") + ": " + secret + "<hr />" +
				dgettext("plugin_google2fa", "Afterwards test the function with a generated code to ensure that the configurations are correct."),
			fn: this.openVerifyCodeDialog,
			customButton: [{
                                text: dgettext("plugin_google2fa", "Test generated code"),
                                name: "verify"
                        }],
			scope: this,
			width: 500
		})
	},
	openVerifyCodeDialog: function(a) 
	{
		if (a === "verify")
			Zarafa.common.dialogs.MessageBox.prompt(dgettext("plugin_google2fa", "Test generated code"), dgettext("plugin_google2fa", "Please enter code"), this.verifyCode, this)
	},
	verifyCode: function(a, b) 
	{
		if (a === "ok") {
			container.getRequest().singleRequest("google2famodule", "verifycode", {code: b}, new Zarafa.plugins.google2fa.data.ResponseHandler({
                        	successCallback: this.openResponseDialog.createDelegate(this)
	                }))
		}	
	},
	openResponseDialog: function(a) 
	{
		if (a.isCodeOK) 
		{
			Zarafa.common.dialogs.MessageBox.show({
                                title: dgettext("plugin_google2fa", "Test generated code"),
                                msg: dgettext("plugin_google2fa", "Valid code, you can use the two-factor authentication."),
                                icon: Zarafa.common.dialogs.MessageBox.INFO,
                                buttons: Zarafa.common.dialogs.MessageBox.OK,
                                scope: this
                        })
		} else {
			Zarafa.common.dialogs.MessageBox.show({
				title: dgettext("plugin_google2fa", "Test generated code"),
				msg: dgettext("plugin_google2fa", "Invalid code, please check code.") + "<br />" +
					dgettext("plugin_google2fa", "You can use a code only one-time.") + "<br />" + 
					dgettext("plugin_google2fa", "Please make sure that time from of server and second device are correct."),
				icon: Zarafa.common.dialogs.MessageBox.ERROR,
				buttons: Zarafa.common.dialogs.MessageBox.OK,
				scope: this
			})
		}
	},
	activate: function() 
	{
		container.getRequest().singleRequest("google2famodule", "activate", {}, new Zarafa.plugins.google2fa.data.ResponseHandler({
			successCallback: this.setStatus.createDelegate(this)
		}))
	},
	setStatus: function(a) 	
	{
		Zarafa.plugins.google2fa.data.Configuration.gotIsActivated(a);
		this.status.setValue(this.getStatus());
		container.getNotifier().notify("info.files", dgettext("plugin_google2fa", "Two-factor authentication") + ": " + this.getStatus(), 
			dgettext("plugin_google2fa", "Current status") + ": " + this.getStatus())
	}
});
Ext.reg("Zarafa.plugins.google2fa.settingsgoogle2fawidget", Zarafa.plugins.google2fa.settings.SettingsGoogle2FAWidget);
