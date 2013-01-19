/*
BB to HTML parser class 

 Copyright (c) 2010 BjÃ¶rn BÃ¶sel
 Bug fixes IPS (Matt Mecham)
 
 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/

var IPS_SIZE_ARRAY = { 1: 8,
		               2: 10,
				       3: 12,
				       4: 14,
				       5: 18,
				       6: 24,
				       7: 36,
				       8: 48 };

/* @link http://stackoverflow.com/questions/784012/javascript-equivalent-of-phps-in-array */
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

function getKeys( obj )
{
    var r = [];
    
    for ( var k in obj )
    {
        if ( ! obj.hasOwnProperty(k) )
        {
            continue;
        }
        
        r.push(k);
    }
    
    return r;
}

function parseStyleText( el )
{
	styleText = $(el).getAttribute('style');
	
	var retval = {};
	
	( styleText || '' )
			.replace( /&quot;/g, '"' )
			.replace( /\s*([^ :;]+)\s*:\s*([^;]+)\s*(?=;|$)/g, function( match, name, value )
	{
		retval[ name.toLowerCase() ] = value;
	} );
	
	return retval;
}

var decodeHtml = ( function ()
{
	var regex = [],
		entities =
		{
			nbsp	: '\u00A0',		// IE | FF
			shy		: '\u00AD',		// IE
			gt		: '\u003E',		// IE | FF |   --   | Opera
			lt		: '\u003C'		// IE | FF | Safari | Opera
		};

	for ( var entity in entities )
		regex.push( entity );

	regex = new RegExp( '&(' + regex.join( '|' ) + ');', 'g' );

	return function( html )
	{
		return html.replace( regex, function( match, entity )
		{
			return entities[ entity ];
		});
	};
})();
	
BBCodeUtils = {
	lamda : function(args) {
		return args;
	},
	filterArray : function(arr, callback, scope) {
		if (typeof scope == "undefined")
			scope = this;
		var newArr = [];
		for ( var i = 0; i < arr.length; ++i) {
			if (callback.call(scope, arr[i]))
				newArr.push(arr[i]);
		}
		return newArr;
	},
	itemEquals : function(a, b) {
		return BBCodeUtils.JSONstring.make(a) == BBCodeUtils.JSONstring.make(b);

	},/*
		 * JSONstring v 1.01 copyright 2006 Thomas Frank (small sanitizer added
		 * to the toObject-method, May 2008)
		 * 
		 * This EULA grants you the following rights:
		 * 
		 * Installation and Use. You may install and use an unlimited number of
		 * copies of the SOFTWARE PRODUCT.
		 * 
		 * Reproduction and Distribution. You may reproduce and distribute an
		 * unlimited number of copies of the SOFTWARE PRODUCT either in whole or
		 * in part; each copy should include all copyright and trademark
		 * notices, and shall be accompanied by a copy of this EULA. Copies of
		 * the SOFTWARE PRODUCT may be distributed as a standalone product or
		 * included with your own product.
		 * 
		 * Commercial Use. You may sell for profit and freely distribute scripts
		 * and/or compiled scripts that were created with the SOFTWARE PRODUCT.
		 * 
		 * Based on Steve Yen's implementation:
		 * http://trimpath.com/project/wiki/JsonLibrary
		 * 
		 * Sanitizer regExp: Andrea Giammarchi 2007
		 * 
		 */

	JSONstring : {
		compactOutput : false,
		includeProtos : false,
		includeFunctions : false,
		detectCirculars : true,
		restoreCirculars : true,
		make : function(arg, restore) {
			this.restore = restore;
			this.mem = [];
			this.pathMem = [];
			return this.toJsonStringArray(arg).join('');
		},
		toObject : function(x) {
			if (!this.cleaner) {
				try {
					this.cleaner = new RegExp(
							'^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$')
				} catch (a) {
					this.cleaner = /^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/
				}
			}
			;
			if (!this.cleaner.test(x)) {
				return {}
			}
			;
			eval("this.myObj=" + x);
			if (!this.restoreCirculars || !alert) {
				return this.myObj
			}
			;
			if (this.includeFunctions) {
				var x = this.myObj;
				for ( var i in x) {
					if (typeof x[i] == "string"
							&& !x[i].indexOf("JSONincludedFunc:")) {
						x[i] = x[i].substring(17);
						eval("x[i]=" + x[i])
					}
				}
			}
			;
			this.restoreCode = [];
			this.make(this.myObj, true);
			var r = this.restoreCode.join(";") + ";";
			eval('r=r.replace(/\\W([0-9]{1,})(\\W)/g,"[$1]$2").replace(/\\.\\;/g,";")');
			eval(r);
			return this.myObj
		},
		toJsonStringArray : function(arg, out) {
			if (!out) {
				this.path = []
			}
			;
			out = out || [];
			var u; // undefined
			switch (typeof arg) {
			case 'object':
				this.lastObj = arg;
				if (this.detectCirculars) {
					var m = this.mem;
					var n = this.pathMem;
					for ( var i = 0; i < m.length; i++) {
						if (arg === m[i]) {
							out.push('"JSONcircRef:' + n[i] + '"');
							return out
						}
					}
					;
					m.push(arg);
					n.push(this.path.join("."));
				}
				;
				if (arg) {
					if (arg.constructor == Array) {
						out.push('[');
						for ( var i = 0; i < arg.length; ++i) {
							this.path.push(i);
							if (i > 0)
								out.push(',\n');
							this.toJsonStringArray(arg[i], out);
							this.path.pop();
						}
						out.push(']');
						return out;
					} else if (typeof arg.toString != 'undefined') {
						out.push('{');
						var first = true;
						for ( var i in arg) {
							if (!this.includeProtos
									&& arg[i] === arg.constructor.prototype[i]) {
								continue
							}
							;
							this.path.push(i);
							var curr = out.length;
							if (!first)
								out.push(this.compactOutput ? ',' : ',\n');
							this.toJsonStringArray(i, out);
							out.push(':');
							this.toJsonStringArray(arg[i], out);
							if (out[out.length - 1] == u)
								out.splice(curr, out.length - curr);
							else
								first = false;
							this.path.pop();
						}
						out.push('}');
						return out;
					}
					return out;
				}
				out.push('null');
				return out;
			case 'unknown':
			case 'undefined':
			case 'function':
				if (!this.includeFunctions) {
					out.push(u);
					return out
				}
				;
				arg = "JSONincludedFunc:" + arg;
				out.push('"');
				var a = [ '\n', '\\n', '\r', '\\r', '"', '\\"' ];
				arg += "";
				for ( var i = 0; i < 6; i += 2) {
					arg = arg.split(a[i]).join(a[i + 1])
				}
				;
				out.push(arg);
				out.push('"');
				return out;
			case 'string':
				if (this.restore && arg.indexOf("JSONcircRef:") == 0) {
					this.restoreCode.push('this.myObj.' + this.path.join(".")
							+ "="
							+ arg.split("JSONcircRef:").join("this.myObj."));
				}
				;
				out.push('"');
				var a = [ '\n', '\\n', '\r', '\\r', '"', '\\"' ];
				arg += "";
				for ( var i = 0; i < 6; i += 2) {
					arg = arg.split(a[i]).join(a[i + 1])
				}
				;
				out.push(arg);
				out.push('"');
				return out;
			default:
				out.push(String(arg));
				return out;
			}
		}
	},
	isInArray : function(arr, token) {
		for ( var i = 0; i < arr.length; ++i) {
			if (BBCodeUtils.itemEquals(arr[i], token))
				return true;
		}
		return false;
	},
	includeIntoArray : function(arr, item) {
		if (!this.isInArray(arr, item))
			arr.push(item);
		return arr;
	},
	/*
	 * type function from mootools
	 * 
	 * copyright: Copyright (c) 2006-2008 [Valerio
	 * Proietti](http://mad4milk.net/).
	 */
	type : function(obj) {

		if (obj == undefined)
			return false;
		if (obj.nodeName) {
			switch (obj.nodeType) {
			case 1:
				return 'element';
			case 3:
				return (/\S/).test(obj.nodeValue) ? 'textnode' : 'whitespace';
			}
		} else if (typeof obj.length == 'number') {
			if (obj.callee)
				return 'arguments';
			else if (obj.item)
				return 'collection';
		}
		return typeof obj;
	}
}

