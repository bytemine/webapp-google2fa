Ext.namespace("Zarafa.plugins.google2fa.data");

/**
 * @class Zarafa.plugins.google2fa.data.ResponseHandler
 * @extends Zarafa.core.data.AbstractResponseHandler
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 *
 * Response handler for communication with server
 */
Zarafa.plugins.google2fa.data.ResponseHandler = Ext.extend(Zarafa.core.data.AbstractResponseHandler, 
{
	successCallback: null,
	failureCallback: null,
	doResetconfiguration: function(a) 
	{
		this.successCallback(a);
	},
	doGetsecret: function(a) 
	{
		this.successCallback(a);
	},
	doActivate: function(a) 
	{
		this.successCallback(a);
	},
	doIsactivated: function(a) 
	{
		this.successCallback(a);
	},
	doVerifycode: function(a) 
	{
		this.successCallback(a);
	},
	doGettimelesscodes: function(a) 
	{
		this.successCallback(a);
	},
	doError: function(a) 
	{
		if (a.error)
			Zarafa.common.dialogs.MessageBox.show({
				title: "Error",
				msg: a.error.info.original_message,
				icon: Zarafa.common.dialogs.MessageBox.ERROR,
				buttons: Zarafa.common.dialogs.MessageBox.OK
			});
		else
			Zarafa.common.dialogs.MessageBox.show({
				title: "Error",
				msg: a.info.original_message,
				icon: Zarafa.common.dialogs.MessageBox.ERROR,
				buttons: Zarafa.common.dialogs.MessageBox.OK
			});
	}
});
Ext.reg("zarafa.google2faresponsehandler", Zarafa.plugins.google2fa.data.ResponseHandler);
