/* SCEditor v2.1.3 | (C) 2017, Sam Clarke | sceditor.com/license */

!(function (t) {
	"use strict";
	var h = t.escapeEntities,
		a = t.escapeUriScheme,
		m = t.dom,
		e = t.utils,
		p = m.css,
		g = m.attr,
		v = m.is,
		n = e.extend,
		s = e.each,
		r = t.ie,
		b = r && r < 11,
		y = "data-sceditor-emoticon",
		l = t.command.get,
		x = { always: 1, never: 2, auto: 3 },
		i = {
			bold: { txtExec: ["[b]", "[/b]"] },
			italic: { txtExec: ["[i]", "[/i]"] },
			underline: { txtExec: ["[u]", "[/u]"] },
			strike: { txtExec: ["[s]", "[/s]"] },
			subscript: { txtExec: ["[sub]", "[/sub]"] },
			superscript: { txtExec: ["[sup]", "[/sup]"] },
			left: { txtExec: ["[left]", "[/left]"] },
			center: { txtExec: ["[center]", "[/center]"] },
			right: { txtExec: ["[right]", "[/right]"] },
			justify: { txtExec: ["[justify]", "[/justify]"] },
			font: {
				txtExec: function (t) {
					var e = this;
					l("font")._dropDown(e, t, function (t) {
						e.insertText("[font=" + t + "]", "[/font]");
					});
				},
			},
			size: {
				txtExec: function (t) {
					var e = this;
					l("size")._dropDown(e, t, function (t) {
						e.insertText("[size=" + t + "]", "[/size]");
					});
				},
			},
			color: {
				txtExec: function (t) {
					var e = this;
					l("color")._dropDown(e, t, function (t) {
						e.insertText("[color=" + t + "]", "[/color]");
					});
				},
			},
			bulletlist: {
				txtExec: function (t, e) {
					var n = "";
					s(e.split(/\r?\n/), function () {
						n += (n ? "\n" : "") + "[li]" + this + "[/li]";
					}),
						this.insertText("[ul]\n" + n + "\n[/ul]");
				},
			},
			orderedlist: {
				txtExec: function (t, e) {
					var n = "";
					s(e.split(/\r?\n/), function () {
						n += (n ? "\n" : "") + "[li]" + this + "[/li]";
					}),
						this.insertText("[ol]\n" + n + "\n[/ol]");
				},
			},
			table: { txtExec: ["[table][tr][td]", "[/td][/tr][/table]"] },
			horizontalrule: { txtExec: ["[hr]"] },
			code: { txtExec: ["[code]", "[/code]"] },
			image: {
				txtExec: function (t, e) {
					var i = this;
					l("image")._dropDown(i, t, e, function (t, e, n) {
						var r = "";
						e && (r += " width=" + e),
							n && (r += " height=" + n),
							i.insertText("[img" + r + "]" + t + "[/img]");
					});
				},
			},
			email: {
				txtExec: function (t, n) {
					var r = this;
					l("email")._dropDown(r, t, function (t, e) {
						r.insertText("[email=" + t + "]" + (e || n || t) + "[/email]");
					});
				},
			},
			link: {
				txtExec: function (t, n) {
					var r = this;
					l("link")._dropDown(r, t, function (t, e) {
						r.insertText("[url=" + t + "]" + (e || n || t) + "[/url]");
					});
				},
			},
			quote: { txtExec: ["[quote]", "[/quote]"] },
			youtube: {
				txtExec: function (t) {
					var e = this;
					l("youtube")._dropDown(e, t, function (t) {
						e.insertText("[youtube]" + t + "[/youtube]");
					});
				},
			},
			rtl: { txtExec: ["[rtl]", "[/rtl]"] },
			ltr: { txtExec: ["[ltr]", "[/ltr]"] },
		},
		k = {
			b: {
				tags: { b: null, strong: null },
				styles: {
					"font-weight": ["bold", "bolder", "401", "700", "800", "900"],
				},
				format: "[b]{0}[/b]",
				html: "<strong>{0}</strong>",
			},
			i: {
				tags: { i: null, em: null },
				styles: { "font-style": ["italic", "oblique"] },
				format: "[i]{0}[/i]",
				html: "<em>{0}</em>",
			},
			u: {
				tags: { u: null },
				styles: { "text-decoration": ["underline"] },
				format: "[u]{0}[/u]",
				html: "<u>{0}</u>",
			},
			s: {
				tags: { s: null, strike: null },
				styles: { "text-decoration": ["line-through"] },
				format: "[s]{0}[/s]",
				html: "<s>{0}</s>",
			},
			sub: {
				tags: { sub: null },
				format: "[sub]{0}[/sub]",
				html: "<sub>{0}</sub>",
			},
			sup: {
				tags: { sup: null },
				format: "[sup]{0}[/sup]",
				html: "<sup>{0}</sup>",
			},
			font: {
				tags: { font: { face: null } },
				styles: { "font-family": null },
				quoteType: x.never,
				format: function (t, e) {
					var n;
					return (
						(v(t, "font") && (n = g(t, "face"))) || (n = p(t, "font-family")),
						"[font=" + E(n) + "]" + e + "[/font]"
					);
				},
				html: '<font face="{defaultattr}">{0}</font>',
			},
			size: {
				tags: { font: { size: null } },
				styles: { "font-size": null },
				format: function (t, e) {
					var n = g(t, "size"),
						r = 2;
					return (
						n || (n = p(t, "fontSize")),
						-1 < n.indexOf("px")
							? ((n = n.replace("px", "") - 0) < 12 && (r = 1),
							  15 < n && (r = 3),
							  17 < n && (r = 4),
							  23 < n && (r = 5),
							  31 < n && (r = 6),
							  47 < n && (r = 7))
							: (r = n),
						"[size=" + r + "]" + e + "[/size]"
					);
				},
				html: '<font size="{defaultattr}">{!0}</font>',
			},
			color: {
				tags: { font: { color: null } },
				styles: { color: null },
				quoteType: x.never,
				format: function (t, e) {
					var n;
					return (
						(v(t, "font") && (n = g(t, "color"))) || (n = t.style.color || p(t, "color")),
						"[color=" + c(n) + "]" + e + "[/color]"
					);
				},
				html: function (t, e, n) {
					return '<font color="' + h(c(e.defaultattr), !0) + '">' + n + "</font>";
				},
			},
			ul: {
				tags: { ul: null },
				breakStart: !0,
				isInline: !1,
				skipLastLineBreak: !0,
				format: "[ul]{0}[/ul]",
				html: "<ul>{0}</ul>",
			},
			list: {
				breakStart: !0,
				isInline: !1,
				skipLastLineBreak: !0,
				html: "<ul>{0}</ul>",
			},
			ol: {
				tags: { ol: null },
				breakStart: !0,
				isInline: !1,
				skipLastLineBreak: !0,
				format: "[ol]{0}[/ol]",
				html: "<ol>{0}</ol>",
			},
			li: {
				tags: { li: null },
				isInline: !1,
				closedBy: ["/ul", "/ol", "/list", "*", "li"],
				format: "[li]{0}[/li]",
				html: "<li>{0}</li>",
			},
			"*": {
				isInline: !1,
				closedBy: ["/ul", "/ol", "/list", "*", "li"],
				html: "<li>{0}</li>",
			},
			table: {
				tags: { table: null },
				isInline: !1,
				isHtmlInline: !0,
				skipLastLineBreak: !0,
				format: "[table]{0}[/table]",
				html: "<table>{0}</table>",
			},
			tr: {
				tags: { tr: null },
				isInline: !1,
				skipLastLineBreak: !0,
				format: "[tr]{0}[/tr]",
				html: "<tr>{0}</tr>",
			},
			th: {
				tags: { th: null },
				allowsEmpty: !0,
				isInline: !1,
				format: "[th]{0}[/th]",
				html: "<th>{0}</th>",
			},
			td: {
				tags: { td: null },
				allowsEmpty: !0,
				isInline: !1,
				format: "[td]{0}[/td]",
				html: "<td>{0}</td>",
			},
			emoticon: {
				allowsEmpty: !0,
				tags: { img: { src: null, "data-sceditor-emoticon": null } },
				format: function (t, e) {
					return g(t, y) + e;
				},
				html: "{0}",
			},
			hr: {
				tags: { hr: null },
				allowsEmpty: !0,
				isSelfClosing: !0,
				isInline: !1,
				format: "[hr]{0}",
				html: "<hr />",
			},
			img: {
				allowsEmpty: !0,
				tags: { img: { src: null } },
				allowedChildren: ["#"],
				quoteType: x.never,
				format: function (e, t) {
					var n,
						r,
						i = "",
						l = function (t) {
							return e.style ? e.style[t] : null;
						};
					return g(e, y)
						? t
						: ((n = g(e, "width") || l("width")),
						  (r = g(e, "height") || l("height")),
						  ((e.complete && (n || r)) || (n && r)) && (i = "=" + m.width(e) + "x" + m.height(e)),
						  "[img" + i + "]" + g(e, "src") + "[/img]");
				},
				html: function (t, e, n) {
					var r,
						i,
						l,
						o = "";
					return (
						(r = e.width),
						(i = e.height),
						e.defaultattr && ((r = (l = e.defaultattr.split(/x/i))[0]), (i = 2 === l.length ? l[1] : l[0])),
						void 0 !== r && (o += ' width="' + h(r, !0) + '"'),
						void 0 !== i && (o += ' height="' + h(i, !0) + '"'),
						"<img" + o + ' src="' + a(n) + '" />'
					);
				},
			},
			url: {
				allowsEmpty: !0,
				tags: { a: { href: null } },
				quoteType: x.never,
				format: function (t, e) {
					var n = g(t, "href");
					return "mailto:" === n.substr(0, 7)
						? '[email="' + n.substr(7) + '"]' + e + "[/email]"
						: "[url=" + n + "]" + e + "[/url]";
				},
				html: function (t, e, n) {
					return (
						(e.defaultattr = h(e.defaultattr, !0) || n), '<a href="' + a(e.defaultattr) + '">' + n + "</a>"
					);
				},
			},
			email: {
				quoteType: x.never,
				html: function (t, e, n) {
					return '<a href="mailto:' + (h(e.defaultattr, !0) || n) + '">' + n + "</a>";
				},
			},
			quote: {
				tags: { blockquote: null },
				isInline: !1,
				quoteType: x.never,
				format: function (t, e) {
					for (var n, r = "data-author", i = "", l = t.children, o = 0; !n && o < l.length; o++)
						v(l[o], "cite") && (n = l[o]);
					return (
						(n || g(t, r)) &&
							((i = (n && n.textContent) || g(t, r)),
							g(t, r, i),
							n && t.removeChild(n),
							(e = this.elementToBbcode(t)),
							(i = "=" + i.replace(/(^\s+|\s+$)/g, "")),
							n && t.insertBefore(n, t.firstChild)),
						"[quote" + i + "]" + e + "[/quote]"
					);
				},
				html: function (t, e, n) {
					return (
						e.defaultattr && (n = "<cite>" + h(e.defaultattr) + "</cite>" + n),
						"<blockquote>" + n + "</blockquote>"
					);
				},
			},
			code: {
				tags: { code: null },
				isInline: !1,
				allowedChildren: ["#", "#newline"],
				format: "[code]{0}[/code]",
				html: "<code>{0}</code>",
			},
			left: {
				styles: {
					"text-align": ["left", "-webkit-left", "-moz-left", "-khtml-left"],
				},
				isInline: !1,
				format: "[left]{0}[/left]",
				html: '<div align="left">{0}</div>',
			},
			center: {
				styles: {
					"text-align": ["center", "-webkit-center", "-moz-center", "-khtml-center"],
				},
				isInline: !1,
				format: "[center]{0}[/center]",
				html: '<div align="center">{0}</div>',
			},
			right: {
				styles: {
					"text-align": ["right", "-webkit-right", "-moz-right", "-khtml-right"],
				},
				isInline: !1,
				format: "[right]{0}[/right]",
				html: '<div align="right">{0}</div>',
			},
			justify: {
				styles: {
					"text-align": ["justify", "-webkit-justify", "-moz-justify", "-khtml-justify"],
				},
				isInline: !1,
				format: "[justify]{0}[/justify]",
				html: '<div align="justify">{0}</div>',
			},
			youtube: {
				allowsEmpty: !0,
				tags: { iframe: { "data-youtube-id": null } },
				format: function (t, e) {
					return (t = g(t, "data-youtube-id")) ? "[youtube]" + t + "[/youtube]" : e;
				},
				html:
					'<iframe width="560" height="315" frameborder="0" src="https://www.youtube.com/embed/{0}?wmode=opaque" data-youtube-id="{0}" allowfullscreen></iframe>',
			},
			rtl: {
				styles: { direction: ["rtl"] },
				isInline: !1,
				format: "[rtl]{0}[/rtl]",
				html: '<div style="direction: rtl">{0}</div>',
			},
			ltr: {
				styles: { direction: ["ltr"] },
				isInline: !1,
				format: "[ltr]{0}[/ltr]",
				html: '<div style="direction: ltr">{0}</div>',
			},
			ignore: {},
		};
	function w(t, r) {
		return t.replace(/\{([^}]+)\}/g, function (t, e) {
			var n = !0;
			return (
				"!" === e.charAt(0) && ((n = !1), (e = e.substring(1))),
				"0" === e && (n = !1),
				void 0 === r[e] ? t : n ? h(r[e], !0) : r[e]
			);
		});
	}
	function B(t) {
		return "function" == typeof t;
	}
	function E(t) {
		return t ? t.replace(/\\(.)/g, "$1").replace(/^(["'])(.*?)\1$/, "$2") : t;
	}
	function C(t) {
		var n = arguments;
		return t.replace(/\{(\d+)\}/g, function (t, e) {
			return void 0 !== n[e - 0 + 1] ? n[e - 0 + 1] : "{" + e + "}";
		});
	}
	var I = "open",
		T = "content",
		S = "newline",
		L = "close";
	function u(t, e, n, r, i, l) {
		var o = this;
		(o.type = t), (o.name = e), (o.val = n), (o.attrs = r || {}), (o.children = i || []), (o.closing = l || null);
	}
	function q(t) {
		var m = this;
		function o(t, e) {
			var n, r, i;
			return (
				t === I &&
					(n = e.match(/\[([^\]\s=]+)(?:([^\]]+))?\]/)) &&
					((i = l(n[1])),
					n[2] &&
						(n[2] = n[2].trim()) &&
						(r = (function (t) {
							var e,
								n = /([^\s=]+)=(?:(?:(["'])((?:\\\2|[^\2])*?)\2)|((?:.(?!\s\S+=))*.))/g,
								r = {};
							if ("=" === t.charAt(0) && t.indexOf("=", 1) < 0) r.defaultattr = E(t.substr(1));
							else
								for ("=" === t.charAt(0) && (t = "defaultattr" + t); (e = n.exec(t)); )
									r[l(e[1])] = E(e[3]) || e[4];
							return r;
						})(n[2]))),
				t === L && (n = e.match(/\[\/([^\[\]]+)\]/)) && (i = l(n[1])),
				t === S && (i = "#newline"),
				(i && ((t !== I && t !== L) || k[i])) || ((t = T), (i = "#")),
				new u(t, i, e, r)
			);
		}
		function d(t, e, n) {
			for (var r = n.length; r--; ) if (n[r].type === e && n[r].name === t) return !0;
			return !1;
		}
		function p(t, e) {
			var n = (t ? k[t.name] : {}).allowedChildren;
			return !m.opts.fixInvalidChildren || !n || -1 < n.indexOf(e.name || "#");
		}
		function g(t, e, n) {
			var r = /\s|=/.test(t);
			return B(e)
				? e(t, n)
				: e === x.never || (e === x.auto && !r)
				? t
				: '"' + t.replace("\\", "\\\\").replace('"', '\\"') + '"';
		}
		function v(t) {
			return t.length ? t[t.length - 1] : null;
		}
		function l(t) {
			return t.toLowerCase();
		}
		(m.opts = n({}, q.defaults, t)),
			(m.tokenize = function (t) {
				var e,
					n,
					r,
					i = [],
					l = [
						{ type: T, regex: /^([^\[\r\n]+|\[)/ },
						{ type: S, regex: /^(\r\n|\r|\n)/ },
						{ type: I, regex: /^\[[^\[\]]+\]/ },
						{ type: L, regex: /^\[\/[^\[\]]+\]/ },
					];
				t: for (; t.length; ) {
					for (r = l.length; r--; )
						if (((n = l[r].type), (e = t.match(l[r].regex)) && e[0])) {
							i.push(o(n, e[0])), (t = t.substr(e[0].length));
							continue t;
						}
					t.length && i.push(o(T, t)), (t = "");
				}
				return i;
			}),
			(m.parse = function (t, e) {
				var n = (function (t) {
						var e,
							n,
							r,
							i,
							l,
							o,
							a = [],
							s = [],
							u = [],
							c = function () {
								return v(u);
							},
							f = function (t) {
								c() ? c().children.push(t) : s.push(t);
							},
							h = function (t) {
								return c() && (n = k[c().name]) && n.closedBy && -1 < n.closedBy.indexOf(t);
							};
						for (; (e = t.shift()); )
							switch (
								((o = t[0]),
								p(c(), e) ||
									(e.type === L && c() && e.name === c().name) ||
									((e.name = "#"), (e.type = T)),
								e.type)
							) {
								case I:
									h(e.name) && u.pop(),
										f(e),
										(n = k[e.name]) && !n.isSelfClosing && (n.closedBy || d(e.name, L, t))
											? u.push(e)
											: (n && n.isSelfClosing) || (e.type = T);
									break;
								case L:
									if (
										(c() && e.name !== c().name && h("/" + e.name) && u.pop(),
										c() && e.name === c().name)
									)
										(c().closing = e), u.pop();
									else if (d(e.name, I, u)) {
										for (; (r = u.pop()); ) {
											if (r.name === e.name) {
												r.closing = e;
												break;
											}
											(i = r.clone()), a.length && i.children.push(v(a)), a.push(i);
										}
										for (
											o &&
												o.type === S &&
												(n = k[e.name]) &&
												!1 === n.isInline &&
												(f(o), t.shift()),
												f(v(a)),
												l = a.length;
											l--;

										)
											u.push(a[l]);
										a.length = 0;
									} else (e.type = T), f(e);
									break;
								case S:
									c() &&
										o &&
										h((o.type === L ? "/" : "") + o.name) &&
										((o.type === L && o.name === c().name) ||
											((n = k[c().name]) && n.breakAfter
												? u.pop()
												: n &&
												  !1 === n.isInline &&
												  m.opts.breakAfterBlock &&
												  !1 !== n.breakAfter &&
												  u.pop())),
										f(e);
									break;
								default:
									f(e);
							}
						return s;
					})(m.tokenize(t)),
					r = m.opts;
				return (
					r.fixInvalidNesting &&
						(function t(e, n, r, i) {
							var l, o, a, s, u, c;
							var f = function (t) {
								var e = k[t.name];
								return !e || !1 !== e.isInline;
							};
							n = n || [];
							i = i || e;
							for (o = 0; o < e.length; o++)
								if ((l = e[o]) && l.type === I) {
									if (r && !f(l)) {
										if (
											((a = v(n)),
											(c = a.splitAt(l)),
											(u = 1 < n.length ? n[n.length - 2].children : i),
											p(l, a))
										) {
											var h = a.clone();
											(h.children = l.children), (l.children = [h]);
										}
										if (-1 < (s = u.indexOf(a))) {
											c.children.splice(0, 1), u.splice(s + 1, 0, l, c);
											var d = c.children[0];
											return void (
												d &&
												d.type === S &&
												(f(l) || (c.children.splice(0, 1), u.splice(s + 2, 0, d)))
											);
										}
									}
									n.push(l), t(l.children, n, r || f(l), i), n.pop();
								}
						})(n),
					(function t(e, n, r) {
						var i, l, o, a, s, u, c, f;
						var h = e.length;
						n && (a = k[n.name]);
						var d = h;
						for (; d--; )
							if ((i = e[d]))
								if (i.type === S) {
									if (
										((l = 0 < d ? e[d - 1] : null),
										(o = d < h - 1 ? e[d + 1] : null),
										(f = !1),
										!r &&
											a &&
											!0 !== a.isSelfClosing &&
											(l
												? u ||
												  o ||
												  (!1 === a.isInline &&
														m.opts.breakEndBlock &&
														!1 !== a.breakEnd &&
														(f = !0),
												  a.breakEnd && (f = !0),
												  (u = f))
												: (!1 === a.isInline &&
														m.opts.breakStartBlock &&
														!1 !== a.breakStart &&
														(f = !0),
												  a.breakStart && (f = !0))),
										l &&
											l.type === I &&
											(s = k[l.name]) &&
											(r
												? !1 === s.isInline && (f = !0)
												: (!1 === s.isInline &&
														m.opts.breakAfterBlock &&
														!1 !== s.breakAfter &&
														(f = !0),
												  s.breakAfter && (f = !0))),
										!r &&
											!c &&
											o &&
											o.type === I &&
											(s = k[o.name]) &&
											(!1 === s.isInline &&
												m.opts.breakBeforeBlock &&
												!1 !== s.breakBefore &&
												(f = !0),
											s.breakBefore && (f = !0),
											(c = f)))
									) {
										e.splice(d, 1);
										continue;
									}
									f && e.splice(d, 1), (c = !1);
								} else i.type === I && t(i.children, i, r);
					})(n, null, e),
					r.removeEmptyTags &&
						(function t(e) {
							var n, r;
							var i = function (t) {
								for (var e = t.length; e--; ) {
									var n = t[e].type;
									if (n === I || n === L) return !1;
									if (n === T && /\S|\u00A0/.test(t[e].val)) return !1;
								}
								return !0;
							};
							var l = e.length;
							for (; l--; )
								(n = e[l]) &&
									n.type === I &&
									((r = k[n.name]),
									t(n.children),
									i(n.children) &&
										r &&
										!r.isSelfClosing &&
										!r.allowsEmpty &&
										e.splice.apply(e, [l, 1].concat(n.children)));
						})(n),
					n
				);
			}),
			(m.toHTML = function (t, e) {
				return (function t(e, n) {
					var r,
						i,
						l,
						o,
						a,
						s,
						u,
						c,
						f = [];
					u = function (t) {
						return !1 !== (!t || (void 0 !== t.isHtmlInline ? t.isHtmlInline : t.isInline));
					};
					for (; 0 < e.length; )
						if ((r = e.shift())) {
							if (r.type === I)
								(c = r.children[r.children.length - 1] || {}),
									(i = k[r.name]),
									(a = n && u(i)),
									(l = t(r.children, !1)),
									i && i.html
										? (u(i) ||
												!u(k[c.name]) ||
												i.isPreFormatted ||
												i.skipLastLineBreak ||
												b ||
												(l += "<br />"),
										  B(i.html)
												? (o = i.html.call(m, r, r.attrs, l))
												: ((r.attrs[0] = l), (o = w(i.html, r.attrs))))
										: (o = r.val + l + (r.closing ? r.closing.val : ""));
							else {
								if (r.type === S) {
									if (!n) {
										f.push("<br />");
										continue;
									}
									s || f.push("<div>"),
										b || f.push("<br />"),
										e.length || f.push("<br />"),
										f.push("</div>\n"),
										(s = !1);
									continue;
								}
								(a = n), (o = h(r.val, !0));
							}
							a && !s ? (f.push("<div>"), (s = !0)) : !a && s && (f.push("</div>\n"), (s = !1)),
								f.push(o);
						}
					s && f.push("</div>\n");
					return f.join("");
				})(m.parse(t, e), !0);
			}),
			(m.toBBCode = function (t, e) {
				return (function t(e) {
					var n,
						r,
						i,
						l,
						o,
						a,
						s,
						u,
						c,
						f,
						h = [];
					for (; 0 < e.length; )
						if ((n = e.shift()))
							if (
								((i = k[n.name]),
								(l = !(!i || !1 !== i.isInline)),
								(o = i && i.isSelfClosing),
								(s = (l && m.opts.breakBeforeBlock && !1 !== i.breakBefore) || (i && i.breakBefore)),
								(u = (l && !o && m.opts.breakStartBlock && !1 !== i.breakStart) || (i && i.breakStart)),
								(c = (l && m.opts.breakEndBlock && !1 !== i.breakEnd) || (i && i.breakEnd)),
								(f = (l && m.opts.breakAfterBlock && !1 !== i.breakAfter) || (i && i.breakAfter)),
								(a = (i ? i.quoteType : null) || m.opts.quoteType || x.auto),
								i || n.type !== I)
							)
								if (n.type === I) {
									if ((s && h.push("\n"), h.push("[" + n.name), n.attrs))
										for (r in (n.attrs.defaultattr &&
											(h.push("=", g(n.attrs.defaultattr, a, "defaultattr")),
											delete n.attrs.defaultattr),
										n.attrs))
											n.attrs.hasOwnProperty(r) && h.push(" ", r, "=", g(n.attrs[r], a, r));
									h.push("]"),
										u && h.push("\n"),
										n.children && h.push(t(n.children)),
										o || i.excludeClosing || (c && h.push("\n"), h.push("[/" + n.name + "]")),
										f && h.push("\n"),
										n.closing && o && h.push(n.closing.val);
								} else h.push(n.val);
							else h.push(n.val), n.children && h.push(t(n.children)), n.closing && h.push(n.closing.val);
					return h.join("");
				})(m.parse(t, e));
			});
	}
	function o(t) {
		return (
			(t = parseInt(t, 10)),
			isNaN(t) ? "00" : (t = Math.max(0, Math.min(t, 255)).toString(16)).length < 2 ? "0" + t : t
		);
	}
	function c(t) {
		var e;
		return (e = (t = t || "#000").match(/rgb\((\d{1,3}),\s*?(\d{1,3}),\s*?(\d{1,3})\)/i))
			? "#" + o(e[1]) + o(e[2]) + o(e[3])
			: (e = t.match(/#([0-f])([0-f])([0-f])\s*?$/i))
			? "#" + e[1] + e[1] + e[2] + e[2] + e[3] + e[3]
			: t;
	}
	function f() {
		var u = this;
		u.stripQuotes = E;
		var o = {},
			a = {},
			c = {
				ul: ["li", "ol", "ul"],
				ol: ["li", "ol", "ul"],
				table: ["tr"],
				tr: ["td", "th"],
				code: ["br", "p", "div"],
			};
		function f(n, r, t) {
			var i,
				l,
				o = m.getStyle;
			return (
				a[(t = !!t)] &&
					s(a[t], function (t, e) {
						(i = o(n, t)) &&
							o(n.parentNode, t) !== i &&
							s(e, function (t, e) {
								(!e || -1 < e.indexOf(i.toString())) &&
									((l = k[t].format), (r = B(l) ? l.call(u, n, r) : C(l, r)));
							});
					}),
				r
			);
		}
		function h(n, r, t) {
			var i,
				l,
				e = n.nodeName.toLowerCase();
			return (
				(t = !!t),
				o[e] &&
					o[e][t] &&
					s(o[e][t], function (t, e) {
						(e &&
							((i = !1),
							s(e, function (t, e) {
								if (g(n, t) && !(e && e.indexOf(g(n, t)) < 0)) return !(i = !0);
							}),
							!i)) ||
							((l = k[t].format), (r = B(l) ? l.call(u, n, r) : C(l, r)));
					}),
				r
			);
		}
		function d(t) {
			var u = function (t, a) {
				var s = "";
				return (
					m.traverse(
						t,
						function (t) {
							var e = "",
								n = t.nodeType,
								r = t.nodeName.toLowerCase(),
								i = c[r],
								l = t.firstChild,
								o = !0;
							if (
								("object" == typeof a &&
									((o = -1 < a.indexOf(r)), v(t, "img") && g(t, y) && (o = !0), o || (i = a)),
								3 === n || 1 === n)
							)
								if (1 === n) {
									if (
										v(t, ".sceditor-nlf") &&
										(!l || (!b && 1 === t.childNodes.length && /br/i.test(l.nodeName)))
									)
										return;
									"iframe" !== r && (e = u(t, i)),
										o
											? ("code" !== r && (e = f(t, (e = h(t, (e = f(t, e)))), !0)),
											  (e = h(t, e, !0)),
											  (s += (function (t, e) {
													var n = t.nodeName.toLowerCase(),
														r = m.isInline;
													if (!r(t, !0) || "br" === n) {
														for (
															var i, l, o = t.previousSibling;
															o &&
															1 === o.nodeType &&
															!v(o, "br") &&
															r(o, !0) &&
															!o.firstChild;

														)
															o = o.previousSibling;
														for (
															;
															(i = ((l = t.parentNode) && l.lastChild) === t),
																(t = l) && i && r(l, !0);

														);
														(!i || "li" === n || ("br" === n && b)) && (e += "\n"),
															"br" !== n &&
																o &&
																!v(o, "br") &&
																r(o, !0) &&
																(e = "\n" + e);
													}
													return e;
											  })(t, e)))
											: (s += e);
								} else s += t.nodeValue;
						},
						!1,
						!0,
					),
					s
				);
			};
			return u(t);
		}
		function t(t, e, n) {
			var r,
				i,
				l,
				o,
				a,
				s = new q(u.opts.parserOptions).toHTML(u.opts.bbcodeTrim ? e.trim() : e);
			return t || n
				? ((r = s),
				  (a = document.createElement("div")),
				  (o = function (t, e) {
						if (!m.hasStyling(t)) {
							if (b || 1 !== t.childNodes.length || !v(t.firstChild, "br"))
								for (; (l = t.firstChild); ) a.insertBefore(l, t);
							if (e) {
								var n = a.lastChild;
								t !== n &&
									v(n, "div") &&
									t.nextSibling === n &&
									a.insertBefore(document.createElement("br"), t);
							}
							a.removeChild(t);
						}
				  }),
				  p(a, "display", "none"),
				  (a.innerHTML = r.replace(/<\/div>\n/g, "</div>")),
				  (i = a.firstChild) && v(i, "div") && o(i, !0),
				  (i = a.lastChild) && v(i, "div") && o(i),
				  a.innerHTML)
				: s;
		}
		function e(t, e, n, r) {
			var i,
				l,
				o = (n = n || document).createElement("div"),
				a = n.createElement("div"),
				s = new q(u.opts.parserOptions);
			for (
				a.innerHTML = e,
					p(o, "visibility", "hidden"),
					o.appendChild(a),
					n.body.appendChild(o),
					t && (o.insertBefore(n.createTextNode("#"), o.firstChild), o.appendChild(n.createTextNode("#"))),
					r && p(a, "whiteSpace", p(r, "whiteSpace")),
					l = a.getElementsByClassName("sceditor-ignore");
				l.length;

			)
				l[0].parentNode.removeChild(l[0]);
			return (
				m.removeWhiteSpace(o),
				(i = d(a)),
				n.body.removeChild(o),
				(i = s.toBBCode(i, !0)),
				u.opts.bbcodeTrim && (i = i.trim()),
				i
			);
		}
		(u.init = function () {
			(u.opts = this.opts),
				(u.elementToBbcode = d),
				s(k, function (n) {
					var r,
						t = k[n].tags,
						e = k[n].styles;
					t &&
						s(t, function (t, e) {
							(r = !1 === k[n].isInline),
								(o[t] = o[t] || {}),
								(o[t][r] = o[t][r] || {}),
								(o[t][r][n] = e);
						}),
						e &&
							s(e, function (t, e) {
								(r = !1 === k[n].isInline),
									(a[r] = a[r] || {}),
									(a[r][t] = a[r][t] || {}),
									(a[r][t][n] = e);
							});
				}),
				(this.commands = n(!0, {}, i, this.commands)),
				(this.toBBCode = u.toSource),
				(this.fromBBCode = u.toHtml);
		}),
			(u.toHtml = t.bind(null, !1)),
			(u.fragmentToHtml = t.bind(null, !0)),
			(u.toSource = e.bind(null, !1)),
			(u.fragmentToSource = e.bind(null, !0));
	}
	(u.prototype = {
		clone: function () {
			var t = this;
			return new u(t.type, t.name, t.val, n({}, t.attrs), [], t.closing ? t.closing.clone() : null);
		},
		splitAt: function (t) {
			var e,
				n = this.clone(),
				r = this.children.indexOf(t);
			return -1 < r && ((e = this.children.length - r), (n.children = this.children.splice(r, e))), n;
		},
	}),
		(q.QuoteType = x),
		(q.defaults = {
			breakBeforeBlock: !1,
			breakStartBlock: !1,
			breakEndBlock: !1,
			breakAfterBlock: !0,
			removeEmptyTags: !0,
			fixInvalidNesting: !0,
			fixInvalidChildren: !0,
			quoteType: x.auto,
		}),
		(f.get = function (t) {
			return k[t] || null;
		}),
		(f.set = function (t, e) {
			return (
				t &&
					e &&
					(((e = n(k[t] || {}, e)).remove = function () {
						delete k[t];
					}),
					(k[t] = e)),
				this
			);
		}),
		(f.rename = function (t, e) {
			return t in k && ((k[e] = k[t]), delete k[t]), this;
		}),
		(f.remove = function (t) {
			return t in k && delete k[t], this;
		}),
		(f.formatBBCodeString = w),
		(t.formats.bbcode = f),
		(t.BBCodeParser = q);
})(sceditor);