BBCodeConvertRule = function(options) {
	this.options = {
		"appliesToBB" : function() {
			return false;
		},
		"toBBStart" : BBCodeUtils.lambda,
		"toBBEnd" : BBCodeUtils.lambda,
		"toBBContent" : BBCodeUtils.lambda,
		"appliesToHTML" : function() {
			return false;
		},
		"toHTMLStart" : BBCodeUtils.lambda,
		"toHTMLEnd" : BBCodeUtils.lambda,
		"toHTMLContent" : BBCodeUtils.lambda
	};

	for ( var key in options) {
		this.options[key] = options[key];
	}
}

BBCodeConvertRule.prototype = {
	parser : null,
	appliesToBB : function(el) {
		if (BBCodeUtils.type(this.options.appliesToBB) == "function")
			return !!this.options.appliesToBB.call(this, el);
		return false;
	},
	toBBStart : function(el) {
		if (BBCodeUtils.type(this.options.toBBStart) == "function")
			return this.options.toBBStart.call(this, el);
		if (BBCodeUtils.type(this.options.toBBStart) == "string")
			return this.options.toBBStart;
		return this.options.toBBStart;
	},
	toBBContent : function(el) {
		if (BBCodeUtils.type(this.options.toBBContent) == "function")
			return this.options.toBBContent.call(this, el);
		if (BBCodeUtils.type(this.options.toBBStart) == "string")
			return this.options.toBBContent;
		return this.options.toBBContent;
	},
	toBBEnd : function(el) {
		if (BBCodeUtils.type(this.options.toBBEnd) == "function")
			return this.options.toBBEnd.call(this, el);
		if (BBCodeUtils.type(this.options.toBBEnd) == "string")
			return this.options.toBBEnd;
		return this.options.toBBEnd;
	},
	doesToBBContent : function(el) {
		if (this.options.toBBContent == BBCodeUtils.lambda)
			return false;
		return this.toBBContent(el) !== false;
	},
	appliesToHTML : function(el) {
		if (BBCodeUtils.type(this.options.appliesToHTML) == "function")
			return this.options.appliesToHTML(el);
		return false;
	},
	toHTMLStart : function(el) {
		if (BBCodeUtils.type(this.options.toHTMLStart) == "function")
			return this.options.toHTMLStart(el);
		if (BBCodeUtils.type(this.options.toHTMLStart) == "string")
			return this.options.toHTMLStart;
		return "";
	},
	toHTMLEnd : function(el) {
		if (BBCodeUtils.type(this.options.toHTMLEnd) == "function")
			return this.options.toHTMLEnd(el);
		if (BBCodeUtils.type(this.options.toHTMLEnd) == "string")
			return this.options.toHTMLEnd;
		return "";
	},
	toHTMLContent : function(el) {
		if (BBCodeUtils.type(this.options.toHTMLContent) == "function")
			return this.options.toHTMLContent(el);
		if (BBCodeUtils.type(this.options.toHTMLContent) == "string")
			return this.options.toHTMLContent;
		return false;
	},
	doesToHTMLContent : function(el) {
		return (this.options.toHTMLContent !== BBCodeUtils.lambda);
		if (this.options.toHTMLContent !== BBCodeUtils.lambda)
			return false;
		return this.toHTMLContent(el) !== false;
	}
};

