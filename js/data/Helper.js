Ext.namespace("Zarafa.plugins.google2fa.data");

/**
 * @class Zarafa.plugins.google2fa.data.Helper
 *
 * @author files plugin author
 * @copyright files plugin copyright
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 *
 * Helper class with base64 support
 */
Zarafa.plugins.google2fa.data.Helper = {
    Base64: {
        _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
        encode: function(a) {
            var b = "",
                c, d, e, f, g, h, i = 0;
            for (a = this._utf8_encode(a); i < a.length;) {
                c = a.charCodeAt(i++);
                d = a.charCodeAt(i++);
                e = a.charCodeAt(i++);
                f = c >> 2;
                c = (c & 3) << 4 | d >> 4;
                g = (d & 15) << 2 | e >> 6;
                h = e & 63;
                if (isNaN(d)) g = h = 64;
                else if (isNaN(e)) h = 64;
                b = b + this._keyStr.charAt(f) + this._keyStr.charAt(c) + this._keyStr.charAt(g) + this._keyStr.charAt(h)
            }
            return b
        },
        decode: function(a) {
            var b = "",
                c, d, e, f, g, h = 0;
            for (a =
                a.replace(/[^A-Za-z0-9\+\/\=]/g, ""); h < a.length;) {
                c = this._keyStr.indexOf(a.charAt(h++));
                d = this._keyStr.indexOf(a.charAt(h++));
                f = this._keyStr.indexOf(a.charAt(h++));
                g = this._keyStr.indexOf(a.charAt(h++));
                c = c << 2 | d >> 4;
                d = (d & 15) << 4 | f >> 2;
                e = (f & 3) << 6 | g;
                b += String.fromCharCode(c);
                if (f != 64) b += String.fromCharCode(d);
                if (g != 64) b += String.fromCharCode(e)
            }
            return b = this._utf8_decode(b)
        },
        _utf8_decode: function(a) {
            for (var b = "", c = 0, d = 0, e = 0, f = 0; c < a.length;) {
                d = a.charCodeAt(c);
                if (d < 128) {
                    b += String.fromCharCode(d);
                    c++
                } else if (d >
                    191 && d < 224) {
                    e = a.charCodeAt(c + 1);
                    b += String.fromCharCode((d & 31) << 6 | e & 63);
                    c += 2
                } else {
                    e = a.charCodeAt(c + 1);
                    f = a.charCodeAt(c + 2);
                    b += String.fromCharCode((d & 15) << 12 | (e & 63) << 6 | f & 63);
                    c += 3
                }
            }
            return b
        },
        _utf8_encode: function(a) {
            a = a.replace(/\r\n/g, "\n");
            for (var b = "", c = 0; c < a.length; c++) {
                var d = a.charCodeAt(c);
                if (d < 128) b += String.fromCharCode(d);
                else {
                    if (d > 127 && d < 2048) b += String.fromCharCode(d >> 6 | 192);
                    else {
                        b += String.fromCharCode(d >> 12 | 224);
                        b += String.fromCharCode(d >> 6 & 63 | 128)
                    }
                    b += String.fromCharCode(d & 63 | 128)
                }
            }
            return b
        }
    }
};

