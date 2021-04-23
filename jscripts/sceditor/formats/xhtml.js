/* SCEditor v2.1.3 | (C) 2017, Sam Clarke | sceditor.com/license */

!(function (y) {
	"use strict";
	var t = y.ie,
		E = t && t < 11,
		C = y.dom,
		e = y.utils,
		S = C.css,
		n = C.attr,
		T = C.is,
		A = C.removeAttr,
		i = C.convertElement,
		r = e.extend,
		a = e.each,
		N = e.isEmptyObject,
		l = y.command.get,
		s = {
			bold: { txtExec: ["<strong>", "</strong>"] },
			italic: { txtExec: ["<em>", "</em>"] },
			underline: {
				txtExec: ['<span style="text-decoration:underline;">', "</span>"],
			},
			strike: {
				txtExec: ['<span style="text-decoration:line-through;">', "</span>"],
			},
			subscript: { txtExec: ["<sub>", "</sub>"] },
			superscript: { txtExec: ["<sup>", "</sup>"] },
			left: { txtExec: ['<div style="text-align:left;">', "</div>"] },
			center: { txtExec: ['<div style="text-align:center;">', "</div>"] },
			right: { txtExec: ['<div style="text-align:right;">', "</div>"] },
			justify: { txtExec: ['<div style="text-align:justify;">', "</div>"] },
			font: {
				txtExec: function (t) {
					var e = this;
					l("font")._dropDown(e, t, function (t) {
						e.insertText('<span style="font-family:' + t + ';">', "</span>");
					});
				},
			},
			size: {
				txtExec: function (t) {
					var e = this;
					l("size")._dropDown(e, t, function (t) {
						e.insertText('<span style="font-size:' + t + ';">', "</span>");
					});
				},
			},
			color: {
				txtExec: function (t) {
					var e = this;
					l("color")._dropDown(e, t, function (t) {
						e.insertText('<span style="color:' + t + ';">', "</span>");
					});
				},
			},
			bulletlist: { txtExec: ["<ul><li>", "</li></ul>"] },
			orderedlist: { txtExec: ["<ol><li>", "</li></ol>"] },
			table: { txtExec: ["<table><tr><td>", "</td></tr></table>"] },
			horizontalrule: { txtExec: ["<hr />"] },
			code: { txtExec: ["<code>", "</code>"] },
			image: {
				txtExec: function (t, e) {
					var o = this;
					l("image")._dropDown(o, t, e, function (t, e, n) {
						var i = "";
						e && (i += ' width="' + e + '"'),
							n && (i += ' height="' + n + '"'),
							o.insertText("<img" + i + ' src="' + t + '" />');
					});
				},
			},
			email: {
				txtExec: function (t, n) {
					var i = this;
					l("email")._dropDown(i, t, function (t, e) {
						i.insertText('<a href="mailto:' + t + '">' + (e || n || t) + "</a>");
					});
				},
			},
			link: {
				txtExec: function (t, n) {
					var i = this;
					l("link")._dropDown(i, t, function (t, e) {
						i.insertText('<a href="' + t + '">' + (e || n || t) + "</a>");
					});
				},
			},
			quote: { txtExec: ["<blockquote>", "</blockquote>"] },
			youtube: {
				txtExec: function (t) {
					var n = this;
					l("youtube")._dropDown(n, t, function (t, e) {
						n.insertText(
							'<iframe width="560" height="315" src="https://www.youtube.com/embed/{id}?wmode=opaque&start=' +
								e +
								'" data-youtube-id="' +
								t +
								'" frameborder="0" allowfullscreen></iframe>',
						);
					});
				},
			},
			rtl: { txtExec: ['<div stlye="direction:rtl;">', "</div>"] },
			ltr: { txtExec: ['<div stlye="direction:ltr;">', "</div>"] },
		};
	function z() {
		var o = this,
			n = {},
			p = {};
		function t(t, e, n) {
			var i,
				o,
				h,
				r,
				a,
				l,
				s,
				c,
				u,
				d,
				f,
				g,
				v,
				x,
				m = n.createElement("div");
			return (
				(m.innerHTML = e),
				S(m, "visibility", "hidden"),
				n.body.appendChild(m),
				(o = m),
				C.traverse(
					o,
					function (t) {
						var e = t.nodeName.toLowerCase();
						b("*", t), b(e, t);
					},
					!0,
				),
				(h = m),
				C.traverse(
					h,
					function (t) {
						var e,
							n = t.nodeName.toLowerCase(),
							i = t.parentNode,
							o = t.nodeType,
							r = !C.isInline(t),
							a = t.previousSibling,
							l = t.nextSibling,
							s = i === h,
							c = !a && !l,
							u =
								"iframe" !== n &&
								(function t(e, n) {
									var i,
										o = e.childNodes,
										r = e.nodeName.toLowerCase(),
										a = e.nodeValue,
										l = o.length,
										s = z.allowedEmptyTags || [];
									if (n && "br" === r) return !0;
									if (T(e, ".sceditor-ignore")) return !0;
									if (-1 < s.indexOf(r) || "td" === r || !C.canHaveChildren(e)) return !1;
									if (a && /\S|\u00A0/.test(a)) return !1;
									for (; l--; ) if (!t(o[l], n && !e.previousSibling && !e.nextSibling)) return !1;
									return (
										!e.getBoundingClientRect ||
										(!e.className && !e.hasAttributes("style")) ||
										!(i = e.getBoundingClientRect()).width ||
										!i.height
									);
								})(t, s && c && "br" !== n),
							d = t.ownerDocument,
							f = z.allowedTags,
							g = t.firstChild,
							v = z.disallowedTags;
						if (
							3 !== o &&
							(4 === o ? (n = "!cdata") : ("!" !== n && 8 !== o) || (n = "!comment"),
							1 === o &&
								T(t, ".sceditor-nlf") &&
								(!g || (!E && 1 === t.childNodes.length && /br/i.test(g.nodeName))
									? (u = !0)
									: (t.classList.remove("sceditor-nlf"), t.className || A(t, "class"))),
							u
								? (e = !0)
								: f && f.length
								? (e = f.indexOf(n) < 0)
								: v && v.length && (e = -1 < v.indexOf(n)),
							e)
						) {
							if (!u) {
								for (
									r && a && C.isInline(a) && i.insertBefore(d.createTextNode(" "), t);
									t.firstChild;

								)
									i.insertBefore(t.firstChild, l);
								r && l && C.isInline(l) && i.insertBefore(d.createTextNode(" "), l);
							}
							i.removeChild(t);
						}
					},
					!0,
				),
				(r = m),
				(g = (f = z.allowedAttribs) && !N(f)),
				(x = (v = z.disallowedAttribs) && !N(v)),
				(p = {}),
				C.traverse(r, function (t) {
					if (t.attributes && ((a = t.nodeName.toLowerCase()), (c = t.attributes.length)))
						for (p[a] || (p[a] = g ? w(f["*"], f[a]) : w(v["*"], v[a])); c--; )
							(l = t.attributes[c]),
								(s = l.name),
								(u = p[a][s]),
								(d = !1),
								g
									? (d = null !== u && (!Array.isArray(u) || u.indexOf(l.value) < 0))
									: x && (d = null === u || (Array.isArray(u) && -1 < u.indexOf(l.value))),
								d && t.removeAttribute(s);
				}),
				t ||
					(function (t) {
						var e;
						C.removeWhiteSpace(t);
						var n,
							i = t.firstChild;
						for (; i; )
							(n = i.nextSibling),
								C.isInline(i) && !T(i, ".sceditor-ignore")
									? (e || ((e = t.ownerDocument.createElement("p")), i.parentNode.insertBefore(e, i)),
									  e.appendChild(i))
									: (e = null),
								(i = n);
					})(m),
				(i = new y.XHTMLSerializer().serialize(m, !0)),
				n.body.removeChild(m),
				i
			);
		}
		function b(t, i) {
			n[t] &&
				n[t].forEach(function (n) {
					n.tags[t]
						? a(n.tags[t], function (t, e) {
								i.getAttributeNode &&
									(!(t = i.getAttributeNode(t)) ||
										(e && e.indexOf(t.value) < 0) ||
										n.conv.call(o, i));
						  })
						: n.conv && n.conv.call(o, i);
				});
		}
		function w(t, e) {
			var n = {};
			return (
				t && r(n, t),
				e &&
					a(e, function (t, e) {
						Array.isArray(e) ? (n[t] = (n[t] || []).concat(e)) : n[t] || (n[t] = null);
					}),
				n
			);
		}
		(o.init = function () {
			N(z.converters || {}) ||
				a(z.converters, function (t, e) {
					a(e.tags, function (t) {
						n[t] || (n[t] = []), n[t].push(e);
					});
				}),
				(this.commands = r(!0, {}, s, this.commands));
		}),
			(o.toSource = t.bind(null, !1)),
			(o.fragmentToSource = t.bind(null, !0));
	}
	(y.XHTMLSerializer = function () {
		var i = { indentStr: "\t" },
			o = [],
			d = 0;
		function f(t) {
			var e = {
				"&": "&amp;",
				"<": "&lt;",
				">": "&gt;",
				'"': "&quot;",
				" ": "&nbsp;",
			};
			return t
				? t.replace(/[&<>"\xa0]/g, function (t) {
						return e[t] || t;
				  })
				: "";
		}
		function g(t, e) {
			switch (t.nodeType) {
				case 1:
					"!" === t.nodeName.toLowerCase()
						? n(t)
						: (function (t, e) {
								var n,
									i,
									o,
									r = t.nodeName.toLowerCase(),
									a = "iframe" === r,
									l = t.attributes.length,
									s = t.firstChild,
									c = e || /pre(?:\-wrap)?$/i.test(S(t, "whiteSpace")),
									u = !t.firstChild && !C.canHaveChildren(t) && !a;
								if (T(t, ".sceditor-ignore")) return;
								v("<" + r, !e && h(t));
								for (; l--; )
									(i = t.attributes[l]),
										(o = i.value),
										v(" " + i.name.toLowerCase() + '="' + f(o) + '"', !1);
								v(u ? " />" : ">", !1), a || (n = s);
								for (; n; ) d++, g(n, c), (n = n.nextSibling), d--;
								u || v("</" + r + ">", !c && !a && h(t) && s && h(s));
						  })(t, e);
					break;
				case 3:
					!(function (t, e) {
						var n = t.nodeValue;
						e || (n = n.replace(/[\r\n]/, " ").replace(/[^\S|\u00A0]+/g, " "));
						n && v(f(n), !e && h(t));
					})(t, e);
					break;
				case 4:
					v("<![CDATA[" + f(t.nodeValue) + "]]>");
					break;
				case 8:
					n(t);
					break;
				case 9:
				case 11:
					!(function (t) {
						var e = t.firstChild;
						for (; e; ) g(e), (e = e.nextSibling);
					})(t);
			}
		}
		function n(t) {
			v("\x3c!-- " + f(t.nodeValue) + " --\x3e");
		}
		function v(t, e) {
			var n = d;
			if (!1 !== e) for (o.length && o.push("\n"); n--; ) o.push(i.indentStr);
			o.push(t);
		}
		function h(t) {
			var e = t.previousSibling;
			return 1 !== t.nodeType && e ? !C.isInline(e) : (!e && !C.isInline(t.parentNode)) || !C.isInline(t);
		}
		this.serialize = function (t, e) {
			if (((o = []), e)) for (t = t.firstChild; t; ) g(t), (t = t.nextSibling);
			else g(t);
			return o.join("");
		};
	}),
		(z.converters = [
			{
				tags: { "*": { width: null } },
				conv: function (t) {
					S(t, "width", n(t, "width")), A(t, "width");
				},
			},
			{
				tags: { "*": { height: null } },
				conv: function (t) {
					S(t, "height", n(t, "height")), A(t, "height");
				},
			},
			{
				tags: { li: { value: null } },
				conv: function (t) {
					A(t, "value");
				},
			},
			{
				tags: { "*": { text: null } },
				conv: function (t) {
					S(t, "color", n(t, "text")), A(t, "text");
				},
			},
			{
				tags: { "*": { color: null } },
				conv: function (t) {
					S(t, "color", n(t, "color")), A(t, "color");
				},
			},
			{
				tags: { "*": { face: null } },
				conv: function (t) {
					S(t, "fontFamily", n(t, "face")), A(t, "face");
				},
			},
			{
				tags: { "*": { align: null } },
				conv: function (t) {
					S(t, "textAlign", n(t, "align")), A(t, "align");
				},
			},
			{
				tags: { "*": { border: null } },
				conv: function (t) {
					S(t, "borderWidth", n(t, "border")), A(t, "border");
				},
			},
			{
				tags: {
					applet: { name: null },
					img: { name: null },
					layer: { name: null },
					map: { name: null },
					object: { name: null },
					param: { name: null },
				},
				conv: function (t) {
					n(t, "id") || n(t, "id", n(t, "name")), A(t, "name");
				},
			},
			{
				tags: { "*": { vspace: null } },
				conv: function (t) {
					S(t, "marginTop", n(t, "vspace") - 0), S(t, "marginBottom", n(t, "vspace") - 0), A(t, "vspace");
				},
			},
			{
				tags: { "*": { hspace: null } },
				conv: function (t) {
					S(t, "marginLeft", n(t, "hspace") - 0), S(t, "marginRight", n(t, "hspace") - 0), A(t, "hspace");
				},
			},
			{
				tags: { hr: { noshade: null } },
				conv: function (t) {
					S(t, "borderStyle", "solid"), A(t, "noshade");
				},
			},
			{
				tags: { "*": { nowrap: null } },
				conv: function (t) {
					S(t, "whiteSpace", "nowrap"), A(t, "nowrap");
				},
			},
			{
				tags: { big: null },
				conv: function (t) {
					S(i(t, "span"), "fontSize", "larger");
				},
			},
			{
				tags: { small: null },
				conv: function (t) {
					S(i(t, "span"), "fontSize", "smaller");
				},
			},
			{
				tags: { b: null },
				conv: function (t) {
					i(t, "strong");
				},
			},
			{
				tags: { u: null },
				conv: function (t) {
					S(i(t, "span"), "textDecoration", "underline");
				},
			},
			{
				tags: { s: null, strike: null },
				conv: function (t) {
					S(i(t, "span"), "textDecoration", "line-through");
				},
			},
			{
				tags: { dir: null },
				conv: function (t) {
					i(t, "ul");
				},
			},
			{
				tags: { center: null },
				conv: function (t) {
					S(i(t, "div"), "textAlign", "center");
				},
			},
			{
				tags: { font: { size: null } },
				conv: function (t) {
					S(t, "fontSize", S(t, "fontSize")), A(t, "size");
				},
			},
			{
				tags: { font: null },
				conv: function (t) {
					i(t, "span");
				},
			},
			{
				tags: { "*": { type: ["_moz"] } },
				conv: function (t) {
					A(t, "type");
				},
			},
			{
				tags: { "*": { _moz_dirty: null } },
				conv: function (t) {
					A(t, "_moz_dirty");
				},
			},
			{
				tags: { "*": { _moz_editor_bogus_node: null } },
				conv: function (t) {
					t.parentNode.removeChild(t);
				},
			},
		]),
		(z.allowedAttribs = {}),
		(z.disallowedAttribs = {}),
		(z.allowedTags = []),
		(z.disallowedTags = []),
		(z.allowedEmptyTags = []),
		(y.formats.xhtml = z);
})(sceditor);