BBCodeTree = function(BBcode, options) {
	this.options = {
		tagsSingle : [],
	};
	this.rules = [];
	for ( var key in options) {
		this.options[key] = options[key];
	}
	this.text = BBcode;
	
	this.toTree();
};

BBCodeTree.prototype = {
	tagStack : [],
	offset : 0,
	text : "",
	tree : [],
	toTree : function() {
		this.tree = [];
		var result = this.locateTagAfter(this.offset);

		while (result !== false) {
			
			// any text inbetween?
			if (result.start > (this.offset)) {
				if (this.tagStack.length == 0) {
					this.tree.push(this.text.substring(this.offset,
							result.start));
				} else {
					this.tagStack[this.tagStack.length - 1].children
							.push(this.text
									.substring(this.offset, result.start));
				}

			}
			// starting a new child
			if (result.data.type == "start") {
				if (BBCodeUtils.isInArray(this.options.tagsSingle,
						result.data.tag)) {
					if (this.tagStack.length == 0) {
						this.tree.push(result);
					} else {
						this.tagStack[this.tagStack.length - 1].children
								.push(result);
					}
				} else {

					this.tagStack.push(result);
				}
			} else // ending this is a end tag, maybe it fits current or
					// parent?
			if (this.tagStack.length > 0) {
				// it fits current?
				if (result.data.tag == this.tagStack[this.tagStack.length - 1].data.tag) {
					// no parent node on thestack? -> push it into result tree
					if (this.tagStack.length == 1) {
						this.tree.push(this.tagStack.pop());
					} else { // well just put it into its parent
						this.tagStack[this.tagStack.length - 2].children
								.push(this.tagStack.pop());
					}
					// if it doesnt fit the current, current might be missing
					// its end, maybe there is any parent fitting?
				} else if (BBCodeUtils.filterArray(this.tagStack, function(el) {
					return el.data.tag == this.data.tag;
				}, result)) {
					// some parent fits (see above), lets close all unclosed
					// ones
					while (result.data.tag !== this.tagStack[this.tagStack.length - 1].data.tag) {
						/* Matt fix for incorrectly nested or broken tags */
						if ( this.tagStack.length - 2 > 0 )
						{
							this.tagStack[this.tagStack.length - 2].children.push(this.tagStack.pop());
						}
						else
						{
							break;
						}
					}
					// so we finally found the tag to close!
					// no parent node on thestack? -> push it into result tree
					if (this.tagStack.length == 1) {
						this.tree.push(this.tagStack.pop());
					} else { // well just put it into its parent
						this.tagStack[this.tagStack.length - 2].children
								.push(this.tagStack.pop());
					}
				} else {
					// well we didnt find a tag to close, somebody screwed up
					// appearenty, so discard the closing tag...
				}
			} else {
				// a ending tag with no open tags? screw that one too...
			}
			// repeat after last tag...
			this.offset = result.end;
			result = this.locateTagAfter(this.offset);
		}
		// text at the end ?
		if (this.offset < this.text.length) {

			if (this.tagStack.length == 0) {
				this.tree.push(this.text.substring(this.offset));
			} else { // well just put it into its parent
				this.tagStack[this.tagStack.length - 1].children.push(this.text
						.substring(this.offset));
			}
		}
		while (this.tagStack.length > 0) {
			if (this.tagStack.length == 1) {
				this.tree.push(this.tagStack.pop());
			} else { // well just put it into its parent
				this.tagStack[this.tagStack.length - 2].children
						.push(this.tagStack.pop());
			}
		}
	},
	locateTagAfter : function(offset) {
		var foundAt = -2;
		while (foundAt == -2 && offset <= this.text.length) {

			foundAt = this.text.substr(offset).indexOf(this.options.openSymbol);
			if (foundAt == -1) {
				return false;
			}
			var endAt = this.text.substr(offset + foundAt + 1).indexOf(
					this.options.closeSymbol);
			if (this.text.substr(offset + foundAt + 1).indexOf(
					this.options.openSymbol) < endAt
					&& this.text.substr(offset + foundAt + 1).indexOf(
							this.options.openSymbol) >= 0) {
				offset = offset + foundAt + 1;
				foundAt = -2;
			} else {
				return {
					data : this.tagToObject(this.text.substr(offset + foundAt
							+ 1, endAt)),
					start : (offset + foundAt),
					end : (offset + foundAt + endAt + 2),
					children : []
				};
			}
		}
	},
	tagToObject : function(tag) {
		var type = '';
		
		if (tag.substr(0, 1) == "/") {
			type = "end";
			tag = tag.substr(1);
		} else {
			type = "start";
		}
		
		var hasEqual = false;
		
		parts = tag.match(/([^\s=]+(=("[^"]+"|'[^']+'|[^\s]+))?)/gi);

		var params = {};
	
		if ( parts == null || parts.length == 0 )
		{
			return {
				tag : null,
				params : params,
				type : 'start',
				hasEqual: false,
				children : []
			};
		}
		
		if ( parts[0].indexOf("=") > 0 )
		{
			tag = parts[0].split("=")[0];
		}
		else
		{
			tag = parts[0];
		}

		for ( var i = 0; i < parts.length; ++i)
		{
			if ( parts[i].indexOf("=") > 0 )
			{
				var value = parts[i].split("=").slice(1).join("=");
				hasEqual = true;
				
				if ( value[0] == "\"" && value[value.length - 1] == "\"" )
				{
					value = value.substring(1, value.length - 1);
				}
				else if ( value[0] == "'" && value[value.length - 1] == "'" )
				{
					value = value.substring(1, value.length - 1);
				}
				
				key = parts[i].split("=")[0];
				
				if ( key && value )
				{
					params[ key.toLowerCase() ] = value;
				}
			}
			else
			{
				params[parts[i].toLowerCase()] = parts[i];
			}
		}
		
		return {
			tag : tag,
			params : params,
			type : type,
			hasEqual: hasEqual,
			children : []
		};
	}
};

var BBCode = function(options) {
	this.options = {
		tagsSingle : [],
		openSymbol : "[",
		closeSymbol : "]"
	};

	for (var key in options) {
		this.options[key] = options[key];
	}
};

BBCode.prototype={
	rules : [],
	BBTextFilters : [],
	HTMLTextFilters : [],
	addRule : function(rule) {
		this.rules.push(new BBCodeConvertRule(rule));
		return this;
	},
	addBBTextFilter : function(filter) {
		if (BBCodeUtils.type(filter) == "function")
			this.BBTextFilters = BBCodeUtils.includeIntoArray(
					this.BBTextFilters, filter);
		return this;
	},
	addHTMLTextFilter : function(filter) {
		if (BBCodeUtils.type(filter) == "function")
			this.HTMLTextFilters = BBCodeUtils.includeIntoArray(
					this.HTMLTextFilters, filter);
		return this;
	},

	/* ! ======== toBBCode */
	toBBCode : function(html) {
		var html = this.preToBBConversion( html );
		var tmp = document.createElement("div");
		
		tmp.innerHTML = unescape(html);
		var ret = this.nodesToBBcode(tmp.childNodes);
		ret = this.postToBBConversion( ret );
		return ret;
	},
	/* ! ======== ToHTML */
	toHTML : function(bbcode) {
		var t = this.preToHtmlConversion( bbcode );
		t = new BBCodeTree(t, this.options);
		_ret  = this.nodesToHTML(t.tree);
		_ret  = this.postToHtmlConversion( _ret );
		return _ret;
	},
	applyBBTextFilters : function(text) {

		if (text == undefined)
			return "";
		for ( var i = 0; i < this.BBTextFilters.length; ++i) {
			var tmp = this.BBTextFilters[i](text);
			if (tmp != undefined)
				text = tmp;
		}

		return text;
	},
	applyHTMLTextFilters : function(text) {
		if (text == undefined)
			return "";
		for ( var i = 0; i < this.HTMLTextFilters.length; ++i) {
			var tmp = this.HTMLTextFilters[i](text);
			if (tmp != undefined)
				text = tmp;
		}
		return text;
	},
	nodeToBBcode : function(node) {
		var ret = "";

		if (BBCodeUtils.type(node) == "textnode") {
			return this
					.applyBBTextFilters((typeof node.textContent != "undefined" ? node.textContent
							: node.data));
		}
		if (BBCodeUtils.type(node) == "whitespace") {
			return this
					.applyBBTextFilters((typeof node.textContent != "undefined" ? node.textContent
							: node.data));
		}

		if (BBCodeUtils.type(node) !== "element")
			return "";
		var localrules = BBCodeUtils.filterArray(this.rules, function(rule) {
			return rule.appliesToHTML(this);
		}, node);
		for ( var i = 0; i < localrules.length; ++i) {
			var tmp = localrules[i].toBBStart(node);
			if (typeof tmp != "undefined")
				ret += tmp;
		}

		var contentrules = BBCodeUtils.filterArray(localrules, function(rule) {
			return rule.doesToBBContent(this);
		}, node);
		if (contentrules.length > 0) {
			ret += contentrules[0].toBBContent(node);
		} else {
			ret += this.nodesToBBcode(node.childNodes);
		}

		for ( var i = (localrules.length - 1); i >= 0; --i) {
			var tmp = localrules[i].toBBEnd(node);
			if (typeof tmp != "undefined")
				ret += tmp;

		}
		return ret;
	},
	nodesToBBcode : function(nodes) {
		var ret = "";
		for ( var i = 0; i < nodes.length; ++i) {
			ret += this.nodeToBBcode(nodes[i]);
		}
		return ret;
	},
	nodeToHTML : function(node) {
		var ret = "";
		var ipsBbcodeTags = getKeys( CKEDITOR.config.IPS_BBCODE );
		
		if (BBCodeUtils.type(node) == "string") {
			return this.applyHTMLTextFilters(node);
		}
		var localrules = BBCodeUtils.filterArray(this.rules, function(rule) {
			return rule.appliesToBB(this);
		}, node);

		for ( var i = 0; i < localrules.length; ++i) {
			var tmp = localrules[i].toHTMLStart(node);

			if (tmp != undefined)
				ret += tmp;
		}
		var contentrules = BBCodeUtils.filterArray(localrules, function(rule) {
			return rule.doesToHTMLContent(this);
		}, node);
		
		if (contentrules.length > 0)
		{
			ret += contentrules[0].toHTMLContent(node);
		}
		else
		{
/*! ======= FIX TO PUSH IN UNMATCHED TAGS */
			if (localrules.length == 0)
			{
				if (node.data.tag)
				{
					ret += '[' + node.data.tag;
					
					if ( typeof(node.data.params) == 'object' )
					{
						for( var i in node.data.params )
						{
							if ( i && node.data.params[i] && i != node.data.tag && node.data.params[i] != node.data.tag )
							{
								if ( node.data.hasEqual === true )
								{
									var _t = ( node.data.params[i].match( /\s/ ) ) ? '"' + node.data.params[i] + '"' : node.data.params[i];
									ret += ' ' + i + '=' + _t;
								}
								else
								{
									var _t = ( node.data.params[i].match( /\s/ ) ) ? '"' + node.data.params[i] + '"' : node.data.params[i];
									ret += ' ' + _t;
								}
							}
							else if ( i == node.data.tag && i != node.data.params[i] )
							{
								var _t = ( node.data.params[i].match( /\s/ ) ) ? '"' + node.data.params[i] + '"' : node.data.params[i];
								ret += '=' + _t;
							}
						}
					}
					
					ret += ']';
				}
			}
			
			ret += this.nodesToHTML(node.children);
			
			if (localrules.length == 0)
			{
				if (node.data.tag && ipsBbcodeTags.indexOf( node.data.tag ) != -1 && this.options.tagsSingle.indexOf( node.data.tag ) == -1 )
				{
					ret += '[/' + node.data.tag + ']';
				}
			}
		}
		for ( var i = (localrules.length - 1); i >= 0; --i) {
			var tmp = localrules[i].toHTMLEnd(node);
			if (tmp != undefined)
				ret += tmp;
		}

		return ret;
	},
	nodesToHTML : function(nodes) {
		var ret = "";
		for ( var i = 0; i < nodes.length; ++i) {
			ret += this.nodeToHTML(nodes[i]);
		}
		return ret;
	},
	/* IPS (Matt) additions below */
	/*! preToBBConversion */
	preToBBConversion: function( text )
	{
		/* </3 IE */
		text = text.replace( /(\r\n|\r)/g, "\n" );
		
		/* Make sure BR is on a new line */
		//text = text.replace( /(<br(?:[^>])?>)([^\n]+?)/g, "$1\n$2" );
		//text = text.replace( /\n(<br(?:[^>])?>)\n/g, "$1\n" );
		
		/* Make sure two BRs are on their own line */
		//text = text.replace( /(<br(?:[^>])?>)(<br(?:[^>])?>)/g, "$1\n$2" );
		
		//text = text.replace( /(<br(?:[^>]+?)?>)/g, "$1\n" );
		
		if ( Prototype.Browser.IE )
		{
			text = text.replace( /\n/g, '!!~~~~~~~~~~ie-sucks~~~~~~~~~~~~!!' );
		}
		Debug.write( "preToBBConversion: " + text );
		return text;
	},
	/*! postBBConversion */
	postToBBConversion: function( text )
	{
		if ( Prototype.Browser.IE )
		{
			text = text.replace( /\!\!~~~~~~~~~~ie-sucks~~~~~~~~~~~~\!\!/g, "\n" );
		}
		
		/* Convert emos */
		text = ipb.textEditor.smiliesToCode( text );
		
		/* remove whitespace at tut-top and tut-bottom */
		text = text.strip();
		
		Debug.write( "postToBBConversion: " + text );
		return text;
	},
	/*! preTotHtmlConversion */
	preToHtmlConversion: function( text )
	{
		/* </3 IE */
		text = text.replace( /(\r\n|\r)/g, "\n" );
		
		/* Fix up CODE tags so other tags inside do not embed */
		var map = IPSCKTools.getEmbeddedTagPositions( text, 'code', [ '[', ']' ] );
		
		$H(map.open).each( function( m )
		{
			id = m.key;
			o  = map['open'][ id ];
			c  = map['close'][ id ] - o;
			
			slice = phpjs.substr( text, o, c );
			
			/* Need to bump up lengths of opening and closing */
			var _origLength = phpjs.strlen( slice );
			
			if ( _origLength > 0 )
			{
				slice = slice.replace( /\[/g, '&#91;' );
				slice = slice.replace( /\/(\w+?)\]/g, '/$1&#93;' );
				
				var _newLength  = phpjs.strlen( slice );
				
				text = phpjs.substr_replace( text, slice, o, c );
				
				/* Bump! */
				if ( _newLength != _origLength )
				{
					$H(map.open).each( function( x )
					{
						_id = x.key;
						_o  = map['open'][ _id ];
						
						if ( _o > o )
						{
							map['open'][ _id ]  += ( _newLength - _origLength );
							map['close'][ _id ] += ( _newLength - _origLength );
						}
					} );
				}
			}
		} );
		
		var blocks = { 'list' : ['b', 'a'],
					   'quote': ['ao'    ],
					   '\\\*' : ['b', 'a'] };
		
		$H(blocks).each( function( i )
		{
			tag = i.key;
			arr = i.value;
			
			/* Before tag */
			if ( arr.indexOf('b') != -1 )
			{
				text = text.replace( new RegExp( "\n" + '([ ]+?)?\\\[' + tag + '(\\\]| )', 'gi'), '$1[' + tag.replace( /\\/g, '' ) + '$2' );
			}
			
			/* After open tag */
			if ( arr.indexOf('ao') != -1 )
			{
				text = text.replace( new RegExp( '\\\[(' + tag + '(?:[^\\\]]+?)?)\\\]([ ]+?)?' + "\n", 'gi'), '[$1]' );
			}
			
			/* After Tag */
			if ( arr.indexOf('a') != -1 )
			{
				text = text.replace( new RegExp( '\\\[/' + tag + '\\\]([ ]+?)?' + "\n", 'gi'), '[/' + tag.replace( /\\/g, '' ) + ']' );
			}
		} );
		
		/* Handle HTML entities */
		text = text.replace( /&(#[0-9]{3,4}|[a-zA-Z]{2,5});/g, '&amp;$1;' );
		
		text = text.replace( /</g, '&lt;' );
		text = text.replace( />/g, '&gt;' );

		/* Fix &sect from turning into section symbol */
		text	= text.replace( /&sect(?!;)/g, '&amp;sect' );
		
		/* Make sure non tag brackets are made safe */
		text = text.replace( /\[\]/g, '&#91;&#93;' );
		text = text.replace( /\[([^a-zA-Z\*\/]+?)\]/g, '&#91;$1&#93;' );
		text = text.replace( /\[(\s|$)/g, '&#91;$1' );
		
		/* Fix up single IMG tags */
		text = text.replace( new RegExp( '\\\[img\\\]([^\\\[]+?)\\\[/img\\\]', 'gi' ), '[img=$1]' );
		
		Debug.write( "preToHtmlConversion: " + text );
		
		return text;
	},
	/*! postHtmlConversion */
	postToHtmlConversion: function( text )
	{
		/* </3 IE */
		text = text.replace( /(\r\n|\r)/g, "\n" );
		
		/* Fix block elements */
		var blocks = { 'div'	   : ['b', 'a'],
					   'pre'	   : ['b', 'a'],
					   'blockquote': ['b', 'a'],
					   'p'		   : ['b', 'a'],
					   'ul'		   : ['b', 'a'],
					   'ol'		   : ['b', 'a'],
					   'li'		   : ['b', 'a'] };
		
		$H(blocks).each( function( i )
		{
			tag = i.key;
			arr = i.value;
			
			if ( arr.indexOf('b') != -1 )
			{
				text = text.replace( new RegExp( "\n" + '([ ]+?)?<' + tag + '(>| )', 'gi'), '$1<' + tag + '$2' );
			}
			
			if ( arr.indexOf('a') != -1 )
			{
				text = text.replace( new RegExp( '</' + tag + '>([ ]+?)?' + "\n", 'gi'), '</' + tag + '>' );
			}
		} );
		
		
		Debug.write( 'postToHtmlConversion: ' + text.replace( /\n/g, "\n-" ) );
		
		/* Convert emos */
		text = ipb.textEditor.codeToSmilies( text );
		
		/* Convert BRs */
		text = text.replace( /\n/g, '<br>' );
		
		return text;
	}
};

/*! ============= PARSER START */
/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview The "sourcearea" plugin. It registers the "source" editing
 *		mode, which displays the raw data being edited in the editor.
 */

/* Parser rules - Matt Mecham */
var myParser = new BBCode({tagsSingle:['img','sharedmedia','attachment','member']});

/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/single-tag-only-ignored-in-stdswap-r40232 */
document.observe("dom:loaded", function(){
	$H( CKEDITOR.config.IPS_BBCODE ).each( function(bbcode){
		if( bbcode.value.single_tag == 1 )
		{
			myParser.options.tagsSingle.push( bbcode.value.tag );
		}
	} );
});

/*! == CODE  */
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'pre' && $(el).hasClassName('_prettyXprint'); },
	toBBStart:function(el)
	{
		var langs  = 'auto';
		var lnums  = 0;
		
		classes = el.className;
		
		Debug.write( classes );
		
		var lang = classes.match( /_lang-(\w{2,10})/i );
		var lnum = classes.match( /_linenums:(\d{1,100})/i );
	
		if ( lang instanceof Array )
		{
			langs = lang[1]; 
		}
		
		if ( lnum instanceof Array )
		{
			lnums = parseInt( lnum[1] );
		}
		
		lnums = ( lnums < 1 ) ? 0 : lnums;
		
		return '[code=' + langs + ':' + lnums + ']';
	},
	toBBEnd  :		"[/" + 'code' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'code'; },
	toHTMLStart:	function(el)
	{
		var params = el.data.params.code.split(':');
		
		return '<' + 'pre' + ' class="_prettyXprint _lang-' + params[0] + ' _linenums:' + parseInt( params[1] ) + '">';
	},
	toHTMLEnd:		'</' + 'pre' + '>'
});

/*! == QUOTE  */
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'blockquote' && $(el).hasClassName('ipsBlockquote');  },
	toBBStart:function(el)
	{
		var author    = '';
		var cid       = '';
		var time      = 0;
		var date      = '';
		var collapsed = 0;
		var _extra    = '';
		
		try {
			author    = $(el).getAttribute( 'data-author' )    ? $(el).getAttribute( 'data-author' )    : '';
			cid       = $(el).getAttribute( 'data-cid' )       ? $(el).getAttribute( 'data-cid' )       : '';
			time      = $(el).getAttribute( 'data-time' )      ? $(el).getAttribute( 'data-time' )      : 0;
			date      = $(el).getAttribute( 'data-date' )      ? $(el).getAttribute( 'data-date' )      : '';
			collapsed = $(el).getAttribute( 'data-collapsed' ) ? $(el).getAttribute( 'data-collapsed' ) : 0;
		} catch( aCold ) { }
		
		if ( phpjs.strlen( author ) > 0 )
		{
			_extra += ' name="' + author + '"';
		}
		
		if ( phpjs.strlen( cid ) > 0 )
		{
			_extra += ' post="' + cid + '"';
		}
		
		if ( time > 0 )
		{
			_extra += ' timestamp="' + time + '"';
		}
		
		if ( phpjs.strlen( date ) > 0 )
		{
			_extra += ' date="' + date + '"';
		}
		
		if ( phpjs.strlen( collapsed ) > 0 )
		{
			_extra += ' collapsed="' + collapsed + '"';
		}
		
		return '[quote' + _extra + ']';
	},
	toBBEnd  :		"[/" + 'quote' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'quote'; },
	toHTMLStart:	function(el)
	{
		var author    = '';
		var cid       = '';
		var time      = 0;
		var date      = '';
		var collapsed = 0;
		var _extra    = '';
		Debug.dir( el );
		try {
			author    = el.data.params.name      ? el.data.params.name      : '';
			cid       = el.data.params.post      ? el.data.params.post      : '';
			time      = el.data.params.time      ? el.data.params.time      : 0;
			date      = el.data.params.date      ? el.data.params.date      : '';
			collapsed = el.data.params.collapsed ? el.data.params.collapsed : '';
		} catch( aCold ) { }
		
		if ( phpjs.strlen( author ) > 0 )
		{
			_extra += ' data-author="' + author + '"';
		}
		
		if ( phpjs.strlen( cid ) > 0 )
		{
			_extra += ' data-cid="' + cid + '"';
		}
		
		if ( time > 0 )
		{
			_extra += ' data-time="' + time + '"';
		}
		
		if ( phpjs.strlen( date ) > 0 )
		{
			_extra += ' data-date="' + date + '"';
		}
		
		if ( phpjs.strlen( collapsed ) > 0 )
		{
			_extra += ' data-collapsed="' + parseInt( collapsed ) + '"';
		}
		
		return '<blockquote class="ipsBlockquote"' + _extra + '><p>';
	},
	toHTMLEnd:	'</p></blockquote>'
});

/* ! == B, I, U, S, SUB, SUP */
myParser.addRule({
	appliesToHTML:	function(el){ return ( el.tagName.toLowerCase() == 'b' || el.tagName.toLowerCase() == 'strong' ); },
	toBBStart:	  	"[" + 'b' + "]",
	toBBEnd  :		"[/" + 'b' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'b'; },
	toHTMLStart:	function(el){ return '<' + 'strong' + '>'},
	toHTMLEnd:		'</' + 'strong' + '>'
});
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'u'; },
	toBBStart:	  	"[" + 'u' + "]",
	toBBEnd  :		"[/" + 'u' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'u'; },
	toHTMLStart:	function(el){ return '<' + 'u' + '>'},
	toHTMLEnd:		'</' + 'u' + '>'
});
myParser.addRule({
	appliesToHTML:	function(el){ return ( el.tagName.toLowerCase() == 'i' || el.tagName.toLowerCase() == 'em' ); },
	toBBStart:	  	"[" + 'i' + "]",
	toBBEnd  :		"[/" + 'i' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'i'; },
	toHTMLStart:	function(el){ return '<' + 'em' + '>'},
	toHTMLEnd:		'</' + 'em' + '>'
});
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'strike'; },
	toBBStart:	  	"[" + 's' + "]",
	toBBEnd  :		"[/" + 's' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 's'; },
	toHTMLStart:	function(el){ return '<' + 'strike' + '>'},
	toHTMLEnd:		'</' + 'strike' + '>'
});
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'sub'; },
	toBBStart:	  	"[" + 'sub' + "]",
	toBBEnd  :		"[/" + 'sub' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'sub'; },
	toHTMLStart:	function(el){ return '<' + 'sub' + '>'},
	toHTMLEnd:		'</' + 'sub' + '>'
});
myParser.addRule({
	appliesToHTML:	function(el){ return el.tagName.toLowerCase() == 'sup'; },
	toBBStart:	  	"[" + 'sup' + "]",
	toBBEnd  :		"[/" + 'sup' + "]",
	appliesToBB:	function(el){ return el.data.tag.toLowerCase() == 'sup'; },
	toHTMLStart:	function(el){ return '<' + 'sup' + '>'},
	toHTMLEnd:		'</' + 'sup' + '>'
});



/*! == FONT, SIZE, COLOR */
myParser.addRule({
		appliesToHTML:function(el)
		{
			return ( el.tagName.toLowerCase()  == 'span' );
		},
		toBBStart:function(el)
		{
			_ret = '';
			styles = parseStyleText( el );
			
			if ( ( typeof( styles['color'] ) != 'undefined' ) )
			{
				_ret += "[color="+ RGBToHex( styles['color'] )+"]";
			}
			if ( ( typeof( styles['font-family'] ) != 'undefined' ) )
			{
				_ret += "[font="+ styles['font-family'] +"]";
			}
			if ( ( typeof( styles['font-size'] ) != 'undefined' ) )
			{
				_ret += "[size="+ parseInt( styles['font-size'] ) + "]";
			}
			
			return _ret;
	
		},
		toBBEnd:function(el)
		{
			_ret = '';
			styles = parseStyleText( el );
			
			if ( ( typeof( styles['font-size'] ) != 'undefined' ) )
			{
				_ret += "[/size]";
			}
			if ( ( typeof( styles['font-family'] ) != 'undefined' ) )
			{
				_ret += "[/font]";
			}
			if ( ( typeof( styles['color'] ) != 'undefined' ) )
			{
				_ret += "[/color]";
			}
			
			return _ret;
		}, 
		appliesToBB:function(el)
		{
			return ( el.data.tag.toLowerCase() == "color" || el.data.tag.toLowerCase() == "font" || el.data.tag.toLowerCase() == "size" );
		},
		toHTMLStart:function(el)
		{
			var _style = '';
			if ( el.data.tag.toLowerCase() == 'color' )
			{
				_style += 'color:' + el.data.params.color;
			}
			if ( el.data.tag.toLowerCase() == 'font' )
			{
				_style += 'font-family:' + el.data.params.font;
			}
			if ( el.data.tag.toLowerCase() == 'size' )
			{
				_style += 'font-size:' + ( fontSizeToPx( el.data.params.size ) ) + 'px';
			}
			
			return '<span style="' + _style + ';">';
		},
		toHTMLEnd: '</span>'
} );

/*! == ALIGN / INDENT */
myParser.addRule({
		appliesToHTML:function(el)
		{
			return ( el.tagName.toLowerCase()  == 'p' || el.tagName.toLowerCase()  == 'div' );
		},
		toBBStart:function(el)
		{
			_ret = '';
			styles = parseStyleText( el );
			
			if ( ( typeof( styles['text-align'] ) != 'undefined' ) )
			{
				if ( styles['text-align'] == 'center' || styles['text-align'] == 'right' )
				{
					_ret += "[" + styles['text-align'] +"]";
				}
			}
			
			if ( ( typeof( styles['margin-left'] ) != 'undefined' ) )
			{
				_value  = parseInt( styles['margin-left'] );
				_factor = 40;
				_level  = ( _value >= _factor ) ? Math.round( _value / _factor ) : 0;
				
				if ( _level >= 1 )
				{				
					_ret += "[indent=" + _level +"]";
				}
			}
			
			return _ret;
		},
		toBBEnd:function(el)
		{
			_ret = '';
			styles = parseStyleText( el );
			
			if ( ( typeof( styles['text-align'] ) != 'undefined' ) )
			{
				if ( styles['text-align'] == 'center' || styles['text-align'] == 'right' )
				{
					_ret += "[/" + styles['text-align'] +"]";
				}
			}
			
			if ( ( typeof( styles['margin-left'] ) != 'undefined' ) )
			{
				_value  = parseInt( styles['margin-left'] );
				_factor = 40;
				
				if ( _value >= _factor )
				{
					_ret += "[/indent]";
				}
			}
			
			return _ret;
		}, 
		appliesToBB:function(el)
		{
			return ( el.data.tag.toLowerCase() == "right" || el.data.tag.toLowerCase() == "center" || el.data.tag.toLowerCase() == "indent" );
		},
		toHTMLStart:function(el)
		{
			var _style = '';
			
			if ( el.data.tag.toLowerCase() == "right" || el.data.tag.toLowerCase() == "center" )
			{
				_style += 'text-align:' + el.data.tag.toLowerCase() + ';';
			}
			
			if ( el.data.tag.toLowerCase() == 'indent' )
			{
				_factor = 40;
				_value  = parseInt( el.data.params.indent );
				_px     = ( _value ) ? _value * _factor : _factor; 
				
				_style += ' margin-left: ' + _px + 'px;';
			}
			
			if ( _style )
			{
				return '<p style="' + _style + '">';
			}
		},
		toHTMLEnd: '</p>'
} );

/*! == LIST */
myParser.addRule({
		appliesToHTML:function(el)
		{
			return ( el.tagName.toLowerCase()  == 'ul' || el.tagName.toLowerCase()  == 'ol'  );
		},
		toBBStart:function(el)
		{
			var _type = '';
			
			if ( $(el).hasClassName('decimal') || el.tagName.toLowerCase()  == 'ol' )
			{
				_type = '=1';
			}
			
			return '[LIST' + _type + ']';
		},
		toBBEnd:function(el)
		{
			return '[/LIST]';
		},
		appliesToBB:function(el)
		{
			return ( el.data.tag.toLowerCase() == "list" );
		},
		toHTMLStart:function(el)
		{
			var _type = ' class="bbc"';
			
			if ( parseInt( el.data.params.list ) == 1 )
			{
				_type = ' class="bbc bbcol decimal"';
			}
			
			return '<ul' + _type + '>';
		},
		toHTMLEnd: '</ul>'
} );
myParser.addRule({
		appliesToHTML:function(el)
		{
			return el.tagName.toLowerCase()  == 'li';
		},
		toBBStart:function(el)
		{
			return '[*]';
		},
		toBBEnd:function(el)
		{
			return "[/*]";
		},
		appliesToBB:function(el)
		{
			return ( el.data.tag == "*" );
		},
		toHTMLStart:function(el)
		{
			return '<li>';
		},
		toHTMLEnd: '</li>'
} );


/* == IMG */
myParser.addRule({
	appliesToHTML:	function(el){return el.tagName.toLowerCase() == "img" ;},
	toBBContent:	function(el){return "[img="+el.src+"]"},
	appliesToBB:	function(el){return el.data.tag.toLowerCase() == "img"; },
	toHTMLContent:	function(el){return "<img src=\"" + el.data.params.img + "\">";}
});

/* URL */
myParser.addRule( { appliesToHTML:function(el) { return (el.tagName.toLowerCase() == "a" && typeof el.href !== "undefined");},
					toBBStart:    function(el) { return "[url="+ ( el.href.indexOf(" " ) >= 0 ? "\"" + el.href + "\"" : el.href ) +"]"; },
					toBBEnd: 	  function(el) { return "[/url]"; },
					appliesToBB:  function(el) { return el.data.tag.toLowerCase() == "url"; },
					toHTMLStart:  function(el) { return '<a href="'+el.data.params.url+'">'; },
					toHTMLEnd:	  '</a>'
} );

/* Specific HTML conversion stuffs */
myParser.addHTMLTextFilter( function( text )
{
	
} );

function fontSizeToPx( size )
{
	size = parseInt( size );
	
	if ( size > 0 && size < 9 )
	{
		return IPS_SIZE_ARRAY[ size ];
	}
	else
	{
		return size;
	}
}


function RGBToHex( cssStyle )
{
	return cssStyle.replace( /(?:rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\))/gi, function( match, red, green, blue )
		{
			red = parseInt( red, 10 ).toString( 16 );
			green = parseInt( green, 10 ).toString( 16 );
			blue = parseInt( blue, 10 ).toString( 16 );
			var color = [red, green, blue] ;

			// Add padding zeros if the hex value is less than 0x10.
			for ( var i = 0 ; i < color.length ; i++ )
				color[i] = String( '0' + color[i] ).slice( -2 ) ;

			return '#' + color.join( '' ) ;
		 });
}
	